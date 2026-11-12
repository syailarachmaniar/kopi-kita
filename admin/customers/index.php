<?php
require_once __DIR__ . '/../../includes/admin-header.php';
$q = trim((string)($_GET['q'] ?? '')); $per = 20; $page = current_page();
$w = $q !== '' ? 'WHERE c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?' : ''; $a = $q !== '' ? ["%$q%", "%$q%", "%$q%"] : [];
$cnt = db()->prepare("SELECT COUNT(*) FROM customers c $w"); $cnt->execute($a); $total = (int)$cnt->fetchColumn();
$st = db()->prepare("SELECT c.*, u.username, u.is_active, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) oc,
  (SELECT COALESCE(SUM(o.total_amount),0) FROM orders o JOIN sales s ON s.order_id = o.id WHERE o.customer_id = c.id AND o.order_status='completed' AND s.payment_status='paid') spent
  FROM customers c LEFT JOIN users u ON u.id = c.user_id $w ORDER BY c.created_at DESC, c.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($a); $rows = $st->fetchAll();
admin_start('Data Pelanggan', 'customers');
?>
<form class="page-actions filter" method="get"><div class="fg"><label>Cari</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Nama, telepon, email"></div><button class="btn btn-dark">Cari</button></form>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th>Nama</th><th>Kontak</th><th>Akun</th><th>Bergabung</th><th class="num">Pesanan</th><th class="num">Total Belanja</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $c): ?><tr><td><b><?= e($c['name']) ?></b></td><td><?= e($c['phone'] ?: '-') ?><div class="small muted"><?= e($c['email'] ?: '') ?></div></td><td><?= $c['username'] ? e($c['username']) : '<span class="muted">walk-in</span>' ?></td><td><?= fmt_date($c['created_at'], false) ?></td><td class="num"><?= (int)$c['oc'] ?></td><td class="num"><?= rupiah($c['spent']) ?></td><td><a class="btn btn-sm" href="show.php?id=<?= (int)$c['id'] ?>">Riwayat</a></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">Tidak ada pelanggan.</td></tr><?php endif; ?></tbody></table></div><?= pager($total, $per, $page, array_filter(['q' => $q])) ?></div>
<?php admin_end(); ?>
