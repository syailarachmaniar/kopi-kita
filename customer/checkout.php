<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['customer']);
$cart = cart_detail();
if (!$cart['lines']) { flash('info', 'Keranjang masih kosong.'); redirect('menu.php'); }
$cid = current_customer_id();
if (!$cid) { flash('error', 'Profil pelanggan tidak ditemukan.'); redirect('index.php'); }
$err = [];
$f = ['type' => 'dine_in', 'table' => '', 'notes' => '', 'method' => 'cash'];
if (is_post()) {
    csrf_check();
    $f['type'] = $_POST['type'] ?? 'dine_in';
    $f['table'] = trim((string)($_POST['table'] ?? ''));
    $f['notes'] = trim((string)($_POST['notes'] ?? ''));
    $f['method'] = $_POST['method'] ?? 'cash';
    if (!in_array($f['type'], ['dine_in', 'takeaway'], true)) $err[] = 'Jenis pesanan tidak valid.';
    if (!isset(PAYMENT_METHODS[$f['method']])) $err[] = 'Metode pembayaran tidak valid.';
    if ($f['type'] === 'dine_in' && $f['table'] === '') $err[] = 'Nomor meja wajib diisi untuk dine-in.';
    if (mb_strlen($f['notes']) > 500) $err[] = 'Catatan maksimal 500 karakter.';
    if (!$err) {
        try {
            $id = create_order($cart['lines'], ['channel' => 'online', 'type' => $f['type'], 'table' => $f['table'], 'notes' => $f['notes'], 'method' => $f['method'], 'customer_id' => $cid]);
            $_SESSION['cart'] = [];
            $o = get_order($id);
            redirect('customer/order-success.php?code=' . urlencode($o['order_code']));
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
            redirect('customer/cart.php');
        }
    }
}
$pageTitle = 'Checkout'; $active = ''; $hideNewsletter = true;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <div class="page-head"><h1>Check<em>out</em></h1></div>
  <?php foreach ($err as $m): ?><div class="alert alert-error"><?= e($m) ?></div><?php endforeach; ?>
  <form method="post" class="cart-layout" data-validate>
    <?= csrf_field() ?>
    <div class="panel-box" style="padding:2rem">
      <div class="form-group"><label>Jenis Pesanan</label>
        <div class="radio-cards"><label><input type="radio" name="type" value="dine_in" <?= $f['type'] === 'dine_in' ? 'checked' : '' ?>><span>Dine-in</span></label><label><input type="radio" name="type" value="takeaway" <?= $f['type'] === 'takeaway' ? 'checked' : '' ?>><span>Takeaway</span></label></div></div>
      <div class="form-group" id="tableRow"><label for="table">Nomor Meja</label><input class="input" id="table" name="table" maxlength="10" value="<?= e($f['table']) ?>" placeholder="Contoh: 5"></div>
      <div class="form-group"><label>Metode Pembayaran</label>
        <div class="radio-cards"><?php foreach (PAYMENT_METHODS as $k => $lab): ?><label><input type="radio" name="method" value="<?= e($k) ?>" <?= $f['method'] === $k ? 'checked' : '' ?>><span><?= e($lab) ?></span></label><?php endforeach; ?></div>
        <p class="small" style="font-size:.76rem;margin-top:.5rem">Pembayaran dikonfirmasi oleh kasir. QRIS dan transfer bersifat simulasi (belum terhubung payment gateway).</p></div>
      <div class="form-group"><label for="notes">Catatan Pesanan (opsional)</label><textarea class="input" id="notes" name="notes" maxlength="500" placeholder="Instruksi untuk barista"><?= e($f['notes']) ?></textarea></div>
    </div>
    <aside class="summary">
      <h3>Pesanan Anda</h3>
      <?php foreach ($cart['lines'] as $l): ?>
      <div class="sum-row"><span><?= $l['qty'] ?>× <?= e($l['product']['name']) ?><?= $l['size'] ? ' (' . e(SIZE_LABELS[$l['size']]) . ')' : '' ?></span><span><?= rupiah($l['line_total']) ?></span></div>
      <?php endforeach; ?>
      <div class="sum-row" style="border-top:1px dashed rgba(243,230,211,.2);margin-top:.5rem"><span>Subtotal</span><span><?= rupiah($cart['subtotal']) ?></span></div>
      <div class="sum-row"><span>Diskon</span><span>-<?= rupiah($cart['discount']) ?></span></div>
      <div class="sum-row sum-total"><span>Total Pembayaran</span><span><?= rupiah($cart['total']) ?></span></div>
      <button class="btn btn-pill btn-primary btn-block" style="margin-top:1rem" type="submit">Buat Pesanan</button>
      <a class="btn btn-pill btn-ghost btn-block" style="margin-top:.6rem" href="<?= url('customer/cart.php') ?>">Kembali ke Keranjang</a>
    </aside>
  </form>
</div>
<script>
(function(){var r=document.querySelectorAll('input[name=type]'),t=document.getElementById('tableRow'),i=document.getElementById('table');
function u(){var d=document.querySelector('input[name=type]:checked').value==='dine_in';t.hidden=!d;i.required=d;}r.forEach(function(x){x.addEventListener('change',u)});u();})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
