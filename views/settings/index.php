<?php
$pageTitle = "Pengaturan";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    
    // Handle file upload
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = UPLOAD_PATH . 'profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            // Delete old photo if exists
            if (!empty($user['photo']) && file_exists($user['photo'])) {
                unlink($user['photo']);
            }
            $photo = $filePath;
        }
    }
    
    $query = "UPDATE users SET full_name = ?, email = ?, photo = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$full_name, $email, $photo, $_SESSION['user_id']])) {
        $_SESSION['success_message'] = 'Profil berhasil diperbarui';
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        header('Location: index.php');
        exit();
    } else {
        $error = 'Gagal memperbarui profil';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Password saat ini salah';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru tidak cocok';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            $_SESSION['success_message'] = 'Password berhasil diubah';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal mengubah password';
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pengaturan</h1>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <!-- Profile Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Profil Pengguna</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <?php if (!empty($user['photo'])): ?>
                        <img src="<?php echo SITE_URL . '/uploads/profiles/' . basename($user['photo']); ?>" 
                             class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user text-white fa-2x"></i>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label for="photo" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-camera me-1"></i> Ubah Foto
                            </label>
                            <input type="file" id="photo" name="photo" accept="image/*" class="d-none" 
                                   onchange="document.getElementById('photo-name').textContent = this.files[0]?.name || 'Pilih file'">
                            <div id="photo-name" class="form-text">Format: JPG, PNG (Maks. 2MB)</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <div class="form-text">Username tidak dapat diubah</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    
                    <input type="hidden" name="update_profile" value="1">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Password Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru *</label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6">
                    </div>
                    
                    <input type="hidden" name="change_password" value="1">
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </form>
            </div>
        </div>
        
        <!-- System Settings -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pengaturan Sistem</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Mode Tampilan</label>
                    <div>
                        <button class="btn btn-outline-secondary" id="dark-mode-toggle">
                            <i class="fas fa-moon me-1"></i> Mode Gelap
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Bahasa</label>
                    <select class="form-select">
                        <option selected>Indonesia</option>
                        <option>English</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notifikasi Email</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="email-notifications" checked>
                        <label class="form-check-label" for="email-notifications">
                            Aktifkan notifikasi email
                        </label>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary">Simpan Pengaturan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle photo preview
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.rounded-circle');
                    if (img) {
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
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
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>