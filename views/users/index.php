<?php
$pageTitle = "Manajemen User";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Pastikan hanya admin yang bisa mengakses
if (!hasPermission('admin')) {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit();
}

// Inisialisasi database dan model
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Tambah user baru
        $user->username = $_POST['username'];
        $user->password = $_POST['password'];
        $user->email = $_POST['email'];
        $user->full_name = $_POST['full_name'];
        $user->role = $_POST['role'];
        
        if ($user->create()) {
            $_SESSION['success_message'] = 'User berhasil ditambahkan!';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal menambahkan user! Username atau email mungkin sudah ada.';
        }
    }
    
    if (isset($_POST['edit_user'])) {
        // Edit user
        $user->id = $_POST['id'];
        $user->username = $_POST['username'];
        $user->email = $_POST['email'];
        $user->full_name = $_POST['full_name'];
        $user->role = $_POST['role'];
        
        // Handle password jika diisi
        if (!empty($_POST['password'])) {
            $user->password = $_POST['password'];
        }
        
        if ($user->update()) {
            $_SESSION['success_message'] = 'User berhasil diperbarui!';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal memperbarui user!';
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $user->id = $_GET['delete'];
    
    // Cegah admin menghapus dirinya sendiri
    if ($_GET['delete'] != $_SESSION['user_id']) {
        if ($user->delete()) {
            $_SESSION['success_message'] = 'User berhasil dihapus!';
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal menghapus user!';
        }
    } else {
        $error = 'Tidak dapat menghapus akun sendiri!';
    }
}

// Get all users
$users = $user->read();

require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-1"></i> Tambah User
        </button>
    </div>
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

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Dibuat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($usr = $users->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($usr['username']); ?></td>
                        <td><?php echo htmlspecialchars($usr['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($usr['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $usr['role'] === 'admin' ? 'danger' : 
                                    ($usr['role'] === 'pimpinan' ? 'warning' : 'info'); 
                            ?>">
                                <?php echo ucfirst($usr['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($usr['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $usr['id'] == $_SESSION['user_id'] ? 'success' : 'secondary'; ?>">
                                <?php echo $usr['id'] == $_SESSION['user_id'] ? 'Anda' : 'Aktif'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                data-id="<?php echo $usr['id']; ?>"
                                data-username="<?php echo htmlspecialchars($usr['username']); ?>"
                                data-email="<?php echo htmlspecialchars($usr['email']); ?>"
                                data-full_name="<?php echo htmlspecialchars($usr['full_name']); ?>"
                                data-role="<?php echo $usr['role']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <?php if ($usr['id'] != $_SESSION['user_id']): ?>
                            <a href="index.php?delete=<?php echo $usr['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Hapus user <?php echo htmlspecialchars($usr['username']); ?>? Tindakan ini tidak dapat dibatalkan.')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required 
                               pattern="[a-zA-Z0-9_]{3,20}" title="Username harus 3-20 karakter, hanya boleh huruf, angka, dan underscore">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required 
                               minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="pimpinan">Pimpinan</option>
                            <option value="staf">Staf</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="add_user" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" id="editUsername" required 
                               pattern="[a-zA-Z0-9_]{3,20}" title="Username harus 3-20 karakter, hanya boleh huruf, angka, dan underscore">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" 
                               minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <div class="form-text">Biarkan kosong jika tidak ingin mengubah password</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name" id="editFullName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" id="editRole" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="pimpinan">Pimpinan</option>
                            <option value="staf">Staf</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="id" id="editUserId">
                    <input type="hidden" name="edit_user" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit modal data
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editUserModal');
    
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            document.getElementById('editUserId').value = button.getAttribute('data-id');
            document.getElementById('editUsername').value = button.getAttribute('data-username');
            document.getElementById('editEmail').value = button.getAttribute('data-email');
            document.getElementById('editFullName').value = button.getAttribute('data-full_name');
            document.getElementById('editRole').value = button.getAttribute('data-role');
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