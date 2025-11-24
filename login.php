<?php
session_start();

// Cargar las clases
require_once 'classes/Database.php';
require_once 'classes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Crear objetos
    $db = new Database();
    $usuario = new Usuario($db);

    // Intentar login
    if ($usuario->login($email, $password)) {
        // Login exitoso
        $_SESSION['usuario_id'] = $usuario->getId();
        $_SESSION['usuario_nombre'] = $usuario->getNombre();

        header("Location: dashboard.php");
        exit;
    } else {
        // Login fallido - regresar al index con error
        header("Location: index.html?error=1");
        exit;
    }
} else {
    // Si acceden directamente sin POST, redirigir al index
    header("Location: index.html");
    exit;
}
?>