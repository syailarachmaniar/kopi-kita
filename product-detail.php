<?php
require_once __DIR__ . '/includes/auth.php';
$slug = (string)($_GET['slug'] ?? '');
$st = db()->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.is_available = 1 AND c.is_active = 1');
$st->execute([$slug]);
$p = $st->fetch();
if (!$p) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$pageTitle = $p['name']; $active = 'menu';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/header.php';
$out = $p['stock'] <= 0;
$rel = array_filter(get_products(['category' => $p['category_slug'], 'limit' => 5]), fn($x) => $x['id'] != $p['id']);
?>
<div class="container">
  <div class="detail" data-detail data-price="<?= (float)$p['price'] ?>" data-extra='<?= e(json_encode(SIZE_PRICES)) ?>'>
    <div class="detail-img"><img src="<?= product_image($p['image']) ?>" alt="<?= e($p['name']) ?>"></div>
    <div>
      <div class="breadcrumb"><a href="<?= url('menu.php') ?>">Menu</a> / <a href="<?= url('menu.php?cat=' . urlencode($p['category_slug'])) ?>"><?= e($p['category_name']) ?></a></div>
      <?php if ($p['is_best_seller']): ?><span class="badge st-preparing" style="margin-bottom:.6rem">Best Seller</span><?php endif; ?>
      <h1><?= e($p['name']) ?></h1>
      <span class="price" id="priceView"><?= rupiah($p['price']) ?></span>
      <p><?= nl2br(e($p['description'])) ?></p>
      <p class="small" style="color:<?= $out ? 'var(--danger)' : ($p['stock'] <= $p['min_stock'] ? 'var(--warning)' : 'var(--success)') ?>">
        <?= $out ? 'Stok habis' : ($p['stock'] <= $p['min_stock'] ? 'Stok terbatas – tersisa ' . (int)$p['stock'] : 'Tersedia') ?></p>
      <form method="post" action="<?= url('customer/cart-action.php') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="back" value="<?= e(BASE_URL . '/menu.php') ?>">
        <?php if ($p['has_size_option']): ?>
        <div class="form-group"><label>Ukuran</label>
          <div class="size-options">
            <label><input type="radio" name="size" value="regular" checked><span>Regular</span></label>
            <label><input type="radio" name="size" value="large"><span>Large (+<?= rupiah(SIZE_PRICES['large']) ?>)</span></label>
          </div></div>
        <?php endif; ?>
        <div class="form-group"><label>Jumlah</label>
          <div class="qty"><button type="button" data-d="-">−</button><input type="number" name="qty" value="1" min="1" max="<?= max(1, min(50, (int)$p['stock'])) ?>"><button type="button" data-d="+">+</button></div></div>
        <div class="form-group"><label for="note">Catatan khusus (opsional)</label><input class="input" id="note" name="note" maxlength="200" placeholder="Contoh: less sugar, extra shot"></div>
        <div class="sum-row sum-total" style="max-width:320px"><span>Total</span><span data-total><?= rupiah($p['price']) ?></span></div>
        <div style="margin-top:1.2rem;display:flex;gap:.7rem;flex-wrap:wrap">
          <button class="btn btn-pill btn-primary" type="submit" <?= $out ? 'disabled' : '' ?>>Tambah ke Keranjang</button>
          <a class="btn btn-pill btn-ghost" href="<?= url('menu.php') ?>">Kembali</a>
        </div>
      </form>
    </div>
  </div>
  <?php if ($rel): ?>
  <h2 style="margin-top:1rem">Menu <em>serupa</em></h2>
  <div class="product-grid" style="padding-bottom:4rem"><?php foreach (array_slice($rel, 0, 4) as $r) product_card($r); ?></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
