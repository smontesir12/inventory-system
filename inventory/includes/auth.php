<?php
require_once __DIR__ . '/../config/db.php';

function requireLogin() {
    if (empty($_SESSION['user_id'])) { 
        header('Location: login.php'); 
        exit; 
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        setFlash('error', 'Access denied — admin only.');
        header('Location: dashboard.php'); 
        exit;
    }
}
?>