<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
require_once __DIR__ . '/_order_lookup.php';
$o = my_order_by_code((string)($_GET['code'] ?? ''));
$items = get_order_items($o['id']);
$pageTitle = 'Pesanan Berhasil'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:720px">
  <div class="panel-box" style="margin:3rem 0;text-align:center">
    <div style="font-size:3rem">✅</div>
    <h1>Pesanan <em>berhasil dibuat!</em></h1>
    <p>Kode pesanan Anda</p>
    <div style="font-family:var(--f-serif);font-size:2rem;color:var(--orange);margin-bottom:1rem"><?= e($o['order_code']) ?></div>
    <div class="table-wrap" style="text-align:left"><table class="tbl">
      <?php foreach ($items as $it): ?><tr><td><?= (int)$it['quantity'] ?>× <?= e($it['product_name']) ?><?= $it['size'] ? ' (' . e(SIZE_LABELS[$it['size']]) . ')' : '' ?><?= $it['item_notes'] ? '<br><small>' . e($it['item_notes']) . '</small>' : '' ?></td><td class="num"><?= rupiah($it['line_total']) ?></td></tr><?php endforeach; ?>
      <tr><td>Diskon</td><td class="num">-<?= rupiah($o['discount_amount']) ?></td></tr>
      <tr><td><b>Total</b></td><td class="num"><b><?= rupiah($o['total_amount']) ?></b></td></tr>
    </table></div>
    <p style="margin-top:1.2rem"><?= status_badge($o['order_status']) ?> <?= pay_badge($o['payment_status']) ?></p>
    <div class="alert alert-info" style="text-align:left">
      <?php if ($o['payment_method'] === 'cash'): ?>Silakan menuju kasir dan sebutkan kode pesanan untuk membayar tunai.
      <?php elseif ($o['payment_method'] === 'qris'): ?>Pembayaran QRIS (simulasi): tunjukkan kode pesanan ke kasir; kasir akan mengonfirmasi pembayaran.
      <?php else: ?>Transfer Bank (simulasi): rekening contoh 123-456-7890 a.n. KopiKita. Tunjukkan bukti ke kasir untuk konfirmasi.<?php endif; ?>
    </div>
    <div style="display:flex;gap:.7rem;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-pill btn-primary" href="<?= url('customer/order-detail.php?code=' . urlencode($o['order_code'])) ?>">Pantau Status</a>
      <a class="btn btn-pill btn-ghost" href="<?= url('menu.php') ?>">Pesan Lagi</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
