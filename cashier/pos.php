<?php
require_once __DIR__ . '/../includes/cashier-header.php';
$cats = get_categories(); $products = get_products();
$customers = db()->query('SELECT id, name, phone FROM customers ORDER BY (id = 1) DESC, name LIMIT 300')->fetchAll();
if (is_post()) {
    csrf_check();
    $items = json_decode((string)($_POST['items'] ?? '[]'), true);
    $lines = [];
    if (is_array($items)) foreach ($items as $it) {
        $pid = (int)($it['id'] ?? 0); $qty = (int)($it['qty'] ?? 0);
        if ($pid > 0 && $qty > 0 && $qty <= 50) $lines[] = ['product' => ['id' => $pid], 'qty' => $qty, 'size' => in_array($it['size'] ?? '', ['regular', 'large'], true) ? $it['size'] : null, 'note' => ''];
    }
    $later = isset($_POST['pay_later']);
    $method = $_POST['method'] ?? 'cash';
    $opt = ['channel' => 'cashier', 'type' => $_POST['type'] ?? 'takeaway', 'table' => $_POST['table'] ?? '', 'notes' => trim((string)($_POST['notes'] ?? '')), 'method' => $method,
        'customer_id' => (int)($_POST['customer_id'] ?? 1) ?: 1, 'user_id' => (int)current_user()['id']];
    if (!$later) $opt['paid_now'] = ['amount' => (float)($_POST['amount_paid'] ?? 0), 'ref' => (string)($_POST['ref'] ?? '')];
    try {
        if (!$lines) throw new RuntimeException('Pilih minimal satu produk.');
        if (!$later && $method !== 'cash' && trim((string)($_POST['ref'] ?? '')) === '') throw new RuntimeException('Nomor referensi wajib diisi untuk QRIS/transfer.');
        $id = create_order($lines, $opt);
        flash('success', $later ? 'Pesanan disimpan, pembayaran menyusul.' : 'Transaksi berhasil.');
        redirect($later ? 'cashier/orders/show.php?id=' . $id : 'cashier/receipt.php?id=' . $id . '&print=1');
    } catch (RuntimeException $ex) { flash('error', $ex->getMessage()); redirect('cashier/pos.php'); }
}
$js = []; foreach ($products as $p) $js[$p['id']] = ['name' => $p['name'], 'price' => (float)$p['price'], 'size' => (bool)$p['has_size_option']];
cashier_start('Transaksi Baru (POS)', 'pos');
?>
<form id="posForm" method="post" class="pos"><?= csrf_field() ?><input type="hidden" name="items" id="itemsJson" value="[]">
  <div>
    <div class="page-actions"><div class="tabs" id="posCats" style="margin:0"><a href="#" class="active" data-cat="all">Semua</a><?php foreach ($cats as $c): ?><a href="#" data-cat="<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?></div>
      <input class="input" style="max-width:240px" id="posSearch" type="search" placeholder="Cari produk…"></div>
    <div class="pos-products">
      <?php foreach ($products as $p): ?><button type="button" class="pos-item" data-id="<?= (int)$p['id'] ?>" data-cat="<?= e($p['category_slug']) ?>" data-name="<?= e(mb_strtolower($p['name'])) ?>" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
        <img src="<?= product_image($p['image']) ?>" alt=""><b><?= e($p['name']) ?></b><span><?= rupiah($p['price']) ?></span><small><?= $p['stock'] <= 0 ? 'Habis' : 'Stok ' . (int)$p['stock'] ?></small></button><?php endforeach; ?>
    </div>
  </div>
  <div class="card pos-cart">
    <h2>Pesanan</h2>
    <div id="pcLines"></div>
    <div class="sum"><span>Subtotal</span><span id="sSub">Rp0</span></div><div class="sum"><span>Diskon</span><span id="sDisc">-Rp0</span></div><div class="sum total"><span>Total</span><span id="sTotal">Rp0</span></div>
    <div class="row-2 mt"><div class="fg-block"><label>Pelanggan</label><select class="input" name="customer_id"><?php foreach ($customers as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?><?= $c['phone'] ? ' · ' . e($c['phone']) : '' ?></option><?php endforeach; ?></select></div>
      <div class="fg-block"><label>Jenis</label><select class="input" id="ordType" name="type"><option value="takeaway">Takeaway</option><option value="dine_in">Dine-in</option></select></div></div>
    <div class="fg-block" id="tableRow" hidden><label>No. Meja</label><input class="input" name="table" maxlength="10"></div>
    <div class="fg-block"><label>Catatan</label><input class="input" name="notes" maxlength="500"></div>
    <div class="fg-block"><label>Metode Bayar</label><select class="input" id="payMethod" name="method"><?php foreach (PAYMENT_METHODS as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="fg-block" id="cashRow"><label>Uang diterima</label><input class="input" type="number" id="amountPaid" name="amount_paid" min="0" step="500" value="0">
      <div style="display:flex;gap:4px;margin-top:6px;flex-wrap:wrap"><button type="button" class="btn btn-sm" data-quick="pas">Uang pas</button><button type="button" class="btn btn-sm" data-quick="20000">20rb</button><button type="button" class="btn btn-sm" data-quick="50000">50rb</button><button type="button" class="btn btn-sm" data-quick="100000">100rb</button></div></div>
    <div class="fg-block" id="refRow" hidden><label>No. Referensi (simulasi)</label><input class="input" name="ref" maxlength="100"></div>
    <div class="change-box" id="changeBox"><span>Kembalian</span><b>Rp0</b></div>
    <button class="btn btn-success btn-block" id="btnPay" disabled>Bayar &amp; Cetak Struk</button>
    <button class="btn btn-block" style="margin-top:6px" name="pay_later" value="1" formnovalidate>Simpan (bayar nanti)</button>
  </div>
</form>
<?php cashier_end('<script>window.KK_POS=' . json_encode(['products' => $js, 'sizeExtra' => SIZE_PRICES, 'discountMin' => 100000, 'discountAmt' => 5000]) . ';</script><script src="' . asset('js/pos.js') . '"></script>'); ?>
