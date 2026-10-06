<?php
// Start session for login + cart tracking
session_start();

// Database credentials (XAMPP defaults)
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'inventory_db';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

// If connection fails, stop everything and show the error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Currency symbol used everywhere in the app
define('CURRENCY', '$');

/**
 * Store a one-time message in the session (shown once after a redirect).
 * $type = 'success' or 'error'
 */
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/**
 * Retrieve and clear the flash message.
 * Returns null if there's nothing to show.
 */
function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Count total items in the cart (used for the navbar badge)
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>