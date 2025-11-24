<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/Database.php';
require_once '../classes/Usuario.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if ($email && $password) {
    $db = new Database();
    $usuario = new Usuario($db);
    
    if ($usuario->login($email, $password)) {
        echo json_encode([
            'success' => true,
            'usuario_id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'message' => 'Login exitoso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Correo o contraseña incorrectos'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
}
?>