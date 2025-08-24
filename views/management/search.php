<?php
$pageTitle = "Pencarian Dokumen";
require_once '../partials/header.php';

$documentController = new DocumentController();
$categories = $documentController->getCategories();

// Get search results
$documents = null;
if (!empty($_GET['search']) || !empty($_GET['category']) || !empty($_GET['year']) || !empty($_GET['month']) || !empty($_GET['type'])) {
    $filters = [];
    
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
    if (!empty($_GET['type'])) {
        $filters['document_type'] = $_GET['type'];
    }
    
    $documents = $documentController->getDocuments($filters);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pencarian Dokumen</h1>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Kata kunci pencarian..." name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">Semua Jenis</option>
                    <option value="incoming" <?php echo (!empty($_GET['type']) && $_GET['type'] == 'incoming') ? 'selected' : ''; ?>>Surat Masuk</option>
                    <option value="outgoing" <?php echo (!empty($_GET['type']) && $_GET['type'] == 'outgoing') ? 'selected' : ''; ?>>Surat Keluar</option>
                    <option value="report" <?php echo (!empty($_GET['type']) && $_GET['type'] == 'report') ? 'selected' : ''; ?>>Laporan</option>
                </select>
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
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
                <a href="search.php" class="btn btn-secondary">
                    <i class="fas fa-sync me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Search Results -->
<?php if ($documents): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Hasil Pencarian</h5>
    </div>
    <div class="card-body">
        <?php if ($documents->rowCount() > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Jenis</th>
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
                        <td>
                            <?php 
                            $typeLabels = [
                                'incoming' => 'Surat Masuk',
                                'outgoing' => 'Surat Keluar',
                                'report' => 'Laporan'
                            ];
                            echo $typeLabels[$document['document_type']] ?? 'Lainnya';
                            ?>
                        </td>
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
                            <a href="../documents/view.php?id=<?php echo $document['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Tidak ada dokumen yang ditemukan.
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once '../partials/footer.php'; ?>