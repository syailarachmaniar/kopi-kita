<?php
require_once __DIR__ . '/../../includes/admin-header.php';
$me = (int)current_user()['id'];
if (is_post()) {
    csrf_check(); $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if ($act === 'toggle') {
        if ($id === $me) flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        else { db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]); flash('success', 'Status akun diubah.'); }
    } elseif ($act === 'reset') {
        $pw = (string)($_POST['password'] ?? '');
        if (strlen($pw) < 6) flash('error', 'Password baru minimal 6 karakter.');
        else { db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($pw, PASSWORD_DEFAULT), $id]); flash('success', 'Password direset.'); }
    } elseif ($act === 'add') {
        $name = trim((string)$_POST['name']); $un = strtolower(trim((string)$_POST['username'])); $em = trim((string)$_POST['email']); $role = $_POST['role'] ?? ''; $pw = (string)$_POST['password'];
        $err = '';
        if ($name === '' || !preg_match('/^[a-z0-9_]{3,30}$/', $un) || !filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6 || !in_array($role, ['admin', 'cashier', 'customer'], true)) $err = 'Data tidak valid (username 3–30 huruf kecil/angka/_, email valid, password ≥ 6).';
        else {
            $c = db()->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?'); $c->execute([$un, $em]);
            if ($c->fetchColumn()) $err = 'Username atau email sudah dipakai.';
        }
        if ($err) flash('error', $err);
        else {
            $pdo = db(); $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO users (name, username, email, password, role) VALUES (?,?,?,?,?)')->execute([$name, $un, $em, password_hash($pw, PASSWORD_DEFAULT), $role]);
            if ($role === 'customer') $pdo->prepare('INSERT INTO customers (user_id, name, email) VALUES (?,?,?)')->execute([(int)$pdo->lastInsertId(), $name, $em]);
            $pdo->commit(); flash('success', 'Akun ditambahkan.');
        }
    }
    redirect('admin/users/index.php');
}
$role = $_GET['role'] ?? '';
$st = db()->prepare('SELECT * FROM users ' . (in_array($role, ['admin', 'cashier', 'customer'], true) ? 'WHERE role = ?' : 'WHERE ? = ?') . ' ORDER BY role, name');
$st->execute(in_array($role, ['admin', 'cashier', 'customer'], true) ? [$role] : [1, 1]); $rows = $st->fetchAll();
$rl = ['admin' => 'Admin', 'cashier' => 'Kasir', 'customer' => 'Pelanggan'];
admin_start('Kelola Pengguna', 'users');
?>
<div class="tabs"><a href="?" class="<?= $role === '' ? 'active' : '' ?>">Semua</a><?php foreach ($rl as $k => $l): ?><a href="?role=<?= $k ?>" class="<?= $role === $k ? 'active' : '' ?>"><?= $l ?></a><?php endforeach; ?></div>
<details class="card" style="margin-bottom:18px"><summary style="cursor:pointer;font-weight:700">+ Tambah akun baru</summary>
<form method="post" class="filter" style="margin-top:14px"><?= csrf_field() ?><input type="hidden" name="action" value="add">
  <div class="fg"><label>Nama</label><input class="input" name="name" required></div><div class="fg"><label>Username</label><input class="input" name="username" required pattern="[a-z0-9_]{3,30}"></div>
  <div class="fg"><label>Email</label><input class="input" type="email" name="email" required></div><div class="fg"><label>Password</label><input class="input" type="password" name="password" minlength="6" required></div>
  <div class="fg"><label>Role</label><select class="input" name="role"><?php foreach ($rl as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?></select></div><button class="btn btn-primary">Simpan</button></form></details>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Login terakhir</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $u): ?><tr><td><b><?= e($u['name']) ?></b><?= (int)$u['id'] === $me ? ' <span class="badge">Anda</span>' : '' ?></td><td><?= e($u['username']) ?></td><td><?= e($u['email']) ?></td><td><?= e($rl[$u['role']]) ?></td><td><?= fmt_date($u['last_login_at']) ?></td><td><span class="badge <?= $u['is_active'] ? 'on' : 'off' ?>"><?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
<td class="actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-sm" <?= (int)$u['id'] === $me ? 'disabled' : '' ?>><?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
<form method="post" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input class="input" style="width:120px" type="password" name="password" minlength="6" placeholder="Password baru" required><button class="btn btn-sm">Reset</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php admin_end(); ?>
