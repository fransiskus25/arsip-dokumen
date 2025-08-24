<?php
require_once '../config/config.php';
require_once '../models/User.php';

class UserController {
    private $user;
    
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }
    
    public function createUser($data, $file = null) {
        $this->user->username  = $data['username'];
        $this->user->password  = $data['password'];
        $this->user->email     = $data['email'];
        $this->user->full_name = $data['full_name'];
        $this->user->role      = $data['role'];
        
        // Handle photo upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . 'profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName      = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath      = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->user->photo = $filePath;
            }
        }
        
        return $this->user->create();
    }
    
    public function getUsers() {
        return $this->user->read();
    }
    
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->user->getConnection()->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateUser($id, $data, $file = null) {
        $user = $this->getUserById($id);
        if (!$user) return false;
        
        $this->user->id        = $id;
        $this->user->username  = $data['username'] ?? $user['username'];
        $this->user->email     = $data['email'] ?? $user['email'];
        $this->user->full_name = $data['full_name'] ?? $user['full_name'];
        $this->user->role      = $data['role'] ?? $user['role'];
        $this->user->photo     = $user['photo'];
        
        // Handle password update
        if (!empty($data['password'])) {
            $this->user->password = $data['password'];
        }
        
        // Handle photo upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            if (!empty($user['photo']) && is_file($user['photo'])) {
                unlink($user['photo']);
            }
            
            $uploadDir = UPLOAD_PATH . 'profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName      = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath      = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->user->photo = $filePath;
            }
        }
        
        return $this->user->update();
    }
    
    public function deleteUser($id) {
        $user = $this->getUserById($id);
        if (!$user) return false;
        
        if (!empty($user['photo']) && is_file($user['photo'])) {
            unlink($user['photo']);
        }
        
        $this->user->id = $id;
        return $this->user->delete();
    }
    
    public function updateProfile($id, $data, $file = null) {
        $user = $this->getUserById($id);
        if (!$user) return false;
        
        $this->user->id        = $id;
        $this->user->username  = $data['username'] ?? $user['username'];
        $this->user->email     = $data['email'] ?? $user['email'];
        $this->user->full_name = $data['full_name'] ?? $user['full_name'];
        $this->user->photo     = $user['photo'];
        
        if (!empty($data['password'])) {
            $this->user->password = $data['password'];
        }
        
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            if (!empty($user['photo']) && is_file($user['photo'])) {
                unlink($user['photo']);
            }
            
            $uploadDir = UPLOAD_PATH . 'profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName      = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath      = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $this->user->photo = $filePath;
            }
        }
        
        return $this->user->update();
    }
}
?>
