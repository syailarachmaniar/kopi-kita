<?php
/**
 * Koneksi database (PDO). Sesuaikan untuk XAMPP: user root, password kosong.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $host = getenv('KK_DB_HOST') ?: 'localhost';
    $name = getenv('KK_DB_NAME') ?: 'kopikita_db';
    $user = getenv('KK_DB_USER') ?: 'root';
    $pass = getenv('KK_DB_PASS') !== false ? getenv('KK_DB_PASS') : '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . date('P') . "'");
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h2 style="font-family:sans-serif">Koneksi database gagal.</h2><p style="font-family:sans-serif">Pastikan MySQL di XAMPP aktif dan database <b>kopikita_db</b> sudah diimpor (database/kopikita_db.sql).</p>');
    }
    return $pdo;
}
