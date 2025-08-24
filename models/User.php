<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $role;
    public $photo;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getter untuk koneksi (biar bisa dipanggil di Controller)
    public function getConnection() {
        return $this->conn;
    }

    public function login() {
        $query = "SELECT id, username, password, role, full_name, email, photo 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->id        = $row['id'];
                $this->username  = $row['username'];
                $this->role      = $row['role'];
                $this->full_name = $row['full_name'];
                $this->email     = $row['email'];
                $this->photo     = $row['photo'];
                return true;
            }
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (username, password, email, full_name, role, photo, created_at)
                VALUES (:username, :password, :email, :full_name, :role, :photo, :created_at)";

        $stmt = $this->conn->prepare($query);

        $this->password   = password_hash($this->password, PASSWORD_DEFAULT);
        $this->created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":photo", $this->photo);
        $stmt->bindParam(":created_at", $this->created_at);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        // Jika password tidak kosong, update password juga
        if (!empty($this->password)) {
            $query = "UPDATE " . $this->table_name . "
                    SET username=:username, email=:email, full_name=:full_name, 
                        role=:role, photo=:photo, password=:password, updated_at=:updated_at
                    WHERE id=:id";
        } else {
            $query = "UPDATE " . $this->table_name . "
                    SET username=:username, email=:email, full_name=:full_name, 
                        role=:role, photo=:photo, updated_at=:updated_at
                    WHERE id=:id";
        }

        $stmt = $this->conn->prepare($query);

        $this->updated_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":photo", $this->photo);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->password)) {
            $hashed = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $hashed);
        }

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $stmt->rowCount();
        }
        return false;
    }
}
?>
