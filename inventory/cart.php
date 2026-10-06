<?php
require_once 'includes/header.php';
requireLogin();

if (isset($_GET['remove']))  { removeFromCart($conn, intval($_GET['remove'])); setFlash('success','Item removed.'); header('Location: cart.php'); exit; }
if (isset($_GET['clear']))   {
    foreach (array_keys($_SESSION['cart'] ?? []) as $id) removeFromCart($conn, $id);
    setFlash('success','Cart cleared.'); header('Location: cart.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $q) {
        $id = intval($id); $q = intval($q);
        if ($q <= 0) removeFromCart($conn, $id); else updateCartQuantity($conn, $id, $q);
    }
    setFlash('success', 'Cart updated.'); header('Location: cart.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_checkout'])) {
    $cash = floatval($_POST['cash']);
    $txId = processCheckout($conn, $cash);
    if ($txId) { header("Location: receipt.php?id=$txId"); exit; }
    setFlash('error', 'Cash tendered is not enough.');
    header('Location: cart.php'); exit;
}

$items = getCartItems($conn);
$total = array_sum(array_column($items, 'subtotal'));
?>

<h1>Shopping Cart</h1>

<div class="card">
  <?php if (empty($items)): ?>
    <p class="muted">Your cart is empty. <a href="shop.php">Go shopping</a>.</p>
  <?php else: ?>
    <form method="post">
      <table>
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= htmlspecialchars($it['name']) ?></td>
              <td><?= CURRENCY . number_format($it['price'], 2) ?></td>
              <td><input type="number" name="qty[<?= $it['id'] ?>]" value="<?= $it['quantity'] ?>" min="0" style="width:80px"></td>
              <td><?= CURRENCY . number_format($it['subtotal'], 2) ?></td>
              <td><a href="cart.php?remove=<?= $it['id'] ?>" class="btn btn-sm btn-red">Remove</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
          <td colspan="3" style="text-align:right"><strong>Total:</strong></td>
          <td colspan="2"><strong><?= CURRENCY . number_format($total, 2) ?></strong></td>
        </tr></tfoot>
      </table>
      <div style="margin-top:15px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-gray" name="update_cart">Update Cart</button>
        <a href="cart.php?clear=1" class="btn btn-red">Clear Cart</a>
        <button type="button" id="openCheckout" class="btn btn-green"
                data-total="<?= $total ?>">💵 Checkout</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<!-- Checkout Modal -->
<div class="modal-overlay" id="checkoutModal">
  <div class="modal">
    <h3>💵 Payment</h3>
    <div class="total-line"><span>Total:</span><span><?= CURRENCY ?><span id="modalTotal">0.00</span></span></div>
    <form method="post" id="checkoutForm">
      <input type="hidden" name="cash" id="hiddenCash">
      <div class="field" style="margin-top:12px">
        <label>Cash Tendered (<?= CURRENCY ?>)</label>
        <input type="number" step="0.01" min="0" id="cashInput" required autocomplete="off">
      </div>
      <div class="change-line"><span>Change:</span><span><?= CURRENCY ?><span id="changeDisplay">0.00</span></span></div>
      <div class="actions">
        <button type="button" id="cancelCheckout" class="btn btn-gray">Cancel</button>
        <button type="submit" name="process_checkout" class="btn btn-green">Confirm Payment</button>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>