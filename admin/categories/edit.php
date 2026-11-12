<?php
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/_form.php';
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM categories WHERE id = ?'); $st->execute([$id]); $c = $st->fetch();
if (!$c) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
$err = [];
if (is_post()) { csrf_check(); $err = category_save($id, $c); if (!$err) { flash('success', 'Kategori diperbarui.'); redirect('admin/categories/index.php'); } }
admin_start('Edit Kategori', 'categories'); category_form($c, $err, ''); admin_end();
