<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? APP_NAME;
$active = $active ?? '';
$user = current_user();
$cartN = ($user && $user['role'] === 'customer') ? cart_count() : 0;
$nav = ['home' => ['Home', 'index.php'], 'menu' => ['Menu', 'menu.php'], 'gallery' => ['Gallery', 'gallery.php'], 'about' => ['About', 'about.php'], 'contact' => ['Contact', 'contact.php']];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
<meta name="description" content="<?= e(APP_NAME) ?> – <?= e(APP_SLOGAN) ?>">
<link rel="icon" type="image/svg+xml" href="<?= asset('images/ui/favicon.svg') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="site <?= e($bodyClass ?? '') ?>">
<div class="bg-beans" aria-hidden="true"></div>
<style>
/* Dropdown kategori (disertakan di sini agar tetap rapi walau style.css belum diperbarui) */
.nav-links .has-sub{position:relative}
.nav-links .caret{font-size:.7em;opacity:.7}
.nav-links .sub{position:absolute;top:100%;left:0;min-width:200px;background:#3a2418;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:.5rem;list-style:none;margin:0;display:none;z-index:60;box-shadow:0 12px 30px rgba(0,0,0,.35)}
.nav-links .has-sub:hover .sub,.nav-links .has-sub:focus-within .sub{display:block}
.nav-links .sub li{display:block;margin:0}
.nav-links .sub a{display:block;padding:.5rem .8rem;border-radius:8px;white-space:nowrap}
.nav-links .sub a:hover{background:rgba(255,255,255,.07)}
@media(max-width:900px){.nav-links .sub{position:static;display:block;box-shadow:none;border:0;background:transparent;padding:0 0 0 1rem}}
</style>
<header class="navbar-wrap">
  <nav class="navbar" aria-label="Navigasi utama">
    <a class="brand" href="<?= url('index.php') ?>">
      <img src="<?= asset('images/ui/logo.svg') ?>" alt="" width="38" height="38">
      <span><b>KopiKita</b><small>Est. 2026</small></span>
    </a>
    <button class="nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navMenu"><span></span><span></span><span></span></button>
    <ul class="nav-links" id="navMenu">
      <?php foreach ($nav as $k => [$label, $href]): ?>
        <?php if ($k === 'menu'): $navCats = get_categories(); ?>
        <li class="has-sub"><a href="<?= url($href) ?>" class="<?= $active === $k ? 'active' : '' ?>"><?= e($label) ?> <span class="caret" aria-hidden="true">▾</span></a>
          <ul class="sub">
            <li><a href="<?= url('menu.php') ?>">Semua Menu</a></li>
            <?php foreach ($navCats as $nc): ?><li><a href="<?= url('menu.php?cat=' . urlencode($nc['slug'])) ?>"><?= e($nc['name']) ?></a></li><?php endforeach; ?>
          </ul></li>
        <?php else: ?>
        <li><a href="<?= url($href) ?>" class="<?= $active === $k ? 'active' : '' ?>"><?= e($label) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
      <li class="nav-mobile-only">
        <?php if ($user): ?><a href="<?= url(home_for_role($user['role'])) ?>">Akun Saya</a><?php else: ?><a href="<?= url('login.php') ?>">Sign In</a><?php endif; ?>
      </li>
    </ul>
    <div class="nav-actions">
      <a class="icon-btn" href="<?= url('menu.php?focus=1') ?>" aria-label="Cari menu"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></a>
      <a class="icon-btn" href="<?= url($user && $user['role'] === 'customer' ? 'customer/cart.php' : 'login.php?next=' . urlencode(BASE_URL . '/customer/cart.php')) ?>" aria-label="Keranjang">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6L5 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
        <?php if ($cartN > 0): ?><span class="cart-badge"><?= $cartN ?></span><?php endif; ?>
      </a>
      <?php if ($user): ?>
        <div class="user-menu">
          <button class="btn btn-pill btn-ghost user-btn" aria-haspopup="true" aria-expanded="false"><?= e(mb_strimwidth($user['name'], 0, 14, '…')) ?> ▾</button>
          <div class="user-dropdown">
            <?php if ($user['role'] === 'customer'): ?>
              <a href="<?= url('customer/index.php') ?>">Beranda Saya</a>
              <a href="<?= url('customer/orders.php') ?>">Pesanan Saya</a>
              <a href="<?= url('customer/profile.php') ?>">Profil Saya</a>
            <?php else: ?>
              <a href="<?= url(home_for_role($user['role'])) ?>">Buka Dashboard</a>
            <?php endif; ?>
            <a href="<?= url('logout.php') ?>">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-pill btn-ghost nav-signin" href="<?= url('login.php') ?>">Sign In</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
<?= flash_render() ?>
<main id="main">