<?php
require_once 'config/config.php';
require_once 'controllers/AuthController.php';

$authController = new AuthController();
$authController->logout();

header('Location: ' . SITE_URL . '/login.php');
exit();
?>