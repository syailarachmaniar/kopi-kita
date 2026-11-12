<?php
require_once __DIR__ . '/../../includes/cashier-header.php';
require_once __DIR__ . '/../../includes/order_view.php';
$o = get_order((int)($_GET['id'] ?? 0));
if (!$o) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
order_handle_post($o, 'cashier/orders/show.php?id=' . (int)$o['id']);
cashier_start('Detail Pesanan', 'orders');
order_render($o);
cashier_end();
