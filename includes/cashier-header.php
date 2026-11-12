<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/panel.php';
require_role(['cashier', 'admin']);
function cashier_start(string $title, string $active, string $extraHead = ''): void
{
    $nav = [
        'dashboard' => ['Dashboard Kasir', 'cashier/index.php', ico('home')],
        'pos' => ['Transaksi Baru (POS)', 'cashier/pos.php', ico('pos')],
        'orders' => ['Pesanan Masuk', 'cashier/orders/index.php', ico('bag')],
        'transactions' => ['Riwayat Transaksi', 'cashier/transactions/index.php', ico('list')],
    ];
    if (user_role() === 'admin') $nav['admin'] = ['Kembali ke Admin', 'admin/index.php', ico('chart')];
    panel_start($title, $active, $nav, user_role() === 'admin' ? 'Admin (Kasir)' : 'Kasir', $extraHead);
}
function cashier_end(string $extra = ''): void { panel_end($extra); }
