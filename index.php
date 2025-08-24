<?php
require_once 'config/config.php';

// Redirect to dashboard if logged in, otherwise to login page
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard.php');
} else {
    header('Location: ' . SITE_URL . '/login.php');
}
exit();
?>