<?php
class Document {
    private $conn;
    private $table_name = "documents";

    public $id;
    public $document_number;
    public $document_type;
    public $title;
    public $description;
    public $category_id;
    public $sender;
    public $receiver;
    public $file_path;
    public $file_name;
    public $file_size;
    public $tags;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE - Tambah dokumen baru
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET document_number=:document_number, document_type=:document_type, 
                title=:title, description=:description, category_id=:category_id,
                sender=:sender, receiver=:receiver, file_path=:file_path, 
                file_name=:file_name, file_size=:file_size, tags=:tags, 
                created_by=:created_by, created_at=:created_at";

        $stmt = $this->conn->prepare($query);

        $this->created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":document_number", $this->document_number);
        $stmt->bindParam(":document_type", $this->document_type);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":sender", $this->sender);
        $stmt->bindParam(":receiver", $this->receiver);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":file_name", $this->file_name);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":created_by", $this->created_by);
        $stmt->bindParam(":created_at", $this->created_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ - Baca data dokumen dengan filter
    public function read($filters = []) {
        try {
            $query = "SELECT d.*, c.name as category_name, u.full_name as creator_name 
                      FROM " . $this->table_name . " d 
                      LEFT JOIN categories c ON d.category_id = c.id 
                      LEFT JOIN users u ON d.created_by = u.id 
                      WHERE 1=1";
            
            $params = [];
            
            // Filter document type
            if (!empty($filters['document_type'])) {
                $query .= " AND d.document_type = :document_type";
                $params[':document_type'] = $filters['document_type'];
            }
            
            // Filter category
            if (!empty($filters['category_id'])) {
                $query .= " AND d.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            
            // Filter tahun
            if (!empty($filters['year'])) {
                $query .= " AND YEAR(d.created_at) = :year";
                $params[':year'] = $filters['year'];
            }
            
            // Filter bulan
            if (!empty($filters['month'])) {
                $query .= " AND MONTH(d.created_at) = :month";
                $params[':month'] = $filters['month'];
            }
            
            // Filter pencarian
            if (!empty($filters['search'])) {
                $query .= " AND (d.title LIKE :search OR d.document_number LIKE :search 
                            OR d.sender LIKE :search OR d.receiver LIKE :search)";
                $params[':search'] = "%{$filters['search']}%";
            }
            
            $query .= " ORDER BY d.created_at DESC";
            
            // Handle limit - dipisahkan karena tidak bisa di-binding
            if (!empty($filters['limit'])) {
                $limit = (int)$filters['limit'];
                if ($limit > 0) {
                    $query .= " LIMIT " . $limit;
                }
            }
            
            $stmt = $this->conn->prepare($query);
            
            // Bind semua parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Database Error in Document::read(): " . $e->getMessage());
            // Return statement kosong untuk menghindari error
            return $this->conn->prepare("SELECT 1 WHERE 1=0");
        }
    }

    // UPDATE - Update data dokumen
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET document_number=:document_number, title=:title, 
                description=:description, category_id=:category_id,
                sender=:sender, receiver=:receiver, file_path=:file_path, 
                file_name=:file_name, file_size=:file_size, tags=:tags, 
                updated_at=:updated_at
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->updated_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":document_number", $this->document_number);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":sender", $this->sender);
        $stmt->bindParam(":receiver", $this->receiver);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":file_name", $this->file_name);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE - Hapus dokumen
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            // Hapus file fisik jika ada
            if (!empty($this->file_path) && file_exists($this->file_path)) {
                unlink($this->file_path);
            }
            return true;
        }
        return false;
    }

    // COUNT BY TYPE - Hitung dokumen berdasarkan jenis
    public function countByType($type = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        if ($type) {
            $query .= " WHERE document_type = :type";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($type) {
            $stmt->bindParam(":type", $type);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // COUNT BY CATEGORY - Hitung dokumen berdasarkan kategori
    public function countByCategory($category_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // COUNT BY YEAR - Hitung dokumen berdasarkan tahun
    public function countByYear($year) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE YEAR(created_at) = :year";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // GET MONTHLY STATS - Statistik bulanan
    public function getMonthlyStats($year) {
        $query = "SELECT MONTH(created_at) as month, COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  WHERE YEAR(created_at) = :year
                  GROUP BY MONTH(created_at) 
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        
        $results = array_fill(1, 12, 0);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['month']] = $row['count'];
        }
        
        return $results;
    }

    // GET CATEGORY STATS - Statistik kategori
    public function getCategoryStats() {
        $query = "SELECT c.name as category_name, COUNT(d.id) as document_count 
                  FROM categories c 
                  LEFT JOIN documents d ON c.id = d.category_id 
                  GROUP BY c.id, c.name 
                  ORDER BY document_count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['category_name']] = $row['document_count'];
        }
        
        return $results;
    }

    // GET ANNUAL TREND - Trend tahunan
    public function getAnnualTrend($years = 5) {
        $currentYear = date('Y');
        $results = [];
        
        for ($i = $years - 1; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $results[$year] = $this->countByYear($year);
        }
        
        return $results;
    }

    // GET DOCUMENT TYPE STATS - Statistik jenis dokumen
    public function getDocumentTypeStats() {
        $query = "SELECT document_type, COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  GROUP BY document_type 
                  ORDER BY count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['document_type']] = $row['count'];
        }
        
        return $results;
    }

    // GET USER ACTIVITY STATS - Statistik aktivitas user
    public function getUserActivityStats() {
        $query = "SELECT u.username, u.full_name, COUNT(d.id) as document_count 
                  FROM users u 
                  LEFT JOIN documents d ON u.id = d.created_by 
                  GROUP BY u.id, u.username, u.full_name 
                  ORDER BY document_count DESC 
                  LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'username' => $row['username'],
                'full_name' => $row['full_name'],
                'count' => $row['document_count']
            ];
        }
        
        return $results;
    }

    // GET DASHBOARD STATS - Semua statistik untuk dashboard
    public function getDashboardStats() {
        $currentYear = date('Y');
        
        return [
            'total' => $this->countByType(),
            'incoming' => $this->countByType('incoming'),
            'outgoing' => $this->countByType('outgoing'),
            'reports' => $this->countByType('report'),
            'monthly' => $this->getMonthlyStats($currentYear),
            'category' => $this->getCategoryStats(),
            'annual_trend' => $this->getAnnualTrend(),
            'document_types' => $this->getDocumentTypeStats(),
            'user_activity' => $this->getUserActivityStats()
        ];
    }

    // GET RECENT DOCUMENTS - Dokumen terbaru (method khusus untuk menghindari error LIMIT)
    public function getRecentDocuments($limit = 5) {
        try {
            $query = "SELECT d.*, c.name as category_name, u.full_name as creator_name 
                      FROM " . $this->table_name . " d 
                      LEFT JOIN categories c ON d.category_id = c.id 
                      LEFT JOIN users u ON d.created_by = u.id 
                      ORDER BY d.created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Database Error in getRecentDocuments(): " . $e->getMessage());
            return $this->conn->prepare("SELECT 1 WHERE 1=0");
        }
    }

    // GET BY ID - Ambil dokumen berdasarkan ID
    public function getById($id) {
        $query = "SELECT d.*, c.name as category_name, u.full_name as creator_name 
                  FROM " . $this->table_name . " d 
                  LEFT JOIN categories c ON d.category_id = c.id 
                  LEFT JOIN users u ON d.created_by = u.id 
                  WHERE d.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>