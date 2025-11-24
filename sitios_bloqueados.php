<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Cargar clases
require_once 'classes/Database.php';
require_once 'classes/SitioBloqueado.php';

$db = new Database();
$sitio = new SitioBloqueado($db);

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// AGREGAR SITIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_sitio'])) {
    $dominio = trim($_POST['dominio'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'apuestas');
    
    if ($dominio) {
        if ($sitio->agregar($usuario_id, $dominio, $categoria)) {
            $mensaje_exito = "Sitio bloqueado correctamente";
        } else {
            $mensaje_error = "Error al bloquear sitio";
        }
    }
}

// ELIMINAR SITIO
if (isset($_GET['eliminar'])) {
    $sitio_id = intval($_GET['eliminar']);
    $sitio->eliminar($sitio_id, $usuario_id);
    header('Location: sitios_bloqueados.php');
    exit;
}

// CAMBIAR ESTADO
if (isset($_GET['toggle'])) {
    $sitio_id = intval($_GET['toggle']);
    $sitio->cambiarEstado($sitio_id, $usuario_id);
    header('Location: sitios_bloqueados.php');
    exit;
}

// OBTENER SITIOS
$sitios = $sitio->obtenerPorUsuario($usuario_id);
$total_sitios = count($sitios);
$sitios_activos = count(array_filter($sitios, fn($s) => $s['activo'] == 1));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockBet - Sitios Bloqueados</title>
    <link rel="stylesheet" href="dashboard-styles.css">
    <style>
        .sites-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #4f46e5, #a855f7);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4f46e5;
        }
        
        .stat-label {
            color: #6b7280;
            margin-top: 8px;
        }
        
        .add-site-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 200px 150px;
            gap: 15px;
            align-items: end;
        }
        
        .sites-list-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .site-row {
            display: grid;
            grid-template-columns: 40px 1fr 150px 100px 150px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .site-row:hover {
            background: #f9fafb;
        }
        
        .site-icon {
            width: 32px;
            height: 32px;
            background: #4f46e5;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .site-category {
            display: inline-block;
            padding: 4px 12px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-delete:hover {
            background: #dc2626;
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
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }

        .form-group { margin-bottom: 0; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }
        .btn-primary {
            margin-left: 30px;
            background: #4f46e5;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>
<body style="background: #f5f7fb;">
    <div class="sites-container">
        <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <div class="page-header">
            <h1>🔒 Sitios Bloqueados</h1>
            <p>Gestiona los sitios que deseas bloquear</p>
        </div>

        <?php if (isset($mensaje_exito)): ?>
        <div class="alert alert-success">✓ <?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
        <div class="alert alert-error">✗ <?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_sitios; ?></div>
                <div class="stat-label">Total de Sitios</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $sitios_activos; ?></div>
                <div class="stat-label">Bloqueos Activos</div>
            </div>
        </div>

        <!-- Agregar Sitio -->
        <div class="add-site-card">
            <h3 style="margin-bottom: 20px;">➕ Agregar Nuevo Sitio</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Dominio o URL</label>
                        <input type="text" name="dominio" placeholder="bet365.com" required>
                    </div>
                    <button type="submit" name="agregar_sitio" class="btn-primary">Agregar</button>
                </div>
            </form>
        </div>

        <!-- Lista de Sitios -->
        <div class="sites-list-card">
            <h3 style="margin-bottom: 20px;">📋 Mis Sitios Bloqueados</h3>
            
            <?php if (count($sitios) > 0): ?>
                <?php foreach ($sitios as $s): ?>
                <div class="site-row">
                    <div class="site-icon">
                        <?php echo strtoupper(substr($s['dominio'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4><?php echo htmlspecialchars($s['dominio']); ?></h4>
                        <small style="color: #6b7280;">
                            <?php echo date('d/m/Y', strtotime($s['fecha_agregado'])); ?>
                        </small>
                    </div>
                    <div>
                    </div>
                    <div style="text-align: right;">
                        <button class="btn-delete" 
                                onclick="if(confirm('¿Eliminar?')) window.location.href='sitios_bloqueados.php?eliminar=<?php echo $s['id']; ?>'">
                            Eliminar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">
                    No has agregado sitios aún
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>