<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Cargar clases
require_once 'classes/Database.php';
require_once 'classes/Acompanante.php';

$db = new Database();
$acompanante = new Acompanante($db);

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// AGREGAR ACOMPAÑANTE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_acompanante'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $relacion = trim($_POST['relacion'] ?? 'amigo');

    if ($nombre && $email) {
        if ($acompanante->agregar($usuario_id, $nombre, $email, $telefono, $relacion)) {
            $mensaje_exito = "Acompañante agregado correctamente";
        } else {
            $mensaje_error = "Error al agregar acompañante";
        }
    }
}

// ELIMINAR ACOMPAÑANTE
if (isset($_GET['eliminar'])) {
    $acomp_id = intval($_GET['eliminar']);
    $acompanante->eliminar($acomp_id, $usuario_id);
    header('Location: acompanantes.php');
    exit;
}

// CAMBIAR NOTIFICACIONES
if (isset($_GET['toggle_notif'])) {
    $acomp_id = intval($_GET['toggle_notif']);
    $acompanante->cambiarNotificaciones($acomp_id, $usuario_id);
    header('Location: acompanantes.php');
    exit;
}

// OBTENER ACOMPAÑANTES
$acompanantes = $acompanante->obtenerPorUsuario($usuario_id);
$total_acompanantes = count($acompanantes);
$con_notif = count(array_filter($acompanantes, fn($a) => $a['notificar'] == 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockBet - Red de Apoyo</title>
    <link rel="stylesheet" href="dashboard-styles.css">
    <style>
        .support-container {
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

        .add-support-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
        }

        .support-list-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .support-row {
            display: grid;
            grid-template-columns: 50px 1fr 200px 150px 180px;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .support-row:hover {
            background: #f9fafb;
        }

        .support-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #a855f7);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .support-info h4 {
            margin: 0 0 5px 0;
            color: #111827;
        }

        .support-info p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .support-badge {
            display: inline-block;
            padding: 6px 14px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .btn-toggle-notif {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-toggle-notif.active {
            background: #10b981;
            color: white;
        }

        .btn-toggle-notif.inactive {
            background: #e5e7eb;
            color: #6b7280;
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

        .btn-primary {
            background: #4f46e5;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: #4338ca;
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

        .back-link:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="background: #f5f7fb;">
    <div class="support-container">
        <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>

        <div class="page-header">
            <h1> Red de Apoyo</h1>
            <p>Personas de confianza que te acompañan en tu proceso</p>
        </div>

        <?php if (isset($mensaje_exito)): ?>
            <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_acompanantes; ?></div>
                <div class="stat-label">Acompañantes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $con_notif; ?></div>
                <div class="stat-label">Con Notificaciones</div>
            </div>
        </div>

        <!-- Agregar Acompañante -->
        <div class="add-support-card">
            <h3 style="margin-bottom: 20px;"> Agregar Acompañante</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" placeholder="Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="juan@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" placeholder="+52 123 456 7890">
                    </div>
                    <div class="form-group">
                        <label>Relación</label>
                        <select name="relacion">
                            <option value="amigo">Amigo/a</option>
                            <option value="familiar">Familiar</option>
                            <option value="terapeuta">Terapeuta</option>
                            <option value="pareja">Pareja</option>
                            <option value="mentor">Mentor</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="agregar_acompanante" class="btn-primary">Agregar Acompañante</button>
            </form>
        </div>

        <!-- Lista de Acompañantes -->
        <div class="support-list-card">
            <h3 style="margin-bottom: 20px;">👥 Mis Acompañantes</h3>

            <?php if ($total_acompanantes > 0): ?>
                <?php foreach ($acompanantes as $a): ?>
                    <div class="support-row">
                        <div class="support-avatar">
                            <?php echo strtoupper(substr($a['nombre'], 0, 1)); ?>
                        </div>
                        <div class="support-info">
                            <h4><?php echo htmlspecialchars($a['nombre']); ?></h4>
                            <p>
                                <?php echo htmlspecialchars($a['email']); ?>
                                <?php if ($a['telefono']): ?>
                                    <?php echo htmlspecialchars($a['telefono']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <span class="support-badge"><?php echo htmlspecialchars($a['relacion']); ?></span>
                        </div>
                        <div>
                            <button 
                                class="btn-toggle-notif <?php echo $a['notificar'] ? 'active' : 'inactive'; ?>"
                                onclick="window.location.href='acompanantes.php?toggle_notif=<?php echo $a['id']; ?>'">
                                <?php echo $a['notificar'] ? ' Notif ON' : ' Notif OFF'; ?>
                            </button>
                        </div>
                        <div style="text-align: right;">
                            <button 
                                class="btn-delete" 
                                onclick="if(confirm('¿Eliminar este acompañante?')) window.location.href='acompanantes.php?eliminar=<?php echo $a['id']; ?>'">
                                Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h3>No tienes acompañantes aún</h3>
                    <p>Agrega personas de confianza que te apoyen en tu proceso</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
