<?php
/** Ambil pesanan milik pelanggan yang sedang login berdasarkan kode. Menampilkan 404 jika bukan miliknya. */
function my_order_by_code(string $code): array
{
    $st = db()->prepare('SELECT id FROM orders WHERE order_code = ? AND customer_id = ?');
    $st->execute([$code, current_customer_id()]);
    $id = $st->fetchColumn();
    $o = $id ? get_order((int)$id) : null;
    if (!$o) { http_response_code(404); include APP_ROOT . '/404.php'; exit; }
    return $o;
}
