<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Carrega o PHPMailer

$mail = new PHPMailer(true);

try {
    // Configurações do servidor
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';      // Servidor SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'renatoesteujr@gmail.com';  // Seu e-mail
    $mail->Password   = 'rknisfditosbcosm'; // Senha ou App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remetente e destinatário
    $mail->setFrom('seuemail@gmail.com', 'Seu Nome');
    $mail->addAddress('destinatario@exemplo.com', 'Destinatário');

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Teste de e-mail com PHPMailer';
    $mail->Body    = '<h3>Olá!</h3><p>Este é um e-mail enviado com <b>PHPMailer</b>.</p>';
    $mail->AltBody = 'Olá! Este é um e-mail enviado com PHPMailer (modo texto).';

    // Enviar
    $mail->send();
    echo 'E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
}
?>