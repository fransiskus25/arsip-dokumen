<?php
// Ubah path require_once
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }
    
    public function login($username, $password) {
        $this->user->username = $username;
        $this->user->password = $password;
        
        if ($this->user->login()) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['username'] = $this->user->username;
            $_SESSION['role'] = $this->user->role;
            $_SESSION['full_name'] = $this->user->full_name;
            $_SESSION['email'] = $this->user->email;
            
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>