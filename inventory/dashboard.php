<?php
require_once 'includes/header.php';
requireLogin();
$s = dashboardStats($conn);
$lowStock = $conn->query("SELECT * FROM products WHERE quantity <= 5 ORDER BY quantity ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recentTx = $conn->query("SELECT * FROM transactions ORDER BY id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<h1>Dashboard</h1>

<div class="stats">
  <div class="stat"><span>Total Products</span><strong><?= $s['products'] ?></strong></div>
  <div class="stat"><span>Total Stock</span><strong><?= $s['stock'] ?></strong></div>
  <div class="stat"><span>Inventory Value</span><strong><?= CURRENCY . number_format($s['value'], 2) ?></strong></div>
  <div class="stat"><span>Sales Today</span><strong><?= CURRENCY . number_format($s['sales_today'], 2) ?></strong></div>
  <div class="stat"><span>Transactions</span><strong><?= $s['tx_count'] ?></strong></div>
  <div class="stat"><span>Low Stock Items</span><strong><?= $s['low_stock'] ?></strong></div>
</div>

<div class="card">
  <h2>Quick Actions</h2>
  <a href="shop.php" class="btn">🛒 Go to Shop</a>
  <?php if ($isAdmin): ?>
    <a href="products.php" class="btn btn-green">➕ Add Product</a>
    <a href="users.php" class="btn btn-gray">👥 Manage Users</a>
  <?php endif; ?>
</div>

<div class="card">
  <h2>⚠️ Low Stock Alert</h2>
  <?php if (empty($lowStock)): ?>
    <p class="muted">All products are well-stocked.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($lowStock as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= CURRENCY . number_format($p['price'], 2) ?></td>
            <td><?= $p['quantity'] ?></td>
            <td>
              <?php if ($p['quantity'] == 0): ?>
                <span class="badge out">Out of stock</span>
              <?php else: ?>
                <span class="badge low">Low stock</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="card">
  <h2>🧾 Recent Transactions</h2>
  <?php if (empty($recentTx)): ?>
    <p class="muted">No transactions yet.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Code</th><th>Cashier</th><th>Total</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recentTx as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['code']) ?></td>
            <td><?= htmlspecialchars($t['cashier_name']) ?></td>
            <td><?= CURRENCY . number_format($t['total'], 2) ?></td>
            <td><?= $t['created_at'] ?></td>
            <td><a href="receipt.php?id=<?= $t['id'] ?>" class="btn btn-sm">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>