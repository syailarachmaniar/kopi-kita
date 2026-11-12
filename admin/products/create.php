<?php
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/_form.php';
$p = ['name' => '', 'sku' => '', 'category_id' => 0, 'price' => '', 'stock' => 0, 'min_stock' => 10, 'description' => '', 'image' => null, 'has_size_option' => 1, 'is_best_seller' => 0, 'is_available' => 1]; $err = [];
if (is_post()) { csrf_check(); $err = product_save(null, $p); if (!$err) { flash('success', 'Produk ditambahkan.'); redirect('admin/products/index.php'); } }
admin_start('Tambah Produk', 'products'); product_form($p, $err, get_categories(false)); admin_end();
