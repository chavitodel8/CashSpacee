<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$conn = getConnection();

// Obtener todos los avisos
$result = $conn->query("SELECT a.*, u.telefono as admin_telefono FROM avisos a LEFT JOIN users u ON a.admin_id = u.id ORDER BY a.prioridad DESC, a.fecha_creacion DESC");
$avisos = $result->fetch_all(MYSQLI_ASSOC);

// Estadísticas
$result = $conn->query("SELECT COUNT(*) as total FROM avisos WHERE estado = 'activo' AND fecha_inicio <= NOW() AND fecha_fin >= NOW()");
$avisos_activos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM avisos WHERE estado = 'inactivo'");
$avisos_inactivos = $result->fetch_assoc()['total'];

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Avisos - CashSpace Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .avisos-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .aviso-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        .aviso-card.tipo-info { border-left-color: #3b82f6; }
        .aviso-card.tipo-success { border-left-color: #10b981; }
        .aviso-card.tipo-warning { border-left-color: #f59e0b; }
        .aviso-card.tipo-error { border-left-color: #ef4444; }
        .aviso-card.tipo-celebracion { border-left-color: #8b5cf6; }
        .aviso-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .aviso-titulo {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }
        .aviso-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-activo { background: #d1fae5; color: #065f46; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; }
        .badge-prioridad { background: #fef3c7; color: #92400e; }
        .aviso-mensaje {
            color: #6b7280;
            margin: 10px 0;
            line-height: 1.6;
        }
        .aviso-fechas {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            font-size: 13px;
            color: #9ca3af;
        }
        .aviso-acciones {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .modal-form {
            display: grid;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .avisos-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <a href="../index.php" class="btn btn-outline">Inicio</a>
                <a href="../logout.php" class="btn btn-danger">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="color: var(--dark-color);">Gestión de Avisos</h1>
            <button class="btn btn-primary" onclick="openModal('crearAvisoModal')">
                <i class="fas fa-plus"></i> Crear Aviso
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);"><?php echo $avisos_activos; ?></div>
                <div class="stat-label">Avisos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger-color);"><?php echo $avisos_inactivos; ?></div>
                <div class="stat-label">Avisos Inactivos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--primary-color);"><?php echo count($avisos); ?></div>
                <div class="stat-label">Total de Avisos</div>
            </div>
        </div>

        <!-- Lista de avisos -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Todos los Avisos</h2>
            </div>
            <div class="avisos-container" style="padding: 20px;">
                <?php if (empty($avisos)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #9ca3af;">
                        <i class="fas fa-bullhorn" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>No hay avisos creados aún</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($avisos as $aviso): ?>
                        <div class="aviso-card tipo-<?php echo $aviso['tipo']; ?>">
                            <div class="aviso-header">
                                <h3 class="aviso-titulo"><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <?php if ($aviso['estado'] === 'activo'): ?>
                                        <span class="aviso-badge badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="aviso-badge badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                    <?php if ($aviso['prioridad'] > 0): ?>
                                        <span class="aviso-badge badge-prioridad">Prioridad <?php echo $aviso['prioridad']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="aviso-mensaje">
                                <?php echo nl2br(htmlspecialchars($aviso['mensaje'])); ?>
                            </div>
                            <div class="aviso-fechas">
                                <div>
                                    <i class="fas fa-calendar-alt"></i> 
                                    <strong>Inicio:</strong> <?php echo date('d/m/Y H:i', strtotime($aviso['fecha_inicio'])); ?>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-times"></i> 
                                    <strong>Fin:</strong> <?php echo date('d/m/Y H:i', strtotime($aviso['fecha_fin'])); ?>
                                </div>
                            </div>
                            <div class="aviso-acciones">
                                <button class="btn btn-sm btn-outline" onclick="editarAviso(<?php echo htmlspecialchars(json_encode($aviso)); ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-outline" onclick="toggleAviso(<?php echo $aviso['id']; ?>, '<?php echo $aviso['estado']; ?>')">
                                    <i class="fas fa-<?php echo $aviso['estado'] === 'activo' ? 'eye-slash' : 'eye'; ?>"></i> 
                                    <?php echo $aviso['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarAviso(<?php echo $aviso['id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Aviso -->
    <div id="crearAvisoModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitulo">Crear Nuevo Aviso</h3>
                <button class="modal-close" onclick="closeModal('crearAvisoModal')">&times;</button>
            </div>
            <form id="avisoForm" onsubmit="guardarAviso(event)">
                <input type="hidden" id="avisoId" name="id" value="">
                <div class="modal-form">
                    <div class="form-group">
                        <label class="form-label">Título del Aviso</label>
                        <input type="text" name="titulo" id="avisoTitulo" class="form-control" required placeholder="Ej: Día Feriado - No se trabajará">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mensaje</label>
                        <textarea name="mensaje" id="avisoMensaje" class="form-control" rows="4" required placeholder="Escribe el mensaje del aviso..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Aviso</label>
                        <select name="tipo" id="avisoTipo" class="form-control" required>
                            <option value="info">Información (Azul)</option>
                            <option value="success">Éxito (Verde)</option>
                            <option value="warning">Advertencia (Amarillo)</option>
                            <option value="error">Error (Rojo)</option>
                            <option value="celebracion">Celebración (Morado)</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="datetime-local" name="fecha_inicio" id="avisoFechaInicio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="datetime-local" name="fecha_fin" id="avisoFechaFin" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad (0 = normal, mayor = más importante)</label>
                        <input type="number" name="prioridad" id="avisoPrioridad" class="form-control" value="0" min="0" max="10">
                    </div>
                    <div id="avisoMessage"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Aviso
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/admin.js"></script>
    <script>
        // Función para guardar aviso
        async function guardarAviso(event) {
            event.preventDefault();
            const form = document.getElementById('avisoForm');
            const formData = new FormData(form);
            const messageDiv = document.getElementById('avisoMessage');
            const avisoId = document.getElementById('avisoId').value;
            
            messageDiv.innerHTML = '<div style="padding: 15px; background: #fef3c7; border-radius: 10px; color: #92400e;"><i class="fas fa-spinner fa-spin"></i> Guardando...</div>';
            
            try {
                const url = avisoId ? '/CashSpace/admin/api/avisos.php?action=update' : '/CashSpace/admin/api/avisos.php?action=create';
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.innerHTML = `<div style="padding: 15px; background: #d1fae5; border-radius: 10px; color: #065f46;"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                }
            } catch (error) {
                messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Error al guardar el aviso</div>`;
            }
        }

        // Función para editar aviso
        function editarAviso(aviso) {
            document.getElementById('avisoId').value = aviso.id;
            document.getElementById('avisoTitulo').value = aviso.titulo;
            document.getElementById('avisoMensaje').value = aviso.mensaje;
            document.getElementById('avisoTipo').value = aviso.tipo;
            document.getElementById('avisoPrioridad').value = aviso.prioridad;
            
            // Formatear fechas para datetime-local
            const fechaInicio = new Date(aviso.fecha_inicio);
            const fechaFin = new Date(aviso.fecha_fin);
            document.getElementById('avisoFechaInicio').value = fechaInicio.toISOString().slice(0, 16);
            document.getElementById('avisoFechaFin').value = fechaFin.toISOString().slice(0, 16);
            
            document.getElementById('modalTitulo').textContent = 'Editar Aviso';
            openModal('crearAvisoModal');
        }

        // Función para toggle estado
        async function toggleAviso(id, estadoActual) {
            if (!confirm(`¿Estás seguro de ${estadoActual === 'activo' ? 'desactivar' : 'activar'} este aviso?`)) {
                return;
            }
            
            try {
                const response = await fetch('/CashSpace/admin/api/avisos.php?action=toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error al cambiar el estado del aviso');
            }
        }

        // Función para eliminar aviso
        async function eliminarAviso(id) {
            if (!confirm('¿Estás seguro de eliminar este aviso? Esta acción no se puede deshacer.')) {
                return;
            }
            
            try {
                const response = await fetch('/CashSpace/admin/api/avisos.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error al eliminar el aviso');
            }
        }

        // Función para abrir modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Si es crear, limpiar formulario
                if (modalId === 'crearAvisoModal' && !document.getElementById('avisoId').value) {
                    document.getElementById('avisoForm').reset();
                    document.getElementById('avisoId').value = '';
                    document.getElementById('modalTitulo').textContent = 'Crear Nuevo Aviso';
                    // Establecer fecha mínima como ahora
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    document.getElementById('avisoFechaInicio').value = now.toISOString().slice(0, 16);
                    const tomorrow = new Date(now);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    document.getElementById('avisoFechaFin').value = tomorrow.toISOString().slice(0, 16);
                }
            }
        }

        // Función para cerrar modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>
</html>

