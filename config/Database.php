<?php
class Database
{
    private $host = '127.0.0.1';
    private $db_name = 'api_db';
    private $username = 'root';
    private $password = 'root';
    private $conn;


    public function getConnection()
    {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'DB connection error']);
            exit;
        }
        return $this->conn;
    }
}
