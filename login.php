<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect(home_for_role(user_role()));

function safe_next(): ?string
{
    $n = $_POST['next'] ?? $_GET['next'] ?? '';
    if (!is_string($n) || $n === '' || $n[0] !== '/' || strpos($n, '//') === 0 || preg_match('/[\r\n\\\\]/', $n)) return null;
    if (BASE_URL !== '' && strpos($n, BASE_URL . '/') !== 0) return null;
    return $n;
}
$error = ''; $ident = '';
if (is_post()) {
    csrf_check();
    $ident = trim((string)($_POST['identifier'] ?? ''));
    $pw = (string)($_POST['password'] ?? '');
    if ($ident === '' || $pw === '') $error = 'Username/email dan password wajib diisi.';
    else {
        $error = attempt_login($ident, $pw) ?? '';
        if (!$error) {
            $role = user_role();
            $next = safe_next();
            flash('success', 'Selamat datang, ' . current_user()['name'] . '!');
            if ($role === 'customer' && $next && strpos($next, '/admin') === false && strpos($next, '/cashier') === false) redirect($next);
            redirect(home_for_role($role));
        }
    }
}
$pageTitle = 'Login'; $active = ''; $hideNewsletter = true;
require __DIR__ . '/includes/header.php';
?>
<div class="auth">
  <div class="auth-art"><span class="hero-tag">Selamat datang kembali</span><h2><?= e('A Little Coffee,') ?><br><em>A Lot</em> of Happiness.</h2><p>Masuk untuk memesan kopi favoritmu, melihat riwayat pesanan, dan mengelola kedai.</p></div>
  <div class="auth-form">
    <a class="brand" href="<?= url('index.php') ?>"><img src="<?= asset('images/ui/logo.svg') ?>" alt="" width="44" height="44"><span><b>KopiKita</b><small>Coffee Shop</small></span></a>
    <h1>Login</h1>
    <p>Gunakan username atau email Anda.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" data-validate>
      <?= csrf_field() ?><input type="hidden" name="next" value="<?= e(safe_next() ?? '') ?>">
      <div class="form-group"><label for="identifier">Username atau Email</label><input class="input" id="identifier" name="identifier" value="<?= e($ident) ?>" required autocomplete="username" autofocus></div>
      <div class="form-group"><label for="password">Password</label><div class="pw-wrap"><input class="input" id="password" type="password" name="password" required autocomplete="current-password"><button type="button" class="pw-toggle">Lihat</button></div></div>
      <button class="btn btn-pill btn-primary btn-block" type="submit">Login</button>
    </form>
    <div class="auth-alt">Belum punya akun? <a href="<?= url('register.php') ?>">Registrasi pelanggan</a></div>
    <div class="demo-box"><b>Akun demo:</b> admin / admin123 · kasir / kasir123 · pelanggan / pelanggan123</div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
