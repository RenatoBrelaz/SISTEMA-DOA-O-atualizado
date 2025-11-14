<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include('conexao.php');
session_start();

$mensagem_feedback = "";

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    $stmt = $conexao->prepare("SELECT id, email FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        $token = bin2hex(random_bytes(50));
        $expira_em = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt_update = $conexao->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expira_em, $email);
        $stmt_update->execute();

        $link_redefinicao = "http://localhost/donation/nova-senha.php?token=" . urlencode($token);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'renatoesteujr@gmail.com';
            $mail->Password = 'gfej kkxe iexd sash';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('SEUEMAIL@gmail.com', 'DONATION');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de senha - DONATION';
            $mail->Body = "
                <p>Olá,</p>
                <p>Você solicitou redefinir sua senha. Clique abaixo:</p>
                <p><a href='$link_redefinicao'>$link_redefinicao</a></p>
                <p>O link expira em 1 hora.</p>
            ";

            $mail->send();
            $mensagem_feedback = "Um e-mail com o link de recuperação foi enviado para $email.";
        } catch (Exception $e) {
            $mensagem_feedback = "Erro ao enviar e-mail: {$mail->ErrorInfo}";
        }

    } else {
        $mensagem_feedback = "E-mail não encontrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="header">
    <a href="index.html" class="logo-link">
        <img src="img/donation (2) (1).png" class="logo">
    </a>
</header>

<main class="form-container" style="max-width: 450px;">
    <h1>Recuperar Senha</h1>
    <p>Digite o e-mail cadastrado para receber um link de redefinição.</p>

    <?php if (!empty($mensagem_feedback)): ?>
        <p style="color: <?php echo (strpos($mensagem_feedback, 'enviado') !== false) ? 'green' : 'red'; ?>;">
            <?php echo $mensagem_feedback; ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-control">
            <label for="email">E-mail *</label>
            <input type="email" name="email" id="email" required>
        </div>
        <button type="submit" class="action-button primary">Enviar link</button>
    </form>

    <p style="margin-top: 1rem;">
        <a href="login.php" class="link-more">Voltar ao login</a>
    </p>
</main>
</body>
</html>
