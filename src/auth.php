<?php
function current_user(): ?array
{
    static $u = false;
    if ($u === false) {
        $u = isset($_SESSION['uid'])
            ? q_one('SELECT id, username, full_name, email, role FROM users WHERE id = ?', [$_SESSION['uid']])
            : null;
    }
    return $u;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('กรุณาเข้าสู่ระบบก่อน', 'warning');
        redirect('login?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) abort(403, 'เฉพาะผู้ดูแลระบบ (admin)');
}

function attempt_login(string $username, string $password): bool
{
    // หน่วงเวลาเล็กน้อยลดการเดารหัสผ่าน
    usleep(300000);
    $u = q_one('SELECT id, password_hash FROM users WHERE username = ?', [$username]);
    if (!$u || !password_verify($password, $u['password_hash'])) return false;
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    $_SESSION['last_seen'] = time();
    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
