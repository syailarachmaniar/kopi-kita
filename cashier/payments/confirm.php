<?php
require_once __DIR__ . '/../../includes/cashier-header.php';
$o = get_order((int)($_GET['id'] ?? 0));
if (!$o) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
if (is_post()) {
    csrf_check();
    $err = confirm_payment((int)$o['id'], (int)current_user()['id'], (string)($_POST['method'] ?? ''), (float)($_POST['amount_paid'] ?? 0), (string)($_POST['ref'] ?? ''));
    if ($err) { flash('error', $err); redirect('cashier/payments/confirm.php?id=' . (int)$o['id']); }
    flash('success', 'Pembayaran dikonfirmasi.');
    redirect('cashier/receipt.php?id=' . (int)$o['id']);
}
$items = get_order_items((int)$o['id']);
cashier_start('Konfirmasi Pembayaran', 'orders');
?>
<div class="detail-grid">
  <div class="card"><h2><span><?= e($o['order_code']) ?></span><span><?= status_badge($o['order_status']) ?> <?= pay_badge($o['payment_status']) ?></span></h2>
    <table><tbody><?php foreach ($items as $it): ?><tr><td><?= (int)$it['quantity'] ?>× <?= e($it['product_name']) ?><?= $it['size'] ? ' (' . e(SIZE_LABELS[$it['size']]) . ')' : '' ?></td><td class="num"><?= rupiah($it['line_total']) ?></td></tr><?php endforeach; ?>
    <tr><td class="num">Diskon</td><td class="num">-<?= rupiah($o['discount_amount']) ?></td></tr><tr class="total-row"><td class="num">Total tagihan</td><td class="num"><?= rupiah($o['total_amount']) ?></td></tr></tbody></table>
    <p class="muted small mt">Pelanggan: <?= e($o['customer_name']) ?> · Metode dipilih pelanggan: <?= e(PAYMENT_METHODS[$o['payment_method']] ?? '-') ?></p></div>
  <div class="card"><h2>Pembayaran</h2>
  <?php if ($o['payment_status'] === 'paid'): ?>
    <div class="alert alert-success">Sudah lunas pada <?= fmt_date($o['paid_at']) ?>.</div><a class="btn btn-dark btn-block" href="<?= url('cashier/receipt.php?id=' . (int)$o['id']) ?>">Lihat / Cetak Struk</a>
  <?php elseif ($o['order_status'] === 'cancelled'): ?><div class="alert alert-error">Pesanan dibatalkan.</div>
  <?php else: ?>
    <form method="post"><?= csrf_field() ?>
      <div class="fg-block"><label>Metode</label><select class="input" id="m" name="method"><?php foreach (PAYMENT_METHODS as $k => $l): ?><option value="<?= $k ?>" <?= $o['payment_method'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
      <div class="fg-block" id="cash"><label>Uang diterima</label><input class="input" id="ap" type="number" name="amount_paid" min="0" step="500" value="<?= (int)$o['total_amount'] ?>">
        <div class="change-box"><span>Kembalian</span><b id="chg">Rp0</b></div></div>
      <div class="fg-block" id="ref" hidden><label>No. referensi (simulasi)</label><input class="input" name="ref" maxlength="100"></div>
      <button class="btn btn-success btn-block">Tandai Lunas &amp; Cetak Struk</button>
    </form>
    <script>
    (function(){var m=document.getElementById('m'),a=document.getElementById('ap'),t=<?= (float)$o['total_amount'] ?>,c=document.getElementById('chg');
    function u(){document.getElementById('cash').hidden=m.value!=='cash';document.getElementById('ref').hidden=m.value==='cash';var d=(+a.value||0)-t;c.textContent=d>=0?'Rp'+Math.round(d).toLocaleString('id-ID'):'Kurang Rp'+Math.round(-d).toLocaleString('id-ID');}
    m.onchange=a.oninput=u;u();})();
    </script>
  <?php endif; ?></div>
</div>
<?php cashier_end(); ?>
