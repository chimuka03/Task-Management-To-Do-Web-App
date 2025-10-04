<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session timeout (30 min inactivity for mass use)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
}
$_SESSION['last_activity'] = time();

class Database {
    private $host = "localhost";
    private $db_name = "todo_web";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());  // Log for mass debugging
            die("Connection failed. Check logs.");
        }
        return $this->conn;
    }
}

function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

$db = new Database();
$conn = $db->connect();
?>