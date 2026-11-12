<?php
$pageTitle = 'Tidak Ditemukan'; $active = ''; $hideNewsletter = true;
if (!function_exists('e')) require_once __DIR__ . '/includes/auth.php';
http_response_code(404);
require __DIR__ . '/includes/header.php';
?>
<div class="container error-page"><div class="code">404</div><h1>Halaman <em>tidak ditemukan</em></h1><p>Halaman atau data yang Anda cari tidak ada.</p>
<a class="btn btn-pill btn-primary" href="<?= url('index.php') ?>">Ke Beranda</a></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
