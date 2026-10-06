<?php
require_once 'includes/header.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $u = trim($_POST['username']); $p = $_POST['password'];
    $fn = trim($_POST['full_name']); $r = $_POST['role'];
    if ($u && $p && $fn && in_array($r, ['admin','cashier'])) {
        try {
            addUser($conn, $u, $p, $fn, $r);
            setFlash('success', 'User added.');
        } catch (Exception $e) { setFlash('error', 'Username already exists.'); }
    } else setFlash('error', 'Please fill all fields.');
    header('Location: users.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = intval($_POST['id']); $u = trim($_POST['username']);
    $fn = trim($_POST['full_name']); $r = $_POST['role'];
    $p = $_POST['password'] ?? '';
    if ($id && $u && $fn && in_array($r, ['admin','cashier'])) {
        updateUser($conn, $id, $u, $fn, $r, $p ?: null);
        setFlash('success', 'User updated.');
    } else setFlash('error', 'Invalid input.');
    header('Location: users.php'); exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id == $_SESSION['user_id']) setFlash('error', "You can't delete your own account.");
    else { deleteUser($conn, $id); setFlash('success', 'User deleted.'); }
    header('Location: users.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $conn->prepare("SELECT id,username,full_name,role FROM users WHERE id=?");
    $s->bind_param("i", intval($_GET['edit'])); $s->execute();
    $edit = $s->get_result()->fetch_assoc();
}
$users = getUsers($conn);
?>

<h1>User Management</h1>

<div class="card">
  <h2><?= $edit ? 'Edit User' : 'Add New User' ?></h2>
  <form method="post">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="row">
      <div class="field"><label>Username</label>
        <input type="text" name="username" required value="<?= $edit ? htmlspecialchars($edit['username']) : '' ?>"></div>
      <div class="field"><label>Full Name</label>
        <input type="text" name="full_name" required value="<?= $edit ? htmlspecialchars($edit['full_name']) : '' ?>"></div>
      <div class="field"><label>Role</label>
        <select name="role" required>
          <option value="cashier" <?= ($edit && $edit['role']==='cashier') ? 'selected' : '' ?>>Cashier</option>
          <option value="admin"   <?= ($edit && $edit['role']==='admin')   ? 'selected' : '' ?>>Admin</option>
        </select></div>
      <div class="field"><label>Password <?= $edit ? '(leave blank to keep)' : '' ?></label>
        <input type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
      <div class="field">
        <?php if ($edit): ?>
          <button class="btn btn-green" name="update_user">Update</button>
          <a href="users.php" class="btn btn-gray">Cancel</a>
        <?php else: ?>
          <button class="btn" name="add_user">Add User</button>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <h2>All Users</h2>
  <table>
    <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><span class="badge <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
          <td><?= $u['created_at'] ?></td>
          <td>
            <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm">Edit</a>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
              <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-red confirm-delete">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once 'includes/footer.php'; ?>