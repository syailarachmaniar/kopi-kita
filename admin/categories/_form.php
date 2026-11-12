<?php
function category_form(array $c, array $err, string $action): void
{ ?>
<form method="post" class="card form-card" action="<?= e($action) ?>"><?= csrf_field() ?>
  <?php foreach ($err as $m): ?><div class="alert alert-error"><?= e($m) ?></div><?php endforeach; ?>
  <div class="row-2">
    <div class="fg-block"><label for="name">Nama Kategori</label><input class="input" id="name" name="name" required maxlength="80" value="<?= e($c['name']) ?>"></div>
    <div class="fg-block"><label for="slug">Slug (otomatis jika kosong)</label><input class="input" id="slug" name="slug" maxlength="90" value="<?= e($c['slug']) ?>"></div>
  </div>
  <div class="fg-block"><label for="description">Deskripsi</label><textarea class="input" id="description" name="description"><?= e($c['description']) ?></textarea></div>
  <div class="row-2">
    <div class="fg-block"><label for="sort_order">Urutan tampil</label><input class="input" id="sort_order" type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>"></div>
    <div class="fg-block"><label for="icon">Ikon (opsional)</label><input class="input" id="icon" name="icon" maxlength="50" value="<?= e($c['icon']) ?>"></div>
  </div>
  <div class="checks"><label><input type="checkbox" name="is_active" value="1" <?= $c['is_active'] ? 'checked' : '' ?>> Aktif (tampil di katalog)</label></div>
  <button class="btn btn-primary">Simpan</button> <a class="btn" href="index.php">Batal</a>
</form>
<?php }
/** Validasi + simpan. Mengembalikan array error (kosong = sukses). */
function category_save(?int $id, array &$c): array
{
    $c['name'] = trim((string)($_POST['name'] ?? '')); $c['slug'] = slugify(trim((string)($_POST['slug'] ?? '')) ?: $c['name']);
    $c['description'] = trim((string)($_POST['description'] ?? '')); $c['sort_order'] = (int)($_POST['sort_order'] ?? 0);
    $c['icon'] = trim((string)($_POST['icon'] ?? '')); $c['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $err = [];
    if ($c['name'] === '' || mb_strlen($c['name']) > 80) $err[] = 'Nama wajib diisi (maks. 80 karakter).';
    $d = db()->prepare('SELECT COUNT(*) FROM categories WHERE (name = ? OR slug = ?) AND id <> ?'); $d->execute([$c['name'], $c['slug'], $id ?? 0]);
    if ($d->fetchColumn() > 0) $err[] = 'Nama atau slug sudah dipakai kategori lain.';
    if ($err) return $err;
    if ($id) db()->prepare('UPDATE categories SET name=?, slug=?, description=?, icon=?, sort_order=?, is_active=? WHERE id=?')->execute([$c['name'], $c['slug'], $c['description'] ?: null, $c['icon'] ?: null, $c['sort_order'], $c['is_active'], $id]);
    else db()->prepare('INSERT INTO categories (name, slug, description, icon, sort_order, is_active) VALUES (?,?,?,?,?,?)')->execute([$c['name'], $c['slug'], $c['description'] ?: null, $c['icon'] ?: null, $c['sort_order'], $c['is_active']]);
    return [];
}
