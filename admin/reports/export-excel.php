<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role(['admin']);
[$from, $to] = date_range();
$r = report_data($from, $to);
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="laporan-penjualan-kopikita_' . $from . '_sd_' . $to . '.xls"');
echo "\xEF\xBB\xBF";
$n = fn($v) => number_format((float)$v, 0, '', '');
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"><style>th{background:#F3E6D3;font-weight:bold}td,th{border:1px solid #999;padding:3px}</style></head><body>
<h2>Laporan Penjualan KopiKita</h2>
<p>Periode: <?= e($from) ?> s/d <?= e($to) ?> &middot; Dicetak: <?= date('d-m-Y H:i') ?></p>
<table><tr><th>Total Penjualan (Rp)</th><th>Jumlah Transaksi</th></tr><tr><td><?= $n($r['total']) ?></td><td><?= $r['count'] ?></td></tr></table><br>
<h3>Penjualan per Hari</h3>
<table><tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Penjualan (Rp)</th></tr>
<?php foreach ($r['daily'] as $d): ?><tr><td><?= e($d['tanggal']) ?></td><td><?= (int)$d['jumlah_transaksi'] ?></td><td><?= $n($d['total_penjualan']) ?></td></tr><?php endforeach; ?></table><br>
<h3>Produk Terlaris</h3>
<table><tr><th>Produk</th><th>Jumlah Terjual</th><th>Nilai Penjualan (Rp)</th></tr>
<?php foreach ($r['top'] as $t): ?><tr><td><?= e($t['product_name']) ?></td><td><?= (int)$t['jumlah_terjual'] ?></td><td><?= $n($t['nilai_penjualan']) ?></td></tr><?php endforeach; ?></table><br>
<h3>Daftar Transaksi</h3>
<table><tr><th>Invoice</th><th>Kode Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Channel</th><th>Metode</th><th>Total (Rp)</th></tr>
<?php foreach ($r['transactions'] as $t): ?><tr><td><?= e($t['invoice_number']) ?></td><td><?= e($t['order_code']) ?></td><td><?= e($t['ordered_at']) ?></td><td><?= e($t['customer_name']) ?></td><td><?= e($t['order_channel']) ?></td><td><?= e($t['payment_method']) ?></td><td><?= $n($t['total_amount']) ?></td></tr><?php endforeach; ?></table>
</body></html>
