<?php
// Cargar las clases
require_once 'classes/Database.php';
require_once 'classes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validar contraseñas
    if ($password !== $password_confirm) {
        die("Las contraseñas no coinciden");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico inválido");
    }
    
    // Crear objetos
    $db = new Database();
    $usuario = new Usuario($db);
    
    // Intentar registrar
    if ($usuario->registrar($nombre, $email, $password)) {
        // Registro exitoso
        header("Location: index.html");
        exit;
    } else {
        die("Error al registrar. El email puede estar en uso");
    }
}
?>