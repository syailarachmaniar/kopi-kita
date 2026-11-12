<?php
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../includes/order_view.php';
$o = get_order((int)($_GET['id'] ?? 0));
if (!$o) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
order_handle_post($o, 'admin/orders/show.php?id=' . (int)$o['id']);
admin_start('Detail Pesanan', 'orders');
order_render($o);
admin_end();
