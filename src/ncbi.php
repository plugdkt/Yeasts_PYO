<?php
/**
 * NCBI E-utilities client
 * https://www.ncbi.nlm.nih.gov/books/NBK25497/
 * - ส่ง tool/email/api_key ทุก request
 * - จำกัดอัตรา 3 req/s (10 req/s ถ้ามี API key)
 */
const EUTILS = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';

function ncbi_get(string $endpoint, array $params): string
{
    $c = config('ncbi');
    $params['tool'] = $c['tool'];
    if ($c['email'])   $params['email'] = $c['email'];
    if ($c['api_key']) $params['api_key'] = $c['api_key'];

    // rate limit แบบง่ายภายใน process
    static $last = 0.0;
    $gap = $c['api_key'] ? 0.11 : 0.34;
    $wait = $last + $gap - microtime(true);
    if ($wait > 0) usleep((int) ($wait * 1e6));
    $last = microtime(true);

    $ch = curl_init(EUTILS . $endpoint . '?' . http_build_query($params));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $c['timeout'],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'PYO-YDB/1.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($body === false || $code >= 400) {
        throw new RuntimeException("NCBI request failed ($code) $err");
    }
    return $body;
}

/** ค้นหา TaxID จากชื่อวิทยาศาสตร์ */
function ncbi_taxid(string $name): ?int
{
    $name = trim(preg_replace('/\s+/', ' ', $name));
    if ($name === '') return null;
    // ชื่อวิทยาศาสตร์ตรงตัวก่อน แล้วค่อยค้นแบบรวม synonym/ชื่อเดิม
    foreach (["$name [Scientific Name]", "\"$name\"[All Names]"] as $term) {
        $json = json_decode(ncbi_get('esearch.fcgi', ['db' => 'taxonomy', 'term' => $term, 'retmode' => 'json']), true);
        $res = $json['esearchresult'] ?? [];
        // NCBI จะตัดคำที่หาไม่เจอทิ้งแล้วค้นต่อ (เช่น "Pichia anomala" → ได้แมลงสกุล Anomala) จึงต้องปฏิเสธผลแบบนั้น
        if (!empty($res['errorlist']['phrasesnotfound'])) continue;
        $ids = $res['idlist'] ?? [];
        if (count($ids) === 1) return (int) $ids[0];
    }
    return null;
}

/**
 * ดึง lineage ของ TaxID คืนค่า ['taxid','name','rank','lineage','ranks'=>[rank=>name]]
 */
function ncbi_taxonomy(int $taxid): ?array
{
    $xml = @simplexml_load_string(ncbi_get('efetch.fcgi', ['db' => 'taxonomy', 'id' => $taxid, 'retmode' => 'xml']));
    if (!$xml || !isset($xml->Taxon)) return null;
    $t = $xml->Taxon;
    $ranks = [];
    foreach ($t->LineageEx->Taxon ?? [] as $x) {
        $ranks[(string) $x->Rank] = (string) $x->ScientificName;
    }
    $ranks[(string) $t->Rank] = (string) $t->ScientificName;
    return [
        'taxid'   => (int) $t->TaxId,
        'name'    => (string) $t->ScientificName,
        'rank'    => (string) $t->Rank,
        'lineage' => (string) $t->Lineage,
        'ranks'   => $ranks,
    ];
}

/** แปลง lineage ของ NCBI เป็นคอลัมน์ในตาราง species */
function ncbi_taxonomy_to_species(array $tax): array
{
    $r = $tax['ranks'];
    return [
        'ncbi_taxid'   => $tax['taxid'],
        'kingdom'      => $r['kingdom'] ?? null,
        'subkingdom'   => $r['subkingdom'] ?? null,
        'phylum'       => $r['phylum'] ?? null,
        'subphylum'    => $r['subphylum'] ?? null,
        'class_name'   => $r['class'] ?? null,
        'order_name'   => $r['order'] ?? null,
        'family'       => $r['family'] ?? null,
        'genus'        => $r['genus'] ?? null,
        'ncbi_lineage' => $tax['lineage'],
    ];
}

/** ดึงลำดับ + title จาก GenBank accession */
function ncbi_sequence(string $accession): ?array
{
    $fasta = trim(ncbi_get('efetch.fcgi', [
        'db' => 'nuccore', 'id' => $accession, 'rettype' => 'fasta', 'retmode' => 'text',
    ]));
    if (!str_starts_with($fasta, '>')) return null;
    $lines = explode("\n", $fasta);
    $title = substr(array_shift($lines), 1);
    $seq = clean_sequence(implode('', $lines));
    return ['title' => $title, 'sequence' => $seq, 'length' => strlen($seq)];
}

/**
 * ค้นลำดับ D1/D2 (28S/26S LSU rRNA) ของ type material ใน GenBank
 * คืนรายการ ['accession','title','length','refseq'] เรียง RefSeq (NG_) ก่อน
 */
function ncbi_d1d2_type_candidates(string $species_name, int $limit = 5): array
{
    $term = "\"$species_name\"[Organism] AND (28S[Title] OR 26S[Title] OR \"large subunit\"[Title] OR D1/D2[Title]) AND sequence_from_type[Filter]";
    $json = json_decode(ncbi_get('esearch.fcgi', ['db' => 'nuccore', 'term' => $term, 'retmode' => 'json', 'retmax' => 30]), true);
    $ids = $json['esearchresult']['idlist'] ?? [];
    if (!$ids) return [];
    $sum = json_decode(ncbi_get('esummary.fcgi', ['db' => 'nuccore', 'id' => implode(',', $ids), 'retmode' => 'json']), true);
    $out = [];
    foreach ($ids as $id) {
        $r = $sum['result'][$id] ?? null;
        if (!$r) continue;
        $title = $r['title'];
        // ส่วนแรกของ title ต้องเป็น LSU — ตัดลำดับ ITS/SSU ที่มี 26S ต่อท้ายเพียงบางส่วนออก
        $first = strtolower(strtok($title, ';'));
        if (!preg_match('/28s|26s|large subunit|d1\/d2/', $first) || preg_match('/18s|small subunit|internal transcribed/', $first)) continue;
        // ต้องเป็น rRNA ไม่ใช่ mRNA ของโปรตีนที่บังเอิญชื่อ "large subunit"
        if (!preg_match('/rrna|ribosomal/', $first) || preg_match('/mrna/', $first)) continue;
        $out[] = ['accession' => $r['accessionversion'], 'title' => $title, 'length' => (int) $r['slen'],
            'refseq' => str_starts_with($r['accessionversion'], 'NG_')];
    }
    // RefSeq ก่อน แล้วตามด้วยลำดับที่ความยาวใกล้ช่วง D1/D2 (~550–650 bp ขึ้นไป)
    usort($out, fn($a, $b) => [$b['refseq'], $b['length'] >= 500] <=> [$a['refseq'], $a['length'] >= 500]);
    return array_slice($out, 0, $limit);
}

/** ลิงก์เปิด NCBI BLAST (blastn, core_nt) พร้อมใส่ลำดับไว้ให้ */
function blast_url(string $sequence): string
{
    return 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?' . http_build_query([
        'PROGRAM' => 'blastn', 'PAGE_TYPE' => 'BlastSearch', 'LINK_LOC' => 'blasthome',
        'QUERY' => $sequence,
    ]);
}

function ncbi_taxonomy_url(int $taxid): string
{
    return "https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=$taxid";
}

function genbank_url(string $acc): string
{
    return 'https://www.ncbi.nlm.nih.gov/nuccore/' . rawurlencode($acc);
}

function mycobank_url(string $no): string
{
    return 'https://www.mycobank.org/MB/' . rawurlencode($no);
}
