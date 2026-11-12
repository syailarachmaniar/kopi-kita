<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role(['admin']);
[$from, $to] = date_range();
$r = report_data($from, $to);
?><!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Laporan Penjualan KopiKita <?= e($from) ?> - <?= e($to) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/print.css') ?>"></head><body>
<div class="noprint"><button onclick="window.print()">Cetak / Simpan sebagai PDF</button><a href="javascript:window.close()">Tutup</a><p style="font-size:12px;color:#666">Di dialog cetak, pilih tujuan <b>Save as PDF</b>.</p></div>
<div class="sheet">
  <div class="rep-head"><div><h1>KopiKita</h1><small><?= e(APP_SLOGAN) ?></small></div><div style="text-align:right"><b>Laporan Penjualan</b><br><small>Periode <?= fmt_date($from, false) ?> – <?= fmt_date($to, false) ?><br>Dicetak <?= date('d M Y H:i') ?></small></div></div>
  <div class="kpis"><div class="kpi">Total Penjualan<b><?= rupiah($r['total']) ?></b></div><div class="kpi">Jumlah Transaksi<b><?= $r['count'] ?></b></div><div class="kpi">Rata-rata / Transaksi<b><?= rupiah($r['count'] ? $r['total'] / $r['count'] : 0) ?></b></div></div>
  <h2>Penjualan per Hari</h2><table><tr><th>Tanggal</th><th class="num">Transaksi</th><th class="num">Penjualan</th></tr>
  <?php foreach ($r['daily'] as $d): ?><tr><td><?= fmt_date($d['tanggal'], false) ?></td><td class="num"><?= (int)$d['jumlah_transaksi'] ?></td><td class="num"><?= rupiah($d['total_penjualan']) ?></td></tr><?php endforeach; ?></table>
  <h2>Produk Terlaris</h2><table><tr><th>Produk</th><th class="num">Terjual</th><th class="num">Nilai</th></tr>
  <?php foreach ($r['top'] as $t): ?><tr><td><?= e($t['product_name']) ?></td><td class="num"><?= (int)$t['jumlah_terjual'] ?></td><td class="num"><?= rupiah($t['nilai_penjualan']) ?></td></tr><?php endforeach; ?></table>
  <h2>Daftar Transaksi</h2><table><tr><th>Invoice</th><th>Tanggal</th><th>Pelanggan</th><th>Metode</th><th class="num">Total</th></tr>
  <?php foreach ($r['transactions'] as $t): ?><tr><td><?= e($t['invoice_number']) ?></td><td><?= fmt_date($t['ordered_at']) ?></td><td><?= e($t['customer_name']) ?></td><td><?= e(PAYMENT_METHODS[$t['payment_method']] ?? '') ?></td><td class="num"><?= rupiah($t['total_amount']) ?></td></tr><?php endforeach; ?></table>
</div></body></html>
