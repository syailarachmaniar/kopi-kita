<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
require_once __DIR__ . '/_order_lookup.php';
$o = my_order_by_code((string)($_GET['code'] ?? ''));
if (is_post()) {
    csrf_check();
    if (($_POST['action'] ?? '') === 'cancel') {
        $err = $o['order_status'] === 'pending' ? change_order_status((int)$o['id'], 'cancelled', null, 'Dibatalkan oleh pelanggan') : 'Hanya pesanan berstatus Pending yang dapat dibatalkan. Hubungi kasir.';
        flash($err ? 'error' : 'success', $err ?: 'Pesanan dibatalkan.');
    }
    redirect('customer/order-detail.php?code=' . urlencode($o['order_code']));
}
$items = get_order_items($o['id']);
$pageTitle = 'Detail Pesanan'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
$flow = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
$idx = array_search($o['order_status'], $flow, true);
?>
<div class="container" style="padding-bottom:4rem">
  <div class="page-head"><div class="breadcrumb"><a href="<?= url('customer/orders.php') ?>">← Pesanan Saya</a></div><h1><?= e($o['order_code']) ?></h1></div>
  <div class="panel-box" style="padding:2rem">
    <?php if ($o['order_status'] === 'cancelled'): ?>
      <div class="alert alert-error">Pesanan dibatalkan. <?= e($o['cancel_reason'] ?? '') ?></div>
    <?php else: ?>
    <div class="timeline">
      <?php foreach ($flow as $i => $s): $cls = $i < $idx ? 'done' : ($i === $idx ? 'done now' : ''); ?>
        <div class="tl-step <?= $cls ?>"><i><?= $i < $idx ? '✓' : $i + 1 ?></i><?= e(ORDER_STATUSES[$s][0]) ?><br><small style="font-size:.62rem"><?= e(ORDER_STATUSES[$s][1]) ?></small></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="row-2" style="margin:1.2rem 0">
      <div><p><b>Tanggal:</b> <?= fmt_date($o['ordered_at']) ?><br><b>Jenis:</b> <?= $o['order_type'] === 'dine_in' ? 'Dine-in' . ($o['table_number'] ? ' (meja ' . e($o['table_number']) . ')' : '') : 'Takeaway' ?><br><b>Catatan:</b> <?= e($o['notes'] ?: '-') ?></p></div>
      <div><p><b>Pembayaran:</b> <?= e(PAYMENT_METHODS[$o['payment_method']] ?? '-') ?> <?= pay_badge($o['payment_status']) ?><br><b>Invoice:</b> <?= e($o['invoice_number'] ?? '-') ?><br><b>Dibayar pada:</b> <?= fmt_date($o['paid_at']) ?></p></div>
    </div>
    <div class="table-wrap"><table class="tbl">
      <thead><tr><th>Item</th><th class="num">Harga</th><th class="num">Jml</th><th class="num">Total</th></tr></thead><tbody>
      <?php foreach ($items as $it): ?><tr><td><?= e($it['product_name']) ?><?= $it['size'] ? ' <small>(' . e(SIZE_LABELS[$it['size']]) . ')</small>' : '' ?><?= $it['item_notes'] ? '<br><small>' . e($it['item_notes']) . '</small>' : '' ?></td><td class="num"><?= rupiah($it['unit_price']) ?></td><td class="num"><?= (int)$it['quantity'] ?></td><td class="num"><?= rupiah($it['line_total']) ?></td></tr><?php endforeach; ?>
      <tr><td colspan="3" class="num">Subtotal</td><td class="num"><?= rupiah($o['subtotal']) ?></td></tr>
      <tr><td colspan="3" class="num">Diskon</td><td class="num">-<?= rupiah($o['discount_amount']) ?></td></tr>
      <tr><td colspan="3" class="num"><b>Total</b></td><td class="num"><b><?= rupiah($o['total_amount']) ?></b></td></tr></tbody></table></div>
    <?php if ($o['order_status'] === 'pending'): ?>
    <form method="post" data-confirm="Batalkan pesanan ini?" style="margin-top:1.2rem"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><button class="btn btn-pill btn-danger" type="submit">Batalkan Pesanan</button></form>
    <?php endif; ?>
  </div>
</div>
<?php if (in_array($o['order_status'], ['pending', 'confirmed', 'preparing', 'ready'], true)): ?><script>setTimeout(function(){location.reload()},30000);</script><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
