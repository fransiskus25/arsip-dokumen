<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/Category.php';

class DocumentController {
    private $document;
    private $category;
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->document = new Document($this->db);
        $this->category = new Category($this->db);
    }
    
    public function createDocument($data, $file) {
        $this->document->document_number = $data['document_number'];
        $this->document->document_type = $data['document_type'];
        $this->document->title = $data['title'];
        $this->document->description = $data['description'];
        $this->document->category_id = $data['category_id'];
        $this->document->sender = $data['sender'] ?? null;
        $this->document->receiver = $data['receiver'] ?? null;
        $this->document->tags = $data['tags'] ?? null;
        $this->document->created_by = $_SESSION['user_id'];
        
        // Handle file upload
        if ($file && $file['error'] == UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . 'documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->document->file_path = $filePath;
                $this->document->file_name = $file['name'];
                $this->document->file_size = $file['size'];
            }
        }
        
        return $this->document->create();
    }
    
    public function getDocuments($filters = []) {
        return $this->document->read($filters);
    }
    
    public function getDocumentById($id) {
        return $this->document->getById($id);
    }
    
    public function updateDocument($id, $data, $file = null) {
        $document = $this->getDocumentById($id);
        if (!$document) return false;
        
        $this->document->id = $id;
        $this->document->document_number = $data['document_number'] ?? $document['document_number'];
        $this->document->title = $data['title'] ?? $document['title'];
        $this->document->description = $data['description'] ?? $document['description'];
        $this->document->category_id = $data['category_id'] ?? $document['category_id'];
        $this->document->sender = $data['sender'] ?? $document['sender'];
        $this->document->receiver = $data['receiver'] ?? $document['receiver'];
        $this->document->tags = $data['tags'] ?? $document['tags'];
        $this->document->file_path = $document['file_path'] ?? '';
        $this->document->file_name = $document['file_name'] ?? '';
        $this->document->file_size = $document['file_size'] ?? 0;
        
        // Handle file upload if a new file is provided
        if ($file && $file['error'] == UPLOAD_ERR_OK) {
            // Delete old file if exists
            if (!empty($document['file_path']) && file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            $uploadDir = UPLOAD_PATH . 'documents/';
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->document->file_path = $filePath;
                $this->document->file_name = $file['name'];
                $this->document->file_size = $file['size'];
            }
        }
        
        return $this->document->update();
    }
    
    public function deleteDocument($id) {
        $document = $this->getDocumentById($id);
        if (!$document) return false;
        
        $this->document->id = $id;
        $this->document->file_path = $document['file_path'] ?? '';
        
        return $this->document->delete();
    }
    
    public function getCategories() {
        return $this->category->read();
    }
    
    public function getStats() {
        $incomingCount = $this->document->countByType('incoming');
        $outgoingCount = $this->document->countByType('outgoing');
        $reportCount = $this->document->countByType('report');
        $totalCount = $this->document->countByType();
        
        $currentYear = date('Y');
        $monthlyStats = $this->document->getMonthlyStats($currentYear);
        
        return [
            'incoming' => $incomingCount,
            'outgoing' => $outgoingCount,
            'reports' => $reportCount,
            'total' => $totalCount,
            'monthly' => $monthlyStats
        ];
    }

    // Method untuk mendapatkan file path dengan pengecekan null
    public function getDocumentFilePath($id) {
        $document = $this->getDocumentById($id);
        return !empty($document['file_path']) ? $document['file_path'] : null;
    }

    // Method untuk memeriksa apakah file ada dengan pengecekan null
    public function documentFileExists($id) {
        $filePath = $this->getDocumentFilePath($id);
        return !empty($filePath) && file_exists($filePath);
    }

    // Method untuk mendapatkan informasi file dengan pengecekan null
    public function getDocumentFileInfo($id) {
        $document = $this->getDocumentById($id);
        if (!$document || empty($document['file_path'])) {
            return null;
        }

        $filePath = $document['file_path'];
        
        return [
            'path' => $filePath,
            'name' => $document['file_name'] ?? '',
            'size' => $document['file_size'] ?? 0,
            'exists' => file_exists($filePath)
        ];
    }

    // Method untuk handle preview dengan pengecekan null
    public function getDocumentForPreview($id) {
        $document = $this->getDocumentById($id);
        if (!$document || empty($document['file_path']) || !file_exists($document['file_path'])) {
            return null;
        }
        return $document;
    }
}
?>