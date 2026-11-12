<?php
require_once __DIR__ . '/../../includes/cashier-header.php';
[$from, $to] = date_range();
$mine = user_role() === 'cashier'; $a = [$from, $to]; $w = '';
if ($mine) { $w = 'AND s.cashier_id = ?'; $a[] = current_user()['id']; }
$st = db()->prepare("SELECT o.id, o.order_code, o.order_status, o.total_amount, s.* , c.name customer_name FROM orders o JOIN sales s ON s.order_id = o.id JOIN customers c ON c.id = o.customer_id WHERE s.payment_status = 'paid' AND DATE(s.paid_at) BETWEEN ? AND ? $w ORDER BY s.paid_at DESC");
$st->execute($a); $rows = $st->fetchAll();
$sum = array_sum(array_map(fn($r) => $r['order_status'] === 'cancelled' ? 0 : (float)$r['amount_due'], $rows));
cashier_start('Riwayat Transaksi', 'transactions');
?>
<form class="page-actions filter" method="get"><div style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap"><div class="fg"><label>Dari</label><input class="input" type="date" name="from" value="<?= e($from) ?>"></div><div class="fg"><label>Sampai</label><input class="input" type="date" name="to" value="<?= e($to) ?>"></div><button class="btn btn-dark">Filter</button></div><b>Total: <?= rupiah($sum) ?> (<?= count($rows) ?> transaksi)</b></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Pesanan</th><th>Waktu bayar</th><th>Pelanggan</th><th>Metode</th><th class="num">Tagihan</th><th class="num">Diterima</th><th class="num">Kembali</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['invoice_number']) ?></td><td><?= e($r['order_code']) ?> <?= status_badge($r['order_status']) ?></td><td><?= fmt_date($r['paid_at']) ?></td><td><?= e($r['customer_name']) ?></td><td><?= e(PAYMENT_METHODS[$r['payment_method']] ?? '') ?></td><td class="num"><?= rupiah($r['amount_due']) ?></td><td class="num"><?= rupiah($r['amount_paid']) ?></td><td class="num"><?= rupiah($r['change_amount']) ?></td><td><a class="btn btn-sm" target="_blank" href="../receipt.php?id=<?= (int)$r['order_id'] ?>">Struk</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="9" class="empty">Tidak ada transaksi.</td></tr><?php endif; ?></tbody></table></div></div>
<?php cashier_end(); ?>
