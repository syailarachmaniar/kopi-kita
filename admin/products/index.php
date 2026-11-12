<?php
require_once __DIR__ . '/../../includes/admin-header.php';
if (is_post()) {
    csrf_check(); $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'stock') {
        db()->prepare('UPDATE products SET stock = ?, price = ? WHERE id = ?')->execute([max(0, (int)$_POST['stock']), max(0, (float)$_POST['price']), $id]);
        flash('success', 'Harga dan stok diperbarui.');
    }
    redirect('admin/products/index.php' . (!empty($_POST['qs']) ? '?' . ltrim((string)$_POST['qs'], '?') : ''));
}
$cat = (int)($_GET['cat'] ?? 0); $low = !empty($_GET['low']); $q = trim((string)($_GET['q'] ?? ''));
$w = ['1=1']; $a = [];
if ($cat) { $w[] = 'p.category_id = ?'; $a[] = $cat; }
if ($low) $w[] = 'p.stock <= p.min_stock';
if ($q !== '') { $w[] = '(p.name LIKE ? OR p.sku LIKE ?)'; $a[] = "%$q%"; $a[] = "%$q%"; }
$st = db()->prepare('SELECT p.*, c.name cat FROM products p JOIN categories c ON c.id = p.category_id WHERE ' . implode(' AND ', $w) . ' ORDER BY c.sort_order, p.name'); $st->execute($a); $rows = $st->fetchAll();
$cats = get_categories(false);
admin_start('Produk & Stok', 'products');
?>
<div class="page-actions">
  <form class="filter" method="get">
    <div class="fg"><label>Cari</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Nama / SKU"></div>
    <div class="fg"><label>Kategori</label><select class="input" name="cat"><option value="0">Semua</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    <label style="display:flex;gap:.4rem;align-items:center;font-weight:500"><input type="checkbox" name="low" value="1" <?= $low ? 'checked' : '' ?>> Stok menipis</label>
    <button class="btn btn-dark">Filter</button>
  </form>
  <a class="btn btn-primary" href="create.php">+ Tambah Produk</a>
</div>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th></th><th>Produk</th><th>Kategori</th><th>Harga &amp; Stok (edit cepat)</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $p): ?><tr>
    <td><img class="thumb" src="<?= product_image($p['image']) ?>" alt=""></td>
    <td><b><?= e($p['name']) ?></b><div class="small muted"><?= e($p['sku']) ?><?= $p['is_best_seller'] ? ' · ★ best seller' : '' ?></div></td><td><?= e($p['cat']) ?></td>
    <td><form method="post" style="display:flex;gap:6px;align-items:center"><?= csrf_field() ?><input type="hidden" name="action" value="stock"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="qs" value="<?= e($_SERVER['QUERY_STRING'] ?? '') ?>">
      <input class="input" style="width:100px" type="number" name="price" min="0" step="500" value="<?= (int)$p['price'] ?>" aria-label="Harga"><input class="input" style="width:72px" type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>" aria-label="Stok"><button class="btn btn-sm">Simpan</button>
      <?php if ($p['stock'] <= $p['min_stock']): ?><span class="badge low">Menipis</span><?php endif; ?></form></td>
    <td><span class="badge <?= $p['is_available'] ? 'on' : 'off' ?>"><?= $p['is_available'] ? 'Tersedia' : 'Nonaktif' ?></span></td>
    <td class="actions"><a class="btn btn-sm" href="edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
      <form method="post" action="delete.php" data-confirm="Hapus produk ini? Jika sudah pernah dipesan, produk hanya dinonaktifkan."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty">Tidak ada produk.</td></tr><?php endif; ?></tbody></table></div></div>
<?php admin_end(); ?>
