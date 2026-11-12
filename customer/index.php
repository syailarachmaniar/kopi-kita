<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
require_once __DIR__ . '/../includes/components.php';
$cid = current_customer_id();
$st = db()->prepare("SELECT COUNT(*) total, SUM(order_status IN ('pending','confirmed','preparing','ready')) active FROM orders WHERE customer_id = ?");
$st->execute([$cid]); $sum = $st->fetch();
$st = db()->prepare("SELECT * FROM orders WHERE customer_id = ? AND order_status IN ('pending','confirmed','preparing','ready') ORDER BY ordered_at DESC");
$st->execute([$cid]); $activeOrders = $st->fetchAll();
$best = get_products(['best' => true, 'limit' => 4]);
$pageTitle = 'Beranda Saya'; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-bottom:4rem">
  <div class="page-head"><span style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">Halo</span><h1>Selamat datang, <em><?= e(current_user()['name']) ?></em></h1><p>Mau minum apa hari ini?</p></div>
  <div class="dash-cards">
    <div class="dash-card"><span>Total Pesanan</span><b><?= (int)$sum['total'] ?></b></div>
    <div class="dash-card"><span>Pesanan Aktif</span><b><?= (int)$sum['active'] ?></b></div>
    <div class="dash-card"><span>Keranjang</span><b><?= cart_count() ?></b><a class="link-arrow" href="<?= url('customer/cart.php') ?>">Buka keranjang</a></div>
  </div>
  <?php if ($activeOrders): ?>
  <h2>Pesanan <em>aktif</em></h2>
  <div class="table-wrap" style="margin-bottom:2rem"><table class="tbl"><tbody>
    <?php foreach ($activeOrders as $o): ?><tr><td><b><?= e($o['order_code']) ?></b></td><td><?= status_badge($o['order_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td><td class="num"><a class="btn btn-pill btn-ghost btn-sm" href="<?= url('customer/order-detail.php?code=' . urlencode($o['order_code'])) ?>">Pantau</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
  <div class="section-head"><h2>Menu <em>unggulan</em></h2><a class="link-arrow" href="<?= url('menu.php') ?>">Lihat semua menu ↗</a></div>
  <div class="product-grid"><?php foreach ($best as $p) product_card($p); ?></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
