<?php
require_once 'config/db.php';

// Auto-seed default users if table is empty
$row = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc();
if ($row['c'] == 0) {
    $adminHash   = password_hash('admin123', PASSWORD_DEFAULT);
    $cashierHash = password_hash('cashier123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username,password,full_name,role) VALUES
        ('admin',   '$adminHash',   'Administrator',   'admin'),
        ('cashier', '$cashierHash', 'Default Cashier', 'cashier')");
}

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']); $p = $_POST['password'];
    $s = $conn->prepare("SELECT * FROM users WHERE username=?"); $s->bind_param("s", $u); $s->execute();
    $user = $s->get_result()->fetch_assoc();
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header('Location: dashboard.php'); exit;
    }
    setFlash('error', 'Invalid username or password.');
    header('Location: login.php'); exit;
}
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <h1>📦 Inventory Login</h1>
  <?php if ($flash): ?>
    <div class="alert <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>
  <div class="card">
    <form method="post">
      <div class="field">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%">Login</button>
    </form>
    <p class="muted" style="margin-top:14px;font-size:12px">
      Default: <strong>admin/admin123</strong> or <strong>cashier/cashier123</strong>
    </p>
  </div>
</div>
</body>
</html>