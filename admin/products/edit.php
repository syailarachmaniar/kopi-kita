<?php
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/_form.php';
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM products WHERE id = ?'); $st->execute([$id]); $p = $st->fetch();
if (!$p) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
$err = [];
if (is_post()) { csrf_check(); $img = $p['image']; $err = product_save($id, $p); $p['image'] = $img; if (!$err) { flash('success', 'Produk diperbarui.'); redirect('admin/products/index.php'); } }
admin_start('Edit Produk', 'products'); product_form($p, $err, get_categories(false)); admin_end();
