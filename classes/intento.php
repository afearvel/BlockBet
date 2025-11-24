<?php
// classes/IntentoBloqueo.php

class IntentoBloqueo {
    private $id;
    private $usuario_id;
    private $dominio;
    private $resultado;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function registrar($usuario_id, $dominio) {
        $dominio = trim($dominio);
        $resultado = 'bloqueado';
        
        $sql = "INSERT INTO intentos_bloqueo (usuario_id, dominio, resultado) VALUES (?, ?, ?)";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("iss", $usuario_id, $dominio, $resultado);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function obtenerPorUsuario($usuario_id, $limite = 10) {
        $sql = "SELECT * FROM intentos_bloqueo WHERE usuario_id = ? ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $intentos = [];
        while ($intento = $resultado->fetch_assoc()) {
            $intentos[] = $intento;
        }
        $stmt->close();
        return $intentos;
    }
    
    public function contarHoy($usuario_id) {
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM intentos_bloqueo WHERE usuario_id = ? AND DATE(timestamp) = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("is", $usuario_id, $hoy);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $total = $resultado->fetch_assoc()['total'];
        $stmt->close();
        return $total;
    }
    
    public function contarTotal($usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM intentos_bloqueo WHERE usuario_id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $total = $resultado->fetch_assoc()['total'];
        $stmt->close();
        return $total;
    }
}
?>