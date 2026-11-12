<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/* ---------- Helper umum ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function asset(string $path): string { return url('assets/' . ltrim($path, '/')); }
function rupiah($n): string { return 'Rp' . number_format((float)$n, 0, ',', '.'); }
function redirect(string $path): void
{
    if (!preg_match('#^(https?:)?//#', $path) && strpos($path, BASE_URL . '/') !== 0 && $path !== BASE_URL) {
        $path = url($path);
    }
    header('Location: ' . $path);
    exit;
}
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'item';
}
function fmt_date($d, bool $time = true): string
{
    if (!$d) return '-';
    return date($time ? 'd M Y, H:i' : 'd M Y', strtotime($d));
}
function safe_back(string $fallback): string
{
    $b = $_POST['back'] ?? $_GET['back'] ?? '';
    if ($b !== '' && $b[0] === '/' && strpos($b, '//') !== 0 && strpos($b, "\n") === false) return $b;
    return $fallback;
}

/* ---------- Flash message ---------- */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function flash_render(): string
{
    if (empty($_SESSION['flash'])) return '';
    $h = '';
    foreach ($_SESSION['flash'] as $f) {
        $h .= '<div class="alert alert-' . e($f['type']) . '" role="alert">' . e($f['msg']) . '<button type="button" class="alert-x" aria-label="Tutup">&times;</button></div>';
    }
    unset($_SESSION['flash']);
    return '<div class="flash-wrap">' . $h . '</div>';
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau kedaluwarsa. Silakan kembali dan muat ulang halaman.');
    }
}
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }

/* ---------- Gambar ---------- */
function product_image(?string $img): string
{
    if ($img && is_file(APP_ROOT . '/assets/images/products/' . basename($img))) {
        return asset('images/products/' . rawurlencode(basename($img)));
    }
    return asset('images/ui/hero.svg');
}
function gallery_image(?string $img): string
{
    if ($img && is_file(APP_ROOT . '/assets/images/gallery/' . basename($img))) {
        return asset('images/gallery/' . rawurlencode(basename($img)));
    }
    return asset('images/ui/hero.svg');
}
/** Upload gambar (JPG/PNG/WEBP, maks 2 MB). Mengembalikan nama file atau null. Melempar RuntimeException bila gagal. */
function upload_image(array $file, string $subdir): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gambar gagal (kode ' . (int)$file['error'] . ').');
    if ($file['size'] > 2 * 1024 * 1024) throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($allowed[$info[2]])) throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP.');
    $dir = APP_ROOT . '/assets/images/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Gagal menyimpan gambar.');
    return $name;
}
function delete_image(?string $name, string $subdir): void
{
    if (!$name) return;
    $p = APP_ROOT . '/assets/images/' . $subdir . '/' . basename($name);
    // hanya hapus file hasil upload (nama acak), bukan gambar bawaan
    if (preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', basename($name)) && is_file($p)) @unlink($p);
}

/* ---------- Badge ---------- */
function status_badge(string $s): string
{
    $label = ORDER_STATUSES[$s][0] ?? $s;
    return '<span class="badge st-' . e($s) . '">' . e($label) . '</span>';
}
function pay_badge(?string $s): string
{
    $map = ['pending' => 'Belum Bayar', 'paid' => 'Lunas', 'failed' => 'Gagal', 'refunded' => 'Refund'];
    $s = $s ?: 'pending';
    return '<span class="badge pay-' . e($s) . '">' . e($map[$s] ?? $s) . '</span>';
}

/* ---------- Kategori & Produk ---------- */
function get_categories(bool $onlyActive = true): array
{
    $sql = 'SELECT * FROM categories' . ($onlyActive ? ' WHERE is_active = 1' : '') . ' ORDER BY sort_order, name';
    return db()->query($sql)->fetchAll();
}
function get_products(array $opt = []): array
{
    $where = ['c.is_active = 1', 'p.is_available = 1'];
    $args = [];
    if (!empty($opt['best'])) $where[] = 'p.is_best_seller = 1';
    if (!empty($opt['category'])) { $where[] = 'c.slug = ?'; $args[] = $opt['category']; }
    if (!empty($opt['q'])) { $where[] = 'p.name LIKE ?'; $args[] = '%' . $opt['q'] . '%'; }
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE '
        . implode(' AND ', $where) . ' ORDER BY c.sort_order, p.name' . (!empty($opt['limit']) ? ' LIMIT ' . (int)$opt['limit'] : '');
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}
function unit_price(array $product, ?string $size): float
{
    $extra = ($product['has_size_option'] && $size && isset(SIZE_PRICES[$size])) ? SIZE_PRICES[$size] : 0;
    return (float)$product['price'] + $extra;
}

/* ---------- Keranjang (session) ---------- */
function cart_get(): array { return $_SESSION['cart'] ?? []; }
function cart_count(): int { $n = 0; foreach (cart_get() as $i) $n += (int)$i['qty']; return $n; }
function cart_key(int $pid, ?string $size, string $note): string { return $pid . '|' . ($size ?: '-') . '|' . md5(mb_strtolower(trim($note))); }
function cart_add(int $pid, int $qty, ?string $size, string $note): void
{
    $qty = max(1, min(50, $qty));
    $note = mb_substr(trim($note), 0, 200);
    $size = in_array($size, ['regular', 'large'], true) ? $size : null;
    $k = cart_key($pid, $size, $note);
    if (isset($_SESSION['cart'][$k])) $_SESSION['cart'][$k]['qty'] = min(50, $_SESSION['cart'][$k]['qty'] + $qty);
    else $_SESSION['cart'][$k] = ['product_id' => $pid, 'qty' => $qty, 'size' => $size, 'note' => $note];
}
/** Hitung keranjang dari data database (harga tidak diambil dari browser). */
function cart_detail(array $items = null): array
{
    $items = $items ?? cart_get();
    $lines = []; $subtotal = 0.0;
    if (!$items) return ['lines' => [], 'subtotal' => 0.0, 'total' => 0.0, 'discount' => 0.0];
    $ids = array_values(array_unique(array_map(fn($i) => (int)$i["product_id"], $items)));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT p.*, c.is_active AS cat_active FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id IN ($in)");
    $st->execute($ids);
    $map = [];
    foreach ($st->fetchAll() as $p) $map[$p['id']] = $p;
    foreach ($items as $k => $it) {
        $p = $map[$it['product_id']] ?? null;
        if (!$p) continue;
        $size = $p['has_size_option'] ? ($it['size'] ?: 'regular') : null;
        $unit = unit_price($p, $size);
        $qty = (int)$it['qty'];
        $line = $unit * $qty;
        $subtotal += $line;
        $lines[] = ['key' => $k, 'product' => $p, 'size' => $size, 'note' => $it['note'] ?? '', 'qty' => $qty, 'unit_price' => $unit, 'line_total' => $line,
            'ok' => $p['is_available'] && $p['cat_active'] && $p['stock'] >= $qty];
    }
    $discount = calc_discount($subtotal);
    return ['lines' => $lines, 'subtotal' => $subtotal, 'discount' => $discount, 'total' => $subtotal - $discount];
}
/** Aturan diskon sederhana: belanja >= Rp100.000 mendapat potongan Rp5.000. */
function calc_discount(float $subtotal): float { return $subtotal >= 100000 ? 5000.0 : 0.0; }

/* ---------- Kode ---------- */
function next_code(string $table, string $col, string $prefix): string
{
    $day = $prefix . '-' . date('Ymd') . '-';
    $st = db()->prepare("SELECT MAX($col) FROM $table WHERE $col LIKE ?");
    $st->execute([$day . '%']);
    $max = $st->fetchColumn();
    $n = $max ? (int)substr($max, -4) + 1 : 1;
    return $day . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
}

/* ---------- Pesanan ---------- */
/**
 * Membuat pesanan + rincian + data penjualan dalam satu transaksi database.
 * $lines : hasil cart_detail()['lines'] (atau serupa). Harga dihitung ulang dari DB.
 * Opsi: channel, type, table, notes, method, customer_id, user_id (pemroses), paid_now (array amount_paid, ref) untuk POS.
 * Mengembalikan order id. Melempar RuntimeException bila stok tidak cukup.
 */
function create_order(array $lines, array $o): int
{
    if (!$lines) throw new RuntimeException('Keranjang kosong.');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $sub = 0.0; $rows = [];
        foreach ($lines as $l) {
            $st = $pdo->prepare('SELECT p.*, c.is_active AS cat_active FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ? FOR UPDATE');
            $st->execute([$l['product']['id']]);
            $p = $st->fetch();
            $qty = (int)$l['qty'];
            if (!$p || !$p['is_available'] || !$p['cat_active']) throw new RuntimeException('Produk "' . ($p['name'] ?? '?') . '" sedang tidak tersedia.');
            if ($p['stock'] < $qty) throw new RuntimeException('Stok "' . $p['name'] . '" tidak mencukupi (tersisa ' . $p['stock'] . ').');
            $size = $p['has_size_option'] ? ($l['size'] ?: 'regular') : null;
            $unit = unit_price($p, $size);
            $sub += $unit * $qty;
            $rows[] = [$p['id'], $p['name'], $size, $unit, $qty, $unit * $qty, $l['note'] ?: null];
        }
        $disc = calc_discount($sub);
        $total = $sub - $disc;
        $isPos = ($o['channel'] ?? 'online') === 'cashier';
        $type = in_array($o['type'] ?? '', ['dine_in', 'takeaway'], true) ? $o['type'] : 'takeaway';
        $table = $type === 'dine_in' ? mb_substr(trim((string)($o['table'] ?? '')), 0, 10) : null;

        // kode unik (ulangi bila bentrok)
        for ($try = 0; $try < 5; $try++) {
            $code = next_code('orders', 'order_code', 'KK');
            try {
                $pdo->prepare('INSERT INTO orders (order_code, customer_id, processed_by, order_channel, order_type, table_number, order_status, subtotal, discount_amount, total_amount, notes, stock_deducted, ordered_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())')
                    ->execute([$code, $o['customer_id'], $isPos ? ($o['user_id'] ?? null) : null, $isPos ? 'cashier' : 'online', $type, $table ?: null,
                        $isPos ? 'confirmed' : 'pending', $sub, $disc, $total, ($o['notes'] ?? '') !== '' ? mb_substr($o['notes'], 0, 500) : null, $isPos ? 1 : 0]);
                break;
            } catch (PDOException $ex) {
                if ($try === 4 || strpos($ex->getMessage(), 'Duplicate') === false) throw $ex;
            }
        }
        $orderId = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO order_details (order_id, product_id, product_name, size, unit_price, quantity, line_total, item_notes) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($rows as $r) {
            $ins->execute([$orderId, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]]);
            if ($isPos) $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$r[4], $r[0]]);
        }
        $method = array_key_exists($o['method'] ?? '', PAYMENT_METHODS) ? $o['method'] : 'cash';
        $inv = next_code('sales', 'invoice_number', 'INV');
        $paid = $o['paid_now'] ?? null;
        if ($paid) {
            $amt = (float)$paid['amount'];
            if ($method === 'cash' && $amt < $total) throw new RuntimeException('Uang yang diterima kurang dari total tagihan.');
            if ($method !== 'cash') $amt = $total;
            $pdo->prepare('INSERT INTO sales (order_id, invoice_number, cashier_id, payment_method, amount_due, amount_paid, change_amount, payment_status, reference_number, paid_at)
                VALUES (?,?,?,?,?,?,?,?,?,NOW())')
                ->execute([$orderId, $inv, $o['user_id'] ?? null, $method, $total, $amt, max(0, $amt - $total), 'paid', ($paid['ref'] ?? '') !== '' ? mb_substr($paid['ref'], 0, 100) : null]);
        } else {
            $pdo->prepare('INSERT INTO sales (order_id, invoice_number, payment_method, amount_due, payment_status) VALUES (?,?,?,?,?)')
                ->execute([$orderId, $inv, $method, $total, 'pending']);
        }
        $pdo->commit();
        return $orderId;
    } catch (Throwable $t) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $t;
    }
}

/** Ubah status pesanan sesuai alur. Mengurus stok otomatis. Mengembalikan pesan error atau null jika sukses. */
function change_order_status(int $orderId, string $new, ?int $userId, string $reason = ''): ?string
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT o.*, s.payment_status FROM orders o LEFT JOIN sales s ON s.order_id = o.id WHERE o.id = ? FOR UPDATE');
        $st->execute([$orderId]);
        $o = $st->fetch();
        if (!$o) { $pdo->rollBack(); return 'Pesanan tidak ditemukan.'; }
        if (!in_array($new, STATUS_FLOW[$o['order_status']] ?? [], true)) { $pdo->rollBack(); return 'Perubahan status tidak diizinkan dari ' . $o['order_status'] . ' ke ' . $new . '.'; }
        if ($new === 'completed' && $o['payment_status'] !== 'paid') { $pdo->rollBack(); return 'Pesanan hanya bisa diselesaikan setelah pembayaran lunas.'; }
        if ($new === 'cancelled' && trim($reason) === '') { $pdo->rollBack(); return 'Alasan pembatalan wajib diisi.'; }

        $det = $pdo->prepare('SELECT product_id, quantity FROM order_details WHERE order_id = ?');
        $det->execute([$orderId]);
        $items = $det->fetchAll();

        if ($new === 'confirmed' && !$o['stock_deducted']) {
            foreach ($items as $it) {
                $chk = $pdo->prepare('SELECT stock, name FROM products WHERE id = ? FOR UPDATE');
                $chk->execute([$it['product_id']]);
                $p = $chk->fetch();
                if (!$p || $p['stock'] < $it['quantity']) { $pdo->rollBack(); return 'Stok "' . ($p['name'] ?? '?') . '" tidak mencukupi untuk mengonfirmasi pesanan ini.'; }
            }
            foreach ($items as $it) $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$it['quantity'], $it['product_id']]);
            $pdo->prepare('UPDATE orders SET stock_deducted = 1 WHERE id = ?')->execute([$orderId]);
        }
        if ($new === 'cancelled') {
            if ($o['stock_deducted']) {
                foreach ($items as $it) $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$it['quantity'], $it['product_id']]);
                $pdo->prepare('UPDATE orders SET stock_deducted = 0 WHERE id = ?')->execute([$orderId]);
            }
            if ($o['payment_status'] === 'paid') $pdo->prepare("UPDATE sales SET payment_status = 'refunded' WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare('UPDATE orders SET cancel_reason = ? WHERE id = ?')->execute([mb_substr(trim($reason), 0, 255), $orderId]);
        }
        $pdo->prepare('UPDATE orders SET order_status = ?, processed_by = COALESCE(?, processed_by), completed_at = ? WHERE id = ?')
            ->execute([$new, $userId, $new === 'completed' ? date('Y-m-d H:i:s') : null, $orderId]);
        $pdo->commit();
        return null;
    } catch (Throwable $t) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return 'Terjadi kesalahan: ' . $t->getMessage();
    }
}

/** Konfirmasi pembayaran (kasir/admin). Mengembalikan pesan error atau null. */
function confirm_payment(int $orderId, int $userId, string $method, float $amountPaid, string $ref = ''): ?string
{
    if (!isset(PAYMENT_METHODS[$method])) return 'Metode pembayaran tidak valid.';
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT o.order_status, o.total_amount, s.id AS sale_id, s.payment_status FROM orders o JOIN sales s ON s.order_id = o.id WHERE o.id = ? FOR UPDATE');
        $st->execute([$orderId]);
        $r = $st->fetch();
        if (!$r) { $pdo->rollBack(); return 'Pesanan tidak ditemukan.'; }
        if ($r['order_status'] === 'cancelled') { $pdo->rollBack(); return 'Pesanan sudah dibatalkan.'; }
        if ($r['payment_status'] === 'paid') { $pdo->rollBack(); return 'Pembayaran sudah dikonfirmasi sebelumnya.'; }
        $due = (float)$r['total_amount'];
        if ($method === 'cash') {
            if ($amountPaid < $due) { $pdo->rollBack(); return 'Uang yang diterima kurang dari total tagihan (' . rupiah($due) . ').'; }
        } else {
            $amountPaid = $due;
            if (trim($ref) === '') { $pdo->rollBack(); return 'Nomor referensi wajib diisi untuk QRIS/transfer.'; }
        }
        $pdo->prepare("UPDATE sales SET payment_method = ?, amount_due = ?, amount_paid = ?, change_amount = ?, payment_status = 'paid', reference_number = ?, cashier_id = ?, paid_at = NOW() WHERE id = ?")
            ->execute([$method, $due, $amountPaid, max(0, $amountPaid - $due), trim($ref) !== '' ? mb_substr(trim($ref), 0, 100) : null, $userId, $r['sale_id']]);
        $pdo->commit();
        return null;
    } catch (Throwable $t) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return 'Terjadi kesalahan: ' . $t->getMessage();
    }
}

function get_order(int $id): ?array
{
    $st = db()->prepare('SELECT o.*, c.name AS customer_name, c.phone AS customer_phone, c.user_id AS customer_user_id, u.name AS processed_name,
        s.id AS sale_id, s.invoice_number, s.payment_method, s.amount_due, s.amount_paid, s.change_amount, s.payment_status, s.reference_number, s.paid_at, cu.name AS cashier_name
        FROM orders o JOIN customers c ON c.id = o.customer_id LEFT JOIN users u ON u.id = o.processed_by
        LEFT JOIN sales s ON s.order_id = o.id LEFT JOIN users cu ON cu.id = s.cashier_id WHERE o.id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}
function get_order_items(int $id): array
{
    $st = db()->prepare('SELECT * FROM order_details WHERE order_id = ? ORDER BY id');
    $st->execute([$id]);
    return $st->fetchAll();
}

/* ---------- Laporan ---------- */
function date_range(): array
{
    $from = $_GET['from'] ?? date('Y-m-d', strtotime('-13 days'));
    $to = $_GET['to'] ?? date('Y-m-d');
    $ok = fn($d) => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
    if (!$ok($from)) $from = date('Y-m-d', strtotime('-13 days'));
    if (!$ok($to)) $to = date('Y-m-d');
    if ($from > $to) [$from, $to] = [$to, $from];
    return [$from, $to];
}
function report_data(string $from, string $to): array
{
    $pdo = db();
    $base = "FROM orders o JOIN sales s ON s.order_id = o.id WHERE o.order_status = 'completed' AND s.payment_status = 'paid' AND DATE(o.ordered_at) BETWEEN :f AND :t";
    $a = [':f' => $from, ':t' => $to];
    $st = $pdo->prepare("SELECT COALESCE(SUM(o.total_amount),0) total, COUNT(*) cnt $base"); $st->execute($a);
    $sum = $st->fetch();
    $st = $pdo->prepare("SELECT DATE(o.ordered_at) tanggal, COUNT(*) jumlah_transaksi, SUM(o.total_amount) total_penjualan $base GROUP BY DATE(o.ordered_at) ORDER BY tanggal"); $st->execute($a);
    $daily = $st->fetchAll();
    $st = $pdo->prepare("SELECT od.product_name, SUM(od.quantity) jumlah_terjual, SUM(od.line_total) nilai_penjualan FROM order_details od JOIN orders o ON o.id = od.order_id JOIN sales s ON s.order_id = o.id
        WHERE o.order_status = 'completed' AND s.payment_status = 'paid' AND DATE(o.ordered_at) BETWEEN :f AND :t GROUP BY od.product_id, od.product_name ORDER BY jumlah_terjual DESC, nilai_penjualan DESC LIMIT 10"); $st->execute($a);
    $top = $st->fetchAll();
    $st = $pdo->prepare("SELECT o.order_code, o.ordered_at, c.name customer_name, o.order_channel, s.payment_method, o.total_amount, s.invoice_number FROM orders o JOIN sales s ON s.order_id = o.id JOIN customers c ON c.id = o.customer_id
        WHERE o.order_status = 'completed' AND s.payment_status = 'paid' AND DATE(o.ordered_at) BETWEEN :f AND :t ORDER BY o.ordered_at DESC"); $st->execute($a);
    $tx = $st->fetchAll();
    return ['total' => (float)$sum['total'], 'count' => (int)$sum['cnt'], 'daily' => $daily, 'top' => $top, 'transactions' => $tx];
}
