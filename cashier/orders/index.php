<?php
require_once __DIR__ . '/../../includes/cashier-header.php';
$tab = $_GET['s'] ?? 'active';
$map = ['active' => "o.order_status IN ('pending','confirmed','preparing','ready')", 'unpaid' => "s.payment_status = 'pending' AND o.order_status <> 'cancelled'", 'all' => '1=1'];
foreach (['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'] as $k) $map[$k] = "o.order_status = '$k'";
$w = $map[$tab] ?? $map['active'];
$rows = db()->query("SELECT o.*, c.name customer_name, s.payment_status FROM orders o JOIN customers c ON c.id = o.customer_id LEFT JOIN sales s ON s.order_id = o.id WHERE $w ORDER BY o.ordered_at DESC, o.id DESC LIMIT 100")->fetchAll();
$tabs = ['active' => 'Aktif', 'unpaid' => 'Belum Bayar', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'all' => 'Semua'];
cashier_start('Pesanan Masuk', 'orders', '<meta http-equiv="refresh" content="30">');
?>
<div class="tabs"><?php foreach ($tabs as $k => $l): ?><a class="<?= $tab === $k ? 'active' : '' ?>" href="?s=<?= $k ?>"><?= e($l) ?></a><?php endforeach; ?></div>
<div class="auto-refresh">Diperbarui otomatis tiap 30 detik · <?= count($rows) ?> pesanan</div>
<div class="card mt"><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Waktu</th><th>Pelanggan</th><th>Jenis</th><th>Status</th><th>Bayar</th><th class="num">Total</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $o): ?><tr><td><b><?= e($o['order_code']) ?></b></td><td><?= fmt_date($o['ordered_at']) ?></td><td><?= e($o['customer_name']) ?></td><td><?= $o['order_type'] === 'dine_in' ? 'Dine-in' . ($o['table_number'] ? ' #' . e($o['table_number']) : '') : 'Takeaway' ?></td><td><?= status_badge($o['order_status']) ?></td><td><?= pay_badge($o['payment_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td>
<td class="actions"><?php if ($o['payment_status'] === 'pending' && $o['order_status'] !== 'cancelled'): ?><a class="btn btn-sm btn-success" href="../payments/confirm.php?id=<?= (int)$o['id'] ?>">Bayar</a><?php endif; ?><a class="btn btn-sm" href="show.php?id=<?= (int)$o['id'] ?>">Detail</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada pesanan.</td></tr><?php endif; ?></tbody></table></div></div>
<?php cashier_end(); ?>
