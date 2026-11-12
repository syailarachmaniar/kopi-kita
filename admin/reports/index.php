<?php
require_once __DIR__ . '/../../includes/admin-header.php';
[$from, $to] = date_range();
$r = report_data($from, $to);
$map = []; foreach ($r['daily'] as $d) $map[$d['tanggal']] = (float)$d['total_penjualan'];
$labels = []; $vals = [];
for ($t = strtotime($from), $n = 0; $t <= strtotime($to) && $n < 400; $t += 86400, $n++) { $k = date('Y-m-d', $t); $labels[] = date('d M', $t); $vals[] = $map[$k] ?? 0; }
$avg = $r['count'] ? $r['total'] / $r['count'] : 0;
admin_start('Laporan Penjualan', 'reports', '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>');
?>
<form class="page-actions filter" method="get">
  <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end">
    <div class="fg"><label>Dari tanggal</label><input class="input" type="date" name="from" value="<?= e($from) ?>"></div>
    <div class="fg"><label>Sampai tanggal</label><input class="input" type="date" name="to" value="<?= e($to) ?>"></div>
    <button class="btn btn-dark">Tampilkan</button>
    <a class="btn" href="?from=<?= date('Y-m-d') ?>&to=<?= date('Y-m-d') ?>">Hari ini</a>
    <a class="btn" href="?from=<?= date('Y-m-d', strtotime('-6 days')) ?>&to=<?= date('Y-m-d') ?>">7 hari</a>
    <a class="btn" href="?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>">Bulan ini</a>
  </div>
  <div style="display:flex;gap:.5rem">
    <a class="btn btn-primary" href="export-excel.php?from=<?= $from ?>&to=<?= $to ?>">⬇ Ekspor Excel</a>
    <a class="btn btn-dark" target="_blank" href="export-pdf.php?from=<?= $from ?>&to=<?= $to ?>">⬇ Ekspor PDF</a>
  </div>
</form>
<p class="muted small">Laporan hanya menghitung pesanan <b>Completed</b> dengan pembayaran <b>Lunas</b>.</p>
<div class="grid g-3">
  <div class="card stat-card"><div class="stat-ico"><?= ico('chart') ?></div><div><span>Total Penjualan</span><b><?= rupiah($r['total']) ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('bag') ?></div><div><span>Jumlah Transaksi</span><b><?= $r['count'] ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('tag') ?></div><div><span>Rata-rata / Transaksi</span><b><?= rupiah($avg) ?></b></div></div>
</div>
<div class="card mt"><h2>Penjualan per Hari</h2><div class="chart-box"><canvas id="salesChart"></canvas></div></div>
<div class="grid g-2 mt">
  <div class="card"><h2>Produk Terlaris</h2><table><thead><tr><th>Produk</th><th class="num">Terjual</th><th class="num">Nilai</th></tr></thead><tbody>
    <?php foreach ($r['top'] as $t): ?><tr><td><?= e($t['product_name']) ?></td><td class="num"><?= (int)$t['jumlah_terjual'] ?></td><td class="num"><?= rupiah($t['nilai_penjualan']) ?></td></tr><?php endforeach; ?>
    <?php if (!$r['top']): ?><tr><td colspan="3" class="empty">Tidak ada data.</td></tr><?php endif; ?></tbody></table></div>
  <div class="card"><h2>Ringkasan Harian</h2><table><thead><tr><th>Tanggal</th><th class="num">Transaksi</th><th class="num">Penjualan</th></tr></thead><tbody>
    <?php foreach (array_reverse($r['daily']) as $d): ?><tr><td><?= fmt_date($d['tanggal'], false) ?></td><td class="num"><?= (int)$d['jumlah_transaksi'] ?></td><td class="num"><?= rupiah($d['total_penjualan']) ?></td></tr><?php endforeach; ?>
    <?php if (!$r['daily']): ?><tr><td colspan="3" class="empty">Tidak ada data.</td></tr><?php endif; ?></tbody></table></div>
</div>
<div class="card mt"><h2>Daftar Transaksi</h2><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Kode Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Metode</th><th class="num">Total</th></tr></thead><tbody>
  <?php foreach ($r['transactions'] as $t): ?><tr><td><?= e($t['invoice_number']) ?></td><td><?= e($t['order_code']) ?></td><td><?= fmt_date($t['ordered_at']) ?></td><td><?= e($t['customer_name']) ?></td><td><?= e(PAYMENT_METHODS[$t['payment_method']] ?? $t['payment_method']) ?></td><td class="num"><?= rupiah($t['total_amount']) ?></td></tr><?php endforeach; ?>
  <?php if (!$r['transactions']): ?><tr><td colspan="6" class="empty">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?></tbody></table></div></div>
<?php admin_end('<script>window.KK_CHART=' . json_encode(['labels' => $labels, 'values' => $vals]) . ';</script><script src="' . asset('js/dashboard.js') . '" defer></script>'); ?>
