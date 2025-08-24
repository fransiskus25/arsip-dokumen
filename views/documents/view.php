<?php
$pageTitle = "Detail Dokumen";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

if (!isset($_GET['id'])) {
    header('Location: incoming.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$documentController = new DocumentController();
$document = $documentController->getDocumentById($_GET['id']);

if (!$document) {
    echo '<div class="alert alert-danger">Dokumen tidak ditemukan!</div>';
    require_once '../partials/footer.php';
    exit();
}

$fileInfo = $documentController->getDocumentFileInfo($_GET['id']);
$fileExists = $fileInfo && !empty($fileInfo['exists']) ? $fileInfo['exists'] : false;
$fileName = $fileInfo['name'] ?? '';
$fileSize = $fileInfo['size'] ?? 0;
$filePath = $fileInfo['path'] ?? '';

// Format tanggal Indonesia
function formatDateIndonesian($date) {
    if (empty($date)) return '-';
    
    $months = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    
    $englishDate = date('d F Y H:i', strtotime($date));
    return str_replace(array_keys($months), array_values($months), $englishDate);
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Detail Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-gradient: linear-gradient(135deg, #434343 0%, #000000 100%);
        }

        .premium-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .premium-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.15);
        }

        .premium-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
            border-radius: 20px 20px 0 0;
        }

        .premium-body {
            padding: 30px;
        }

        .info-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #212529;
            font-weight: 500;
        }

        .file-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .file-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            transition: all 0.3s ease;
        }

        .file-card:hover::before {
            transform: rotate(45deg) translate(20px, 20px);
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 15px;
            text-align: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-edit { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .btn-edit:hover { box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4); }

        .btn-delete { background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%); }
        .btn-delete:hover { box-shadow: 0 10px 25px rgba(255, 117, 140, 0.4); }

        .btn-print { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .btn-print:hover { box-shadow: 0 10px 25px rgba(250, 112, 154, 0.4); }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
        }

        .badge-premium {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .document-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .document-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        /* Dark mode support */
        [data-bs-theme="dark"] .premium-card {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        }

        [data-bs-theme="dark"] .info-item {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            border-left: 4px solid #667eea;
        }

        [data-bs-theme="dark"] .info-label {
            color: #cbd5e0;
        }

        [data-bs-theme="dark"] .info-value {
            color: #e2e8f0;
        }

        /* Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .premium-body {
                padding: 20px;
            }
            
            .document-title {
                font-size: 1.8rem;
            }
            
            .info-value {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="document-title animate__animated animate__fadeInDown">
                                <i class="fas fa-file-alt me-3"></i>Detail Dokumen
                            </h1>
                            <p class="document-subtitle animate__animated animate__fadeIn">
                                Informasi lengkap tentang dokumen arsip
                            </p>
                        </div>
                        <a href="incoming.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Information -->
                <div class="col-lg-8 mb-4">
                    <div class="premium-card animate__animated animate__fadeInLeft">
                        <div class="premium-header">
                            <h4 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informasi Dokumen
                            </h4>
                        </div>
                        <div class="premium-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-hashtag me-2"></i>Nomor Dokumen
                                        </div>
                                        <div class="info-value">
                                            <?php echo htmlspecialchars($document['document_number'] ?? '-'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-calendar me-2"></i>Tanggal Dibuat
                                        </div>
                                        <div class="info-value">
                                            <?php echo formatDateIndonesian($document['created_at'] ?? ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-tag me-2"></i>Jenis Dokumen
                                </div>
                                <div class="info-value">
                                    <?php 
                                    $typeLabels = [
                                        'incoming' => 'Surat Masuk',
                                        'outgoing' => 'Surat Keluar', 
                                        'report' => 'Laporan'
                                    ];
                                    $type = $typeLabels[$document['document_type']] ?? 'Lainnya';
                                    echo '<span class="badge-premium">' . $type . '</span>';
                                    ?>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-heading me-2"></i>Judul/Perihal
                                </div>
                                <div class="info-value fs-4 fw-bold text-primary">
                                    <?php echo htmlspecialchars($document['title'] ?? '-'); ?>
                                </div>
                            </div>

                            <?php if ($document['document_type'] === 'incoming'): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user-arrow-down me-2"></i>Pengirim
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($document['sender'] ?? '-'); ?>
                                </div>
                            </div>
                            <?php elseif ($document['document_type'] === 'outgoing'): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user-arrow-up me-2"></i>Tujuan
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($document['receiver'] ?? '-'); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-folder me-2"></i>Kategori
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($document['category_name'] ?? '-'); ?>
                                </div>
                            </div>

                            <?php if (!empty($document['tags'])): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-tags me-2"></i>Tags
                                </div>
                                <div class="info-value">
                                    <?php 
                                    $tags = explode(',', $document['tags']);
                                    foreach ($tags as $tag): 
                                        if (!empty(trim($tag))):
                                    ?>
                                    <span class="badge bg-primary me-1 mb-1"><?php echo trim($tag); ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($document['description'])): ?>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-align-left me-2"></i>Keterangan
                                </div>
                                <div class="info-value">
                                    <?php echo nl2br(htmlspecialchars($document['description'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user me-2"></i>Dibuat Oleh
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($document['creator_name'] ?? '-'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- File Section -->
                    <div class="premium-card mb-4 animate__animated animate__fadeInRight">
                        <div class="file-card">
                            <div class="icon-wrapper float-animation">
                                <?php if ($fileExists && !empty($fileName)): ?>
                                <?php
                                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $iconClass = 'fa-file';
                                
                                switch ($fileExtension) {
                                    case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                    case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                    case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                    case 'jpg': case 'jpeg': case 'png': case 'gif': $iconClass = 'fa-file-image'; break;
                                }
                                ?>
                                <i class="fas <?php echo $iconClass; ?>"></i>
                                <?php else: ?>
                                <i class="fas fa-file-excel"></i>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($fileExists && !empty($fileName)): ?>
                            <h5 class="mb-2"><?php echo htmlspecialchars($fileName); ?></h5>
                            <p class="mb-3 opacity-75">
                                <i class="fas fa-database me-1"></i>
                                <?php echo round($fileSize / 1024, 2); ?> KB
                            </p>
                            
                            <div class="d-grid gap-2">
                                <a href="<?php echo SITE_URL . '/uploads/documents/' . basename($filePath); ?>" 
                                   target="_blank" class="action-btn">
                                    <i class="fas fa-download me-2"></i>Unduh File
                                </a>
                                
                                <?php if ($fileExtension === 'pdf'): ?>
                                <button class="action-btn btn-primary" 
                                        onclick="previewPDF('<?php echo SITE_URL . '/uploads/documents/' . basename($filePath); ?>')">
                                    <i class="fas fa-eye me-2"></i>Preview PDF
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <h5 class="mb-2">Tidak ada file</h5>
                            <p class="mb-3 opacity-75">Dokumen ini tidak memiliki file attachment</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="premium-card animate__animated animate__fadeInUp">
                        <div class="premium-header">
                            <h4 class="mb-0">
                                <i class="fas fa-cog me-2"></i>Aksi
                            </h4>
                        </div>
                        <div class="premium-body">
                            <div class="d-grid gap-2">
                                <a href="edit.php?id=<?php echo $document['id']; ?>" class="action-btn btn-edit">
                                    <i class="fas fa-edit me-2"></i>Edit Dokumen
                                </a>
                                
                                <a href="delete.php?id=<?php echo $document['id']; ?>" class="action-btn btn-delete"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                    <i class="fas fa-trash me-2"></i>Hapus Dokumen
                                </a>
                                
                                <a href="print.php?id=<?php echo $document['id']; ?>" target="_blank" class="action-btn btn-print">
                                    <i class="fas fa-print me-2"></i>Cetak Dokumen
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Preview Modal -->
    <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf me-2"></i>Preview Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfPreviewIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewPDF(url) {
        const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
        const iframe = document.getElementById('pdfPreviewIframe');
        if (url) {
            iframe.src = url;
            modal.show();
        } else {
            alert('File tidak ditemukan atau tidak dapat dipreview.');
        }
    }

    // Add animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.premium-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
    </script>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>