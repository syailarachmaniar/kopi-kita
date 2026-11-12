<?php
function product_form(array $p, array $err, array $cats): void
{ ?>
<form method="post" enctype="multipart/form-data" class="card form-card"><?= csrf_field() ?>
  <?php foreach ($err as $m): ?><div class="alert alert-error"><?= e($m) ?></div><?php endforeach; ?>
  <div class="row-2">
    <div class="fg-block"><label for="name">Nama Produk</label><input class="input" id="name" name="name" required maxlength="120" value="<?= e($p['name']) ?>"></div>
    <div class="fg-block"><label for="sku">SKU (kode unik)</label><input class="input" id="sku" name="sku" required maxlength="40" value="<?= e($p['sku']) ?>" placeholder="CF-007"></div>
  </div>
  <div class="row-2">
    <div class="fg-block"><label for="category_id">Kategori</label><select class="input" id="category_id" name="category_id" required><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$p['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    <div class="fg-block"><label for="price">Harga (Rp)</label><input class="input" id="price" type="number" name="price" min="0" step="500" required value="<?= e($p['price']) ?>"></div>
  </div>
  <div class="row-2">
    <div class="fg-block"><label for="stock">Stok</label><input class="input" id="stock" type="number" name="stock" min="0" required value="<?= (int)$p['stock'] ?>"></div>
    <div class="fg-block"><label for="min_stock">Batas stok menipis</label><input class="input" id="min_stock" type="number" name="min_stock" min="0" required value="<?= (int)$p['min_stock'] ?>"><div class="hint">Peringatan muncul bila stok ≤ nilai ini.</div></div>
  </div>
  <div class="fg-block"><label for="description">Deskripsi</label><textarea class="input" id="description" name="description"><?= e($p['description']) ?></textarea></div>
  <div class="fg-block"><label for="image">Foto Produk (JPG/PNG/WEBP, maks. 2 MB)</label>
    <?php if ($p['image']): ?><div style="margin-bottom:6px"><img class="thumb" src="<?= product_image($p['image']) ?>" alt=""></div><?php endif; ?>
    <input class="input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
  <div class="checks">
    <label><input type="checkbox" name="has_size_option" value="1" <?= $p['has_size_option'] ? 'checked' : '' ?>> Punya pilihan ukuran (minuman)</label>
    <label><input type="checkbox" name="is_best_seller" value="1" <?= $p['is_best_seller'] ? 'checked' : '' ?>> Best seller (tampil di beranda)</label>
    <label><input type="checkbox" name="is_available" value="1" <?= $p['is_available'] ? 'checked' : '' ?>> Tersedia di katalog</label>
  </div>
  <button class="btn btn-primary">Simpan</button> <a class="btn" href="index.php">Batal</a>
</form>
<?php }
function product_save(?int $id, array &$p): array
{
    foreach (['name', 'sku', 'description'] as $k) $p[$k] = trim((string)($_POST[$k] ?? ''));
    $p['category_id'] = (int)($_POST['category_id'] ?? 0); $p['price'] = (string)($_POST['price'] ?? '');
    $p['stock'] = (int)($_POST['stock'] ?? 0); $p['min_stock'] = (int)($_POST['min_stock'] ?? 0);
    foreach (['has_size_option', 'is_best_seller', 'is_available'] as $k) $p[$k] = isset($_POST[$k]) ? 1 : 0;
    $err = [];
    if ($p['name'] === '') $err[] = 'Nama produk wajib diisi.';
    if ($p['sku'] === '') $err[] = 'SKU wajib diisi.';
    if (!is_numeric($p['price']) || $p['price'] < 0) $err[] = 'Harga tidak valid.';
    if ($p['stock'] < 0 || $p['min_stock'] < 0) $err[] = 'Stok tidak boleh negatif.';
    $c = db()->prepare('SELECT COUNT(*) FROM categories WHERE id = ?'); $c->execute([$p['category_id']]);
    if (!$c->fetchColumn()) $err[] = 'Kategori tidak valid.';
    $d = db()->prepare('SELECT COUNT(*) FROM products WHERE sku = ? AND id <> ?'); $d->execute([$p['sku'], $id ?? 0]);
    if ($d->fetchColumn() > 0) $err[] = 'SKU sudah dipakai produk lain.';
    $newImg = null;
    if (!$err) { try { $newImg = upload_image($_FILES['image'] ?? [], 'products'); } catch (RuntimeException $ex) { $err[] = $ex->getMessage(); } }
    if ($err) return $err;
    // slug unik
    $base = slugify($p['name']); $slug = $base; $n = 2;
    $sl = db()->prepare('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?');
    while (true) { $sl->execute([$slug, $id ?? 0]); if (!$sl->fetchColumn()) break; $slug = $base . '-' . $n++; }
    if ($id) {
        $old = db()->prepare('SELECT image FROM products WHERE id = ?'); $old->execute([$id]); $oldImg = $old->fetchColumn();
        db()->prepare('UPDATE products SET category_id=?, sku=?, name=?, slug=?, description=?, price=?, stock=?, min_stock=?, image=COALESCE(?, image), has_size_option=?, is_best_seller=?, is_available=? WHERE id=?')
            ->execute([$p['category_id'], $p['sku'], $p['name'], $slug, $p['description'] ?: null, $p['price'], $p['stock'], $p['min_stock'], $newImg, $p['has_size_option'], $p['is_best_seller'], $p['is_available'], $id]);
        if ($newImg) delete_image($oldImg, 'products');
    } else {
        db()->prepare('INSERT INTO products (category_id, sku, name, slug, description, price, stock, min_stock, image, has_size_option, is_best_seller, is_available) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$p['category_id'], $p['sku'], $p['name'], $slug, $p['description'] ?: null, $p['price'], $p['stock'], $p['min_stock'], $newImg, $p['has_size_option'], $p['is_best_seller'], $p['is_available']]);
    }
    return [];
}
