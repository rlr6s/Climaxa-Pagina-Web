<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "climaxa";
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("Conexión fallida: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Error de base de datos: " . $e->getMessage());
            die("Error del sistema. Por favor, intente más tarde.");
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // No cerramos la conexión aquí, se cerrará automáticamente al final del script
}

// Función helper para obtener conexión
function getDBConnection() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->getConnection();
}
?>