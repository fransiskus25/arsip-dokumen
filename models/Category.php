<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getter untuk koneksi
    public function getConnection() {
        return $this->conn;
    }

    // Create category
    public function create() {
        $query = "INSERT INTO {$this->table_name} 
                  (name, description, created_at) 
                  VALUES (:name, :description, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":name", $this->name);
        $stmt->bindValue(":description", $this->description);

        return $stmt->execute();
    }

    // Read categories
    public function read() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update category
    public function update() {
        $query = "UPDATE {$this->table_name} 
                  SET name = :name, 
                      description = :description, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":name", $this->name);
        $stmt->bindValue(":description", $this->description);
        $stmt->bindValue(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete category
    public function delete() {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>
