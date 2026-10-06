// แผนที่จุดเก็บตัวอย่าง (Leaflet + OpenStreetMap)
window.PYOMap = function (elId, points, opts) {
  opts = opts || {};
  const map = L.map(elId, { scrollWheelZoom: opts.scroll !== false }).setView([19.17, 100.05], 9);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // สีตาม genus
  const palette = ['#2f7d63', '#d9a441', '#3b6fb6', '#b5523b', '#7a4fa3', '#3a9aa5', '#8a8a2e', '#c2577f', '#4d4d4d', '#e07b39'];
  const genera = [...new Set(points.map(p => p.genus))].sort();
  const color = g => g === 'Unidentified' ? '#9aa5a1' : palette[genera.filter(x => x !== 'Unidentified').indexOf(g) % palette.length];
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const layer = opts.cluster === false ? L.layerGroup() : L.markerClusterGroup({ maxClusterRadius: 40 });
  points.forEach(p => {
    const m = L.circleMarker([p.lat, p.lng], { radius: 7, weight: 1.5, color: '#fff', fillColor: color(p.genus), fillOpacity: .95 });
    m.bindPopup(`<a href="${esc(p.url)}"><b>${esc(p.code)}</b></a><br><i>${esc(p.name)}</i><br>` +
      `<small>${esc(p.source || '')}${p.substrate ? ' – ' + esc(p.substrate) : ''}<br>อ.${esc(p.district || '-')}</small>`);
    layer.addLayer(m);
  });
  layer.addTo(map);
  if (points.length) map.fitBounds(L.latLngBounds(points.map(p => [p.lat, p.lng])).pad(0.2), { maxZoom: 12 });

  if (opts.legend !== false && genera.length) {
    const lg = L.control({ position: 'bottomright' });
    lg.onAdd = () => {
      const d = L.DomUtil.create('div', 'bg-white p-2 rounded shadow-sm small');
      d.innerHTML = genera.map(g => `<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${color(g)}"></span> <i>${esc(g)}</i></div>`).join('');
      return d;
    };
    lg.addTo(map);
  }
  return map;
};

// ตัวเลือกพิกัดในฟอร์ม: คลิกบนแผนที่เพื่อกรอก lat/lng
window.PYOPicker = function (elId, latInput, lngInput) {
  const lat = document.getElementById(latInput), lng = document.getElementById(lngInput);
  const has = lat.value && lng.value;
  const map = L.map(elId).setView(has ? [lat.value, lng.value] : [19.17, 100.05], has ? 13 : 9);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
  let marker = has ? L.marker([lat.value, lng.value], { draggable: true }).addTo(map) : null;
  const set = ll => {
    lat.value = ll.lat.toFixed(6); lng.value = ll.lng.toFixed(6);
    if (!marker) { marker = L.marker(ll, { draggable: true }).addTo(map); marker.on('dragend', e => set(e.target.getLatLng())); }
    else marker.setLatLng(ll);
  };
  if (marker) marker.on('dragend', e => set(e.target.getLatLng()));
  map.on('click', e => set(e.latlng));
  [lat, lng].forEach(i => i.addEventListener('change', () => {
    if (lat.value && lng.value) { const ll = L.latLng(+lat.value, +lng.value); set(ll); map.setView(ll, 13); }
  }));
  return map;
};
