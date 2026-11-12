<?php
$pageTitle = 'Akses Ditolak'; $active = ''; $hideNewsletter = true;
if (!function_exists('e')) require_once __DIR__ . '/includes/auth.php';
http_response_code(403);
require __DIR__ . '/includes/header.php';
?>
<div class="container error-page"><div class="code">403</div><h1>Akses <em>ditolak</em></h1><p>Anda tidak memiliki hak untuk membuka halaman ini.</p>
<a class="btn btn-pill btn-primary" href="<?= url(is_logged_in() ? home_for_role(user_role()) : 'index.php') ?>">Kembali</a></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
