<?php
/** Tampilan & aksi detail pesanan bersama untuk admin dan kasir. */
function order_handle_post(array $o, string $selfUrl): void
{
    if (!is_post()) return;
    csrf_check();
    $uid = (int)current_user()['id'];
    $act = $_POST['action'] ?? '';
    if ($act === 'status') {
        $err = change_order_status((int)$o['id'], (string)($_POST['new_status'] ?? ''), $uid, (string)($_POST['reason'] ?? ''));
        flash($err ? 'error' : 'success', $err ?: 'Status pesanan diperbarui.');
    } elseif ($act === 'pay') {
        $err = confirm_payment((int)$o['id'], $uid, (string)($_POST['method'] ?? ''), (float)str_replace(['.', ','], ['', '.'], (string)($_POST['amount_paid'] ?? '0')), (string)($_POST['ref'] ?? ''));
        flash($err ? 'error' : 'success', $err ?: 'Pembayaran dikonfirmasi.');
    }
    redirect($selfUrl);
}
function order_render(array $o): void
{
    $items = get_order_items((int)$o['id']);
    $flow = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
    $idx = array_search($o['order_status'], $flow, true);
    $next = array_values(array_filter(STATUS_FLOW[$o['order_status']] ?? [], fn($s) => $s !== 'cancelled'));
    $canCancel = in_array('cancelled', STATUS_FLOW[$o['order_status']] ?? [], true);
    $paid = $o['payment_status'] === 'paid';
    ?>
    <div class="page-actions"><a class="btn" href="javascript:history.back()">← Kembali</a>
      <?php if ($paid): ?><a class="btn btn-dark" target="_blank" href="<?= url('cashier/receipt.php?id=' . (int)$o['id']) ?>">Cetak Struk</a><?php endif; ?></div>
    <div class="detail-grid">
      <div>
        <div class="card">
          <h2><span><?= e($o['order_code']) ?></span><span><?= status_badge($o['order_status']) ?> <?= pay_badge($o['payment_status']) ?></span></h2>
          <div class="table-wrap"><table>
            <thead><tr><th>Produk</th><th>Ukuran</th><th class="num">Harga</th><th class="num">Jml</th><th class="num">Total</th></tr></thead><tbody>
            <?php foreach ($items as $it): ?><tr><td><b><?= e($it['product_name']) ?></b><?= $it['item_notes'] ? '<div class="small muted">Catatan: ' . e($it['item_notes']) . '</div>' : '' ?></td><td><?= $it['size'] ? e(SIZE_LABELS[$it['size']]) : '-' ?></td><td class="num"><?= rupiah($it['unit_price']) ?></td><td class="num"><?= (int)$it['quantity'] ?></td><td class="num"><?= rupiah($it['line_total']) ?></td></tr><?php endforeach; ?>
            <tr><td colspan="4" class="num">Subtotal</td><td class="num"><?= rupiah($o['subtotal']) ?></td></tr>
            <tr><td colspan="4" class="num">Diskon</td><td class="num">-<?= rupiah($o['discount_amount']) ?></td></tr>
            <tr class="total-row"><td colspan="4" class="num">Total</td><td class="num"><?= rupiah($o['total_amount']) ?></td></tr></tbody></table></div>
        </div>
        <div class="card mt"><h2>Informasi</h2>
          <dl class="kv">
            <dt>Pelanggan</dt><dd><?= e($o['customer_name']) ?><?= $o['customer_phone'] ? ' · ' . e($o['customer_phone']) : '' ?></dd>
            <dt>Channel</dt><dd><?= $o['order_channel'] === 'cashier' ? 'Kasir (walk-in)' : 'Online' ?></dd>
            <dt>Jenis</dt><dd><?= $o['order_type'] === 'dine_in' ? 'Dine-in' . ($o['table_number'] ? ' · Meja ' . e($o['table_number']) : '') : 'Takeaway' ?></dd>
            <dt>Dipesan</dt><dd><?= fmt_date($o['ordered_at']) ?></dd>
            <dt>Selesai</dt><dd><?= fmt_date($o['completed_at']) ?></dd>
            <dt>Catatan</dt><dd><?= e($o['notes'] ?: '-') ?></dd>
            <dt>Diproses oleh</dt><dd><?= e($o['processed_name'] ?: '-') ?></dd>
            <?php if ($o['order_status'] === 'cancelled'): ?><dt>Alasan batal</dt><dd><?= e($o['cancel_reason']) ?></dd><?php endif; ?>
            <dt>Invoice</dt><dd><?= e($o['invoice_number'] ?? '-') ?></dd>
            <dt>Metode bayar</dt><dd><?= e(PAYMENT_METHODS[$o['payment_method']] ?? '-') ?></dd>
            <?php if ($paid): ?><dt>Dibayar</dt><dd><?= rupiah($o['amount_paid']) ?> · kembalian <?= rupiah($o['change_amount']) ?></dd><dt>Dikonfirmasi</dt><dd><?= e($o['cashier_name'] ?: '-') ?> · <?= fmt_date($o['paid_at']) ?></dd><?php if ($o['reference_number']): ?><dt>Referensi</dt><dd><?= e($o['reference_number']) ?></dd><?php endif; endif; ?>
          </dl>
        </div>
      </div>
      <div>
        <div class="card"><h2>Status Pesanan</h2>
          <?php if ($o['order_status'] !== 'cancelled'): ?>
          <div class="stepper"><?php foreach ($flow as $i => $s): ?><span class="step <?= $i < $idx ? 'done' : ($i === $idx ? 'now' : '') ?>"><?= e(ORDER_STATUSES[$s][0]) ?></span><?php endforeach; ?></div>
          <?php endif; ?>
          <?php foreach ($next as $s): $blocked = $s === 'completed' && !$paid; ?>
            <form method="post" style="margin-bottom:8px"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="new_status" value="<?= e($s) ?>">
              <button class="btn btn-primary btn-block" <?= $blocked ? 'disabled' : '' ?>>Tandai <?= e(ORDER_STATUSES[$s][0]) ?> – <?= e(ORDER_STATUSES[$s][1]) ?></button>
              <?php if ($blocked): ?><div class="hint">Konfirmasi pembayaran lebih dulu.</div><?php endif; ?></form>
          <?php endforeach; ?>
          <?php if (!$next && !$canCancel): ?><p class="muted small">Tidak ada aksi lanjutan.</p><?php endif; ?>
          <?php if ($canCancel): ?>
            <form method="post" data-confirm="Batalkan pesanan ini?"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="new_status" value="cancelled">
              <div class="fg-block"><label for="reason">Alasan pembatalan</label><input class="input" id="reason" name="reason" required maxlength="255" placeholder="Contoh: stok habis"></div>
              <button class="btn btn-danger btn-block">Batalkan Pesanan</button></form>
          <?php endif; ?>
        </div>
        <?php if (!$paid && $o['order_status'] !== 'cancelled'): ?>
        <div class="card mt"><h2>Konfirmasi Pembayaran</h2>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="pay">
            <p class="sum total" style="display:flex;justify-content:space-between"><span>Tagihan</span><b><?= rupiah($o['total_amount']) ?></b></p>
            <div class="fg-block"><label for="method">Metode</label><select class="input" id="method" name="method" onchange="document.getElementById('cashF').hidden=this.value!=='cash';document.getElementById('refF').hidden=this.value==='cash'">
              <?php foreach (PAYMENT_METHODS as $k => $lab): ?><option value="<?= e($k) ?>" <?= $o['payment_method'] === $k ? 'selected' : '' ?>><?= e($lab) ?></option><?php endforeach; ?></select></div>
            <div class="fg-block" id="cashF" <?= $o['payment_method'] !== 'cash' ? 'hidden' : '' ?>><label for="ap">Uang diterima</label><input class="input" id="ap" name="amount_paid" type="number" min="0" step="500" value="<?= (int)$o['total_amount'] ?>"></div>
            <div class="fg-block" id="refF" <?= $o['payment_method'] === 'cash' ? 'hidden' : '' ?>><label for="rf">No. referensi (QRIS/transfer – simulasi)</label><input class="input" id="rf" name="ref" maxlength="100"></div>
            <button class="btn btn-success btn-block">Tandai Lunas</button>
          </form></div>
        <?php endif; ?>
      </div>
    </div>
    <?php
}
