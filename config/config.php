<?php
// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Site Configuration
define('SITE_NAME', 'Sistem Pengarsipan Dokumen');
define('SITE_URL', 'http://localhost/arsip-dokumen');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/arsip-dokumen/uploads/');

// Include database connection
require_once 'database.php';

// FIX: Autoloader yang lebih aman
spl_autoload_register(function ($class_name) {
    // List of directories to search
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Get user role
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

// Check permission
function hasPermission($requiredRole) {
    $userRole = getUserRole();
    $roles = ['admin' => 3, 'pimpinan' => 2, 'staf' => 1, 'guest' => 0];
    
    return ($roles[$userRole] >= $roles[$requiredRole]);
}

// Simple debug function
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>