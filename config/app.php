<?php
/**
 * Konfigurasi aplikasi KopiKita.
 */
date_default_timezone_set('Asia/Makassar');

define('APP_NAME', 'KopiKita');
define('APP_SLOGAN', 'A Little Coffee, A Lot of Happiness.');
define('APP_ROOT', dirname(__DIR__));

// BASE_URL dideteksi otomatis (contoh: /kopikita). Ubah manual bila perlu.
$__docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$__appRoot = realpath(APP_ROOT);
$__base = '';
if ($__docRoot && $__appRoot) {
    $__docRoot = str_replace('\\', '/', $__docRoot);
    $__appRoot = str_replace('\\', '/', $__appRoot);
    if (strpos($__appRoot, $__docRoot) === 0) {
        $__base = rtrim(substr($__appRoot, strlen($__docRoot)), '/');
    }
}
define('BASE_URL', $__base);

// Biaya tambahan ukuran minuman
const SIZE_PRICES = ['regular' => 0, 'large' => 5000];
const SIZE_LABELS = ['regular' => 'Regular', 'large' => 'Large'];

const ORDER_STATUSES = [
    'pending'   => ['Pending',   'Pesanan baru masuk'],
    'confirmed' => ['Confirmed', 'Pesanan dikonfirmasi'],
    'preparing' => ['Preparing', 'Sedang dibuat barista'],
    'ready'     => ['Ready',     'Siap disajikan'],
    'completed' => ['Completed', 'Pesanan selesai'],
    'cancelled' => ['Cancelled', 'Pesanan dibatalkan'],
];
// Urutan status yang diizinkan
const STATUS_FLOW = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['preparing', 'cancelled'],
    'preparing' => ['ready'],
    'ready'     => ['completed'],
    'completed' => [],
    'cancelled' => [],
];
const PAYMENT_METHODS = ['cash' => 'Cash di Kasir', 'qris' => 'QRIS (simulasi)', 'bank_transfer' => 'Transfer Bank (simulasi)'];
const GALLERY_COLLECTIONS = ['coffee' => 'Coffee', 'drinks' => 'Drinks', 'food' => 'Food', 'ambience' => 'Ambience', 'desserts' => 'Desserts'];

// Info kedai (simulasi)
const SHOP_ADDRESS = 'Jl. Kopi Nusantara No. 12, Makassar, Sulawesi Selatan';
const SHOP_PHONE = '0812-3456-7890';
const SHOP_WA = '6281234567890';
const SHOP_EMAIL = 'halo@kopikita.test';
const SHOP_HOURS = 'Setiap hari, 08.00 – 22.00 WITA';
