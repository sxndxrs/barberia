<?php
require_once 'conexion.php';

$reserva = null;
$error = "";

if (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);

    $sql = "SELECT * FROM reservaciones WHERE codigo_reserva = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $reserva = $resultado->fetch_assoc();
    } else {
        $error = "No se encontró ninguna reserva con el código ingresado.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería Leiva - Consultar Cita</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="navbar">
        <div class="logo">Barberia<span>Leiva</span></div>
        <button class="menu-toggle" id="mobile-menu" aria-label="Abrir menú">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
        <nav class="nav-menu" id="nav-menu">
            <a href="index.html" class="nav-link">Inicio</a>
            <a href="cortes.html" class="nav-link">Catalago de Cortes</a>
            <a href="cortesrea.html" class="nav-link">Cortes Realizados</a>
            <a href="tarifas.html" class="nav-link">Tarifas</a>
            <a href="servicios.html" class="nav-link">Servicios</a>
            <a href="contacto.php" class="nav-link">Contacto</a>
            <a href="mi_reserva.php" class="nav-link">Consultar Cita</a>
        </nav>
    </header>

    <div class="site-selector">
        <a href="index.html" class="selector-btn active" data-target="barberia">Barbería</a>
        <a href="tienda.html" class="selector-btn" data-target="tienda">Tienda</a>
    </div>

    <section class="page-section">
        <h2>Consultar Cita</h2>
        <p class="section-subtitle">Ingresa tu código de reserva para consultar los detalles de tu turno.</p>

        <!-- Formulario de Consulta -->
        <div class="contact-container" style="max-width: 500px; margin: 0 auto 40px auto;">
            <form action="mi_reserva.php" method="GET" class="catalog-card" style="padding: 25px; background: rgba(42, 15, 15, 0.6); border: 1px solid rgba(255, 255, 255, 0.1);">
                <div style="margin-bottom: 15px;">
                    <label for="codigo" style="display: block; color: #b89494; margin-bottom: 8px; font-weight: 600;">Código de Cita:</label>
                    <input type="text" id="codigo" name="codigo" placeholder="Ej: LEI-A1B2" value="<?php echo isset($_GET['codigo']) ? htmlspecialchars($_GET['codigo']) : ''; ?>" required 
                           style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(0,0,0,0.5); color: #fff; font-size: 1rem; text-transform: uppercase;">
                </div>
                <button type="submit" class="hero-btn" style="width: 100%; text-align: center; border: none; cursor: pointer;">Buscar Cita</button>
            </form>
        </div>

        <?php if ($error): ?>
            <div style="max-width: 500px; margin: 0 auto; padding: 15px; background: rgba(248, 56, 56, 0.2); border: 1px solid #f83838; border-radius: 10px; color: #fff; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Tarjeta del Recibo -->
        <?php if ($reserva): ?>
            <div class="receipt-card">
                <div style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print();" class="hero-btn" style="padding: 8px 18px; font-size: 0.88rem; cursor: pointer; border: none;">
                        🖨️ Imprimir / Guardar Comprobante
                    </button>
                </div>
                <div class="receipt-header">
                    <h3 style="color: #fff; font-size: 1.6rem; margin-bottom: 5px;">Comprobante de Cita</h3>
                    <p style="color: #b89494; font-size: 0.9rem;">Barbería Leiva</p>
                </div>

                <!-- Caja del código modificada para permitir clic y copiado -->
                <div class="receipt-code-box" onclick="copiarCodigoReserva('<?php echo htmlspecialchars($reserva['codigo_reserva']); ?>')" style="cursor: pointer;" title="Clic para copiar código">
                    <span style="display: block; color: #b89494; font-size: 0.8rem; text-transform: uppercase;">Código de Reserva (Clic para copiar 📋)</span>
                    <strong style="color: #ff7878; font-size: 1.6rem; letter-spacing: 2px; font-family: monospace;"><?php echo htmlspecialchars($reserva['codigo_reserva']); ?></strong>
                </div>

                <table class="receipt-table">
                    <tr>
                        <td class="label">Cliente:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['nombre_cliente']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Teléfono:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['telefono']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['fecha_cita']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Hora:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['hora_cita']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Método de Pago:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['metodo_pago']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Estado Cita:</td>
                        <td class="value" style="color: #ff7878;"><?php echo htmlspecialchars($reserva['estado_cita']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Estado Pago:</td>
                        <td class="value"><?php echo htmlspecialchars($reserva['estado_pago']); ?></td>
                    </tr>
                </table>

                <div class="receipt-total">
                    <span style="font-size: 1.1rem; color: #fff; font-weight: bold;">Monto Total:</span>
                    <span style="font-size: 1.5rem; color: #ff7878; font-weight: bold;">L <?php echo number_format($reserva['monto_total'], 2); ?></span>
                </div>
            </div>

            <!-- Contenedor del aviso flotante (Toast) -->
            <div id="toastNotification" class="toast-notification">
                ¡Código copiado al portapapeles! 📋
            </div>

        <?php endif; ?>
    </section>

    <script src="script.js"></script>


    <?php if (isset($_GET['auto_ws'])): ?>
 <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Redirige directamente a la app de WhatsApp
        window.location.href = "<?php echo $_GET['auto_ws']; ?>";
    });
</script>
<?php endif; ?>

</body>
</html>