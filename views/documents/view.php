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

// Mendapatkan ekstensi file untuk preview
$fileExtension = '';
if ($fileExists && !empty($fileName)) {
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
}

// Dapatkan relative path untuk web
$relativeFilePath = '';
if ($fileExists && !empty($filePath)) {
    $relativeFilePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
}

// PERBAIKAN: Buat URL download yang benar
$downloadUrl = 'download.php?id=' . $document['id'];

// Dapatkan URL kembali berdasarkan jenis dokumen
$returnUrl = getReturnUrlByDocumentType($document['document_type']);
$returnParam = 'return=' . urlencode($document['document_type']);
$documentTypeLabel = getDocumentTypeLabel($document['document_type']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Detail Dokumen</title>
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
        .document-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* HEADER PAGE */
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        /* CARD STYLES */
        .document-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .card-body-custom {
            padding: 1.5rem;
        }
        
        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
        
        /* FILE SECTION */
        .file-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .file-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        /* BUTTON STYLES */
        .btn-document {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
        }
        
        .btn-download:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-preview {
            background: #17a2b8;
            color: white;
        }
        
        .btn-preview:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* ACTIONS SECTION */
        .actions-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
        }
        
        .btn-action {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1rem;
            margin-bottom: 0.75rem;
            text-align: left;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: white;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-document {
                width: 100%;
                justify-content: center;
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
        <div class="document-container">
            <!-- HEADER SECTION -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="h3 mb-1"><i class="fas fa-file-alt me-2"></i>Detail Dokumen</h1>
                        <p class="text-muted mb-0">Informasi lengkap tentang dokumen arsip - <?php echo $documentTypeLabel; ?></p>
                    </div>
                    <a href="<?php echo $returnUrl . '?' . $returnParam; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke <?php echo $documentTypeLabel; ?>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="document-card">
                        <div class="card-header-custom">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dokumen</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">NOMOR DOKUMEN</div>
                                    <div class="info-value"><?php echo htmlspecialchars($document['document_number'] ?? '-'); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">TANGGAL DIBUAT</div>
                                    <div class="info-value"><?php echo formatDateIndonesian($document['created_at'] ?? ''); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">JENIS DOKUMEN</div>
                                    <div class="info-value">
                                        <?php 
                                        $typeLabels = [
                                            'incoming' => 'Surat Masuk',
                                            'outgoing' => 'Surat Keluar', 
                                            'report' => 'Laporan'
                                        ];
                                        echo $typeLabels[$document['document_type']] ?? 'Lainnya';
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">KATEGORI</div>
                                    <div class="info-value"><?php echo htmlspecialchars($document['category_name'] ?? '-'); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">JUDUL/PERIHAL</div>
                                <div class="info-value fw-bold fs-5"><?php echo htmlspecialchars($document['title'] ?? '-'); ?></div>
                            </div>

                            <?php if ($document['document_type'] === 'incoming'): ?>
                            <div class="info-item">
                                <div class="info-label">PENGIRIM</div>
                                <div class="info-value"><?php echo htmlspecialchars($document['sender'] ?? '-'); ?></div>
                            </div>
                            <?php elseif ($document['document_type'] === 'outgoing'): ?>
                            <div class="info-item">
                                <div class="info-label">TUJUAN</div>
                                <div class="info-value"><?php echo htmlspecialchars($document['receiver'] ?? '-'); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($document['tags'])): ?>
                            <div class="info-item">
                                <div class="info-label">TAGS</div>
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
                                <div class="info-label">KETERANGAN</div>
                                <div class="info-value"><?php echo nl2br(htmlspecialchars($document['description'])); ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="info-label">DIBUAT OLEH</div>
                                <div class="info-value"><?php echo htmlspecialchars($document['creator_name'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- File Section -->
                    <div class="file-section">
                        <div class="file-icon">
                            <?php if ($fileExists && !empty($fileName)): ?>
                            <?php
                            $iconClass = 'fa-file';
                            switch ($fileExtension) {
                                case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                case 'jpg': case 'jpeg': case 'png': case 'gif': $iconClass = 'fa-file-image'; break;
                                case 'zip': case 'rar': $iconClass = 'fa-file-archive'; break;
                            }
                            ?>
                            <i class="fas <?php echo $iconClass; ?>"></i>
                            <?php else: ?>
                            <i class="fas fa-file"></i>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($fileExists && !empty($fileName)): ?>
                        <h5><?php echo htmlspecialchars($fileName); ?></h5>
                        <p class="mb-3">
                            <i class="fas fa-database me-1"></i>
                            <?php echo round($fileSize / 1024, 2); ?> KB
                        </p>
                        
                        <div class="action-buttons">
                            <!-- PERBAIKAN LINK DOWNLOAD DI SINI -->
                            <a href="<?php echo $downloadUrl; ?>" 
                               class="btn-document btn-download">
                                <i class="fas fa-download"></i>Unduh File
                            </a>
                            
                            <?php if (in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])): ?>
                            <button class="btn-document btn-preview" onclick="previewFile('<?php echo $fileExtension; ?>', '<?php echo $relativeFilePath; ?>')">
                                <i class="fas fa-eye"></i>Preview
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <h5>Tidak ada file</h5>
                        <p>Dokumen ini tidak memiliki file attachment</p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions Section -->
                    <div class="actions-section">
                        <h5 class="mb-3"><i class="fas fa-cog me-2"></i>Aksi</h5>
                        
                        <a href="edit.php?id=<?php echo $document['id']; ?>&<?php echo $returnParam; ?>" class="btn-action">
                            <i class="fas fa-edit"></i>Edit Dokumen
                        </a>
                        
                        <a href="delete.php?id=<?php echo $document['id']; ?>&<?php echo $returnParam; ?>" class="btn-action"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                            <i class="fas fa-trash"></i>Hapus Dokumen
                        </a>
                        
                        <a href="print.php?id=<?php echo $document['id']; ?>" class="btn-action" target="_blank">
                            <i class="fas fa-print"></i>Cetak Dokumen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalTitle">Preview Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="previewIframe" width="100%" height="600px" frameborder="0"></iframe>
                    <img id="previewImage" src="" class="img-fluid d-none">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewFile(fileType, fileUrl) {
        console.log("Previewing:", fileType, fileUrl);
        
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        const iframe = document.getElementById('previewIframe');
        const image = document.getElementById('previewImage');
        const title = document.getElementById('previewModalTitle');
        
        if (fileUrl) {
            if (fileType === 'pdf') {
                title.textContent = 'Preview PDF';
                iframe.classList.remove('d-none');
                image.classList.add('d-none');
                iframe.src = fileUrl;
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                title.textContent = 'Preview Gambar';
                iframe.classList.add('d-none');
                image.classList.remove('d-none');
                image.src = fileUrl;
            }
            modal.show();
        } else {
            alert('File tidak ditemukan');
        }
    }

    // Debug info
    console.log("Download URL:", "<?php echo $downloadUrl; ?>");
    </script>
</body>
</html>