<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role(['admin']);
if (!is_post()) redirect('admin/products/index.php');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$st = db()->prepare('SELECT image, name FROM products WHERE id = ?'); $st->execute([$id]); $p = $st->fetch();
if (!$p) { flash('error', 'Produk tidak ditemukan.'); redirect('admin/products/index.php'); }
$u = db()->prepare('SELECT COUNT(*) FROM order_details WHERE product_id = ?'); $u->execute([$id]);
if ($u->fetchColumn() > 0) {
    db()->prepare('UPDATE products SET is_available = 0 WHERE id = ?')->execute([$id]);
    flash('info', '"' . $p['name'] . '" sudah ada di riwayat pesanan, jadi hanya dinonaktifkan.');
} else {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    delete_image($p['image'], 'products');
    flash('success', 'Produk dihapus.');
}
redirect('admin/products/index.php');
