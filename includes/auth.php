<?php
// Authentication functions

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Require login for protected pages
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Check if user has required role
function has_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    global $conn;
    $sql = "SELECT role FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && $user['role'] === $required_role;
}

// Require specific role for protected pages
function require_role($required_role) {
    if (!has_role($required_role)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Update last login timestamp
function update_last_login($user_id) {
    global $conn;
    $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
}

// Get current user data
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $conn;
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in for protected pages
require_login(); 