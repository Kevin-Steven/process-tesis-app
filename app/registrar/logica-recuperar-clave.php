<?php
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';
require '../config/config.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = mysqli_real_escape_string($conn, $_POST['recuperar-clave']);
    
    // Verificar si el correo existe en la base de datos
    $sql = "SELECT id, email FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // El correo existe, generar un token de recuperacin
        $usuario = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50)); // Generar un token seguro
        $expira = date("Y-m-d H:i:s", strtotime("+10 minutes")); // El token expira en 1 hora

        // Almacenar el token y la fecha de expiración en la base de datos
        $sql = "INSERT INTO recuperacion_clave (usuario_id, token, expira) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $usuario['id'], $token, $expira);
        $stmt->execute();

        // Crear el enlace de recuperación
        $link = "https://wilsoncanizares.com/app/registrar/restablecer-clave.php?token=" . $token;

        // Enviar el correo electrónico
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP de Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tds.titulacion.istjba@gmail.com';
            $mail->Password   = 'ecic zfih ifqj utgv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->CharSet = 'UTF-8';

            // Configuración del remitente y destinatario
            $mail->setFrom('tds.titulacion.istjba@gmail.com', 'Recuperación de Contraseña');
            $mail->addAddress($correo); // Correo del destinatario (usuario)

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Recupera credenciales de acceso';
            $mail->Body    = "
                <h3>Hola.</h3>
                <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                <br>
                <p><a href='$link' style='color: blue; text-decoration: underline;'>Restablecer contraseña</a></p>
                <br>
                <p><strong>Nota:</strong> El enlace estará disponible por 10 minutos.</p>
                <p>Si no solicitaste el restablecimiento de tu contraseña, puedes ignorar este mensaje.</p>
                <p>Saludos cordiales,<br>Instituto Tecnológico Juan Bautista Aguirre.</p>
                <hr>
                <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>
            ";
            $mail->AltBody = "Haz clic en el siguiente enlace para restablecer tu contraseña: $link";

            // Enviar el correo
            $mail->send();
            header("Location: recuperar-cuenta.php?mensaje=Correo de recuperación enviado. Revisa tu bandeja de entrada.&tipo=success");
            exit();
        } catch (Exception $e) {
            $error = urlencode("Error al enviar el correo: {$mail->ErrorInfo}");
            header("Location: recuperar-cuenta.php?mensaje=$error&tipo=danger");
            exit();
        }
    } else {
        header("Location: recuperar-cuenta.php?mensaje=El correo no está registrado.&tipo=danger");
        exit();
    }
} else {
    header("Location: ../../index.php");
    exit();
}
