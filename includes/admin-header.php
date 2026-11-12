<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/panel.php';
require_role(['admin']);
function admin_start(string $title, string $active, string $extraHead = ''): void
{
    $nav = [
        'dashboard' => ['Dashboard', 'admin/index.php', ico('home')],
        'categories' => ['Kategori', 'admin/categories/index.php', ico('tag')],
        'products' => ['Produk & Stok', 'admin/products/index.php', ico('box')],
        'orders' => ['Pesanan', 'admin/orders/index.php', ico('bag')],
        'customers' => ['Pelanggan', 'admin/customers/index.php', ico('users')],
        'reports' => ['Laporan Penjualan', 'admin/reports/index.php', ico('chart')],
        'gallery' => ['Galeri', 'admin/gallery/index.php', ico('img')],
        'newsletter' => ['Newsletter', 'admin/newsletter/index.php', ico('mail')],
        'users' => ['Pengguna', 'admin/users/index.php', ico('user')],
        'cashier' => ['Area Kasir', 'cashier/index.php', ico('pos')],
    ];
    panel_start($title, $active, $nav, 'Admin', $extraHead);
}
function admin_end(string $extra = ''): void { panel_end($extra); }
