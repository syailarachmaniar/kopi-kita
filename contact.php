<?php
$pageTitle = 'Contact'; $active = 'contact';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="page-head"><span style="color:var(--caramel);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase">Contact</span><h1>Mampir atau <em>sapa kami</em></h1><p>Informasi di bawah ini adalah data simulasi untuk proyek kuliah.</p></div>
  <div class="contact-grid">
    <div>
      <ul class="info-list">
        <li><div class="ii">📍</div><div><b>Alamat</b><?= e(SHOP_ADDRESS) ?></div></li>
        <li><div class="ii">🕗</div><div><b>Jam Operasional</b><?= e(SHOP_HOURS) ?></div></li>
        <li><div class="ii">📞</div><div><b>Telepon / WhatsApp</b><?= e(SHOP_PHONE) ?></div></li>
        <li><div class="ii">✉</div><div><b>Email</b><?= e(SHOP_EMAIL) ?></div></li>
      </ul>
      <iframe class="map-frame" title="Peta lokasi" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=119.38%2C-5.20%2C119.46%2C-5.10&amp;layer=mapnik"></iframe>
    </div>
    <div class="panel-box" style="padding:2rem">
      <h2>Kirim Pesan via WhatsApp</h2>
      <form id="waForm" data-validate>
        <div class="form-group"><label for="wn">Nama</label><input class="input" id="wn" required maxlength="80"></div>
        <div class="form-group"><label for="wm">Pesan</label><textarea class="input" id="wm" rows="5" required maxlength="500"></textarea></div>
        <button class="btn btn-pill btn-primary" type="submit">Kirim ke WhatsApp</button>
      </form>
    </div>
  </div>
  <div class="faq" id="faq" style="padding:3rem 0 4rem">
    <h2>Pertanyaan <em>Umum</em></h2>
    <details><summary>Bagaimana cara memesan?</summary><p>Login atau daftar, pilih menu, masukkan ke keranjang, lalu checkout. Pesanan akan dikonfirmasi oleh kasir.</p></details>
    <details><summary>Apa saja metode pembayaran?</summary><p>Cash di kasir, QRIS, dan transfer bank. Untuk saat ini QRIS dan transfer bersifat simulasi dan dikonfirmasi manual oleh kasir.</p></details>
    <details><summary>Bisakah pesanan dibatalkan?</summary><p>Pesanan berstatus Pending dapat dibatalkan dari halaman detail pesanan. Setelah dikonfirmasi, hubungi kasir.</p></details>
    <details><summary>Apakah ada diskon?</summary><p>Belanja minimal Rp100.000 mendapat potongan Rp5.000 otomatis.</p></details>
  </div>
</div>
<script>
document.getElementById('waForm').addEventListener('submit',function(e){e.preventDefault();
 var n=document.getElementById('wn').value.trim(),m=document.getElementById('wm').value.trim();if(!n||!m)return;
 window.open('https://wa.me/<?= e(SHOP_WA) ?>?text='+encodeURIComponent('Halo KopiKita, saya '+n+'. '+m),'_blank','noopener');});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
