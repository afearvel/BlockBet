<?php
// classes/Database.php

class Database {
    private $server = "localhost";
    private $user = "root";
    private $password = "";
    private $bd = "blockbet";
    private $conexion;
    
    public function __construct() {
        $this->conexion = new mysqli($this->server, $this->user, $this->password, $this->bd);
        
        if ($this->conexion->connect_errno) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }
    
    public function getConexion() {
        return $this->conexion;
    }
}
?>