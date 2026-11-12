<?php
$pageTitle = 'Gallery'; $active = 'gallery'; $hideNewsletter = false;
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/header.php';
$imgs = db()->query('SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
?>
<div class="container">
  <div class="page-head"><span class="eyebrow" style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">Gallery</span><h1>Popular <em>Collections</em></h1><p>Potret kopi, minuman, makanan, dan suasana hangat di KopiKita.</p></div>
  <div class="chips" data-chips="gal" data-attr="col">
    <button class="chip active" data-value="all">All</button>
    <?php foreach (GALLERY_COLLECTIONS as $k => $label): ?><button class="chip" data-value="<?= e($k) ?>"><?= e($label) ?></button><?php endforeach; ?>
  </div>
  <div class="gallery-grid" style="padding-bottom:3rem">
    <?php foreach ($imgs as $im): ?>
    <figure class="gal-item" style="margin:0" data-item="gal" data-col="<?= e($im['collection']) ?>" data-name="<?= e(strtolower($im['title'])) ?>" data-zoom="<?= gallery_image($im['image']) ?>">
      <img src="<?= gallery_image($im['image']) ?>" alt="<?= e($im['title']) ?>" loading="lazy"><figcaption><?= e($im['title']) ?> · <?= e(GALLERY_COLLECTIONS[$im['collection']] ?? '') ?></figcaption>
    </figure>
    <?php endforeach; ?>
  </div>
  <?php if (!$imgs): ?><p class="empty-note">Belum ada foto galeri.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
