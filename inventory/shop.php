<?php
require_once 'includes/header.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pid = intval($_POST['product_id']); $qty = intval($_POST['quantity']);
    if ($qty > 0 && addToCart($conn, $pid, $qty)) setFlash('success', 'Added to cart.');
    else setFlash('error', 'Not enough stock or invalid quantity.');
    header('Location: shop.php'); exit;
}

$products = getProducts($conn);
?>

<h1>Shop</h1>

<div class="card">
  <?php if (empty($products)): ?>
    <p class="muted">No products available.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Add to Cart</th></tr></thead>
      <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= CURRENCY . number_format($p['price'], 2) ?></td>
          <td><?= $p['quantity'] ?></td>
          <td>
            <?php if ($p['quantity'] > 0): ?>
              <form method="post" style="display:flex;gap:8px;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $p['quantity'] ?>" style="width:80px">
                <button class="btn btn-sm" name="add_to_cart">Add</button>
              </form>
            <?php else: ?><span class="badge out">Out of stock</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>