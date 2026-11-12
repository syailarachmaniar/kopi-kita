<?php
/** Layout bersama untuk admin & kasir (sidebar + topbar). */
function panel_start(string $title, string $active, array $nav, string $roleLabel, string $extraHead = ''): void
{
    $u = current_user();
    ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> – <?= e($roleLabel) ?> KopiKita</title>
<link rel="icon" type="image/svg+xml" href="<?= asset('images/ui/favicon.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<?= $extraHead ?>
</head>
<body class="panel">
<aside class="sidebar" id="sidebar">
  <a class="side-brand" href="<?= url(home_for_role($u['role'])) ?>"><img src="<?= asset('images/ui/logo.svg') ?>" alt="" width="36" height="36"><span><b>KopiKita</b><small><?= e($roleLabel) ?></small></span></a>
  <nav>
    <?php foreach ($nav as $key => [$label, $href, $icon]): ?>
      <a href="<?= url($href) ?>" class="<?= $active === $key ? 'active' : '' ?>"><span class="ico"><?= $icon ?></span><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-foot">
    <a href="<?= url('index.php') ?>" target="_blank" rel="noopener">Lihat Website ↗</a>
    <a href="<?= url('logout.php') ?>" class="logout">Logout</a>
  </div>
</aside>
<div class="panel-main">
  <header class="topbar">
    <button class="side-toggle" aria-label="Menu" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
    <h1><?= e($title) ?></h1>
    <div class="top-user"><span class="avatar"><?= e(mb_strtoupper(mb_substr($u['name'], 0, 1))) ?></span><span><b><?= e($u['name']) ?></b><small><?= e($roleLabel) ?></small></span></div>
  </header>
  <?= flash_render() ?>
  <div class="panel-content">
<?php
}
function panel_end(string $extraScripts = ''): void
{
    ?>
  </div>
</div>
<script>
document.querySelectorAll('.alert-x').forEach(b=>b.addEventListener('click',()=>b.parentElement.remove()));
document.querySelectorAll('form[data-confirm]').forEach(f=>f.addEventListener('submit',e=>{if(!confirm(f.dataset.confirm))e.preventDefault();}));
</script>
<?= $extraScripts ?>
</body>
</html>
<?php
}
// ikon sederhana (SVG inline)
function ico(string $n): string
{
    $p = [
        'home' => '<path d="M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.3"/>',
        'box' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/>',
        'bag' => '<path d="M5 8h14l-1 12H6z"/><path d="M9 8a3 3 0 0 1 6 0"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-4 3-6 7-6s7 2 7 6"/><path d="M17 5a3 3 0 0 1 0 6M22 20c0-3-1.5-5-4-5.7"/>',
        'chart' => '<path d="M4 20V4M4 20h16"/><path d="M8 16v-5M12 16V8M16 16v-8"/>',
        'img' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.8"/><path d="M3 17l5-4 4 3 3-2 6 4"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>',
        'pos' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h2M12 11h2M8 15h2M12 15h4"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($p[$n] ?? '') . '</svg>';
}

/** Pagination sederhana. $q = array parameter query tambahan. */
function pager(int $total, int $per, int $page, array $q = []): string
{
    $pages = (int)ceil($total / $per);
    if ($pages <= 1) return '';
    $h = '<div class="pager">';
    for ($i = 1; $i <= $pages; $i++) {
        $qs = http_build_query($q + ['page' => $i]);
        $h .= $i === $page ? '<span class="cur">' . $i . '</span>' : '<a href="?' . e($qs) . '">' . $i . '</a>';
    }
    return $h . '</div>';
}
function current_page(): int { return max(1, (int)($_GET['page'] ?? 1)); }
