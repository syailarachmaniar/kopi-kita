<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['cashier', 'admin']);
$o = get_order((int)($_GET['id'] ?? 0));
if (!$o) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
$items = get_order_items((int)$o['id']);
$paid = $o['payment_status'] === 'paid';
$line = fn($l, $r) => '<div class="r"><span>' . $l . '</span><span>' . $r . '</span></div>';
?><!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Struk <?= e($o['order_code']) ?></title><link rel="stylesheet" href="<?= asset('css/print.css') ?>"></head><body>
<div class="noprint"><button onclick="window.print()">Cetak Struk</button><a href="<?= url(user_role() === 'admin' ? 'admin/orders/show.php?id=' . (int)$o['id'] : 'cashier/orders/show.php?id=' . (int)$o['id']) ?>">Detail Pesanan</a><a href="<?= url('cashier/pos.php') ?>">Transaksi Baru</a></div>
<div class="receipt">
  <h1>KopiKita</h1><div class="c"><?= e(SHOP_ADDRESS) ?><br>Telp <?= e(SHOP_PHONE) ?></div><hr>
  <?= $line('Invoice', e($o['invoice_number'] ?? '-')) ?><?= $line('Pesanan', e($o['order_code'])) ?><?= $line('Tanggal', date('d/m/Y H:i', strtotime($o['paid_at'] ?: $o['ordered_at']))) ?>
  <?= $line('Kasir', e($o['cashier_name'] ?: '-')) ?><?= $line('Pelanggan', e($o['customer_name'])) ?>
  <?= $line('Jenis', $o['order_type'] === 'dine_in' ? 'Dine-in' . ($o['table_number'] ? ' #' . e($o['table_number']) : '') : 'Takeaway') ?><hr>
  <?php foreach ($items as $it): ?><div class="it"><?= e($it['product_name']) ?><?= $it['size'] ? ' (' . e(SIZE_LABELS[$it['size']]) . ')' : '' ?><?= $line((int)$it['quantity'] . ' x ' . number_format($it['unit_price'], 0, ',', '.'), number_format($it['line_total'], 0, ',', '.')) ?><?= $it['item_notes'] ? '<div class="nt">* ' . e($it['item_notes']) . '</div>' : '' ?></div><?php endforeach; ?><hr>
  <?= $line('Subtotal', rupiah($o['subtotal'])) ?><?= $line('Diskon', '-' . rupiah($o['discount_amount'])) ?><div class="r"><b>TOTAL</b><b><?= rupiah($o['total_amount']) ?></b></div>
  <?php if ($paid): ?><?= $line('Bayar (' . e(strtoupper($o['payment_method'])) . ')', rupiah($o['amount_paid'])) ?><?= $line('Kembali', rupiah($o['change_amount'])) ?><hr><div class="c"><b>LUNAS</b></div>
  <?php else: ?><hr><div class="c"><b>BELUM LUNAS</b></div><?php endif; ?>
  <hr><div class="c">Terima kasih!<br><?= e(APP_SLOGAN) ?></div>
</div>
<?php if (!empty($_GET['print'])): ?><script>window.onload=function(){window.print()}</script><?php endif; ?>
</body></html>
