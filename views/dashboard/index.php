<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/../partials/header.php';

// Inisialisasi database dan model
$database = new Database();
$db = $database->getConnection();

// Get document statistics
$document = new Document($db);
$stats = [
    'incoming' => $document->countByType('incoming'),
    'outgoing' => $document->countByType('outgoing'),
    'reports' => $document->countByType('report'),
    'total' => $document->countByType()
];

// PERBAIKAN: Gunakan method khusus untuk recent documents
$recentDocuments = $document->getRecentDocuments(5);

// Get categories for chart
$category = new Category($db);
$categories = $category->read();
$categoryStats = [];
while ($cat = $categories->fetch(PDO::FETCH_ASSOC)) {
    $categoryStats[$cat['name']] = $document->countByCategory($cat['id']);
}

// Get monthly stats for current year
$currentYear = date('Y');
$monthlyStats = $document->getMonthlyStats($currentYear);
$monthlyData = array_fill(0, 12, 0);
foreach ($monthlyStats as $month => $count) {
    if ($month >= 1 && $month <= 12) {
        $monthlyData[$month - 1] = $count;
    }
}

// Get annual trend (last 5 years)
$annualTrend = [];
for ($i = 4; $i >= 0; $i--) {
    $year = $currentYear - $i;
    $annualTrend[$year] = $document->countByYear($year);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="../documents/incoming.php?action=create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Dokumen
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Arsip</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-archive fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Surat Masuk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['incoming']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-inbox fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Surat Keluar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['outgoing']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Laporan & Arsip Lain</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['reports']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data untuk Chart -->
<script>
// Data untuk chart
const monthlyData = <?php 
    echo json_encode($monthlyData);
?>;

const categoryData = {
    labels: <?php 
        echo json_encode(array_keys($categoryStats)); 
    ?>,
    values: <?php echo json_encode(array_values($categoryStats)); ?>
};

const annualTrendData = {
    years: <?php 
        echo json_encode(array_keys($annualTrend)); 
    ?>,
    values: <?php echo json_encode(array_values($annualTrend)); ?>
};

console.log('Chart data loaded:', {
    monthlyData: monthlyData,
    categoryData: categoryData,
    annualTrendData: annualTrendData
});
</script>

<div class="row">
    <!-- Monthly Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Statistik Bulanan (<?php echo date('Y'); ?>)</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                        aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#" onclick="exportChart('monthly', 'statistik-bulanan')">
                            <i class="fas fa-download me-2"></i> Export
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="printChart('monthly')">
                            <i class="fas fa-print me-2"></i> Print
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Kategori</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                        aria-labelledby="dropdownMenuLink2">
                        <li><a class="dropdown-item" href="#" onclick="exportChart('category', 'distribusi-kategori')">
                            <i class="fas fa-download me-2"></i> Export
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="printChart('category')">
                            <i class="fas fa-print me-2"></i> Print
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <?php 
                    $totalCategory = array_sum($categoryStats);
                    foreach ($categoryStats as $categoryName => $count): 
                        $percentage = $totalCategory > 0 ? round(($count / $totalCategory) * 100) : 0;
                    ?>
                    <span class="me-3">
                        <i class="fas fa-circle text-primary"></i> <?php echo $categoryName . ' (' . $percentage . '%)'; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Annual Trend Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Trend Tahunan (<?php echo ($currentYear - 4) . ' - ' . $currentYear; ?>)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="annualTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Documents -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Dokumen Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. Dokumen</th>
                                <th>Judul</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Pengirim/Penerima</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentDocuments && $recentDocuments->rowCount() > 0): ?>
                                <?php while ($doc = $recentDocuments->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['document_number']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                    <td>
                                        <?php 
                                        $typeLabels = [
                                            'incoming' => 'Surat Masuk',
                                            'outgoing' => 'Surat Keluar',
                                            'report' => 'Laporan'
                                        ];
                                        echo $typeLabels[$doc['document_type']] ?? 'Lainnya';
                                        ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($doc['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($doc['document_type'] === 'incoming') {
                                            echo htmlspecialchars($doc['sender'] ?? '-');
                                        } else {
                                            echo htmlspecialchars($doc['receiver'] ?? '-');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="../documents/view.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Belum ada dokumen</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/chart.js"></script>

<script>
// Inisialisasi chart setelah halaman selesai load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded, initializing charts...');
    
    // Pastikan data tersedia
    if (typeof monthlyData === 'undefined') {
        console.warn('Monthly data not found, using demo data');
        window.monthlyData = [12, 19, 15, 17, 19, 23, 17, 15, 18, 16, 14, 11];
    }
    
    if (typeof categoryData === 'undefined') {
        console.warn('Category data not found, using demo data');
        window.categoryData = {
            labels: ['Surat Masuk', 'Surat Keluar', 'Laporan'],
            values: [45, 30, 25]
        };
    }
    
    // Pastikan Chart.js terload
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }
    
    console.log('Charts initialized successfully');
});

// Fallback function untuk export dan print
function exportChart(chartId, fileName) {
    if (typeof chartManager !== 'undefined') {
        chartManager.exportChartAsImage(chartId, fileName);
    } else {
        alert('Chart manager belum siap. Silakan refresh halaman.');
    }
}

function printChart(chartId) {
    if (typeof chartManager !== 'undefined') {
        chartManager.printChart(chartId);
    } else {
        alert('Chart manager belum siap. Silakan refresh halaman.');
    }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>