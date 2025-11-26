<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();

// Verificar si las nuevas columnas existen
$check_columns = $conn->query("SHOW COLUMNS FROM codigos_promocionales LIKE 'limite_activaciones'");
$has_new_columns = $check_columns->num_rows > 0;
$check_columns->close();

// Estadísticas
$result = $conn->query("SELECT COUNT(*) as total FROM codigos_promocionales WHERE estado = 'activo'");
$codigos_activos = $result->fetch_assoc()['total'];

if ($has_new_columns) {
    $result = $conn->query("SELECT COUNT(*) as total FROM codigos_promocionales WHERE estado = 'usado' OR (limite_activaciones > 0 AND activaciones_usadas >= limite_activaciones)");
    $codigos_usados = $result->fetch_assoc()['total'];
    
    // Total de activaciones realizadas
    $result = $conn->query("SELECT COALESCE(SUM(activaciones_usadas), 0) as total FROM codigos_promocionales");
    $total_activaciones = $result->fetch_assoc()['total'];
} else {
    $result = $conn->query("SELECT COUNT(*) as total FROM codigos_promocionales WHERE estado = 'usado'");
    $codigos_usados = $result->fetch_assoc()['total'];
    $total_activaciones = 0;
}

// Verificar si existe la tabla codigos_canjeados
$check_table = $conn->query("SHOW TABLES LIKE 'codigos_canjeados'");
$has_canjeados_table = $check_table->num_rows > 0;
$check_table->close();

if ($has_canjeados_table) {
    $result = $conn->query("SELECT COALESCE(SUM(monto), 0) as total FROM codigos_canjeados");
    $monto_total_canjeado = $result->fetch_assoc()['total'];
} else {
    $monto_total_canjeado = 0;
}

// Procesar creación de código
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_codigo'])) {
    // Verificar si las nuevas columnas existen
    if (!$has_new_columns) {
        $error = "Por favor, ejecute primero la migración SQL para agregar las nuevas columnas. Archivo: database/codigos_activaciones_migration.sql";
        } else {
            $limite_activaciones = isset($_POST['limite_activaciones']) ? intval($_POST['limite_activaciones']) : 0;
            $fecha_expiracion = isset($_POST['fecha_expiracion']) ? sanitize($_POST['fecha_expiracion']) : '';
            
            // Validar que el límite sea mayor a 0
            if ($limite_activaciones <= 0) {
                $error = "El límite de activaciones debe ser mayor a 0.";
            } else {
                // Verificar si ya existe un código activo y NO expirado
                $query_activo = "SELECT id FROM codigos_promocionales WHERE estado = 'activo' AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURDATE()) LIMIT 1";
                $result = $conn->query($query_activo);
                $codigo_existente = $result->fetch_assoc();
                
                if ($codigo_existente) {
                    $error = "Ya existe un código activo y vigente. Debe desactivar o eliminar el código existente antes de crear uno nuevo.";
                } else {
                // Generar un solo código
                $codigo = generateUniqueCode(12);
                
                // El monto será aleatorio (1-50 Bs) al canjear, así que no lo guardamos en la tabla
                // Verificar si la columna monto existe antes de intentar insertarla
                $check_monto = $conn->query("SHOW COLUMNS FROM codigos_promocionales LIKE 'monto'");
                $has_monto_column = $check_monto->num_rows > 0;
                $check_monto->close();
                
                if (!empty($fecha_expiracion)) {
                    if ($has_monto_column) {
                        // Si la columna existe, insertarla con 0 (no se usará)
                        $stmt = $conn->prepare("INSERT INTO codigos_promocionales (codigo, monto, limite_activaciones, activaciones_usadas, fecha_expiracion) VALUES (?, 0, ?, 0, ?)");
                        if (!$stmt) {
                            $error = "Error al preparar la consulta: " . $conn->error;
                        } else {
                            $stmt->bind_param("sis", $codigo, $limite_activaciones, $fecha_expiracion);
                            if ($stmt->execute()) {
                                $mensaje = "Código promocional creado exitosamente: <strong>{$codigo}</strong><br>Límite de activaciones: {$limite_activaciones}<br>El monto será aleatorio entre 1 y 50 Bs cada vez que se canjee.";
                            } else {
                                $error = "Error al crear el código: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    } else {
                        // Si no existe la columna monto, no la incluimos en el INSERT
                        $stmt = $conn->prepare("INSERT INTO codigos_promocionales (codigo, limite_activaciones, activaciones_usadas, fecha_expiracion) VALUES (?, ?, 0, ?)");
                        if (!$stmt) {
                            $error = "Error al preparar la consulta: " . $conn->error;
                        } else {
                            $stmt->bind_param("sis", $codigo, $limite_activaciones, $fecha_expiracion);
                            if ($stmt->execute()) {
                                $mensaje = "Código promocional creado exitosamente: <strong>{$codigo}</strong><br>Límite de activaciones: {$limite_activaciones}<br>El monto será aleatorio entre 1 y 50 Bs cada vez que se canjee.";
                            } else {
                                $error = "Error al crear el código: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                } else {
                    if ($has_monto_column) {
                        // Si la columna existe, insertarla con 0 (no se usará)
                        $stmt = $conn->prepare("INSERT INTO codigos_promocionales (codigo, monto, limite_activaciones, activaciones_usadas) VALUES (?, 0, ?, 0)");
                        if (!$stmt) {
                            $error = "Error al preparar la consulta: " . $conn->error;
                        } else {
                            $stmt->bind_param("si", $codigo, $limite_activaciones);
                            if ($stmt->execute()) {
                                $mensaje = "Código promocional creado exitosamente: <strong>{$codigo}</strong><br>Límite de activaciones: {$limite_activaciones}<br>El monto será aleatorio entre 1 y 50 Bs cada vez que se canjee.";
                            } else {
                                $error = "Error al crear el código: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    } else {
                        // Si no existe la columna monto, no la incluimos en el INSERT
                        $stmt = $conn->prepare("INSERT INTO codigos_promocionales (codigo, limite_activaciones, activaciones_usadas) VALUES (?, ?, 0)");
                        if (!$stmt) {
                            $error = "Error al preparar la consulta: " . $conn->error;
                        } else {
                            $stmt->bind_param("si", $codigo, $limite_activaciones);
                            if ($stmt->execute()) {
                                $mensaje = "Código promocional creado exitosamente: <strong>{$codigo}</strong><br>Límite de activaciones: {$limite_activaciones}<br>El monto será aleatorio entre 1 y 50 Bs cada vez que se canjee.";
                            } else {
                                $error = "Error al crear el código: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}

// Filtros
$filtro_estado = isset($_GET['estado']) ? sanitize($_GET['estado']) : 'todos';
$filtro_busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

$query_where = "WHERE 1=1";

if ($filtro_estado !== 'todos') {
    $filtro_estado = in_array($filtro_estado, ['activo', 'usado', 'expirado']) ? $filtro_estado : 'todos';
    if ($filtro_estado !== 'todos') {
        $query_where .= " AND c.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
    }
}

if (!empty($filtro_busqueda)) {
    $busqueda = $conn->real_escape_string($filtro_busqueda);
    $query_where .= " AND c.codigo LIKE '%$busqueda%'";
}

// Obtener códigos
$query = "SELECT c.*, u1.telefono as creador_telefono
          FROM codigos_promocionales c
          LEFT JOIN users u1 ON c.usuario_id = u1.id
          $query_where
          ORDER BY c.fecha_creacion DESC
          LIMIT 100";
$result = $conn->query($query);
$codigos = $result->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Códigos - CashSpace Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar Admin -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <img src="../assets/images/logo.png" alt="CashSpace" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">CashSpace - Admin</span>
            </a>
            <div class="navbar-user">
                <a href="index.php" class="btn btn-outline">Panel</a>
                <a href="../index.php" class="btn btn-outline">Ir al Inicio</a>
                <a href="../logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="color: var(--dark-color); margin: 0;">Gestión de Códigos Promocionales</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 8px; color: #065f46;">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; background: #fee2e2; border-left: 4px solid #ef4444; border-radius: 8px; color: #991b1b;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);"><?php echo number_format($codigos_activos); ?></div>
                <div class="stat-label">Códigos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo number_format($codigos_usados); ?></div>
                <div class="stat-label">Códigos Usados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo number_format($total_activaciones); ?></div>
                <div class="stat-label">Total Activaciones</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--secondary-color);"><?php echo formatCurrency($monto_total_canjeado); ?></div>
                <div class="stat-label">Monto Total Canjeado</div>
            </div>
        </div>

        <!-- Crear código -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Crear Nuevo Código Promocional</h2>
            </div>
            <div style="padding: 20px;">
                <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #0369a1; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> <strong>Información:</strong> Se generará un solo código promocional. El monto será aleatorio entre 1 y 50 Bs cada vez que un usuario lo canjee. Configure cuántas veces se puede usar este código.
                    </p>
                </div>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Límite de Activaciones:</label>
                            <input type="number" name="limite_activaciones" min="1" required 
                                   placeholder="Ej: 30 o 50" 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                            <small style="color: #666; font-size: 12px;">Cantidad de veces que se puede canjear este código</small>
                        </div>
                        <div>
                            <label style="font-weight: 600; display: block; margin-bottom: 5px;">Fecha Expiración (Opcional):</label>
                            <input type="date" name="fecha_expiracion" 
                                   style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                        </div>
                    </div>
                    <button type="submit" name="crear_codigo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Código
                    </button>
                </form>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Filtros y Búsqueda</h2>
            </div>
            <div style="padding: 20px;">
                <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Estado:</label>
                        <select name="estado" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                            <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                            <option value="usado" <?php echo $filtro_estado === 'usado' ? 'selected' : ''; ?>>Usados</option>
                            <option value="expirado" <?php echo $filtro_estado === 'expirado' ? 'selected' : ''; ?>>Expirados</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Buscar Código:</label>
                        <input type="text" name="busqueda" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" 
                               placeholder="Código..." 
                               style="padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 100%;">
                    </div>
                    <div style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="codigos.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de códigos -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista de Códigos</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <?php if ($has_new_columns): ?>
                                <th>Límite Activaciones</th>
                                <th>Activaciones Usadas</th>
                            <?php else: ?>
                                <th>Monto</th>
                            <?php endif; ?>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Expiración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($codigos)): ?>
                            <tr>
                                <td colspan="<?php echo $has_new_columns ? '8' : '7'; ?>" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-ticket-alt" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No hay códigos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($codigos as $codigo): ?>
                                <tr>
                                    <td>#<?php echo $codigo['id']; ?></td>
                                    <td><code style="font-size: 14px; padding: 5px 10px; background: #f5f5f5; border-radius: 3px;"><?php echo htmlspecialchars($codigo['codigo']); ?></code></td>
                                    <?php if ($has_new_columns): ?>
                                        <td style="font-weight: 600; color: var(--primary-color);"><?php echo $codigo['limite_activaciones'] ?? 'N/A'; ?></td>
                                        <td style="font-weight: 600; color: var(--secondary-color);">
                                            <?php echo ($codigo['activaciones_usadas'] ?? 0) . ' / ' . ($codigo['limite_activaciones'] ?? 0); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estado_class = '';
                                            $estado_icon = '';
                                            $limite = $codigo['limite_activaciones'] ?? 0;
                                            $usadas = $codigo['activaciones_usadas'] ?? 0;
                                            
                                            if ($codigo['estado'] === 'expirado') {
                                                $estado_class = 'badge-danger';
                                                $estado_icon = 'times';
                                                $estado_text = 'Expirado';
                                            } elseif ($limite > 0 && $usadas >= $limite) {
                                                $estado_class = 'badge-primary';
                                                $estado_icon = 'check-double';
                                                $estado_text = 'Agotado';
                                            } elseif ($codigo['estado'] === 'activo') {
                                                $estado_class = 'badge-success';
                                                $estado_icon = 'check';
                                                $estado_text = 'Activo';
                                            } else {
                                                $estado_class = 'badge-primary';
                                                $estado_icon = 'check-double';
                                                $estado_text = ucfirst($codigo['estado']);
                                            }
                                            ?>
                                            <span class="badge <?php echo $estado_class; ?>">
                                                <i class="fas fa-<?php echo $estado_icon; ?>"></i> 
                                                <?php echo $estado_text; ?>
                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td style="font-weight: 600; color: var(--secondary-color);"><?php echo formatCurrency($codigo['monto'] ?? 0); ?></td>
                                        <td>
                                            <?php
                                            $estado_class = '';
                                            $estado_icon = '';
                                            switch($codigo['estado']) {
                                                case 'activo':
                                                    $estado_class = 'badge-success';
                                                    $estado_icon = 'check';
                                                    break;
                                                case 'usado':
                                                    $estado_class = 'badge-primary';
                                                    $estado_icon = 'check-double';
                                                    break;
                                                case 'expirado':
                                                    $estado_class = 'badge-danger';
                                                    $estado_icon = 'times';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $estado_class; ?>">
                                                <i class="fas fa-<?php echo $estado_icon; ?>"></i> 
                                                <?php echo ucfirst($codigo['estado']); ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    <td><?php echo date('d/m/Y H:i', strtotime($codigo['fecha_creacion'])); ?></td>
                                    <td><?php echo $codigo['fecha_expiracion'] ? date('d/m/Y', strtotime($codigo['fecha_expiracion'])) : 'Sin expiración'; ?></td>
                                    <td>
                                        <button onclick="eliminarCodigo(<?php echo $codigo['id']; ?>, '<?php echo htmlspecialchars($codigo['codigo'], ENT_QUOTES); ?>', event)" 
                                                class="btn btn-danger" 
                                                style="padding: 5px 12px; font-size: 12px;"
                                                title="Eliminar código">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación Personalizado -->
    <div id="confirmModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title" style="color: var(--dark-color);">
                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-right: 10px;"></i>
                    Confirmar Eliminación
                </h3>
                <button class="modal-close" onclick="cerrarConfirmModal()">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="font-size: 16px; color: var(--dark-color); margin-bottom: 15px;">
                    ¿Está seguro de que desea eliminar el código <strong id="codigoConfirmText"></strong>?
                </p>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #92400e; font-size: 14px;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                        <strong>ADVERTENCIA:</strong> Esta acción eliminará el código permanentemente y no se puede deshacer.
                    </p>
                    <p style="margin: 10px 0 0 0; color: #92400e; font-size: 14px;">
                        Si el código está activo, los usuarios ya no podrán canjearlo.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button onclick="cerrarConfirmModal()" class="btn btn-outline" style="padding: 10px 20px;">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button onclick="confirmarEliminacion()" class="btn btn-danger" style="padding: 10px 20px;" id="btnConfirmarEliminar">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script src="js/admin.js"></script>
    <script>
        let codigoAEliminar = null;
        let codigoIdAEliminar = null;
        let botonOriginal = null;
        let scrollPositionConfirm = 0;
        
        function eliminarCodigo(codigoId, codigoTexto, event) {
            console.log('eliminarCodigo llamado:', { codigoId, codigoTexto, event });
            
            codigoIdAEliminar = codigoId;
            codigoAEliminar = codigoTexto;
            
            // Actualizar texto del modal
            const codigoText = document.getElementById('codigoConfirmText');
            if (codigoText) {
                codigoText.textContent = `"${codigoTexto}"`;
            }
            
            // Guardar referencia al botón
            if (event && event.target) {
                botonOriginal = event.target.closest('button');
            } else if (window.event && window.event.target) {
                botonOriginal = window.event.target.closest('button');
            }
            
            // Mostrar modal personalizado
            const modal = document.getElementById('confirmModal');
            if (!modal) {
                console.error('Modal de confirmación no encontrado');
                alert('Error: Modal de confirmación no encontrado');
                return;
            }
            
            modal.style.display = 'flex';
            modal.classList.add('active');
            
            // Bloquear scroll del body
            scrollPositionConfirm = window.pageYOffset || document.documentElement.scrollTop;
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollPositionConfirm}px`;
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
            
            console.log('Modal mostrado');
        }
        
        function cerrarConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'none';
            modal.classList.remove('active');
            
            // Restaurar scroll
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, scrollPositionConfirm);
            scrollPositionConfirm = 0;
            
            codigoIdAEliminar = null;
            codigoAEliminar = null;
            botonOriginal = null;
        }
        
        async function confirmarEliminacion() {
            if (!codigoIdAEliminar) {
                cerrarConfirmModal();
                return;
            }
            
            const codigoId = codigoIdAEliminar;
            
            // Cerrar modal primero
            cerrarConfirmModal();
            
            // Mostrar indicador de carga en el botón
            if (botonOriginal) {
                botonOriginal.disabled = true;
                botonOriginal.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
            }
            
            try {
                console.log('Intentando eliminar código ID:', codigoId);
                
                const response = await fetch('api/eliminar_codigo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: codigoId
                    })
                });
                
                console.log('Respuesta recibida:', response.status);
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error('Respuesta no JSON:', text);
                    mostrarMensaje('Error: La respuesta del servidor no es válida. Ver consola para más detalles.', 'error');
                    if (botonOriginal) {
                        botonOriginal.disabled = false;
                        botonOriginal.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
                    }
                    return;
                }
                
                const data = await response.json();
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    mostrarMensaje('Código eliminado exitosamente', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    mostrarMensaje('Error: ' + (data.message || 'Error desconocido'), 'error');
                    if (botonOriginal) {
                        botonOriginal.disabled = false;
                        botonOriginal.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
                    }
                }
            } catch (error) {
                console.error('Error completo:', error);
                mostrarMensaje('Error al eliminar el código: ' + error.message, 'error');
                if (botonOriginal) {
                    botonOriginal.disabled = false;
                    botonOriginal.innerHTML = '<i class="fas fa-trash"></i> Eliminar';
                }
            }
        }
        
        function mostrarMensaje(mensaje, tipo) {
            // Crear o actualizar mensaje flotante
            let mensajeDiv = document.getElementById('mensajeFlotante');
            if (!mensajeDiv) {
                mensajeDiv = document.createElement('div');
                mensajeDiv.id = 'mensajeFlotante';
                mensajeDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; padding: 15px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); max-width: 400px; animation: slideInRight 0.3s ease-out;';
                document.body.appendChild(mensajeDiv);
            }
            
            const bgColor = tipo === 'success' ? '#d1fae5' : '#fee2e2';
            const textColor = tipo === 'success' ? '#065f46' : '#991b1b';
            const icon = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            mensajeDiv.style.background = bgColor;
            mensajeDiv.style.color = textColor;
            mensajeDiv.style.borderLeft = `4px solid ${tipo === 'success' ? '#10b981' : '#ef4444'}`;
            mensajeDiv.innerHTML = `<i class="fas ${icon}" style="margin-right: 10px;"></i> ${mensaje}`;
            
            setTimeout(() => {
                mensajeDiv.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (mensajeDiv.parentNode) {
                        mensajeDiv.parentNode.removeChild(mensajeDiv);
                    }
                }, 300);
            }, 3000);
        }
        
        // Cerrar modal al hacer clic fuera
        const confirmModal = document.getElementById('confirmModal');
        if (confirmModal) {
            confirmModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarConfirmModal();
                }
            });
        }
        
        // Debug: Verificar que las funciones estén disponibles
        console.log('Funciones de eliminación cargadas:', {
            eliminarCodigo: typeof eliminarCodigo,
            confirmarEliminacion: typeof confirmarEliminacion,
            cerrarConfirmModal: typeof cerrarConfirmModal
        });
    </script>
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</body>
</html>

