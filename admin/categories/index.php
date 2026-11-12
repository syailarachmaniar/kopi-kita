<?php
require_once __DIR__ . '/../../includes/admin-header.php';
if (is_post()) {
    csrf_check(); $id = (int)($_POST['id'] ?? 0); $act = $_POST['action'] ?? '';
    if ($act === 'toggle') { db()->prepare('UPDATE categories SET is_active = 1 - is_active WHERE id = ?')->execute([$id]); flash('success', 'Status kategori diubah.'); }
    if ($act === 'delete') {
        $n = db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?'); $n->execute([$id]);
        if ($n->fetchColumn() > 0) flash('error', 'Kategori masih memiliki produk. Nonaktifkan saja, atau pindahkan produknya.');
        else { db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]); flash('success', 'Kategori dihapus.'); }
    }
    redirect('admin/categories/index.php');
}
$rows = db()->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) pc FROM categories c ORDER BY sort_order, name')->fetchAll();
admin_start('Kelola Kategori', 'categories');
?>
<div class="page-actions"><span class="muted">Kategori: Coffee, Non-Coffee, Food &amp; Pastry, Signature Drinks.</span><a class="btn btn-primary" href="create.php">+ Tambah Kategori</a></div>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th>Urut</th><th>Nama</th><th>Slug</th><th class="num">Produk</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $c): ?><tr><td><?= (int)$c['sort_order'] ?></td><td><b><?= e($c['name']) ?></b><div class="small muted"><?= e(mb_strimwidth((string)$c['description'], 0, 70, '…')) ?></div></td><td><?= e($c['slug']) ?></td><td class="num"><?= (int)$c['pc'] ?></td><td><span class="badge <?= $c['is_active'] ? 'on' : 'off' ?>"><?= $c['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
  <td class="actions"><a class="btn btn-sm" href="edit.php?id=<?= (int)$c['id'] ?>">Edit</a>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-sm"><?= $c['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
    <form method="post" data-confirm="Hapus kategori ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-danger" <?= $c['pc'] ? 'disabled title="Masih ada produk"' : '' ?>>Hapus</button></form></td></tr><?php endforeach; ?>
  </tbody></table></div></div>
<?php admin_end(); ?>
