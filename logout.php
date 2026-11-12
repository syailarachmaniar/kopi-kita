<?php
require_once __DIR__ . '/includes/auth.php';
logout_user();
session_start();
flash('success', 'Anda sudah logout. Sampai jumpa di KopiKita!');
redirect('index.php');
