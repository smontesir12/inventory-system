<?php
require_once 'includes/header.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']); $price = floatval($_POST['price']); $qty = intval($_POST['quantity']);
    if ($name !== '' && $price >= 0 && $qty >= 0) {
        addProduct($conn, $name, $price, $qty);
        setFlash('success', 'Product added.');
    } else setFlash('error', 'Invalid input.');
    header('Location: products.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['id']); $name = trim($_POST['name']);
    $price = floatval($_POST['price']); $qty = intval($_POST['quantity']);
    if ($id && $name !== '' && $price >= 0 && $qty >= 0) {
        updateProduct($conn, $id, $name, $price, $qty);
        setFlash('success', 'Product updated.');
    } else setFlash('error', 'Invalid input.');
    header('Location: products.php'); exit;
}

if (isset($_GET['delete'])) {
    deleteProduct($conn, intval($_GET['delete']));
    setFlash('success', 'Product deleted.');
    header('Location: products.php'); exit;
}

$edit = isset($_GET['edit']) ? getProduct($conn, intval($_GET['edit'])) : null;
$products = getProducts($conn);
?>

<h1>Product Management</h1>

<div class="card">
  <h2><?= $edit ? 'Edit Product' : 'Add New Product' ?></h2>
  <form method="post">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="row">
      <div class="field"><label>Name</label>
        <input type="text" name="name" required value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>"></div>
      <div class="field"><label>Price (<?= CURRENCY ?>)</label>
        <input type="number" step="0.01" min="0" name="price" required value="<?= $edit ? $edit['price'] : '' ?>"></div>
      <div class="field"><label>Quantity</label>
        <input type="number" min="0" name="quantity" required value="<?= $edit ? $edit['quantity'] : '' ?>"></div>
      <div class="field">
        <?php if ($edit): ?>
          <button class="btn btn-green" name="update_product">Update</button>
          <a href="products.php" class="btn btn-gray">Cancel</a>
        <?php else: ?>
          <button class="btn" name="add_product">Add Product</button>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <h2>All Products</h2>
  <?php if (empty($products)): ?>
    <p class="muted">No products yet.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Qty</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= CURRENCY . number_format($p['price'], 2) ?></td>
            <td><?= $p['quantity'] ?></td>
            <td>
              <?php if ($p['quantity'] == 0): ?><span class="badge out">Out</span>
              <?php elseif ($p['quantity'] <= 5): ?><span class="badge low">Low</span>
              <?php else: ?><span class="badge ok">In stock</span><?php endif; ?>
            </td>
            <td>
              <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-sm">Edit</a>
              <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-red confirm-delete">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>