<?php
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Handle return URL
$returnMenu = $_GET['return'] ?? $_SESSION['return_url'] ?? 'incoming';
$_SESSION['return_url'] = $returnMenu;

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = '❌ ID dokumen tidak valid!';
    redirectToReturnUrl();
    exit();
}

$id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();
$documentController = new DocumentController();

$document = $documentController->getDocumentById($id);
if (!$document) {
    $_SESSION['error_message'] = '❌ Dokumen tidak ditemukan!';
    redirectToReturnUrl();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm === 'yes') {
        $result = $documentController->deleteDocument($id);
        
        if ($result) {
            $_SESSION['success_message'] = '✅ Dokumen berhasil dihapus!';
            
            // Log activity
            $logMessage = "Dokumen {$document['document_number']} - {$document['title']} telah dihapus oleh {$_SESSION['full_name']}";
            error_log($logMessage);
            
        } else {
            $_SESSION['error_message'] = '❌ Gagal menghapus dokumen!';
        }
    } else {
        $_SESSION['info_message'] = '⚠️ Penghapusan dibatalkan.';
    }
    
    redirectToReturnUrl();
    exit();
}

$pageTitle = "Hapus Dokumen";
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Hapus Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* ... (CSS yang sama) ... */
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="confirmation-card animate__animated animate__fadeIn">
                        <div class="confirmation-header">
                            <h3 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Penghapusan
                            </h3>
                        </div>
                        
                        <!-- ... (Konten konfirmasi) ... -->
                        
                        <form method="POST" action="">
                            <!-- ... (Form konfirmasi) ... -->
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn-danger-confirm">
                                    <i class="fas fa-trash me-2"></i>HAPUS PERMANEN
                                </button>
                                <a href="view.php?id=<?php echo $id; ?>&<?php echo getReturnUrlParam(); ?>" class="btn btn-warning btn-lg">
                                    <i class="fas fa-edit me-2"></i>Edit Dokumen
                                </a>
                                <a href="<?php echo getReturnUrl(); ?>" class="btn-cancel text-center">
                                    <i class="fas fa-times me-2"></i>BATALKAN
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ... (JavaScript yang sama) ...
    </script>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>