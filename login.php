<?php
session_start();
require_once 'conexion.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (!empty($usuario) && !empty($password)) {
        // Consulta usando la columna password_hash
        $stmt = $conn->prepare("SELECT id, usuario, password_hash FROM usuarios_admin WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $admin = $resultado->fetch_assoc();
            
            // Verifica hash encriptado o texto plano
            if (password_verify($password, $admin['password_hash']) || $password === $admin['password_hash']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                header("Location: admin.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario no existe.";
        }
        $stmt->close();
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería Leiva - Acceso Administrativo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; min-height:100vh; background:#0f172a; margin:0;">

    <div class="producto-card" style="width:100%; max-width:400px; padding:30px; text-align:center;">
        <h2 style="color:#fff; margin-bottom:10px;">Panel de Administración</h2>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:20px;">Acceso exclusivo para el personal</p>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:10px; border-radius:6px; font-size:14px; margin-bottom:15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="text-align:left; margin-bottom:15px;">
                <label style="color:#fff; font-size:14px;">Usuario:</label>
                <input type="text" name="usuario" required placeholder="admin" style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff; box-sizing:border-box;">
            </div>

            <div style="text-align:left; margin-bottom:20px;">
                <label style="color:#fff; font-size:14px;">Contraseña:</label>
                <input type="password" name="password" required placeholder="••••••••" style="width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #333; background:#1e293b; color:#fff; box-sizing:border-box;">
            </div>

            <button type="submit" class="hero-btn" style="width:100%; border:none; cursor:pointer;">Iniciar Sesión</button>
        </form>
    </div>

</body>
</html>