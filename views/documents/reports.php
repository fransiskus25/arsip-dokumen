<?php
$pageTitle = "Laporan & Arsip Lain";
require_once '../partials/header.php';

$documentController = new DocumentController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $documentController->createDocument($_POST, $_FILES['document_file']);
    
    if ($result) {
        echo '<div class="alert alert-success">Dokumen berhasil ditambahkan!</div>';
    } else {
        echo '<div class="alert alert-danger">Gagal menambahkan dokumen!</div>';
    }
}

// Get documents
$filters = ['document_type' => 'report'];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (!empty($_GET['category'])) {
    $filters['category_id'] = $_GET['category'];
}
if (!empty($_GET['year'])) {
    $filters['year'] = $_GET['year'];
}
if (!empty($_GET['month'])) {
    $filters['month'] = $_GET['month'];
}

$documents = $documentController->getDocuments($filters);
$categories = $documentController->getCategories();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan & Arsip Lain</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
            <i class="fas fa-plus me-1"></i> Tambah Laporan
        </button>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Cari..." name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="category">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $categories = $documentController->getCategories();
                    while ($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (!empty($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="year">
                    <option value="">Semua Tahun</option>
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (!empty($_GET['year']) && $_GET['year'] == $y) ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="month">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'Nov', 12 => 'Des'
                    ];
                    foreach ($months as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo (!empty($_GET['month']) && $_GET['month'] == $num) ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
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

<!-- Documents Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($document = $documents->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $document['document_number']; ?></td>
                        <td><?php echo date('d M Y', strtotime($document['created_at'])); ?></td>
                        <td><?php echo $document['title']; ?></td>
                        <td><?php echo $document['category_name']; ?></td>
                        <td>
                            <?php if (!empty($document['file_name'])): ?>
                            <a href="<?php echo SITE_URL . '/uploads/documents/' . basename($document['file_path']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Tidak ada file</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $document['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $document['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $document['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Hapus dokumen ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Document Modal -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Laporan/Arsip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nomor Dokumen</label>
                            <input type="text" class="form-control" name="document_number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Dokumen</label>
                            <input type="date" class="form-control" name="document_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                $categories = $documentController->getCategories();
                                while ($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tags</label>
                            <input type="text" class="form-control" name="tags" placeholder="Pisahkan dengan koma">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Dokumen</label>
                        <input type="file" class="form-control" name="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div class="form-text">Format: PDF, Word, JPG, PNG (Maks. 5MB)</div>
                    </div>
                    
                    <input type="hidden" name="document_type" value="report">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>