<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/Usuario.php';

$db = new Database();
$usuario = new Usuario($db);

$usuario_id = $_SESSION['usuario_id'];
$usuario->cargarPorId($usuario_id);

// ACTUALIZAR PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($usuario->actualizar($nombre, $email)) {
        $_SESSION['usuario_nombre'] = $nombre;
        $mensaje_exito = "Perfil actualizado";
    } else {
        $mensaje_error = "Error al actualizar";
    }
}

// ACTUALIZAR META
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_meta'])) {
    $meta = intval($_POST['meta'] ?? 30);
    
    if ($usuario->actualizarMeta($meta)) {
        $mensaje_exito = "Meta actualizada";
    } else {
        $mensaje_error = "Error al actualizar meta";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockBet - Configuración</title>
    <link rel="stylesheet" href="dashboard-styles.css">
    <style>
        .config-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        
        .config-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .help-text {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .btn-save {
            background: #6366f1;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-save:hover {
            background: #4f46e5;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body style="background: #f5f7fb;">
    <div class="config-container">
        <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <div class="page-header">
            <h1> Configuración</h1>
            <p>Administra tu cuenta</p>
        </div>

        <?php if (isset($mensaje_exito)): ?>
        <div class="alert alert-success"> <?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
        <div class="alert alert-error"> <?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <!-- Información del Usuario -->
        <div class="config-section">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($usuario->getNombre(), 0, 2)); ?>
                </div>
                <h3><?php echo htmlspecialchars($usuario->getNombre()); ?></h3>
                <p style="color: #6b7280;"><?php echo htmlspecialchars($usuario->getEmail()); ?></p>
            </div>
        </div>

        <!-- Editar Perfil -->
        <div class="config-section">
            <h2 class="section-title">👤 Información Personal</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario->getNombre()); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario->getEmail()); ?>" required>
                </div>
                
                <button type="submit" name="actualizar_perfil" class="btn-save"> Guardar Cambios</button>
            </form>
        </div>

        <!-- Enlaces Rápidos -->
        <div class="config-section">
            <h2 class="section-title"> Acciones Rápidas</h2>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="sitios_bloqueados.php" style="background: #e5e7eb; padding: 12px 24px; border-radius: 8px; text-decoration: none; color: #374151; font-weight: 600;">
                     Sitios Bloqueados
                </a>
                <a href="acompanantes.php" style="background: #e5e7eb; padding: 12px 24px; border-radius: 8px; text-decoration: none; color: #374151; font-weight: 600;">
                     Acompañantes
                </a>
                <a href="logout.php" style="background: #fee2e2; padding: 12px 24px; border-radius: 8px; text-decoration: none; color: #991b1b; font-weight: 600;">
                     Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</body>
</html>