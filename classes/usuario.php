<?php
// classes/Usuario.php

class Usuario {
    private $id;
    private $nombre;
    private $email;
    private $password;
    private $meta;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNombre() {
        return $this->nombre;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getMeta() {
        return $this->meta;
    }
    
    public function registrar($nombre, $email, $password) {
        $nombre = trim($nombre);
        $email = trim($email);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $hash);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['password'])) {
                $this->id = $usuario['id'];
                $this->nombre = $usuario['nombre'];
                $this->email = $usuario['email'];
                $this->meta = $usuario['meta'];
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }
    
    public function cargarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            $this->id = $usuario['id'];
            $this->nombre = $usuario['nombre'];
            $this->email = $usuario['email'];
            $this->meta = $usuario['meta'];
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }
    
    public function actualizar($nombre, $email) {
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $email, $this->id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function actualizarMeta($meta) {
        $sql = "UPDATE usuarios SET meta = ? WHERE id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $meta, $this->id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>