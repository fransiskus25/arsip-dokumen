<?php
// PERBAIKI: Gunakan layout yang sama dengan halaman lainnya
$pageTitle = "Kategori Dokumen";
require_once __DIR__ . '/../../config/config.php';
checkAuth();

// Inisialisasi controller dengan cara yang benar
$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category->name = $_POST['name'];
        $category->description = $_POST['description'];
        
        if ($category->create()) {
            $_SESSION['success_message'] = 'Kategori berhasil ditambahkan!';
            header('Location: categories.php');
            exit();
        } else {
            $error = 'Gagal menambahkan kategori!';
        }
    }
    
    if (isset($_POST['edit_category'])) {
        $category->id = $_POST['id'];
        $category->name = $_POST['name'];
        $category->description = $_POST['description'];
        
        if ($category->update()) {
            $_SESSION['success_message'] = 'Kategori berhasil diperbarui!';
            header('Location: categories.php');
            exit();
        } else {
            $error = 'Gagal memperbarui kategori!';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $category->id = $_GET['delete'];
    
    if ($category->delete()) {
        $_SESSION['success_message'] = 'Kategori berhasil dihapus!';
        header('Location: categories.php');
        exit();
    } else {
        $error = 'Gagal menghapus kategori!';
    }
}

// Get categories
$categories = $category->read();

// PERBAIKI PENTING: Gunakan header partial yang sama
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kategori Dokumen</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-1"></i> Tambah Kategori
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

<!-- Categories Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td><?php echo htmlspecialchars($cat['description']) ?: '-'; ?></td>
                        <td><?php echo date('d M Y', strtotime($cat['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal" 
                                data-id="<?php echo $cat['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($cat['name']); ?>" 
                                data-description="<?php echo htmlspecialchars($cat['description']); ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Hapus kategori ini? Dokumen yang terkait akan kehilangan kategori.')">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <input type="hidden" name="add_category" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="editCategoryName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="editCategoryDescription" rows="3"></textarea>
                    </div>
                    
                    <input type="hidden" name="id" id="editCategoryId">
                    <input type="hidden" name="edit_category" value="1">
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
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryDescription').value = description;
        });
    }
});
</script>

<?php
// PERBAIKI: Gunakan footer partial yang sama
require_once __DIR__ . '/../partials/footer.php';
?>