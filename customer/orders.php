<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
$st = db()->prepare('SELECT o.*, s.payment_status FROM orders o LEFT JOIN sales s ON s.order_id = o.id WHERE o.customer_id = ? ORDER BY o.ordered_at DESC, o.id DESC');
$st->execute([current_customer_id()]);
$orders = $st->fetchAll();
$pageTitle = 'Pesanan Saya'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-bottom:4rem">
  <div class="page-head"><h1>Riwayat <em>Pesanan</em></h1></div>
  <?php if (!$orders): ?><div class="panel-box empty"><p>Belum ada pesanan.</p><a class="btn btn-pill btn-primary" href="<?= url('menu.php') ?>">Mulai Pesan</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Kode</th><th>Tanggal</th><th>Jenis</th><th>Status</th><th>Bayar</th><th class="num">Total</th><th></th></tr></thead>
    <tbody><?php foreach ($orders as $o): ?>
      <tr><td><b><?= e($o['order_code']) ?></b></td><td><?= fmt_date($o['ordered_at']) ?></td><td><?= $o['order_type'] === 'dine_in' ? 'Dine-in' : 'Takeaway' ?></td><td><?= status_badge($o['order_status']) ?></td><td><?= pay_badge($o['payment_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td>
      <td><a class="btn btn-pill btn-ghost btn-sm" href="<?= url('customer/order-detail.php?code=' . urlencode($o['order_code'])) ?>">Detail</a></td></tr>
    <?php endforeach; ?></tbody></table></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
