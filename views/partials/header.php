<?php
require_once __DIR__ . '/../../config/config.php';
checkAuth();

$currentPage = basename($_SERVER['PHP_SELF']);
$darkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo $darkMode ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Pengarsipan Dokumen - Kelola dokumen dengan mudah dan efisien">
    <meta name="author" content="Sistem Pengarsipan Dokumen">
    
    <title><?php echo SITE_NAME; ?> - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico">
    
    <style>
        /* Additional inline styles for critical elements */
        .main-content {
            min-height: calc(100vh - 56px);
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="app-container">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/dashboard.php">
                <i class="fas fa-archive me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
               
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" id="dark-mode-toggle" title="Toggle Dark Mode">
                            <i class="fas <?php echo $darkMode ? 'fa-sun' : 'fa-moon'; ?>"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-content">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['incoming.php', 'outgoing.php', 'reports.php', 'categories.php']) ? 'active' : ''; ?>" 
                           href="#documentsCollapse" 
                           data-bs-toggle="collapse" 
                           role="button" 
                           aria-expanded="<?php echo in_array($currentPage, ['incoming.php', 'outgoing.php', 'reports.php', 'categories.php']) ? 'true' : 'false'; ?>" 
                           aria-controls="documentsCollapse">
                            <i class="fas fa-file me-2"></i> Dokumen
                        </a>
                        <div class="collapse <?php echo in_array($currentPage, ['incoming.php', 'outgoing.php', 'reports.php', 'categories.php']) ? 'show' : ''; ?>" id="documentsCollapse">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentPage == 'incoming.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/documents/incoming.php">
                                        <i class="fas fa-inbox me-1"></i> Surat Masuk
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentPage == 'outgoing.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/documents/outgoing.php">
                                        <i class="fas fa-paper-plane me-1"></i> Surat Keluar
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentPage == 'reports.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/documents/reports.php">
                                        <i class="fas fa-chart-bar me-1"></i> Laporan & Arsip
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $currentPage == 'categories.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/documents/categories.php">
                                        <i class="fas fa-tags me-1"></i> Kategori
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'search.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/management/search.php">
                            <i class="fas fa-search me-2"></i> Pencarian
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'reports.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/export/reports.php">
                            <i class="fas fa-download me-2"></i> Ekspor & Laporan
                        </a>
                    </li>
                    
                    <?php if (hasPermission('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/users/index.php">
                            <i class="fas fa-users me-2"></i> Manajemen User
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/notifications/index.php">
                            <i class="fas fa-bell me-2"></i> Notifikasi
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/views/settings/index.php">
                            <i class="fas fa-cog me-2"></i> Pengaturan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-area full-width no-overflow">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>