<?php
// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hanya mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'arsip_dokumen');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'Sistem Pengarsipan Dokumen');
define('SITE_URL', 'http://localhost/arsip-dokumen');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/arsip-dokumen/uploads/');

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

// ==================== AUTH FUNCTIONS ==================== //

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

// ==================== RETURN URL FUNCTIONS ==================== //

// Function to handle redirect back to original page
function getCurrentMenu() {
    $currentUrl = $_SERVER['REQUEST_URI'];
    
    if (strpos($currentUrl, 'incoming.php') !== false) return 'incoming';
    if (strpos($currentUrl, 'outgoing.php') !== false) return 'outgoing';
    if (strpos($currentUrl, 'reports.php') !== false) return 'reports';
    if (strpos($currentUrl, 'search.php') !== false) return 'search';
    if (strpos($currentUrl, 'dashboard.php') !== false) return 'dashboard';
    
    return 'incoming'; // default
}

function setReturnUrl() {
    $menu = getCurrentMenu();
    $_SESSION['return_url'] = $menu;
    return $menu;
}

function getReturnUrl() {
    // Prioritaskan parameter GET, lalu session, terakhir default
    $menu = $_GET['return'] ?? $_SESSION['return_url'] ?? 'incoming';
    $_SESSION['return_url'] = $menu;
    
    $urls = [
        'incoming' => 'incoming.php',
        'outgoing' => 'outgoing.php',
        'reports' => 'reports.php',
        'search' => 'search.php',
        'dashboard' => '../dashboard/index.php'
    ];
    
    return $urls[$menu] ?? 'incoming.php';
}

function redirectToReturnUrl() {
    $url = getReturnUrl();
    header('Location: ' . $url);
    exit();
}

function getReturnUrlParam() {
    $menu = $_GET['return'] ?? $_SESSION['return_url'] ?? 'incoming';
    return 'return=' . urlencode($menu);
}

// ==================== DOCUMENT TYPE FUNCTIONS ==================== //

// Function to get return URL based on document type
function getReturnUrlByDocumentType($documentType) {
    $urls = [
        'incoming' => 'incoming.php',
        'outgoing' => 'outgoing.php',
        'report' => 'reports.php',
        'other' => 'incoming.php'
    ];
    
    return $urls[$documentType] ?? 'incoming.php';
}

// Function to set return URL based on referer
function setReturnUrlFromReferer() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        
        if (strpos($referer, 'outgoing.php') !== false) {
            $_SESSION['return_url'] = 'outgoing';
        } elseif (strpos($referer, 'reports.php') !== false) {
            $_SESSION['return_url'] = 'reports';
        } elseif (strpos($referer, 'incoming.php') !== false) {
            $_SESSION['return_url'] = 'incoming';
        }
        // Jika tidak ada yang cocok, biarkan session return_url yang sudah ada
    }
}

// Function to get document type label
function getDocumentTypeLabel($documentType) {
    $labels = [
        'incoming' => 'Surat Masuk',
        'outgoing' => 'Surat Keluar',
        'report' => 'Laporan',
        'other' => 'Lainnya'
    ];
    
    return $labels[$documentType] ?? 'Lainnya';
}

// ==================== DEBUG FUNCTION ==================== //
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}