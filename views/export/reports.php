<?php
$pageTitle = "Ekspor & Laporan";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Inisialisasi database dan model
$database = new Database();
$db = $database->getConnection();
$document = new Document($db);
$category = new Category($db);

// Handle filter form submission
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['start_date'])) {
        $filters['start_date'] = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) {
        $filters['end_date'] = $_GET['end_date'];
    }
    if (!empty($_GET['document_type'])) {
        $filters['document_type'] = $_GET['document_type'];
    }
    if (!empty($_GET['category_id'])) {
        $filters['category_id'] = $_GET['category_id'];
    }
}

// Get documents based on filters
$documents = $document->read($filters);
$categories = $category->read();

// Handle export actions
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    exportData($documents, $exportType);
}

function exportData($documents, $type) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan-dokumen-' . date('Y-m-d') . '.' . $type);
    
    $output = fopen('php://output', 'w');
    
    // Header CSV
    fputcsv($output, [
        'No. Dokumen', 
        'Jenis', 
        'Tanggal', 
        'Judul', 
        'Kategori', 
        'Pengirim/Penerima',
        'Deskripsi'
    ]);
    
    // Data rows
    while ($doc = $documents->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $doc['document_number'],
            $doc['document_type'],
            $doc['created_at'],
            $doc['title'],
            $doc['category_name'] ?? '-',
            $doc['document_type'] === 'incoming' ? $doc['sender'] : $doc['receiver'],
            $doc['description']
        ]);
    }
    
    fclose($output);
    exit();
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ekspor & Laporan</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-success" onclick="exportReport('csv')">
                <i class="fas fa-file-csv me-1"></i> Ekspor CSV
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="exportReport('pdf')">
                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
            </button>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filter Laporan</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Jenis Dokumen</label>
                <select class="form-select" name="document_type">
                    <option value="">Semua Jenis</option>
                    <option value="incoming" <?= ($_GET['document_type'] ?? '') === 'incoming' ? 'selected' : '' ?>>Surat Masuk</option>
                    <option value="outgoing" <?= ($_GET['document_type'] ?? '') === 'outgoing' ? 'selected' : '' ?>>Surat Keluar</option>
                    <option value="report" <?= ($_GET['document_type'] ?? '') === 'report' ? 'selected' : '' ?>>Laporan</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="category_id">
                    <option value="">Semua Kategori</option>
                    <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="reports.php" class="btn btn-secondary">
                    <i class="fas fa-sync me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Dokumen</h5>
                <p class="card-text display-6"><?= $documents->rowCount() ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Surat Masuk</h5>
                <p class="card-text display-6"><?= $document->countByType('incoming') ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Surat Keluar</h5>
                <p class="card-text display-6"><?= $document->countByType('outgoing') ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Laporan</h5>
                <p class="card-text display-6"><?= $document->countByType('report') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Documents Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Data Dokumen</h5>
        <span class="badge bg-secondary"><?= $documents->rowCount() ?> hasil</span>
    </div>
    
    <div class="card-body">
        <?php if ($documents->rowCount() > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="exportTable">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Pengirim/Penerima</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($doc = $documents->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($doc['document_number']) ?></td>
                        <td>
                            <span class="badge bg-<?= $doc['document_type'] === 'incoming' ? 'success' : ($doc['document_type'] === 'outgoing' ? 'info' : 'warning') ?>">
                                <?= $doc['document_type'] === 'incoming' ? 'Masuk' : ($doc['document_type'] === 'outgoing' ? 'Keluar' : 'Laporan') ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                        <td><?= htmlspecialchars($doc['title']) ?></td>
                        <td><?= htmlspecialchars($doc['category_name'] ?? '-') ?></td>
                        <td>
                            <?= $doc['document_type'] === 'incoming' ? 
                                htmlspecialchars($doc['sender'] ?? '-') : 
                                htmlspecialchars($doc['receiver'] ?? '-') ?>
                        </td>
                        <td>
                            <a href="../documents/view.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-file-excel fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada data dokumen yang ditemukan</p>
            <a href="../documents/incoming.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Dokumen Pertama
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ekspor Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Pilih format ekspor untuk laporan dokumen:</p>
                <div class="d-grid gap-2">
                    <a href="reports.php?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-success">
                        <i class="fas fa-file-csv me-2"></i> Ekspor CSV
                    </a>
                    <a href="reports.php?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i> Ekspor PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(format) {
    // Redirect dengan parameter export
    const url = new URL(window.location.href);
    url.searchParams.set('export', format);
    window.location.href = url.toString();
}

// Initialize datepicker dengan range yang wajar
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    // Set default dates jika kosong
    if (!startDate.value) {
        startDate.value = '<?= date('Y-m-01') ?>'; // Awal bulan ini
    }
    if (!endDate.value) {
        endDate.value = '<?= date('Y-m-d') ?>'; // Hari ini
    }
    
    // Validasi: end date tidak boleh sebelum start date
    endDate.addEventListener('change', function() {
        if (startDate.value && endDate.value < startDate.value) {
            alert('Tanggal akhir tidak boleh sebelum tanggal mulai!');
            endDate.value = startDate.value;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>