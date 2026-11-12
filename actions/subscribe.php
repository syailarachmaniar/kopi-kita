<?php
require_once __DIR__ . '/../includes/auth.php';
if (!is_post()) redirect('index.php');
csrf_check();
$email = strtolower(trim((string)($_POST['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    flash('error', 'Format email tidak valid.');
} else {
    $st = db()->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1');
    $st->execute([$email]);
    flash('success', 'Terima kasih! Email Anda sudah terdaftar di newsletter KopiKita.');
}
redirect(safe_back('index.php'));
