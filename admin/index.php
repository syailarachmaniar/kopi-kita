<?php
require_once __DIR__ . '/../includes/admin-header.php';
[$from, $to] = date_range();
$r = report_data($from, $to);
$pdo = db();
$st = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE DATE(ordered_at) BETWEEN ? AND ?'); $st->execute([$from, $to]); $orderCnt = (int)$st->fetchColumn();
$prodCnt = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_available = 1')->fetchColumn();
$custCnt = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE name <> 'Pelanggan Umum'")->fetchColumn();
$recent = $pdo->query('SELECT o.*, c.name customer_name FROM orders o JOIN customers c ON c.id = o.customer_id ORDER BY o.ordered_at DESC, o.id DESC LIMIT 8')->fetchAll();
$low = $pdo->query('SELECT p.name, p.stock, p.min_stock, c.name cat FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_available = 1 AND p.stock <= p.min_stock ORDER BY p.stock LIMIT 8')->fetchAll();
// isi hari kosong untuk grafik
$map = []; foreach ($r['daily'] as $d) $map[$d['tanggal']] = (float)$d['total_penjualan'];
$labels = []; $vals = [];
for ($t = strtotime($from), $n = 0; $t <= strtotime($to) && $n < 400; $t += 86400, $n++) { $k = date('Y-m-d', $t); $labels[] = date('d M', $t); $vals[] = $map[$k] ?? 0; }
admin_start('Dashboard', 'dashboard', '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>');
?>
<form class="page-actions filter" method="get">
  <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end">
    <div class="fg"><label>Dari tanggal</label><input class="input" type="date" name="from" value="<?= e($from) ?>"></div>
    <div class="fg"><label>Sampai tanggal</label><input class="input" type="date" name="to" value="<?= e($to) ?>"></div>
    <button class="btn btn-dark">Terapkan Filter</button>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a class="btn" href="<?= url('admin/reports/export-excel.php?from=' . $from . '&to=' . $to) ?>">⬇ Ekspor Excel</a>
    <a class="btn" target="_blank" href="<?= url('admin/reports/export-pdf.php?from=' . $from . '&to=' . $to) ?>">⬇ Ekspor PDF</a>
  </div>
</form>
<div class="grid g-4">
  <div class="card stat-card"><div class="stat-ico"><?= ico('chart') ?></div><div><span>Total Penjualan</span><b><?= rupiah($r['total']) ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('bag') ?></div><div><span>Jumlah Pesanan</span><b><?= $orderCnt ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('box') ?></div><div><span>Jumlah Produk</span><b><?= $prodCnt ?></b></div></div>
  <div class="card stat-card"><div class="stat-ico"><?= ico('users') ?></div><div><span>Jumlah Pelanggan</span><b><?= $custCnt ?></b></div></div>
</div>
<div class="grid g-21 mt">
  <div class="card"><h2>Grafik Penjualan <span class="muted small"><?= fmt_date($from, false) ?> – <?= fmt_date($to, false) ?> · <?= $r['count'] ?> transaksi</span></h2><div class="chart-box"><canvas id="salesChart"></canvas></div></div>
  <div class="card"><h2>Produk Paling Banyak Terjual</h2>
    <?php if ($r['top']): ?><table><tbody><?php foreach (array_slice($r['top'], 0, 6) as $i => $t): ?><tr><td><?= $i + 1 ?>.</td><td><b><?= e($t['product_name']) ?></b></td><td class="num"><?= (int)$t['jumlah_terjual'] ?> terjual</td></tr><?php endforeach; ?></tbody></table>
    <?php else: ?><div class="empty">Belum ada penjualan pada periode ini.</div><?php endif; ?></div>
</div>
<div class="grid g-21 mt">
  <div class="card"><h2>Pesanan Terbaru <a class="btn btn-sm" href="orders/index.php">Semua</a></h2><div class="table-wrap"><table>
    <thead><tr><th>Kode</th><th>Pelanggan</th><th>Status</th><th class="num">Total</th><th></th></tr></thead><tbody>
    <?php foreach ($recent as $o): ?><tr><td><b><?= e($o['order_code']) ?></b><div class="small muted"><?= fmt_date($o['ordered_at']) ?></div></td><td><?= e($o['customer_name']) ?></td><td><?= status_badge($o['order_status']) ?></td><td class="num"><?= rupiah($o['total_amount']) ?></td><td><a class="btn btn-sm" href="orders/show.php?id=<?= (int)$o['id'] ?>">Detail</a></td></tr><?php endforeach; ?></tbody></table></div></div>
  <div class="card"><h2>Stok Menipis <a class="btn btn-sm" href="products/index.php?low=1">Kelola</a></h2>
    <?php if ($low): ?><table><tbody><?php foreach ($low as $p): ?><tr><td><b><?= e($p['name']) ?></b><div class="small muted"><?= e($p['cat']) ?></div></td><td class="num"><span class="badge low">Sisa <?= (int)$p['stock'] ?></span><div class="small muted">min <?= (int)$p['min_stock'] ?></div></td></tr><?php endforeach; ?></tbody></table>
    <?php else: ?><div class="empty">Semua stok aman.</div><?php endif; ?></div>
</div>
<?php
admin_end('<script>window.KK_CHART=' . json_encode(['labels' => $labels, 'values' => $vals]) . ';</script><script src="' . asset('js/dashboard.js') . '" defer></script>');
