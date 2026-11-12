<?php
require_once __DIR__ . '/../includes/auth.php';
redirect('product-detail.php?slug=' . urlencode((string)($_GET['slug'] ?? '')));
