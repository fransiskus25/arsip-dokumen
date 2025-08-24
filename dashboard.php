<?php
require_once 'config/config.php';
checkAuth();

// Redirect to dashboard view
header('Location: ' . SITE_URL . '/views/dashboard/index.php');
exit();
?>