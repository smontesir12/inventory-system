<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$flash = getFlash();
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav>
  <span class="brand">📦 Inventory</span>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="dashboard.php">Dashboard</a>
    <a href="shop.php">Shop</a>
    <a href="cart.php">Cart</a>
    <a href="sales.php">Sales</a>
    <?php if ($isAdmin): ?>
      <a href="products.php">Products</a>
      <a href="users.php">Users</a>
    <?php endif; ?>
    <a class="cart" href="cart.php">🛒 <?= $cartCount ?></a>
    <span class="user">👤 <?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>
      <small>(<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</small>
    </span>
    <a href="logout.php" class="logout">Logout</a>
  <?php endif; ?>
</nav>
<div class="container">
<?php if ($flash): ?>
  <div class="alert <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>