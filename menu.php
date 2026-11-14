<?php
$pageTitle = 'Menu'; $active = 'menu';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/header.php';
$cats = get_categories();
// Filter kategori dilakukan di server: hanya produk kategori terpilih yang ditampilkan.
$catSlug = trim((string)($_GET['cat'] ?? ''));
$curCat = null;
foreach ($cats as $c) if ($c['slug'] === $catSlug) $curCat = $c;
$products = get_products($curCat ? ['category' => $curCat['slug']] : []);
$catDesc = [
  'coffee' => 'Espresso klasik hingga latte kekinian dari biji pilihan.',
  'non-coffee' => 'Teh, susu, matcha, dan cokelat tanpa kopi.',
  'signature-drinks' => 'Racikan khas KopiKita yang hanya ada di sini.',
  'food-pastry' => 'Pastry, cake, dan makanan ringan teman ngopi.',
];
?>
<div class="container">
  <div class="page-head toolbar">
    <div>
      <span class="eyebrow" style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase"><?= $curCat ? 'Kategori' : 'Katalog Menu' ?></span>
      <h1><?= $curCat ? e($curCat['name']) : 'Pilih <em>Favoritmu</em>' ?></h1>
      <p><?= $curCat ? e($catDesc[$curCat['slug']] ?? '') . ' (' . count($products) . ' menu)' : 'Kopi, non-kopi, pastry, dan racikan signature.' ?> <?= is_logged_in() ? '' : 'Login untuk memesan.' ?></p>
    </div>
    <div class="search-box"><label class="sr-only" for="q">Cari menu</label><input class="input" id="q" type="search" placeholder="Cari di <?= $curCat ? e($curCat['name']) : 'semua menu' ?>…" data-search="menu"></div>
  </div>
  <nav class="chips" aria-label="Kategori menu">
    <a class="chip <?= $curCat ? '' : 'active' ?>" href="<?= url('menu.php') ?>">All</a>
    <?php foreach ($cats as $c): ?><a class="chip <?= $curCat && $curCat['id'] == $c['id'] ? 'active' : '' ?>" href="<?= url('menu.php?cat=' . urlencode($c['slug'])) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
  </nav>
  <div class="product-grid" style="padding-bottom:3rem">
    <?php foreach ($products as $p) product_card($p); ?>
  </div>
  <p class="empty-note" data-empty="menu" <?= $products ? 'hidden' : '' ?>>Menu tidak ditemukan. Coba kata kunci atau kategori lain.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
