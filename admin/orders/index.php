<?php
require_once __DIR__ . '/../../includes/admin-header.php';
$status = $_GET['status'] ?? ''; $channel = $_GET['channel'] ?? ''; $q = trim((string)($_GET['q'] ?? ''));
$from = $_GET['from'] ?? ''; $to = $_GET['to'] ?? '';
$w = ['1=1']; $a = [];
if (isset(ORDER_STATUSES[$status])) { $w[] = 'o.order_status = ?'; $a[] = $status; }
if (in_array($channel, ['online', 'cashier'], true)) { $w[] = 'o.order_channel = ?'; $a[] = $channel; }
if ($q !== '') { $w[] = '(o.order_code LIKE ? OR c.name LIKE ?)'; $a[] = "%$q%"; $a[] = "%$q%"; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $w[] = 'DATE(o.ordered_at) >= ?'; $a[] = $from; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) { $w[] = 'DATE(o.ordered_at) <= ?'; $a[] = $to; }
$where = implode(' AND ', $w); $per = 20; $page = current_page();
$cnt = db()->prepare("SELECT COUNT(*) FROM orders o JOIN customers c ON c.id = o.customer_id WHERE $where"); $cnt->execute($a); $total = (int)$cnt->fetchColumn();
$st = db()->prepare("SELECT o.*, c.name customer_name, s.payment_status FROM orders o JOIN customers c ON c.id = o.customer_id LEFT JOIN sales s ON s.order_id = o.id WHERE $where ORDER BY o.ordered_at DESC, o.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($a); $rows = $st->fetchAll();
admin_start('Kelola Pesanan', 'orders');
?>
<form class="card filter" method="get" style="margin-bottom:18px">
  <div class="fg"><label>Cari</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Kode / pelanggan"></div>
  <div class="fg"><label>Status</label><select class="input" name="status"><option value="">Semua</option><?php foreach (ORDER_STATUSES as $k => [$lab]): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($lab) ?></option><?php endforeach; ?></select></div>
  <div class="fg"><label>Channel</label><select class="input" name="channel"><option value="">Semua</option><option value="online" <?= $channel === 'online' ? 'selected' : '' ?>>Online</option><option value="cashier" <?= $channel === 'cashier' ? 'selected' : '' ?>>Kasir</option></select></div>
  <div class="fg"><label>Dari</label><input class="input" type="date" name="from" value="<?= e($from) ?>"></div>
  <div class="fg"><label>Sampai</label><input class="input" type="date" name="to" value="<?= e($to) ?>"></div>
  <button class="btn btn-dark">Filter</button><a class="btn" href="index.php">Reset</a>
</form>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Channel</th><th>Status</th><th>Bayar</th><th class="num">Total</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $o): ?><tr><td><b><?= e($o['order_code']) ?></b></td><td><?= fmt_date($o['ordered_at']) ?></td><td><?= e($o['customer_name']) ?></td><td><?= $o['order_channel'] === 'cashier' ? 'Kasir' : 'Online' ?></td><td><?= status_badge($o['order_status']) ?></td><td><?= pay_badge($o['payment_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td><td class="actions"><a class="btn btn-sm" href="show.php?id=<?= (int)$o['id'] ?>">Detail</a></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada pesanan.</td></tr><?php endif; ?></tbody></table></div>
  <?= pager($total, $per, $page, array_filter(['q' => $q, 'status' => $status, 'channel' => $channel, 'from' => $from, 'to' => $to])) ?></div>
<?php admin_end(); ?>
