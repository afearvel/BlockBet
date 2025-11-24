<?php
// classes/Acompanante.php

class Acompanante {
    private $id;
    private $usuario_id;
    private $nombre;
    private $email;
    private $telefono;
    private $relacion;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function agregar($usuario_id, $nombre, $email, $telefono, $relacion) {
        $nombre = trim($nombre);
        $email = trim($email);
        $telefono = trim($telefono);
        
        $sql = "INSERT INTO acompanantes (usuario_id, nombre, email, telefono, relacion) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("issss", $usuario_id, $nombre, $email, $telefono, $relacion);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function obtenerPorUsuario($usuario_id) {
        $sql = "SELECT * FROM acompanantes WHERE usuario_id = ? ORDER BY fecha_agregado DESC";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $acompanantes = [];
        while ($acomp = $resultado->fetch_assoc()) {
            $acompanantes[] = $acomp;
        }
        $stmt->close();
        return $acompanantes;
    }
    
    public function eliminar($id, $usuario_id) {
        $sql = "DELETE FROM acompanantes WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function cambiarNotificaciones($id, $usuario_id) {
        $sql = "UPDATE acompanantes SET notificar = NOT notificar WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>