<?php
require_once __DIR__ . '/../../includes/admin-header.php';
if (is_post()) {
    csrf_check(); $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if ($act === 'add') {
        $title = trim((string)($_POST['title'] ?? '')); $col = $_POST['collection'] ?? '';
        try {
            if ($title === '' || !isset(GALLERY_COLLECTIONS[$col])) throw new RuntimeException('Judul dan koleksi wajib diisi.');
            $img = upload_image($_FILES['image'] ?? [], 'gallery');
            if (!$img) throw new RuntimeException('Pilih file gambar terlebih dahulu.');
            db()->prepare('INSERT INTO gallery_images (title, collection, image, sort_order) VALUES (?,?,?,?)')->execute([mb_substr($title, 0, 120), $col, $img, (int)($_POST['sort_order'] ?? 0)]);
            flash('success', 'Foto ditambahkan.');
        } catch (RuntimeException $ex) { flash('error', $ex->getMessage()); }
    } elseif ($act === 'toggle') { db()->prepare('UPDATE gallery_images SET is_active = 1 - is_active WHERE id = ?')->execute([$id]); flash('success', 'Status foto diubah.'); }
    elseif ($act === 'sort') { db()->prepare('UPDATE gallery_images SET sort_order = ? WHERE id = ?')->execute([(int)$_POST['sort_order'], $id]); flash('success', 'Urutan disimpan.'); }
    elseif ($act === 'delete') {
        $s = db()->prepare('SELECT image FROM gallery_images WHERE id = ?'); $s->execute([$id]); $im = $s->fetchColumn();
        db()->prepare('DELETE FROM gallery_images WHERE id = ?')->execute([$id]); delete_image($im, 'gallery'); flash('success', 'Foto dihapus.');
    }
    redirect('admin/gallery/index.php');
}
$rows = db()->query('SELECT * FROM gallery_images ORDER BY collection, sort_order, id')->fetchAll();
admin_start('Kelola Galeri', 'gallery');
?>
<form method="post" enctype="multipart/form-data" class="card filter" style="margin-bottom:18px"><?= csrf_field() ?><input type="hidden" name="action" value="add">
  <div class="fg"><label>Judul</label><input class="input" name="title" required maxlength="120"></div>
  <div class="fg"><label>Koleksi</label><select class="input" name="collection"><?php foreach (GALLERY_COLLECTIONS as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
  <div class="fg"><label>Urutan</label><input class="input" style="width:80px" type="number" name="sort_order" value="0"></div>
  <div class="fg"><label>Foto (JPG/PNG/WEBP ≤ 2 MB)</label><input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></div>
  <button class="btn btn-primary">+ Upload</button></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th></th><th>Judul</th><th>Koleksi</th><th>Urutan</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $g): ?><tr><td><img class="thumb" style="width:64px" src="<?= gallery_image($g['image']) ?>" alt=""></td><td><b><?= e($g['title']) ?></b></td><td><?= e(GALLERY_COLLECTIONS[$g['collection']] ?? '') ?></td>
<td><form method="post" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="action" value="sort"><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><input class="input" style="width:70px" type="number" name="sort_order" value="<?= (int)$g['sort_order'] ?>"><button class="btn btn-sm">OK</button></form></td>
<td><span class="badge <?= $g['is_active'] ? 'on' : 'off' ?>"><?= $g['is_active'] ? 'Tampil' : 'Disembunyikan' ?></span></td>
<td class="actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><button class="btn btn-sm"><?= $g['is_active'] ? 'Sembunyikan' : 'Tampilkan' ?></button></form>
<form method="post" data-confirm="Hapus foto ini?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php admin_end(); ?>
