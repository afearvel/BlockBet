<?php
// classes/Database.php

class Database {
    private $server;
    private $user;
    private $password;
    private $bd;
    private $conexion;
    
    public function __construct() {
        // Detectar si estamos en Railway o local
        $this->server = getenv('MYSQLHOST') ?: 'localhost';
        $this->user = getenv('MYSQLUSER') ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: '';
        $this->bd = getenv('MYSQLDATABASE') ?: 'blockbet';
        
        $this->conectar();
    }
    
    private function conectar() {
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->bd);
        
        if ($this->conexion->connect_errno) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        
        $this->conexion->set_charset("utf8");
    }
    
    public function getConexion() {
        return $this->conexion;
    }
}
?>