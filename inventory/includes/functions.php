<?php
require_once __DIR__ . '/../config/db.php';

/* ---------- Products ---------- */
function getProducts($conn) {
    return $conn->query("SELECT * FROM products ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}
function getProduct($conn, $id) {
    $s = $conn->prepare("SELECT * FROM products WHERE id=?"); 
    $s->bind_param("i",$id); $s->execute();
    return $s->get_result()->fetch_assoc();
}
function addProduct($conn, $name, $price, $qty) {
    $s = $conn->prepare("INSERT INTO products (name,price,quantity) VALUES (?,?,?)");
    $s->bind_param("sdi", $name, $price, $qty); 
    return $s->execute();
}
function updateProduct($conn, $id, $name, $price, $qty) {
    $s = $conn->prepare("UPDATE products SET name=?,price=?,quantity=? WHERE id=?");
    $s->bind_param("sdii", $name, $price, $qty, $id); 
    return $s->execute();
}
function deleteProduct($conn, $id) {
    $s = $conn->prepare("DELETE FROM products WHERE id=?"); 
    $s->bind_param("i",$id); 
    return $s->execute();
}

/* ---------- Users ---------- */
function getUsers($conn) {
    return $conn->query("SELECT id,username,full_name,role,created_at FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);
}
function addUser($conn, $username, $password, $fullName, $role) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $s = $conn->prepare("INSERT INTO users (username,password,full_name,role) VALUES (?,?,?,?)");
    $s->bind_param("ssss", $username, $hash, $fullName, $role); 
    return $s->execute();
}
function updateUser($conn, $id, $username, $fullName, $role, $password = null) {
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $s = $conn->prepare("UPDATE users SET username=?,full_name=?,role=?,password=? WHERE id=?");
        $s->bind_param("ssssi", $username, $fullName, $role, $hash, $id);
    } else {
        $s = $conn->prepare("UPDATE users SET username=?,full_name=?,role=? WHERE id=?");
        $s->bind_param("sssi", $username, $fullName, $role, $id);
    }
    return $s->execute();
}
function deleteUser($conn, $id) {
    $s = $conn->prepare("DELETE FROM users WHERE id=?"); 
    $s->bind_param("i",$id); 
    return $s->execute();
}

/* ---------- Cart ---------- */
function addToCart($conn, $pid, $qty) {
    $p = getProduct($conn, $pid);
    if (!$p || $p['quantity'] < $qty || $qty <= 0) return false;
    $new = $p['quantity'] - $qty;
    $s = $conn->prepare("UPDATE products SET quantity=? WHERE id=?"); 
    $s->bind_param("ii",$new,$pid); $s->execute();
    $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;
    return true;
}
function removeFromCart($conn, $pid) {
    if (!isset($_SESSION['cart'][$pid])) return false;
    $qty = $_SESSION['cart'][$pid];
    $p = getProduct($conn, $pid);
    if ($p) {
        $new = $p['quantity'] + $qty;
        $s = $conn->prepare("UPDATE products SET quantity=? WHERE id=?"); 
        $s->bind_param("ii",$new,$pid); $s->execute();
    }
    unset($_SESSION['cart'][$pid]); 
    return true;
}
function updateCartQuantity($conn, $pid, $newQty) {
    if ($newQty <= 0) return removeFromCart($conn, $pid);
    if (!isset($_SESSION['cart'][$pid])) return false;
    $old = $_SESSION['cart'][$pid]; 
    $p = getProduct($conn, $pid);
    if (!$p) return false;
    $diff = $newQty - $old;
    if ($diff > 0 && $p['quantity'] < $diff) return false;
    $newStock = $p['quantity'] - $diff;
    $s = $conn->prepare("UPDATE products SET quantity=? WHERE id=?"); 
    $s->bind_param("ii",$newStock,$pid); $s->execute();
    $_SESSION['cart'][$pid] = $newQty; 
    return true;
}
function getCartItems($conn) {
    $items = [];
    if (empty($_SESSION['cart'])) return $items;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $p = getProduct($conn, $id);
        if ($p) $items[] = [
            'id'=>$id, 'name'=>$p['name'], 'price'=>$p['price'],
            'quantity'=>$qty, 'subtotal'=>$p['price']*$qty
        ];
    }
    return $items;
}

/* ---------- Transactions ---------- */
function processCheckout($conn, $cash) {
    $items = getCartItems($conn);
    $total = array_sum(array_column($items, 'subtotal'));
    if (empty($items) || $cash < $total) return false;

    $change = $cash - $total;
    $code = 'TXN-' . date('Ymd-His') . '-' . rand(100,999);
    $userId = $_SESSION['user_id'] ?? null;
    $cashierName = $_SESSION['full_name'] ?? 'Unknown';

    $s = $conn->prepare("INSERT INTO transactions (code,user_id,cashier_name,total,cash,change_amount) VALUES (?,?,?,?,?,?)");
    $s->bind_param("sisddd", $code, $userId, $cashierName, $total, $cash, $change);
    $s->execute();
    $txId = $conn->insert_id;

    foreach ($items as $it) {
        $s = $conn->prepare("INSERT INTO sale_items (transaction_id,product_id,product_name,price,quantity,subtotal) VALUES (?,?,?,?,?,?)");
        $s->bind_param("iisdid", $txId, $it['id'], $it['name'], $it['price'], $it['quantity'], $it['subtotal']);
        $s->execute();
    }
    $_SESSION['cart'] = [];
    return $txId;
}
function getTransactions($conn) {
    return $conn->query("SELECT * FROM transactions ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}
function getTransaction($conn, $id) {
    $s = $conn->prepare("SELECT * FROM transactions WHERE id=?"); 
    $s->bind_param("i",$id); $s->execute();
    return $s->get_result()->fetch_assoc();
}
function getTransactionItems($conn, $id) {
    $s = $conn->prepare("SELECT * FROM sale_items WHERE transaction_id=?"); 
    $s->bind_param("i",$id); $s->execute();
    return $s->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* ---------- Dashboard ---------- */
function dashboardStats($conn) {
    $stats = [];
    $stats['products'] = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
    $stats['stock']    = $conn->query("SELECT COALESCE(SUM(quantity),0) c FROM products")->fetch_assoc()['c'];
    $stats['value']    = $conn->query("SELECT COALESCE(SUM(price*quantity),0) c FROM products")->fetch_assoc()['c'];
    $stats['sales_today'] = $conn->query("SELECT COALESCE(SUM(total),0) c FROM transactions WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
    $stats['tx_count']  = $conn->query("SELECT COUNT(*) c FROM transactions")->fetch_assoc()['c'];
    $stats['low_stock'] = $conn->query("SELECT COUNT(*) c FROM products WHERE quantity <= 5")->fetch_assoc()['c'];
    return $stats;
}
?>