<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'shop';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Optional: set error mode to exception for easier debugging
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
