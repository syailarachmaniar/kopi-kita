<?php
require_once __DIR__ . '/../includes/cashier-header.php';
$pdo = db();
$c = $pdo->query("SELECT SUM(order_status='pending') pending, SUM(order_status='confirmed') confirmed, SUM(order_status='preparing') preparing, SUM(order_status='ready') ready FROM orders")->fetch();
$unpaid = (int)$pdo->query("SELECT COUNT(*) FROM orders o JOIN sales s ON s.order_id = o.id WHERE s.payment_status = 'pending' AND o.order_status <> 'cancelled'")->fetchColumn();
$today = $pdo->query("SELECT COALESCE(SUM(o.total_amount),0) t, COUNT(*) n FROM orders o JOIN sales s ON s.order_id = o.id WHERE o.order_status='completed' AND s.payment_status='paid' AND DATE(o.ordered_at) = CURDATE()")->fetch();
$active = $pdo->query("SELECT o.*, c.name customer_name, s.payment_status FROM orders o JOIN customers c ON c.id = o.customer_id LEFT JOIN sales s ON s.order_id = o.id WHERE o.order_status IN ('pending','confirmed','preparing','ready') ORDER BY o.ordered_at LIMIT 10")->fetchAll();
cashier_start('Dashboard Kasir', 'dashboard', '<meta http-equiv="refresh" content="60">');
?>
<div class="page-actions"><span class="muted">Halo, <?= e(current_user()['name']) ?>. Halaman ini diperbarui otomatis tiap 60 detik.</span><a class="btn btn-primary" href="pos.php">+ Transaksi Baru (POS)</a></div>
<div class="grid g-4">
  <div class="card stat-card"><div class="stat-ico"><?= ico('clock') ?></div><div><span>Pesanan Pending</span><b><?= (int)$c['pending'] ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('pos') ?></div><div><span>Menunggu Pembayaran</span><b><?= $unpaid ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('bag') ?></div><div><span>Preparing / Ready</span><b><?= (int)$c['preparing'] ?> / <?= (int)$c['ready'] ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('chart') ?></div><div><span>Omzet Hari Ini (<?= (int)$today['n'] ?> trx)</span><b><?= rupiah($today['t']) ?></b></div></div>
</div>
<div class="card mt"><h2>Pesanan Perlu Ditangani <a class="btn btn-sm" href="orders/index.php">Semua</a></h2><div class="table-wrap"><table>
<thead><tr><th>Kode</th><th>Pelanggan</th><th>Jenis</th><th>Status</th><th>Bayar</th><th class="num">Total</th><th></th></tr></thead><tbody>
<?php foreach ($active as $o): ?><tr><td><b><?= e($o['order_code']) ?></b><div class="small muted"><?= fmt_date($o['ordered_at']) ?></div></td><td><?= e($o['customer_name']) ?></td><td><?= $o['order_type'] === 'dine_in' ? 'Dine-in' . ($o['table_number'] ? ' #' . e($o['table_number']) : '') : 'Takeaway' ?></td><td><?= status_badge($o['order_status']) ?></td><td><?= pay_badge($o['payment_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td><td class="actions"><a class="btn btn-sm btn-primary" href="orders/show.php?id=<?= (int)$o['id'] ?>">Proses</a></td></tr><?php endforeach; ?>
<?php if (!$active): ?><tr><td colspan="7" class="empty">Tidak ada pesanan aktif. ☕</td></tr><?php endif; ?></tbody></table></div></div>
<?php cashier_end(); ?>
