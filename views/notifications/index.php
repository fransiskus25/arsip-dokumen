<?php
$pageTitle = "Notifikasi";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();

// Handle actions
if (isset($_GET['mark_read'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['mark_read'], $_SESSION['user_id']]);
    $_SESSION['success_message'] = 'Notifikasi ditandai sudah dibaca';
    header('Location: index.php');
    exit();
}

if (isset($_GET['delete'])) {
    $query = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    $_SESSION['success_message'] = 'Notifikasi dihapus';
    header('Location: index.php');
    exit();
}

if (isset($_POST['mark_all_read'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['success_message'] = 'Semua notifikasi ditandai sudah dibaca';
    header('Location: index.php');
    exit();
}

if (isset($_POST['clear_all'])) {
    $query = "DELETE FROM notifications WHERE user_id = ? AND is_read = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['success_message'] = 'Notifikasi yang sudah dibaca dihapus';
    header('Location: index.php');
    exit();
}

// Get notifications
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread notifications
$query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Notifikasi</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <form method="POST" class="d-inline me-2">
            <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-check-double me-1"></i> Tandai Sudah Dibaca
            </button>
        </form>
        <form method="POST" class="d-inline">
            <button type="submit" name="clear_all" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash me-1"></i> Hapus yang Sudah Dibaca
            </button>
        </form>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Notifikasi</h5>
        <span class="badge bg-<?php echo $unreadCount > 0 ? 'danger' : 'success'; ?>">
            <?php echo $unreadCount; ?> belum dibaca
        </span>
    </div>
    
    <div class="card-body p-0">
        <?php if (count($notifications) > 0): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notif): ?>
            <div class="list-group-item <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <span class="badge bg-<?php 
                                echo $notif['type'] === 'danger' ? 'danger' : 
                                    ($notif['type'] === 'warning' ? 'warning' : 
                                    ($notif['type'] === 'success' ? 'success' : 'info')); 
                            ?> me-2">
                                <i class="fas fa-<?php 
                                    echo $notif['type'] === 'danger' ? 'exclamation-triangle' : 
                                        ($notif['type'] === 'warning' ? 'exclamation-circle' : 
                                        ($notif['type'] === 'success' ? 'check-circle' : 'info-circle')); 
                                ?>"></i>
                            </span>
                            <?php echo htmlspecialchars($notif['title']); ?>
                        </h6>
                        <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?>
                        </small>
                    </div>
                    <div class="btn-group">
                        <?php if (!$notif['is_read']): ?>
                        <a href="index.php?mark_read=<?php echo $notif['id']; ?>" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-check"></i>
                        </a>
                        <?php endif; ?>
                        <a href="index.php?delete=<?php echo $notif['id']; ?>" class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Hapus notifikasi ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada notifikasi</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>