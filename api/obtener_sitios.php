<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../classes/Database.php';
require_once '../classes/SitioBloqueado.php';

$usuario_id = intval($_GET['usuario_id'] ?? 0);

if ($usuario_id > 0) {
    $db = new Database();
    $sitioObj = new SitioBloqueado($db);
    
    $sitios = $sitioObj->obtenerPorUsuario($usuario_id);
    
    // Filtrar solo los activos
    $sitios_activos = array_filter($sitios, function($s) {
        return $s['activo'] == 1;
    });
    
    echo json_encode([
        'success' => true,
        'sitios' => array_values($sitios_activos)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario inválido'
    ]);
}
?>