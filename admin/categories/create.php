<?php
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/_form.php';
$c = ['name' => '', 'slug' => '', 'description' => '', 'icon' => '', 'sort_order' => 0, 'is_active' => 1]; $err = [];
if (is_post()) { csrf_check(); $err = category_save(null, $c); if (!$err) { flash('success', 'Kategori ditambahkan.'); redirect('admin/categories/index.php'); } }
admin_start('Tambah Kategori', 'categories'); category_form($c, $err, ''); admin_end();
