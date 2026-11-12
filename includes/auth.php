<?php
require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return isset($_SESSION['user']); }
function user_role(): ?string { return $_SESSION['user']['role'] ?? null; }

function home_for_role(?string $role): string
{
    return ['admin' => 'admin/index.php', 'cashier' => 'cashier/index.php', 'customer' => 'customer/index.php'][$role] ?? 'index.php';
}

/** Cari akun dengan username ATAU email lalu verifikasi password. */
function attempt_login(string $identifier, string $password): ?string
{
    $st = db()->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
    $st->execute([trim($identifier), trim($identifier)]);
    $u = $st->fetch();
    if (!$u || !password_verify($password, $u['password'])) return 'Username/email atau password salah.';
    if (!$u['is_active']) return 'Akun Anda nonaktif. Hubungi admin KopiKita.';
    session_regenerate_id(true);
    $cust = null;
    if ($u['role'] === 'customer') {
        $c = db()->prepare('SELECT id FROM customers WHERE user_id = ?');
        $c->execute([$u['id']]);
        $cust = $c->fetchColumn() ?: null;
    }
    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'username' => $u['username'], 'email' => $u['email'], 'role' => $u['role'], 'customer_id' => $cust ? (int)$cust : null];
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$u['id']]);
    return null;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('info', 'Silakan login terlebih dahulu.');
        redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
    }
}
/** Pastikan role pengguna termasuk dalam daftar. Selain itu tampilkan 403. */
function require_role(array $roles): void
{
    require_login();
    // verifikasi ulang akun masih aktif dan role masih sama
    $st = db()->prepare('SELECT role, is_active FROM users WHERE id = ?');
    $st->execute([$_SESSION['user']['id']]);
    $u = $st->fetch();
    if (!$u || !$u['is_active']) { logout_user(); session_start(); flash('error', 'Akun tidak aktif.'); redirect('login.php'); }
    $_SESSION['user']['role'] = $u['role'];
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        include APP_ROOT . '/403.php';
        exit;
    }
}
/** Pelanggan saat ini (baris customers) */
function current_customer_id(): ?int { return $_SESSION['user']['customer_id'] ?? null; }
