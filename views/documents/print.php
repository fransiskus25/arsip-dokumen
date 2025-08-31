<?php
$pageTitle = "Cetak Dokumen";
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Cetak Dokumen</title>
    <style>
        /* Styles khusus untuk cetak */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .print-header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .print-header .subtitle {
            font-size: 16px;
            color: #666;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 14px;
        }
        
        .document-content {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .document-body {
            font-size: 14px;
            line-height: 1.8;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        
        /* Hide unnecessary elements for print */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .print-container {
                border: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-header {
                border-bottom: 2px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1><?php echo SITE_NAME; ?></h1>
            <div class="subtitle">Detail Dokumen</div>
        </div>
        
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
            <div class="info-value" style="font-weight: bold; font-size: 16px;"><?php echo htmlspecialchars($document['title'] ?? '-'); ?></div>
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
                <span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; margin-right: 5px; font-size: 12px;"><?php echo trim($tag); ?></span>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($document['description'])): ?>
        <div class="document-content">
            <div class="info-label">KETERANGAN</div>
            <div class="document-body"><?php echo nl2br(htmlspecialchars($document['description'])); ?></div>
        </div>
        <?php endif; ?>

        <div class="info-item">
            <div class="info-label">DIBUAT OLEH</div>
            <div class="info-value"><?php echo htmlspecialchars($document['creator_name'] ?? '-'); ?></div>
        </div>

        <?php if ($fileExists && !empty($fileName)): ?>
        <div class="info-item">
            <div class="info-label">FILE LAMPIRAN</div>
            <div class="info-value"><?php echo htmlspecialchars($fileName); ?></div>
        </div>
        <?php endif; ?>

        <div class="footer">
            Dicetak pada: <?php echo date('d/m/Y H:i'); ?> oleh <?php echo $_SESSION['username'] ?? 'Unknown'; ?>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <script>
    // Auto print ketika halaman dimuat
    window.onload = function() {
        window.print();
    }
    </script>
</body>
</html>