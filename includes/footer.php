</main>
<?php if (empty($hideNewsletter)): ?>
<section class="newsletter reveal" id="newsletter">
  <div class="container newsletter-inner">
    <div class="nl-cup" aria-hidden="true"><img src="<?= asset('images/products/cappuccino.svg') ?>" alt=""></div>
    <div class="nl-text">
      <h2>Stay up to <em>date</em></h2>
      <p>Daftarkan email Anda untuk mendapat kabar menu baru, promo, dan cerita seputar KopiKita.</p>
    </div>
    <form class="nl-form" method="post" action="<?= url('actions/subscribe.php') ?>" data-validate>
      <?= csrf_field() ?>
      <input type="hidden" name="back" value="<?= e(($_SERVER['REQUEST_URI'] ?? '/') . '#newsletter') ?>">
      <label class="sr-only" for="nl-email">Email</label>
      <input id="nl-email" type="email" name="email" placeholder="Email Anda" required maxlength="150">
      <button class="btn btn-pill btn-primary" type="submit">Subscribe</button>
    </form>
  </div>
</section>
<?php endif; ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="f-brand">
      <a class="brand" href="<?= url('index.php') ?>"><img src="<?= asset('images/ui/logo.svg') ?>" alt="" width="40" height="40"><span><b>KopiKita</b><small>Est. 2026</small></span></a>
      <p class="serif"><em><?= e(APP_SLOGAN) ?></em></p>
    </div>
    <div><h4>Quick Links</h4><ul>
      <li><a href="<?= url('index.php') ?>">Home</a></li><li><a href="<?= url('menu.php') ?>">Menu</a></li>
      <li><a href="<?= url('gallery.php') ?>">Gallery</a></li><li><a href="<?= url('about.php') ?>">About</a></li><li><a href="<?= url('contact.php') ?>">Contact</a></li></ul></div>
    <div><h4>Informasi</h4><ul>
      <li><?= e(SHOP_HOURS) ?></li><li><?= e(SHOP_ADDRESS) ?></li><li><?= e(SHOP_PHONE) ?></li><li><a href="<?= url('contact.php#faq') ?>">FAQ</a></li></ul></div>
    <div><h4>Social Media</h4><ul class="social">
      <li><a href="#" aria-label="Instagram">Instagram</a></li><li><a href="#" aria-label="TikTok">TikTok</a></li><li><a href="https://wa.me/<?= e(SHOP_WA) ?>" target="_blank" rel="noopener">WhatsApp</a></li></ul></div>
  </div>
  <div class="container copy">© <?= date('Y') ?> KopiKita. Dibuat untuk proyek kuliah – alamat, kontak, dan pembayaran bersifat simulasi.</div>
</footer>
<script src="<?= asset('js/script.js') ?>"></script>
<script src="<?= asset('js/validation.js') ?>"></script>
<script src="<?= asset('js/cart.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
