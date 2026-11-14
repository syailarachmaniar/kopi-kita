<?php
$pageTitle = 'Beranda'; $active = 'home';
require_once __DIR__ . '/includes/components.php';
require_once __DIR__ . '/includes/header.php';
$best = get_products(['best' => true, 'limit' => 10]);
$collections = section_gallery_collections();
$orderUrl = url('menu.php');
?>
<section class="hero-shell"><div class="container">
  <div class="hero-card">
    <div class="hero-slides">
      <?php
      $slides = [
        ['A Little Coffee,', 'A Lot', 'of Happiness.', 'Diracik barista kami dari biji kopi lokal pilihan. Satu cangkir hangat untuk memulai harimu.', 'Fresh Roasted Daily'],
        ['Promo Belanja', 'Hemat', 'Rp5.000.', 'Belanja minimal Rp100.000 otomatis dapat potongan Rp5.000. Ajak teman, pesan lebih banyak.', 'Promo Minggu Ini'],
        ['Coba Menu', 'Signature', 'Terbaru.', 'Caramel Cloud dan Orange Espresso Tonic – racikan khas yang hanya ada di KopiKita.', 'Signature Drinks'],
      ];
      foreach ($slides as $i => [$a, $b, $c, $txt, $tag]): ?>
      <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
        <div>
          <span class="hero-tag"><?= e($tag) ?></span>
          <?php if ($i === 0): ?><h1><?= e($a) ?><br><em><?= e($b) ?></em> <?= e($c) ?></h1><?php else: ?><h1><?= e($a) ?> <em><?= e($b) ?></em> <?= e($c) ?></h1><?php endif; ?>
          <p><?= e($txt) ?></p>
          <div class="hero-cta">
            <a class="btn btn-pill btn-primary" href="<?= $orderUrl ?>">Order Now ›</a>
            <a class="btn btn-pill btn-ghost" href="<?= url('menu.php') ?>">Explore Menu</a>
          </div>
        </div>
        <div class="hero-visual">
          <span class="bean b1"></span><span class="bean b2"></span><span class="bean b3"></span><span class="bean b4"></span>
          <img src="<?= asset('images/ui/hero.svg') ?>" alt="Secangkir kopi KopiKita" width="430" height="430">
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="hero-dots"><?php foreach ($slides as $i => $_): ?><button class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?></div>
  </div>
  <div class="highlights reveal">
    <span>Fresh Roasted Daily</span><span>Expert Baristas</span><span>Local Single Origin</span><span>Cozy Space</span>
  </div>
</div></section>

<section class="section" id="categories" style="padding-bottom:0">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Kategori Menu</span><h2>Pilih <em>Kategori</em></h2></div></div>
    <div class="cat-grid">
      <?php foreach (get_categories() as $hc):
        $hp = get_products(['category' => $hc['slug']]);
        $cover = $hp[0] ?? null;
        foreach ($hp as $x) if ($x['is_best_seller']) { $cover = $x; break; } ?>
      <a class="cat-card reveal" href="<?= url('menu.php?cat=' . urlencode($hc['slug'])) ?>">
        <img src="<?= e(product_image($cover['image'] ?? null)) ?>" alt="<?= e($hc['name']) ?>" loading="lazy">
        <div class="cat-info"><h3><?= e($hc['name']) ?></h3><span><?= count($hp) ?> menu</span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="best">
  <div class="container">
    <div class="section-head reveal">
      <div><span class="eyebrow">Menu Best Seller</span><h2>Discover Our <em>Signature</em><br>Coffee Collection</h2></div>
      <p>Dari espresso pekat sampai racikan khas kami – menu yang paling sering dipesan pelanggan KopiKita.</p>
    </div>
    <div class="carousel reveal">
      <button class="car-btn prev" aria-label="Sebelumnya">‹</button>
      <div class="carousel-track">
        <?php foreach ($best as $p) product_card($p); ?>
      </div>
      <button class="car-btn next" aria-label="Berikutnya">›</button>
    </div>
    <div style="text-align:center;margin-top:1.5rem"><a class="btn btn-pill btn-ghost" href="<?= url('menu.php') ?>">Lihat Semua Menu</a></div>
  </div>
</section>

<section class="section about" id="about-home">
  <div class="container">
    <div class="panel-box reveal" style="position:relative;overflow:hidden">
      <div class="about-watermark" aria-hidden="true">KopiKita</div>
      <div class="about-grid">
        <div class="about-img"><img src="<?= asset('images/ui/about-1.svg') ?>" alt="Kopi hangat" loading="lazy"></div>
        <div class="about-text">
          <span class="eyebrow" style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">Tentang Kami</span>
          <h2>Sebuah kedai yang dibangun oleh <em>pecinta kopi.</em></h2>
          <p>KopiKita berdiri pada 2026 dengan satu keyakinan sederhana: kopi yang baik membuat hari terasa lebih ringan. Kami memilih biji lokal, menyangrai dengan teliti, dan menyajikannya dengan ramah.</p>
          <a class="link-arrow" href="<?= url('about.php') ?>">Baca cerita kami ↗</a>
          <div class="features">
            <div class="feature"><div class="fi">☕</div><h4>Biji Pilihan</h4><p>Dari petani lokal terbaik.</p></div>
            <div class="feature"><div class="fi">✦</div><h4>Diracik Barista Ahli</h4><p>Takaran dan teknik presisi.</p></div>
            <div class="feature"><div class="fi">❦</div><h4>Ramah Lingkungan</h4><p>Kemasan dan ampas kopi didaur ulang.</p></div>
          </div>
        </div>
        <div class="about-img"><img src="<?= asset('images/ui/about-2.svg') ?>" alt="Suasana kedai" loading="lazy"></div>
      </div>
      <div class="stats">
        <div class="stat"><b>2026</b><span>Mulai Berdiri</span></div>
        <div class="stat"><b>10+</b><span>Barista Berpengalaman</span></div>
        <div class="stat"><b>100%</b><span>Biji Kopi Lokal</span></div>
        <div class="stat"><b>5K+</b><span>Pelanggan Bahagia</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="collections" style="padding-top:1rem">
  <div class="container">
    <div class="panel-box reveal">
      <h2 style="font-size:2rem">Popular Collections</h2>
      <div class="chips" data-chips="gal" data-attr="col">
        <button class="chip active" data-value="all">All</button>
        <?php foreach (GALLERY_COLLECTIONS as $k => $label): ?><button class="chip" data-value="<?= e($k) ?>"><?= e($label) ?></button><?php endforeach; ?>
      </div>
      <div class="carousel">
        <button class="car-btn prev" aria-label="Sebelumnya">‹</button>
        <div class="collections carousel-track" style="gap:1.2rem">
          <?php foreach (GALLERY_COLLECTIONS as $k => $label): $imgs = $collections[$k] ?? []; if (!$imgs) continue; ?>
          <div class="collection" data-item="gal" data-col="<?= e($k) ?>" data-name="<?= e(strtolower($label)) ?>" style="flex:0 0 min(360px,86%)">
            <div class="col-main" data-zoom="<?= gallery_image($imgs[0]['image']) ?>"><img src="<?= gallery_image($imgs[0]['image']) ?>" alt="<?= e($imgs[0]['title']) ?>" loading="lazy"></div>
            <div class="col-thumbs">
              <?php foreach (array_slice($imgs, 1, 3) as $im): ?><div data-zoom="<?= gallery_image($im['image']) ?>"><img src="<?= gallery_image($im['image']) ?>" alt="<?= e($im['title']) ?>" loading="lazy"></div><?php endforeach; ?>
            </div>
            <div class="col-meta"><span><?= e($label) ?></span><span>▣ <?= count($imgs) ?></span></div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="car-btn next" aria-label="Berikutnya">›</button>
      </div>
      <div style="margin-top:1rem"><a class="link-arrow" href="<?= url('gallery.php') ?>">Lihat seluruh galeri ↗</a></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
