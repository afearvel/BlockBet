<?php
// classes/SitioBloqueado.php

class SitioBloqueado {
    private $id;
    private $usuario_id;
    private $dominio;
    private $categoria;
    private $activo;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function agregar($usuario_id, $dominio, $categoria) {
        // Limpiar dominio
        $dominio = trim(strtolower($dominio));
        $dominio = preg_replace('#^https?://(www\.)?#', '', $dominio);
        $dominio = rtrim($dominio, '/');
        
        $sql = "INSERT INTO sitios_bloqueados (usuario_id, dominio, categoria, activo) VALUES (?, ?, ?, 1)";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("iss", $usuario_id, $dominio, $categoria);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function obtenerPorUsuario($usuario_id) {
        $sql = "SELECT * FROM sitios_bloqueados WHERE usuario_id = ? ORDER BY fecha_agregado DESC";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $sitios = [];
        while ($sitio = $resultado->fetch_assoc()) {
            $sitios[] = $sitio;
        }
        $stmt->close();
        return $sitios;
    }
    
    public function cambiarEstado($id, $usuario_id) {
        $sql = "UPDATE sitios_bloqueados SET activo = NOT activo WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    public function eliminar($id, $usuario_id) {
        $sql = "DELETE FROM sitios_bloqueados WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>