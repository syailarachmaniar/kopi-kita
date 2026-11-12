<?php
/** Kartu produk dipakai di beranda & katalog. */
function product_card(array $p): void
{
    $out = $p['stock'] <= 0;
    ?>
    <article class="card" data-item="menu" data-cat="<?= e($p['category_slug']) ?>" data-name="<?= e(mb_strtolower($p['name'])) ?>">
      <a class="card-img" href="<?= url('product-detail.php?slug=' . urlencode($p['slug'])) ?>">
        <img src="<?= product_image($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php if ($p['is_best_seller']): ?><span class="tag best">Best Seller</span><?php else: ?><span class="tag"><?= e($p['category_name']) ?></span><?php endif; ?>
        <?php if ($out): ?><span class="tag out">Habis</span><?php endif; ?>
      </a>
      <div class="card-body">
        <span class="cat"><?= e($p['category_name']) ?></span>
        <h3><a href="<?= url('product-detail.php?slug=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a></h3>
        <p><?= e(mb_strimwidth((string)$p['description'], 0, 80, '…')) ?></p>
        <div class="card-foot">
          <div><span class="price"><?= rupiah($p['price']) ?></span><?php if (!$out && $p['stock'] <= $p['min_stock']): ?><div class="stock-note">Sisa <?= (int)$p['stock'] ?></div><?php endif; ?></div>
          <form method="post" action="<?= url('customer/cart-action.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="qty" value="1"><input type="hidden" name="size" value="regular">
            <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
            <button class="btn btn-pill btn-primary btn-sm" type="submit" <?= $out ? 'disabled' : '' ?>><?= $out ? 'Habis' : '+ Keranjang' ?></button>
          </form>
        </div>
      </div>
    </article>
    <?php
}
function section_gallery_collections(): array
{
    $rows = db()->query("SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY collection, sort_order, id")->fetchAll();
    $by = [];
    foreach ($rows as $r) $by[$r['collection']][] = $r;
    return $by;
}
