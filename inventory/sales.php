<?php
require_once 'includes/header.php';
requireLogin();
$txs = getTransactions($conn);
?>

<h1>Sales History</h1>

<div class="card">
  <?php if (empty($txs)): ?>
    <p class="muted">No sales yet.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Code</th><th>Cashier</th><th>Total</th><th>Cash</th><th>Change</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($txs as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['code']) ?></td>
            <td><?= htmlspecialchars($t['cashier_name']) ?></td>
            <td><?= CURRENCY . number_format($t['total'], 2) ?></td>
            <td><?= CURRENCY . number_format($t['cash'], 2) ?></td>
            <td><?= CURRENCY . number_format($t['change_amount'], 2) ?></td>
            <td><?= $t['created_at'] ?></td>
            <td><a href="receipt.php?id=<?= $t['id'] ?>" class="btn btn-sm">Receipt</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>