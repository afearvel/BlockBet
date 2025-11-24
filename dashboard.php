<?php
session_start();
require 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Consultar datos completos del usuario desde BD
$sql = "SELECT id, nombre, email, meta, dias_logrados FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Si no existe el usuario, redirigir
if (!$usuario) {
    header('Location: login.php');
    exit;
}

// Extraer datos del usuario
$nombre = $usuario['nombre'];
$email = $usuario['email'];
$meta = $usuario['meta'] ?? 30;
$dias_logrados = $usuario['dias_logrados'] ?? 0;

// Consultar intentos bloqueados de hoy
$hoy = date('Y-m-d');
$sql_intentos = "SELECT COUNT(*) as total FROM intentos_bloqueo WHERE usuario_id = ? AND DATE(timestamp) = ?";
$stmt_intentos = $conexion->prepare($sql_intentos);
$stmt_intentos->bind_param("is", $usuario_id, $hoy);
$stmt_intentos->execute();
$resultado_intentos = $stmt_intentos->get_result();
$intentos_hoy = $resultado_intentos->fetch_assoc()['total'] ?? 0;
$stmt_intentos->close();

// Consultar dinero ahorrado
$dinero_ahorrado = $dias_logrados * 100;

// Consultar sitios bloqueados activos
$sql_sitios = "SELECT id, dominio, categoria, activo FROM sitios_bloqueados WHERE usuario_id = ? AND activo = 1 ORDER BY fecha_agregado DESC LIMIT 5";
$stmt_sitios = $conexion->prepare($sql_sitios);
$stmt_sitios->bind_param("i", $usuario_id);
$stmt_sitios->execute();
$resultado_sitios = $stmt_sitios->get_result();
$sitios_bloqueados = [];
while ($sitio = $resultado_sitios->fetch_assoc()) {
    $sitios_bloqueados[] = $sitio;
}
$stmt_sitios->close();

// Contar total de sitios bloqueados activos
$sitios_monitoreados = count($sitios_bloqueados);

// Calcular porcentaje de progreso
$porcentaje = ($meta > 0) ? round(($dias_logrados / $meta) * 100) : 0;

// Últimos intentos bloqueados
$sql_ultimos = "SELECT dominio, timestamp, resultado FROM intentos_bloqueo WHERE usuario_id = ? ORDER BY timestamp DESC LIMIT 5";
$stmt_ultimos = $conexion->prepare($sql_ultimos);
$stmt_ultimos->bind_param("i", $usuario_id);
$stmt_ultimos->execute();
$resultado_ultimos = $stmt_ultimos->get_result();
$ultimos_intentos = [];
while ($intento = $resultado_ultimos->fetch_assoc()) {
    $ultimos_intentos[] = $intento;
}
$stmt_ultimos->close();

// ⭐ Consultar acompañantes del usuario
$sql_acompanantes = "SELECT id, nombre, email, relacion, notificar FROM acompanantes WHERE usuario_id = ? ORDER BY fecha_agregado DESC LIMIT 5";
$stmt_acompanantes = $conexion->prepare($sql_acompanantes);
$stmt_acompanantes->bind_param("i", $usuario_id);
$stmt_acompanantes->execute();
$resultado_acompanantes = $stmt_acompanantes->get_result();
$acompanantes = [];
while ($acomp = $resultado_acompanantes->fetch_assoc()) {
    $acompanantes[] = $acomp;
}
$stmt_acompanantes->close();

// Contar total de acompañantes
$total_acompanantes = count($acompanantes);

// Mensajes motivacionales aleatorios
$mensajes = [
    "Cada día que resistes es una victoria. Eres más fuerte de lo que crees.",
    "Tú puedes. Cada momento sin apostar es un paso hacia la libertad.",
    "La recuperación es posible. Hoy es un buen día para comenzar.",
    "Tu lucha tiene valor. No estás solo en esto.",
    "Cada hora sin recaídas es una victoria personal.",
    "La vida sin apuestas es más hermosa. ¡Sigue adelante!"
];
$mensaje_hoy = $mensajes[array_rand($mensajes)];

// Fecha actual formateada
$fecha_hoy = date('l, j \d\e F');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlockBet - Dashboard</title>
    <link rel="stylesheet" href="dashboard-styles.css">
</head>
<body>
    <div class="container">
        <!-- SIDEBAR IZQUIERDO -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='32' fill='%234F46E5'/%3E%3C/svg%3E" alt="BlockBet" class="logo">
                <h1>BlockBet</h1>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                    <li><a href="sitios_bloqueados.php" class="nav-link">Sitios Bloqueados</a></li>
                    <li><a href="acompanantes.php" class="nav-link">Acompañantes</a></li>
                    <li><a href="configuracion.php" class="nav-link">Configuración</a></li>
                </ul>
            </nav>

            <!-- PERFIL DEL USUARIO EN SIDEBAR -->
            <div class="user-profile-sidebar">
                <div class="profile-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nombre); ?>&background=random" alt="Perfil">
                    <div>
                        <p class="user-name-sidebar"><?php echo htmlspecialchars($nombre); ?></p>
                        <p class="user-email-sidebar"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS EN SIDEBAR -->
            <div class="stats-sidebar">
                <div class="stat-item">
                    <span class="stat-label">Sitios Monitoreados</span>
                    <span class="stat-value"><?php echo $sitios_monitoreados; ?></span>
                </div>
            </div>

            <button class="btn-logout"><a href="logout.php" style="color: white; text-decoration: none;">Cerrar Sesión</a></button>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <!-- HEADER CON NAVEGACIÓN -->
            <header class="top-header">
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nombre); ?>&background=random" alt="Usuario">
                    <span><?php echo htmlspecialchars($nombre); ?></span>
                </div>
            </header>

            <!-- CONTENIDO DEL DASHBOARD -->
            <div class="dashboard-content">
                
                <!-- SECCIÓN BIENVENIDA -->
                <div class="welcome-banner">
                    <div>
                        <h2>¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>
                        <p>Estamos aquí para apoyarte en tu camino hacia la recuperación</p>
                    </div>
                    <span class="date"><?php echo htmlspecialchars($fecha_hoy); ?></span>
                </div>

                <!-- GRID PRINCIPAL DE CONTENIDO -->
                <div class="dashboard-grid">
                    
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="left-column">
                        
                        <!-- MENSAJE MOTIVACIONAL -->
                        <div class="card motivational-card">
                            <div class="card-header">
                                <span class="card-title">💌 Mensaje Motivacional</span>
                                <button class="close-btn">×</button>
                            </div>
                            <div class="card-body">
                                <p class="quote"><?php echo htmlspecialchars($mensaje_hoy); ?></p>
                                <p class="quote-author">— Tu Sistema de Apoyo</p>
                            </div>
                        </div>

                        <!-- SITIOS BLOQUEADOS LISTA -->
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">🔒 Sitios Bloqueados</span>
                                <span class="badge"><?php echo $sitios_monitoreados; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="sites-list">
                                    <?php if (count($sitios_bloqueados) > 0): ?>
                                        <?php foreach ($sitios_bloqueados as $sitio): ?>
                                        <div class="site-item">
                                            <span><?php echo htmlspecialchars($sitio['dominio']); ?></span>
                                            <span class="status-badge active">Activo</span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="no-data" style="text-align: center; color: #6b7280; padding: 20px;">
                                            No tienes sitios bloqueados aún. 
                                            <a href="sitios_bloqueados.php" style="color: #4f46e5;">Agrega uno aquí</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="right-column">
                        
                        <!-- INTENTOS DETECTADOS -->
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">🚨 Intentos Detectados</span>
                            </div>
                            <div class="card-body">
                                <div class="attempts-list">
                                    <?php if (count($ultimos_intentos) > 0): ?>
                                        <?php foreach (array_slice($ultimos_intentos, 0, 3) as $intento): ?>
                                        <div class="attempt-item">
                                            <div class="attempt-time">
                                                <?php echo date('H:i', strtotime($intento['timestamp'])); ?>
                                            </div>
                                            <div class="attempt-domain">
                                                <strong><?php echo htmlspecialchars($intento['dominio']); ?></strong>
                                            </div>
                                            <div class="attempt-status">
                                                <span class="status-badge blocked">Bloqueado</span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <p class="no-data">Aún no hay intentos registrados. ¡Excelente!</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- RED DE APOYO -->
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">👥 Tu Red de Apoyo</span>
                                <span class="badge"><?php echo $total_acompanantes; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="support-network">
                                    <?php if (count($acompanantes) > 0): ?>
                                        <?php foreach ($acompanantes as $acomp): ?>
                                        <div class="support-person">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($acomp['nombre']); ?>&background=random" alt="<?php echo htmlspecialchars($acomp['nombre']); ?>">
                                            <div>
                                                <p class="support-name"><?php echo htmlspecialchars($acomp['nombre']); ?></p>
                                                <p class="support-role"><?php echo ucfirst($acomp['relacion']); ?></p>
                                            </div>
                                            <span class="online-status <?php echo $acomp['notificar'] ? 'online' : 'offline'; ?>">●</span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="no-data" style="text-align: center; color: #6b7280; padding: 20px;">
                                            No tienes acompañantes aún. 
                                            <a href="acompanantes.php" style="color: #4f46e5;">Agrega uno aquí</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Cerrar mensaje motivacional
        document.querySelector('.close-btn')?.addEventListener('click', function() {
            this.closest('.motivational-card').style.display = 'none';
        });
    </script>
</body>
</html>