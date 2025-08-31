<?php
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Simpan halaman asal berdasarkan referer
setReturnUrlFromReferer();

// Handle return URL - prioritaskan parameter GET, lalu session, terakhir default
$returnMenu = $_GET['return'] ?? $_SESSION['return_url'] ?? 'incoming';
$_SESSION['return_url'] = $returnMenu;

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = '❌ ID dokumen tidak valid!';
    header('Location: ' . getReturnUrl());
    exit();
}

$id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();
$documentController = new DocumentController();

$document = $documentController->getDocumentById($id);
if (!$document) {
    $_SESSION['error_message'] = '❌ Dokumen tidak ditemukan!';
    header('Location: ' . getReturnUrl());
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
    
    // Redirect berdasarkan jenis dokumen yang dihapus
    $redirectUrl = getReturnUrlByDocumentType($document['document_type']);
    header('Location: ' . $redirectUrl);
    exit();
}

$pageTitle = "Hapus Dokumen";
$returnUrl = getReturnUrl();
$returnParam = 'return=' . urlencode($returnMenu);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Hapus Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* RESET TOTAL - HAPUS SEMUA STYLING YANG ADA */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* HEADER SEDERHANA */
        .custom-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* KONTEN UTAMA - PASTIKAN LEBAR PENUH */
        .main-content {
            padding: 2rem;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* CONTAINER UTAMA */
        .confirmation-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* CARD STYLES */
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px 0;
            border: none;
        }

        .confirmation-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px 15px 0 0;
            margin: -40px -40px 30px -40px;
            text-align: center;
        }
        
        .document-info {
            background: #fff5f5;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #e53e3e;
        }
        
        .btn-danger-confirm {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-danger-confirm:hover {
            background: linear-gradient(135deg, #c53030 0%, #9b2c2c 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(229, 62, 62, 0.3);
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-cancel:hover {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(113, 128, 150, 0.3);
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
            color: white;
        }
        
        .warning-icon {
            font-size: 4rem;
            color: #e53e3e;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .confirmation-card {
                padding: 25px;
                margin: 10px 0;
            }
            
            .confirmation-header {
                margin: -25px -25px 20px -25px;
                padding: 20px;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .custom-header {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER SEDERHANA TANPA SIDEBAR -->
    <header class="custom-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-archive me-3 fs-4"></i>
            <h1 class="h4 mb-0"><?php echo SITE_NAME; ?></h1>
        </div>
        <div>
            <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-light btn-sm me-2">
                <i class="fas fa-home me-1"></i>Dashboard
            </a>
            <a href="<?php echo $returnUrl; ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </header>

    <main class="main-content">
        <div class="confirmation-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="confirmation-card">
                        <div class="confirmation-header">
                            <h3 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Penghapusan
                            </h3>
                        </div>
                        
                        <div class="text-center mb-4">
                            <i class="fas fa-exclamation-circle warning-icon"></i>
                            <h4 class="text-danger fw-bold">PERINGATAN!</h4>
                            <p class="text-muted">Anda akan menghapus dokumen berikut. Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                        
                        <!-- Informasi Dokumen -->
                        <div class="document-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Nomor Dokumen:</strong><br>
                                    <span class="text-dark"><?php echo htmlspecialchars($document['document_number']); ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Jenis:</strong><br>
                                    <span class="text-dark">
                                        <?php 
                                        $typeLabels = [
                                            'incoming' => 'Surat Masuk',
                                            'outgoing' => 'Surat Keluar',
                                            'report' => 'Laporan'
                                        ];
                                        echo $typeLabels[$document['document_type']] ?? 'Lainnya';
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Judul:</strong><br>
                                <span class="text-dark"><?php echo htmlspecialchars($document['title']); ?></span>
                            </div>
                            <?php if (!empty($document['file_name'])): ?>
                            <div class="mb-2">
                                <strong>File:</strong><br>
                                <span class="text-dark"><?php echo htmlspecialchars($document['file_name']); ?></span>
                                <small class="text-muted">
                                    (<?php echo !empty($document['file_size']) ? round($document['file_size'] / 1024, 2) : 0; ?> KB)
                                </small>
                            </div>
                            <?php endif; ?>
                            <div>
                                <strong>Dibuat:</strong><br>
                                <span class="text-dark">
                                    <?php echo !empty($document['created_at']) ? date('d M Y H:i', strtotime($document['created_at'])) : '-'; ?>
                                    oleh <?php echo htmlspecialchars($document['creator_name'] ?? 'Unknown'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Perhatian:</strong> Penghapusan dokumen akan menghapus semua data terkait termasuk file yang diunggah. Tindakan ini tidak dapat dikembalikan.
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="confirm" value="yes" id="confirmDelete" required>
                                <label class="form-check-label text-danger fw-bold" for="confirmDelete">
                                    Ya, saya yakin ingin menghapus dokumen ini
                                </label>
                                <div class="invalid-feedback">Anda harus mencentang kotak konfirmasi</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn-danger-confirm">
                                    <i class="fas fa-trash me-2"></i>HAPUS PERMANEN
                                </button>
                                <a href="edit.php?id=<?php echo $id; ?>&<?php echo $returnParam; ?>" class="btn-edit">
                                    <i class="fas fa-edit me-2"></i>EDIT DOKUMEN
                                </a>
                                <a href="view.php?id=<?php echo $id; ?>&<?php echo $returnParam; ?>" class="btn btn-info">
                                    <i class="fas fa-eye me-2"></i>LIHAT DOKUMEN
                                </a>
                                <a href="<?php echo $returnUrl; ?>" class="btn-cancel text-center">
                                    <i class="fas fa-times me-2"></i>BATALKAN
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html>