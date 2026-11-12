<?php
require_once __DIR__ . '/../includes/auth.php';
redirect('menu.php' . (isset($_GET['cat']) ? '?cat=' . urlencode((string)$_GET['cat']) : ''));
