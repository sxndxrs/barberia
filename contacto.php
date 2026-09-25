<?php
require_once 'conexion.php';

// Consultar servicios desde MySQL
$query_servicios = "SELECT s.id, s.nombre, s.precio, c.nombre AS categoria 
                    FROM servicios s 
                    JOIN categorias c ON s.categoria_id = c.id 
                    ORDER BY c.id, s.nombre";
$resultado_servicios = $conn->query($query_servicios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería Leiva - Reservaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="navbar" style="opacity: 1; transform: none; display: flex;">
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
        <h2>Reserva tu Cita en Línea</h2>
        <p class="section-subtitle">Selecciona tus servicios, fecha y horario de preferencia.</p>
        
        <div class="contact-container">
            <div class="producto-card" style="max-width: 550px; margin: 0 auto; text-align: left; padding: 30px;">
                <form action="procesar_reserva.php" method="POST">
                    
                    <label style="display:block; margin-top:15px; color:#fff;">Nombre Completo:</label>
                    <input type="text" name="nombre_cliente" required placeholder="Tu nombre" style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff;">

                    <label style="display:block; margin-top:15px; color:#fff;">Teléfono / WhatsApp:</label>
                    <input type="tel" name="telefono" required placeholder="+504 9744-3516" style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff;">

                    <label style="display:block; margin-top:15px; margin-bottom:10px; color:#fff;">Selecciona los Servicios (puedes elegir varios):</label>
                    <div style="max-height: 220px; overflow-y: auto; background:#1e293b; border:1px solid #333; border-radius:6px; padding: 12px;">
                        <?php if ($resultado_servicios->num_rows > 0): ?>
                            <?php while ($servicio = $resultado_servicios->fetch_assoc()): ?>
                                <label style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #334155; color: #fff; cursor: pointer;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="checkbox" name="servicios[]" value="<?php echo $servicio['id']; ?>" data-precio="<?php echo $servicio['precio']; ?>" class="check-servicio" onchange="calcularTotal()" style="width: 18px; height: 18px; accent-color: #d9534f; cursor: pointer;">
                                        <span><?php echo htmlspecialchars($servicio['nombre']); ?></span>
                                    </div>
                                    <span style="color: #cbd5e1; font-weight: 500;">L. <?php echo number_format($servicio['precio'], 2); ?></span>
                                </label>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #94a3b8; margin: 0;">No hay servicios disponibles.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Muestra el Total Sumado -->
                    <div style="margin-top: 15px; background: #0f172a; padding: 12px; border-radius: 6px; text-align: right; border: 1px solid #333;">
                        <span style="color: #fff; font-size: 1.1rem;">Total Estimado: </span>
                        <strong style="color: #4ade80; font-size: 1.3rem;">L. <span id="monto-total-display">0.00</span></strong>
                    </div>

                    <div style="display:flex; gap:15px; margin-top:15px;">
                        <div style="flex:1;">
                            <label style="display:block; color:#fff;">Fecha:</label>
                            <input type="date" name="fecha_cita" required min="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff;">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; color:#fff;">Hora:</label>
                            <input type="time" name="hora_cita" required style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff;">
                        </div>
                    </div>

                    <label style="display:block; margin-top:15px; color:#fff;">Método de Pago:</label>
                    <select name="metodo_pago" required style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff;">
                        <option value="Local">Pagar en la Barbería (Efectivo/Tarjeta)</option>
                        <option value="Tarjeta_Online">Pagar Ahora con Tarjeta en Línea</option>
                    </select>

                    <button type="submit" class="hero-btn" style="width:100%; margin-top:25px; border:none; cursor:pointer;">
                        Confirmar Reservación
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script src="script.js"></script>

    <script>
    function calcularTotal() {
        let total = 0;
        const checkboxes = document.querySelectorAll('.check-servicio:checked');
        
        checkboxes.forEach(check => {
            total += parseFloat(check.getAttribute('data-precio')) || 0;
        });

        document.getElementById('monto-total-display').innerText = total.toFixed(2);
    }
    </script>
</body>
</html>