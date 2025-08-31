<?php
$pageTitle = "Edit Dokumen";
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

// Simpan halaman asal berdasarkan referer
setReturnUrlFromReferer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $documentController->updateDocument($_GET['id'], $_POST, $_FILES['document_file']);
    
    if ($result) {
        $_SESSION['flash_message'] = '✅ Dokumen berhasil diperbarui!';
        
        // Redirect berdasarkan jenis dokumen yang diedit
        $redirectUrl = getReturnUrlByDocumentType($document['document_type']);
        header('Location: ' . $redirectUrl);
        exit();
    } else {
        echo '<div class="alert alert-danger">❌ Gagal memperbarui dokumen!</div>';
    }
}

$categories = $documentController->getCategories();
$returnUrl = getReturnUrl();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Edit Dokumen</title>
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
        .form-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* FORM STYLES */
        .premium-form {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px 0;
            border: none;
        }

        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px 15px 0 0;
            margin: -40px -40px 30px -40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }

        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            background: #f8f9fa;
        }

        .file-upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background: #ffffff;
            transform: translateY(-3px);
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 18px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #6c757d 0%, '495057' 100%);
            border: none;
            border-radius: 15px;
            padding: 18px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }

        .btn-cancel:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(108, 117, 125, 0.4);
            color: white;
        }

        .current-file {
            background: #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }

        .tag-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .premium-form {
                padding: 25px;
                margin: 10px 0;
            }
            
            .form-header {
                margin: -25px -25px 20px -25px;
                padding: 20px;
            }
            
            .form-control, .form-select {
                padding: 12px 15px;
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
        <div class="form-container">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 fw-bold text-primary">
                                <i class="fas fa-edit me-2"></i>Edit Dokumen
                            </h1>
                            <p class="text-muted">Perbarui informasi dokumen dengan form berikut</p>
                        </div>
                        <a href="<?php echo $returnUrl; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="premium-form animate-slide-in">
                        <div class="form-header">
                            <h3 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>Form Edit Dokumen
                            </h3>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <!-- Basic Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-hashtag text-primary"></i>Nomor Dokumen *
                                        </label>
                                        <input type="text" class="form-control" name="document_number" 
                                               value="<?php echo htmlspecialchars($document['document_number'] ?? ''); ?>" 
                                               required placeholder="Contoh: 001/SKI/2023">
                                        <div class="invalid-feedback">Harap isi nomor dokumen</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-calendar text-primary"></i>Tanggal Dokumen *
                                        </label>
                                        <input type="date" class="form-control" name="document_date" 
                                               value="<?php echo !empty($document['created_at']) ? date('Y-m-d', strtotime($document['created_at'])) : date('Y-m-d'); ?>" 
                                               required>
                                        <div class="invalid-feedback">Harap pilih tanggal dokumen</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sender/Receiver -->
                            <?php if ($document['document_type'] === 'incoming'): ?>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-arrow-down text-success"></i>Pengirim *
                                </label>
                                <input type="text" class="form-control" name="sender" 
                                       value="<?php echo htmlspecialchars($document['sender'] ?? ''); ?>" 
                                       required placeholder="Nama pengirim dokumen">
                                <div class="invalid-feedback">Harap isi nama pengirim</div>
                            </div>
                            <?php elseif ($document['document_type'] === 'outgoing'): ?>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-arrow-up text-info"></i>Tujuan *
                                </label>
                                <input type="text" class="form-control" name="receiver" 
                                       value="<?php echo htmlspecialchars($document['receiver'] ?? ''); ?>" 
                                       required placeholder="Nama penerima dokumen">
                                <div class="invalid-feedback">Harap isi nama penerima</div>
                            </div>
                            <?php endif; ?>

                            <!-- Title -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading text-warning"></i>Judul/Perihal *
                                </label>
                                <input type="text" class="form-control" name="title" 
                                       value="<?php echo htmlspecialchars($document['title'] ?? ''); ?>" 
                                       required placeholder="Judul atau perihal dokumen">
                                <div class="invalid-feedback">Harap isi judul dokumen</div>
                            </div>

                            <!-- Category and Tags -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-tag text-danger"></i>Kategori *
                                        </label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php 
                                            if ($categories) {
                                                while ($category = $categories->fetch(PDO::FETCH_ASSOC)): 
                                            ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (($document['category_id'] ?? 0) == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                            <?php 
                                                endwhile;
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">Harap pilih kategori</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-tags text-success"></i>Tags
                                        </label>
                                        <input type="text" class="form-control" name="tags" 
                                               value="<?php echo htmlspecialchars($document['tags'] ?? ''); ?>" 
                                               placeholder="Pisahkan dengan koma (contoh: penting,urgent,arsip)">
                                        <div class="form-text">Tekan Enter atau koma untuk menambah tag</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left text-info"></i>Keterangan
                                </label>
                                <textarea class="form-control" name="description" rows="4" 
                                          placeholder="Tambahkan keterangan atau catatan tentang dokumen ini"><?php echo htmlspecialchars($document['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- File Upload -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-paperclip text-secondary"></i>File Dokumen
                                </label>
                                
                                <?php if (!empty($document['file_name'])): ?>
                                <div class="current-file">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file text-primary me-3 fa-2x"></i>
                                        <div>
                                            <strong>File saat ini:</strong> <?php echo htmlspecialchars($document['file_name']); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo !empty($document['file_size']) ? round($document['file_size'] / 1024, 2) : 0; ?> KB • 
                                                <?php echo !empty($document['created_at']) ? date('d M Y H:i', strtotime($document['created_at'])) : ''; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="file-upload-area" onclick="document.getElementById('documentFile').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h6 class="text-muted mb-2">Klik atau seret file ke sini</h6>
                                    <p class="text-muted small">Format: PDF, Word, Excel, JPG, PNG (Maks. 10MB)</p>
                                    <input type="file" class="d-none" name="document_file" 
                                           id="documentFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                    <div id="fileName" class="text-primary small fw-bold mt-2">Belum ada file dipilih</div>
                                </div>
                            </div>

                            <input type="hidden" name="document_type" value="<?php echo $document['document_type']; ?>">
                            
                            <!-- Submit Buttons -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?php echo $returnUrl; ?>" class="btn-cancel">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    // File upload preview
    document.getElementById('documentFile').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Belum ada file dipilih';
        document.getElementById('fileName').textContent = fileName;
    });

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    });

    // Drag and drop functionality
    const fileArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('documentFile');

    fileArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileArea.style.borderColor = '#667eea';
        fileArea.style.background = '#e3f2fd';
    });

    fileArea.addEventListener('dragleave', () => {
        fileArea.style.borderColor = '#dee2e6';
        fileArea.style.background = '#f8f9fa';
    });

    fileArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileArea.style.borderColor = '#dee2e6';
        fileArea.style.background = '#f8f9fa';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            document.getElementById('fileName').textContent = e.dataTransfer.files[0].name;
        }
    });
    </script>
</body>
</html>