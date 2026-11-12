<?php
$pageTitle = 'About'; $active = 'about';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="page-head"><span style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">About us</span><h1>Sebuah kedai yang dibangun oleh <em>pecinta kopi.</em></h1></div>
  <div class="panel-box reveal" style="position:relative;overflow:hidden">
    <div class="about-watermark" aria-hidden="true">KopiKita</div>
    <div class="about-grid">
      <div class="about-img"><img src="<?= asset('images/ui/about-1.svg') ?>" alt=""></div>
      <div class="about-text">
        <p><?= e(APP_NAME) ?> lahir dari obrolan sederhana: bagaimana jika secangkir kopi enak bisa dinikmati siapa saja, di tempat yang hangat, dengan harga yang bersahabat?</p>
        <p>Kami bekerja sama dengan petani lokal, menyangrai biji secara berkala, dan melatih barista agar setiap cangkir konsisten. Selain kopi, kami menyajikan minuman non-kopi, pastry, dan racikan signature yang terus berkembang.</p>
        <p class="serif" style="font-size:1.4rem"><em><?= e(APP_SLOGAN) ?></em></p>
        <div class="features">
          <div class="feature"><div class="fi">☕</div><h4>Biji Pilihan</h4><p>Kopi lokal berkualitas, disangrai segar.</p></div>
          <div class="feature"><div class="fi">✦</div><h4>Barista Ahli</h4><p>Dilatih untuk rasa yang konsisten.</p></div>
          <div class="feature"><div class="fi">❦</div><h4>Berkelanjutan</h4><p>Peduli lingkungan dan petani.</p></div>
        </div>
      </div>
      <div class="about-img"><img src="<?= asset('images/ui/about-2.svg') ?>" alt=""></div>
    </div>
    <div class="stats"><div class="stat"><b>2026</b><span>Mulai Berdiri</span></div><div class="stat"><b>10+</b><span>Barista</span></div><div class="stat"><b>100%</b><span>Biji Lokal</span></div><div class="stat"><b>5K+</b><span>Pelanggan Bahagia</span></div></div>
  </div>
  <div style="text-align:center;padding:2.5rem 0 4rem"><a class="btn btn-pill btn-primary" href="<?= url('menu.php') ?>">Order Now ›</a></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
