<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
$pageTitle = 'Keranjang'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
$cart = cart_detail();
$allOk = $cart['lines'] && !array_filter($cart['lines'], fn($l) => !$l['ok']);
?>
<div class="container">
  <div class="page-head"><h1>Keranjang <em>Pesanan</em></h1></div>
  <?php if (!$cart['lines']): ?>
    <div class="empty panel-box"><div class="big">☕</div><h2>Keranjang masih kosong</h2><p>Yuk pilih menu favoritmu.</p><a class="btn btn-pill btn-primary" href="<?= url('menu.php') ?>">Lihat Menu</a></div>
  <?php else: ?>
  <div class="cart-layout">
    <div>
      <?php foreach ($cart['lines'] as $l): $p = $l['product']; ?>
      <div class="cart-item <?= $l['ok'] ? '' : 'bad' ?>">
        <img src="<?= product_image($p['image']) ?>" alt="">
        <div>
          <div class="ci-name"><?= e($p['name']) ?></div>
          <div class="small" style="font-size:.78rem"><?= rupiah($l['unit_price']) ?> / item<?= $l['size'] ? ' · ' . e(SIZE_LABELS[$l['size']]) : '' ?></div>
          <?php if (!$l['ok']): ?><div class="field-error">Stok tidak mencukupi (tersisa <?= (int)$p['stock'] ?>) atau produk tidak tersedia. Kurangi jumlah atau hapus.</div><?php endif; ?>
          <form class="ci-controls" method="post" action="<?= url('customer/cart-action.php') ?>" data-auto>
            <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="key" value="<?= e($l['key']) ?>"><input type="hidden" name="back" value="/customer/cart.php">
            <div class="qty"><button type="button" data-d="-">−</button><input type="number" name="qty" value="<?= $l['qty'] ?>" min="0" max="50" aria-label="Jumlah"><button type="button" data-d="+">+</button></div>
            <?php if ($p['has_size_option']): ?>
            <select class="input" name="size" aria-label="Ukuran"><?php foreach (SIZE_LABELS as $k => $lab): ?><option value="<?= $k ?>" <?= $l['size'] === $k ? 'selected' : '' ?>><?= e($lab) ?><?= SIZE_PRICES[$k] ? ' (+' . rupiah(SIZE_PRICES[$k]) . ')' : '' ?></option><?php endforeach; ?></select>
            <?php endif; ?>
            <input class="input note" name="note" value="<?= e($l['note']) ?>" maxlength="200" placeholder="Catatan (less sugar, dll.)">
          </form>
        </div>
        <div class="ci-price">
          <b class="price"><?= rupiah($l['line_total']) ?></b>
          <form method="post" action="<?= url('customer/cart-action.php') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="key" value="<?= e($l['key']) ?>"><button class="link-danger" type="submit">Hapus</button></form>
        </div>
      </div>
      <?php endforeach; ?>
      <form method="post" action="<?= url('customer/cart-action.php') ?>" data-confirm="Kosongkan keranjang?"><?= csrf_field() ?><input type="hidden" name="action" value="clear"><button class="link-danger" type="submit">Kosongkan keranjang</button></form>
    </div>
    <aside class="summary">
      <h3>Ringkasan</h3>
      <div class="sum-row"><span>Subtotal</span><span><?= rupiah($cart['subtotal']) ?></span></div>
      <div class="sum-row"><span>Diskon</span><span>-<?= rupiah($cart['discount']) ?></span></div>
      <?php if ($cart['discount'] == 0): ?><div class="small" style="font-size:.74rem;color:var(--caramel)">Belanja min. Rp100.000 dapat potongan Rp5.000.</div><?php endif; ?>
      <div class="sum-row sum-total"><span>Total</span><span><?= rupiah($cart['total']) ?></span></div>
      <a class="btn btn-pill btn-primary btn-block" style="margin-top:1rem;<?= $allOk ? '' : 'opacity:.5;pointer-events:none' ?>" href="<?= url('customer/checkout.php') ?>">Lanjut Checkout ›</a>
      <a class="btn btn-pill btn-ghost btn-block" style="margin-top:.6rem" href="<?= url('menu.php') ?>">Tambah Menu</a>
    </aside>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
