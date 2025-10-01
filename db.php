<?php
class Database {
    private $host = "localhost";
    private $db_name = "todo_app";
    private $username = "Chimuka_03"; // change if needed
    private $password = "12345";
    public $conn;

    public function connect() {
        $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}",
        $this->username),                      
        $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
