<?php
// Halaman diagnosa foto (khusus admin). Hapus file ini bila sudah tidak diperlukan.
require_once __DIR__ . '/includes/functions.php';
require_role('admin');
$ph = asset('images/ui/hero.svg');
$rows = db()->query('SELECT p.name, p.image, c.name cat FROM products p JOIN categories c ON c.id=p.category_id ORDER BY c.sort_order, p.name')->fetchAll();
$gal = db()->query('SELECT title, image FROM gallery_images ORDER BY id')->fetchAll();
$n = count(image_index()['path']);
?><!doctype html><meta charset="utf-8"><title>Cek Gambar</title>
<style>body{font:14px sans-serif;background:#1b100b;color:#f3e6d8;padding:20px}table{border-collapse:collapse}td,th{border:1px solid #5a3b2b;padding:5px 9px}.ok{color:#7fd67f}.no{color:#ff8a80}img{height:40px}</style>
<h2>Cek Gambar KopiKita</h2>
<p>Folder dibaca: <code><?= e(APP_ROOT . '/assets/img') ?></code> &mdash; ditemukan <b><?= $n ?></b> file foto.</p>
<?php if ($n === 0): ?><p class="no">Tidak ada foto di folder itu. Salin folder Product dan Gallery ke dalam assets/img.</p><?php endif; ?>
<h3>Produk</h3><table><tr><th>Kategori</th><th>Menu</th><th>File di database</th><th>Status</th><th>Foto</th></tr>
<?php foreach ($rows as $r): $u = product_image($r['image']); $ok = $u !== $ph; ?>
<tr><td><?= e($r['cat']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['image']) ?></td><td class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? 'OK' : 'TIDAK KETEMU' ?></td><td><img src="<?= e($u) ?>"></td></tr>
<?php endforeach; ?></table>
<h3>Galeri</h3><table><tr><th>Judul</th><th>File</th><th>Status</th><th>Foto</th></tr>
<?php foreach ($gal as $r): $u = gallery_image($r['image']); $ok = $u !== $ph; ?>
<tr><td><?= e($r['title']) ?></td><td><?= e($r['image']) ?></td><td class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? 'OK' : 'TIDAK KETEMU' ?></td><td><img src="<?= e($u) ?>"></td></tr>
<?php endforeach; ?></table>
