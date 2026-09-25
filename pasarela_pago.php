<?php
require_once 'conexion.php';

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$reserva = null;
$error_pago = '';

if (!empty($codigo)) {
    $stmt = $conn->prepare("SELECT r.*, s.nombre AS servicio_nombre 
                            FROM reservaciones r 
                            JOIN servicios s ON r.servicio_id = s.id 
                            WHERE r.codigo_reserva = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $reserva = $resultado->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['procesar_tarjeta'])) {
    $id_reserva = intval($_POST['id_reserva']);
    $num_tarjeta = str_replace(' ', '', trim($_POST['card_number']));
    $cvv = trim($_POST['card_cvv']);
    $exp_mes = trim($_POST['card_exp_month']);
    $exp_anio = trim($_POST['card_exp_year']);
    $nombre_titular = trim($_POST['card_holder']);

    // Configuración PixelPay Honduras (Credenciales de tu cuenta PixelPay)
    $pixelpay_endpoint = "https://pixelpay.app/api/v2/transaction/sale";
    $pixelpay_key = "TU_PIXELPAY_KEY"; 
    $pixelpay_secret = "TU_PIXELPAY_SECRET"; 

    $payload = [
        "order_id" => $reserva['codigo_reserva'],
        "amount" => floatval($reserva['monto_total']),
        "currency" => "HNL",
        "billing" => [
            "name" => $nombre_titular,
            "email" => "cliente@barberialeiva.hn",
            "phone" => $reserva['telefono']
        ],
        "card" => [
            "number" => $num_tarjeta,
            "cvv" => $cvv,
            "expiration_month" => $exp_mes,
            "expiration_year" => $exp_anio
        ]
    ];

    // Enviar Petición a PixelPay mediante cURL
    $ch = curl_init($pixelpay_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-auth-key: ' . $pixelpay_key,
        'x-auth-hash: ' . md5($pixelpay_secret)
    ]);

    $respuesta = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($respuesta, true);

    // Si estás en pruebas locales sin credenciales de PixelPay, simulamos aprobación al enviar el formulario
    $aprobado = ($http_code === 200 && isset($json['success']) && $json['success'] === true);

    if ($aprobado) {
        $stmt_update = $conn->prepare("UPDATE reservaciones SET estado_pago = 'Pagado', estado_cita = 'Confirmada' WHERE id = ?");
        $stmt_update->bind_param("i", $id_reserva);
        $stmt_update->execute();
        $stmt_update->close();

        $reserva['estado_pago'] = 'Pagado';
        $reserva['estado_cita'] = 'Confirmada';
    } else {
        $error_pago = "La tarjeta fue declinada o los datos son incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasarela de Pago - Barbería Leiva</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .pago-card {
            background: #1e293b;
            max-width: 480px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            color: #fff;
        }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 5px; }
        .form-group input {
            width: 100%;
            padding: 10px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            color: #fff;
            box-sizing: border-box;
        }
        .resumen-box {
            background: #0f172a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #38bdf8;
        }
    </style>
</head>
<body style="background:#0f172a; margin:0; padding:20px; font-family:sans-serif;">

    <div class="pago-card">
        <h2 style="margin-top:0; color:#38bdf8; text-align:center;">Pago con Tarjeta Directo</h2>
        <p style="text-align:center; font-size:14px; color:#94a3b8; margin-bottom:25px;">Barbería Leiva - Cobro en Lempiras (HNL)</p>

        <?php if (!$reserva): ?>
            <div style="background:#ef4444; padding:15px; border-radius:6px; text-align:center;">
                Código de reservación inválido o no encontrado.
            </div>
            <div style="text-align:center; margin-top:20px;">
                <a href="contacto.php" style="color:#38bdf8; text-decoration:none;">← Volver al formulario</a>
            </div>

        <?php elseif ($reserva['estado_pago'] === 'Pagado'): ?>
            <div style="background:#10b981; padding:20px; border-radius:8px; text-align:center;">
                <h3 style="margin:0 0 10px 0;">¡Pago Procesado Exitosamente!</h3>
                <p style="margin:0; font-size:14px;">Tu cita <strong>#<?php echo htmlspecialchars($reserva['codigo_reserva']); ?></strong> ha sido confirmada.</p>
            </div>
            <div style="text-align:center; margin-top:25px;">
                <a href="contacto.php" class="hero-btn" style="text-decoration:none; display:inline-block; padding:10px 20px;">Volver a Inicio</a>
            </div>

        <?php else: ?>

            <?php if (!empty($error_pago)): ?>
                <div style="background:#ef4444; padding:10px; border-radius:6px; text-align:center; margin-bottom:15px; font-size:14px;">
                    <?php echo htmlspecialchars($error_pago); ?>
                </div>
            <?php endif; ?>

            <div class="resumen-box">
                <p style="margin:0 0 5px 0;"><strong>Cliente:</strong> <?php echo htmlspecialchars($reserva['nombre_cliente']); ?></p>
                <p style="margin:0 0 5px 0;"><strong>Servicio:</strong> <?php echo htmlspecialchars($reserva['servicio_nombre']); ?></p>
                <p style="margin:0 0 5px 0;"><strong>Fecha y Hora:</strong> <?php echo date('d/m/Y', strtotime($reserva['fecha_cita'])) . ' - ' . date('h:i A', strtotime($reserva['hora_cita'])); ?></p>
                <hr style="border:0; border-top:1px solid #334155; margin:10px 0;">
                <p style="margin:0; font-size:18px; color:#38bdf8;"><strong>Total a Pagar: L. <?php echo number_format($reserva['monto_total'], 2); ?> HNL</strong></p>
            </div>

            <!-- Formulario de Tarjeta de Crédito / Débito Directo -->
            <form action="pasarela_pago.php?codigo=<?php echo urlencode($reserva['codigo_reserva']); ?>" method="POST">
                <input type="hidden" name="id_reserva" value="<?php echo $reserva['id']; ?>">

                <div class="form-group">
                    <label>Nombre del Titular de la Tarjeta</label>
                    <input type="text" name="card_holder" placeholder="Juan Pérez" required>
                </div>

                <div class="form-group">
                    <label>Número de Tarjeta (VISA / Mastercard)</label>
                    <input type="text" name="card_number" placeholder="4000 0000 0000 0000" maxlength="19" required>
                </div>

                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>Mes (MM)</label>
                        <input type="text" name="card_exp_month" placeholder="08" maxlength="2" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Año (AA)</label>
                        <input type="text" name="card_exp_year" placeholder="28" maxlength="2" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>CVV</label>
                        <input type="password" name="card_cvv" placeholder="123" maxlength="4" required>
                    </div>
                </div>

                <button type="submit" name="procesar_tarjeta" class="hero-btn" style="width:100%; border:none; cursor:pointer; margin-top:10px; padding:12px;">
                    Pagar L. <?php echo number_format($reserva['monto_total'], 2); ?> HNL
                </button>
            </form>

        <?php endif; ?>
    </div>

</body>
</html>