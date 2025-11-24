<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../classes/Database.php';
require_once '../classes/SitioBloqueado.php';
require_once '../classes/IntentoBloqueo.php';

$usuario_id = intval($_GET['usuario_id'] ?? 0);

if ($usuario_id > 0) {
    $db = new Database();
    $sitioObj = new SitioBloqueado($db);
    $intentoObj = new IntentoBloqueo($db);
    
    $sitios = $sitioObj->obtenerPorUsuario($usuario_id);
    $total_sitios = count(array_filter($sitios, function($s) { return $s['activo'] == 1; }));
    $intentos_hoy = $intentoObj->contarHoy($usuario_id);
    
    echo json_encode([
        'success' => true,
        'total_sitios' => $total_sitios,
        'intentos_hoy' => $intentos_hoy
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario inválido'
    ]);
}
?>