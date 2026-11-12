<?php
require_once __DIR__ . '/../includes/auth.php';
if (!is_post()) redirect('menu.php');
csrf_check();
$back = safe_back(url('menu.php'));
if (!is_logged_in()) {
    flash('info', 'Silakan login atau registrasi dulu untuk memesan.');
    redirect('login.php?next=' . urlencode($back));
}
if (user_role() !== 'customer') {
    flash('error', 'Pemesanan online hanya untuk akun pelanggan. Gunakan menu POS untuk transaksi kasir.');
    redirect($back);
}
$action = $_POST['action'] ?? '';
$pid = (int)($_POST['product_id'] ?? 0);

if ($action === 'add') {
    $st = db()->prepare('SELECT p.* FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ? AND p.is_available = 1 AND c.is_active = 1');
    $st->execute([$pid]);
    $p = $st->fetch();
    if (!$p) { flash('error', 'Produk tidak tersedia.'); redirect($back); }
    if ($p['stock'] <= 0) { flash('error', $p['name'] . ' sedang habis.'); redirect($back); }
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    cart_add($pid, min($qty, (int)$p['stock']), $p['has_size_option'] ? ($_POST['size'] ?? 'regular') : null, (string)($_POST['note'] ?? ''));
    flash('success', $p['name'] . ' ditambahkan ke keranjang.');
    redirect($back);
}
$key = (string)($_POST['key'] ?? '');
if ($action === 'update' && isset($_SESSION['cart'][$key])) {
    $old = $_SESSION['cart'][$key];
    unset($_SESSION['cart'][$key]);
    $qty = (int)($_POST['qty'] ?? 0);
    if ($qty > 0) cart_add((int)$old['product_id'], $qty, $_POST['size'] ?? $old['size'], (string)($_POST['note'] ?? $old['note']));
    redirect('customer/cart.php');
}
if ($action === 'remove') { unset($_SESSION['cart'][$key]); flash('info', 'Item dihapus dari keranjang.'); redirect('customer/cart.php'); }
if ($action === 'clear') { $_SESSION['cart'] = []; redirect('customer/cart.php'); }
redirect('customer/cart.php');
