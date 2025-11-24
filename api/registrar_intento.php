<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/Database.php';
require_once '../classes/IntentoBloqueo.php';

$data = json_decode(file_get_contents('php://input'), true);

$usuario_id = intval($data['usuario_id'] ?? 0);
$dominio = $data['dominio'] ?? '';

if ($usuario_id > 0 && $dominio) {
    $db = new Database();
    $intento = new IntentoBloqueo($db);
    
    if ($intento->registrar($usuario_id, $dominio)) {
        echo json_encode([
            'success' => true,
            'message' => 'Intento registrado'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
}
?>