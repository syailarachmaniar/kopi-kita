<?php
$pageTitle = 'Menu'; $active = 'menu';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/header.php';
$cats = get_categories();
$products = get_products();
?>
<div class="container">
  <div class="page-head toolbar">
    <div>
      <span class="eyebrow" style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">Katalog Menu</span>
      <h1>Pilih <em>Favoritmu</em></h1>
      <p>Kopi, non-kopi, pastry, dan racikan signature. <?= is_logged_in() ? '' : 'Login untuk memesan.' ?></p>
    </div>
    <div class="search-box"><label class="sr-only" for="q">Cari menu</label><input class="input" id="q" type="search" placeholder="Cari menu…" data-search="menu"></div>
  </div>
  <div class="chips" data-chips="menu">
    <button class="chip active" data-value="all">All</button>
    <?php foreach ($cats as $c): ?><button class="chip" data-value="<?= e($c['slug']) ?>"><?= e($c['name']) ?></button><?php endforeach; ?>
  </div>
  <div class="product-grid" style="padding-bottom:3rem">
    <?php foreach ($products as $p) product_card($p); ?>
  </div>
  <p class="empty-note" data-empty="menu" hidden>Menu tidak ditemukan. Coba kata kunci atau kategori lain.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
