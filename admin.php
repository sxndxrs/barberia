<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['actualizar_estado'])) {
    $id_reserva = intval($_POST['id_reserva']);
    $nuevo_estado = $_POST['estado_cita'];

    $stmt = $conn->prepare("UPDATE reservaciones SET estado_cita = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_reserva);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM reservaciones WHERE id = ?");
    $stmt->bind_param("i", $id_eliminar);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

$query = "SELECT r.*, s.nombre AS servicio_nombre 
          FROM reservaciones r 
          JOIN servicios s ON r.servicio_id = s.id 
          ORDER BY r.fecha_cita DESC, r.hora_cita ASC";
$resultado = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Barbería Leiva</title>
    <link rel="stylesheet" href="styles.css">
    <div class="admin-search-box" style="margin-bottom: 20px; max-width: 400px;">
    <input type="text" id="adminSearchInput" placeholder="🔍 Buscar por cliente, código, fecha o teléfono..." 
           style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.4); color: #fff; font-size: 0.9rem;">
</div>
    <style>
        .admin-table { width:100%; border-collapse:collapse; margin-top:20px; background:#1e293b; border-radius:8px; overflow:hidden; }
        .admin-table th, .admin-table td { padding:12px 15px; text-align:left; border-bottom:1px solid #334155; color:#f8fafc; font-size:14px; }
        .admin-table th { background:#0f172a; color:#38bdf8; font-weight:600; }
    </style>
</head>
<body style="background:#0f172a; color:#fff; padding:20px;">

    <div style="max-width:1100px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #334155; padding-bottom:15px;">
            <div>
                <h1 style="margin:0; font-size:24px;">Panel de Gestión - Barbería Leiva</h1>
                <p style="margin:5px 0 0 0; color:#94a3b8;">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></p>
            </div>
            <div>
                <a href="contacto.php" target="_blank" style="color:#38bdf8; text-decoration:none; margin-right:15px;">Ver Formulario Web</a>
                <a href="logout.php" style="background:#ef4444; color:#fff; padding:8px 15px; border-radius:6px; text-decoration:none; font-size:14px;">Cerrar Sesión</a>
            </div>
        </div>

        <h2>Reservaciones Registradas</h2>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Fecha y Hora</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado Cita</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['codigo_reserva']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nombre_cliente']); ?></td>
                                <td>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['telefono']); ?>" target="_blank" style="color:#25d366; text-decoration:none;">
                                        <?php echo htmlspecialchars($row['telefono']); ?> 💬
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['servicio_nombre']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_cita'])) . ' - ' . date('h:i A', strtotime($row['hora_cita'])); ?></td>
                                <td>L. <?php echo number_format($row['monto_total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['metodo_pago']); ?></td>
                                <td>
                                    <form action="admin.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="id_reserva" value="<?php echo $row['id']; ?>">
                                        <select name="estado_cita" onchange="this.form.submit()" style="padding:4px 8px; border-radius:4px; background:#0f172a; color:#fff; border:1px solid #475569;">
                                            <option value="Pendiente" <?php if ($row['estado_cita'] == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                                            <option value="Confirmada" <?php if ($row['estado_cita'] == 'Confirmada') echo 'selected'; ?>>Confirmada</option>
                                            <option value="Completada" <?php if ($row['estado_cita'] == 'Completada') echo 'selected'; ?>>Completada</option>
                                            <option value="Cancelada" <?php if ($row['estado_cita'] == 'Cancelada') echo 'selected'; ?>>Cancelada</option>
                                        </select>
                                        <input type="hidden" name="actualizar_estado" value="1">
                                    </form>
                                </td>
                                <td>
                                    <a href="admin.php?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta reserva?');" style="color:#ef4444; text-decoration:none;">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center; color:#94a3b8; padding:20px;">No hay reservaciones registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>