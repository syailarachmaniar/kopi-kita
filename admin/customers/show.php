<?php
require_once __DIR__ . '/../../includes/admin-header.php';
$st = db()->prepare('SELECT c.*, u.username, u.last_login_at FROM customers c LEFT JOIN users u ON u.id = c.user_id WHERE c.id = ?'); $st->execute([(int)($_GET['id'] ?? 0)]); $c = $st->fetch();
if (!$c) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
$o = db()->prepare('SELECT o.*, s.payment_status FROM orders o LEFT JOIN sales s ON s.order_id = o.id WHERE o.customer_id = ? ORDER BY o.ordered_at DESC'); $o->execute([$c['id']]); $orders = $o->fetchAll();
admin_start('Detail Pelanggan', 'customers');
?>
<div class="page-actions"><a class="btn" href="index.php">← Kembali</a></div>
<div class="detail-grid">
  <div class="card"><h2>Riwayat Pesanan</h2><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Tanggal</th><th>Status</th><th>Bayar</th><th class="num">Total</th><th></th></tr></thead><tbody>
    <?php foreach ($orders as $r): ?><tr><td><b><?= e($r['order_code']) ?></b></td><td><?= fmt_date($r['ordered_at']) ?></td><td><?= status_badge($r['order_status']) ?></td><td><?= pay_badge($r['payment_status']) ?></td><td class="num"><?= rupiah($r['total_amount']) ?></td><td><a class="btn btn-sm" href="../orders/show.php?id=<?= (int)$r['id'] ?>">Detail</a></td></tr><?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="6" class="empty">Belum ada pesanan.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="card"><h2>Profil</h2><dl class="kv"><dt>Nama</dt><dd><?= e($c['name']) ?></dd><dt>Username</dt><dd><?= e($c['username'] ?: '-') ?></dd><dt>Email</dt><dd><?= e($c['email'] ?: '-') ?></dd><dt>Telepon</dt><dd><?= e($c['phone'] ?: '-') ?></dd><dt>Alamat</dt><dd><?= e($c['address'] ?: '-') ?></dd><dt>Bergabung</dt><dd><?= fmt_date($c['created_at']) ?></dd><dt>Login terakhir</dt><dd><?= fmt_date($c['last_login_at']) ?></dd></dl></div>
</div>
<?php admin_end(); ?>
