<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect(home_for_role(user_role()));
$err = []; $v = ['name' => '', 'username' => '', 'email' => '', 'phone' => ''];
if (is_post()) {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    $v['username'] = strtolower($v['username']);
    $pw = (string)($_POST['password'] ?? ''); $pw2 = (string)($_POST['password2'] ?? '');
    if ($v['name'] === '' || mb_strlen($v['name']) > 100) $err['name'] = 'Nama wajib diisi (maks. 100 karakter).';
    if (!preg_match('/^[a-z0-9_]{3,30}$/', $v['username'])) $err['username'] = 'Username 3–30 karakter: huruf kecil, angka, atau _.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || strlen($v['email']) > 150) $err['email'] = 'Format email tidak valid.';
    if ($v['phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $v['phone'])) $err['phone'] = 'Nomor telepon tidak valid.';
    if (strlen($pw) < 6) $err['password'] = 'Password minimal 6 karakter.';
    elseif ($pw !== $pw2) $err['password2'] = 'Konfirmasi password tidak sama.';
    if (!$err) {
        $c = db()->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $c->execute([$v['username'], $v['email']]);
        foreach ($c->fetchAll() as $r) {
            if ($r['username'] === $v['username']) $err['username'] = 'Username sudah dipakai.';
            if (strcasecmp($r['email'], $v['email']) === 0) $err['email'] = 'Email sudah terdaftar.';
        }
    }
    if (!$err) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?,?,?,?, 'customer')")
                ->execute([$v['name'], $v['username'], $v['email'], password_hash($pw, PASSWORD_DEFAULT)]);
            $uid = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO customers (user_id, name, phone, email) VALUES (?,?,?,?)')->execute([$uid, $v['name'], $v['phone'] ?: null, $v['email']]);
            $pdo->commit();
            flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        } catch (Throwable $t) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err['general'] = 'Registrasi gagal. Username/email mungkin sudah dipakai.';
        }
    }
}
$pageTitle = 'Registrasi'; $active = ''; $hideNewsletter = true;
require __DIR__ . '/includes/header.php';
$fe = fn($k) => isset($err[$k]) ? '<div class="field-error">' . e($err[$k]) . '</div>' : '';
?>
<div class="auth">
  <div class="auth-art"><span class="hero-tag">Bergabung</span><h2>Satu akun,<br>banyak <em>cangkir</em> hangat.</h2><p>Daftar sebagai pelanggan untuk memesan online dan melacak status pesananmu.</p></div>
  <div class="auth-form">
    <h1>Registrasi Pelanggan</h1>
    <?php if (!empty($err['general'])): ?><div class="alert alert-error"><?= e($err['general']) ?></div><?php endif; ?>
    <form method="post" data-validate>
      <?= csrf_field() ?>
      <div class="form-group"><label for="name">Nama Lengkap</label><input class="input <?= isset($err['name']) ? 'invalid' : '' ?>" id="name" name="name" value="<?= e($v['name']) ?>" required maxlength="100"><?= $fe('name') ?></div>
      <div class="row-2">
        <div class="form-group"><label for="username">Username</label><input class="input <?= isset($err['username']) ? 'invalid' : '' ?>" id="username" name="username" data-rule="username" value="<?= e($v['username']) ?>" required maxlength="30"><?= $fe('username') ?></div>
        <div class="form-group"><label for="phone">No. Telepon</label><input class="input <?= isset($err['phone']) ? 'invalid' : '' ?>" id="phone" name="phone" data-rule="phone" value="<?= e($v['phone']) ?>" maxlength="20"><?= $fe('phone') ?></div>
      </div>
      <div class="form-group"><label for="email">Email</label><input class="input <?= isset($err['email']) ? 'invalid' : '' ?>" id="email" type="email" name="email" value="<?= e($v['email']) ?>" required maxlength="150"><?= $fe('email') ?></div>
      <div class="row-2">
        <div class="form-group"><label for="password">Password</label><input class="input <?= isset($err['password']) ? 'invalid' : '' ?>" id="password" type="password" name="password" data-rule="password" required><?= $fe('password') ?></div>
        <div class="form-group"><label for="password2">Ulangi Password</label><input class="input <?= isset($err['password2']) ? 'invalid' : '' ?>" id="password2" type="password" name="password2" data-match="password" required><?= $fe('password2') ?></div>
      </div>
      <button class="btn btn-pill btn-primary btn-block" type="submit">Daftar</button>
    </form>
    <div class="auth-alt">Sudah punya akun? <a href="<?= url('login.php') ?>">Login</a></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
