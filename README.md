# KopiKita – Coffee Shop Management System
*A Little Coffee, A Lot of Happiness.*

Website pemesanan & pengelolaan coffee shop: **HTML, CSS, JavaScript, PHP (PDO), MySQL**, dijalankan di **XAMPP**.
Tiga tampilan: **Customer Website**, **Admin Dashboard**, **Cashier System**.

## Instalasi (XAMPP)
1. Salin folder `kopikita` ke `C:\xampp\htdocs\kopikita`.
2. Buka **XAMPP Control Panel** → Start **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/kopikita_db.sql` → **Go**
   (otomatis membuat database `kopikita_db`, 9 tabel, dan data contoh).
4. Buka `http://localhost/kopikita/`.
5. Jika user/password MySQL berbeda, ubah `config/database.php`.

## Akun contoh
| Role | Username / Email | Password |
|---|---|---|
| Admin | `admin` / admin@kopikita.test | `admin123` |
| Kasir | `kasir` / kasir@kopikita.test | `kasir123` |
| Pelanggan | `pelanggan` / pelanggan@kopikita.test | `pelanggan123` |

## Fitur
- **Pelanggan**: registrasi/login, katalog + filter kategori + pencarian, detail produk (ukuran Regular/Large, catatan), keranjang, checkout (dine-in/takeaway), riwayat & timeline status, batal saat Pending, profil.
- **Admin**: dashboard (kartu ringkasan, grafik penjualan, pesanan terbaru, produk terlaris, stok menipis, filter tanggal, ekspor Excel/PDF), CRUD kategori & produk (upload foto, harga, stok), pesanan, pelanggan, laporan, galeri, newsletter, pengguna.
- **Kasir**: pesanan masuk (auto-refresh), ubah status, konfirmasi pembayaran + kembalian, POS walk-in, cetak struk, riwayat transaksi.
- **Alur status**: Pending → Confirmed → Preparing → Ready → Completed (atau Cancelled).

## Aturan penting
- Password di-hash (`password_hash`), query memakai PDO prepared statement, form dilindungi token CSRF, hak akses dicek di server (`require_role`).
- Harga, diskon (belanja ≥ Rp100.000 → potongan Rp5.000), dan total **dihitung ulang di PHP**.
- Pesanan + rincian + data penjualan disimpan dalam **satu transaksi database**. Stok dikurangi saat pesanan *Confirmed* (online) / saat transaksi POS disimpan, dan dikembalikan bila dibatalkan.
- Pesanan baru bisa *Completed* hanya jika pembayaran **Lunas**. Laporan hanya menghitung *Completed* + *Paid*.
- Pembayaran QRIS/transfer hanyalah **simulasi** (belum terhubung payment gateway).
- Ekspor PDF memakai halaman cetak (*Save as PDF*); ekspor Excel berupa file `.xls`.
- Grafik memakai Chart.js dari CDN (perlu internet; tanpa internet otomatis tampil grafik batang sederhana). Font Google Fonts juga dari CDN.

## Mengganti foto
Gambar bawaan adalah ilustrasi SVG di `assets/images/`. Ganti lewat Admin → Produk/Galeri (upload JPG/PNG/WEBP ≤ 2 MB) atau timpa file di folder tersebut dengan foto Anda sendiri (bebas lisensi/milik sendiri).

## Struktur folder
Sesuai `sitemap.md`, ditambah file bantu: `includes/panel.php` (layout admin/kasir), `includes/components.php` (kartu produk), `includes/order_view.php` (detail pesanan bersama), `customer/_order_lookup.php`.
