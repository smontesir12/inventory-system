<?php
require_once 'includes/header.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$tx = getTransaction($conn, $id);
if (!$tx) { setFlash('error','Receipt not found.'); header('Location: sales.php'); exit; }
$items = getTransactionItems($conn, $id);
?>

<h1>Digital Receipt</h1>

<div class="receipt" id="receiptArea">
  <h2>📦 INVENTORY SHOP</h2>
  <p class="center">Official Receipt</p>
  <hr>
  <p><strong>Code:</strong> <?= htmlspecialchars($tx['code']) ?></p>
  <p><strong>Cashier:</strong> <?= htmlspecialchars($tx['cashier_name']) ?></p>
  <p><strong>Date:</strong> <?= $tx['created_at'] ?></p>
  <hr>
  <table>
    <thead><tr><th>Item</th><th style="text-align:right">Qty</th><th style="text-align:right">Amt</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= htmlspecialchars($it['product_name']) ?><br>
              <small>@ <?= CURRENCY . number_format($it['price'], 2) ?></small></td>
          <td style="text-align:right"><?= $it['quantity'] ?></td>
          <td style="text-align:right"><?= CURRENCY . number_format($it['subtotal'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <hr>
  <table>
    <tr><td>Subtotal</td><td style="text-align:right"><?= CURRENCY . number_format($tx['total'], 2) ?></td></tr>
    <tr><td><strong>TOTAL</strong></td><td style="text-align:right"><strong><?= CURRENCY . number_format($tx['total'], 2) ?></strong></td></tr>
    <tr><td>Cash</td><td style="text-align:right"><?= CURRENCY . number_format($tx['cash'], 2) ?></td></tr>
    <tr><td><strong>Change</strong></td><td style="text-align:right"><strong><?= CURRENCY . number_format($tx['change_amount'], 2) ?></strong></td></tr>
  </table>
  <hr>
  <p class="center">Thank you for shopping! 🛍️</p>
</div>

<div style="text-align:center;margin-top:20px">
  <button onclick="window.print()" class="btn">🖨️ Print Receipt</button>
  <a href="shop.php" class="btn btn-green">New Sale</a>
  <a href="sales.php" class="btn btn-gray">All Sales</a>
</div>

<?php require_once 'includes/footer.php'; ?>