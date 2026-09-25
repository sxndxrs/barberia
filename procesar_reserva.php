<?php
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_cliente = trim($_POST['nombre_cliente']);
    $telefono       = trim($_POST['telefono']);
    $fecha_cita     = $_POST['fecha_cita'];
    $hora_cita      = $_POST['hora_cita'];
    $metodo_pago    = trim($_POST['metodo_pago']);
    
    // Obtener el arreglo de servicios seleccionados
    $servicios_ids  = isset($_POST['servicios']) ? $_POST['servicios'] : [];

    if (empty($servicios_ids)) {
        die("Debes seleccionar al menos un servicio.");
    }

    // Preparar marcadores para la consulta de múltiples IDs (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($servicios_ids), '?'));
    $tipos_datos   = str_repeat('i', count($servicios_ids));

    $sql_servicios = "SELECT nombre, precio FROM servicios WHERE id IN ($placeholders)";
    $stmt_serv = $conn->prepare($sql_servicios);
    $stmt_serv->bind_param($tipos_datos, ...$servicios_ids);
    $stmt_serv->execute();
    $res_serv = $stmt_serv->get_result();

    $nombres_servicios = [];
    $monto_total       = 0.00;

    while ($fila = $res_serv->fetch_assoc()) {
        $nombres_servicios[] = $fila['nombre'];
        $monto_total        += floatval($fila['precio']);
    }
    $stmt_serv->close();

    // Texto combinado de los servicios elegidos (ej: "Corte Normal, Diseño de Barba")
    $texto_servicios = implode(', ', $nombres_servicios);
    $codigo_reserva  = "LEI-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

    // Guardar en la base de datos (se usa el primer ID o se guarda el texto según tu estructura)
    $primer_servicio_id = intval($servicios_ids[0]);

    $sql = "INSERT INTO reservaciones (codigo_reserva, nombre_cliente, telefono, servicio_id, monto_total, fecha_cita, hora_cita, metodo_pago, estado_pago, estado_cita) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 'Pendiente')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssidsss", $codigo_reserva, $nombre_cliente, $telefono, $primer_servicio_id, $monto_total, $fecha_cita, $hora_cita, $metodo_pago);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();

        if ($metodo_pago === 'Tarjeta' || $metodo_pago === 'Tarjeta_Online') {
            header("Location: pasarela_pago.php?codigo=" . urlencode($codigo_reserva));
            exit();
        } else {
            $mi_numero = "50492809797"; // Número de WhatsApp de la barbería

            $mensaje  = "¡Hola Barbería Leiva! ✂️\n";
            $mensaje .= "Acabo de realizar una reserva desde la página web:\n\n";
            $mensaje .= "📌 *Código:* " . $codigo_reserva . "\n";
            $mensaje .= "👤 *Cliente:* " . $nombre_cliente . "\n";
            $mensaje .= "📞 *Teléfono:* " . $telefono . "\n";
            $mensaje .= "💈 *Servicios:* " . $texto_servicios . "\n";
            $mensaje .= "📅 *Fecha:* " . $fecha_cita . "\n";
            $mensaje .= "⏰ *Hora:* " . $hora_cita . "\n";
            $mensaje .= "💰 *Total:* L " . number_format($monto_total, 2) . "\n\n";
            $mensaje .= "Quedo a la espera de su confirmación.";

            $url_ws = "https://api.whatsapp.com/send?phone=" . $mi_numero . "&text=" . urlencode($mensaje);

            header("Location: mi_reserva.php?codigo=" . urlencode($codigo_reserva) . "&auto_ws=" . urlencode($url_ws));
            exit();
        }
    } else {
        echo "Error al registrar la cita: " . $conn->error;
        $stmt->close();
        $conn->close();
    }
}
?>