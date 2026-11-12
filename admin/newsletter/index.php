<?php
require_once __DIR__ . '/../../includes/admin-header.php';
if (is_post()) {
    csrf_check(); $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle') db()->prepare('UPDATE newsletter_subscribers SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    if (($_POST['action'] ?? '') === 'delete') db()->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$id]);
    flash('success', 'Data diperbarui.'); redirect('admin/newsletter/index.php');
}
$rows = db()->query('SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC, id DESC')->fetchAll();
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="newsletter-kopikita.csv"');
    echo "\xEF\xBB\xBFemail,status,subscribed_at\n";
    foreach ($rows as $r) echo '"' . str_replace('"', '""', $r['email']) . '",' . ($r['is_active'] ? 'aktif' : 'nonaktif') . ',' . $r['subscribed_at'] . "\n";
    exit;
}
admin_start('Newsletter', 'newsletter');
?>
<div class="page-actions"><span class="muted"><?= count($rows) ?> pendaftar</span><a class="btn btn-dark" href="?export=1">⬇ Ekspor CSV</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Email</th><th>Terdaftar</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['email']) ?></td><td><?= fmt_date($r['subscribed_at']) ?></td><td><span class="badge <?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
<td class="actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm"><?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
<form method="post" data-confirm="Hapus email ini?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="4" class="empty">Belum ada pendaftar.</td></tr><?php endif; ?></tbody></table></div></div>
<?php admin_end(); ?>
