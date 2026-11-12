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
<header class="navbar-wrap">
  <nav class="navbar" aria-label="Navigasi utama">
    <a class="brand" href="<?= url('index.php') ?>">
      <img src="<?= asset('images/ui/logo.svg') ?>" alt="" width="38" height="38">
      <span><b>KopiKita</b><small>Est. 2026</small></span>
    </a>
    <button class="nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navMenu"><span></span><span></span><span></span></button>
    <ul class="nav-links" id="navMenu">
      <?php foreach ($nav as $k => [$label, $href]): ?>
        <li><a href="<?= url($href) ?>" class="<?= $active === $k ? 'active' : '' ?>"><?= e($label) ?></a></li>
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
