<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
$uid = current_user()['id']; $cid = current_customer_id();
$st = db()->prepare('SELECT u.name, u.username, u.email, c.phone, c.address FROM users u LEFT JOIN customers c ON c.user_id = u.id WHERE u.id = ?');
$st->execute([$uid]); $d = $st->fetch();
$err = [];
if (is_post()) {
    csrf_check();
    $name = trim((string)($_POST['name'] ?? '')); $phone = trim((string)($_POST['phone'] ?? '')); $addr = trim((string)($_POST['address'] ?? ''));
    $cur = (string)($_POST['current_password'] ?? ''); $new = (string)($_POST['new_password'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) $err[] = 'Nama wajib diisi (maks. 100 karakter).';
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) $err[] = 'Nomor telepon tidak valid.';
    if ($new !== '') {
        $h = db()->prepare('SELECT password FROM users WHERE id = ?'); $h->execute([$uid]);
        if (!password_verify($cur, (string)$h->fetchColumn())) $err[] = 'Password saat ini salah.';
        if (strlen($new) < 6) $err[] = 'Password baru minimal 6 karakter.';
    }
    if (!$err) {
        $pdo = db(); $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $uid]);
        $pdo->prepare('UPDATE customers SET name = ?, phone = ?, address = ? WHERE user_id = ?')->execute([$name, $phone ?: null, $addr ?: null, $uid]);
        if ($new !== '') $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        $pdo->commit();
        $_SESSION['user']['name'] = $name;
        flash('success', 'Profil diperbarui.');
        redirect('customer/profile.php');
    }
    $d['name'] = $name; $d['phone'] = $phone; $d['address'] = $addr;
}
$pageTitle = 'Profil Saya'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:720px;padding-bottom:4rem">
  <div class="page-head"><h1>Profil <em>Saya</em></h1></div>
  <?php foreach ($err as $m): ?><div class="alert alert-error"><?= e($m) ?></div><?php endforeach; ?>
  <form method="post" class="panel-box" style="padding:2rem" data-validate>
    <?= csrf_field() ?>
    <div class="row-2"><div class="form-group"><label>Username</label><input class="input" value="<?= e($d['username']) ?>" disabled></div><div class="form-group"><label>Email</label><input class="input" value="<?= e($d['email']) ?>" disabled></div></div>
    <div class="row-2"><div class="form-group"><label for="name">Nama</label><input class="input" id="name" name="name" value="<?= e($d['name']) ?>" required maxlength="100"></div><div class="form-group"><label for="phone">Telepon</label><input class="input" id="phone" name="phone" data-rule="phone" value="<?= e($d['phone']) ?>" maxlength="20"></div></div>
    <div class="form-group"><label for="address">Alamat</label><textarea class="input" id="address" name="address"><?= e($d['address']) ?></textarea></div>
    <hr style="border:0;border-top:1px solid rgba(243,230,211,.1);margin:1.2rem 0"><p class="small">Ganti password (kosongkan jika tidak ingin mengubah)</p>
    <div class="row-2"><div class="form-group"><label for="cp">Password saat ini</label><input class="input" id="cp" type="password" name="current_password" autocomplete="current-password"></div><div class="form-group"><label for="np">Password baru</label><input class="input" id="np" type="password" name="new_password" autocomplete="new-password"></div></div>
    <button class="btn btn-pill btn-primary" type="submit">Simpan Perubahan</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
