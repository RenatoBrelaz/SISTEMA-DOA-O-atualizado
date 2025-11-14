<?php
include('conexao.php');
session_start();

$mensagem_feedback = "";
$token_valido = false;
$token = null; 

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE token_recuperacao = ? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $token_valido = true;
    } else {
        $mensagem_feedback = "Token inválido ou expirado.";
    }
    $stmt->close();
} else {
    $mensagem_feedback = "Token ausente.";
}

// Quando o formulário é enviado
if (isset($_POST['senha']) && isset($_POST['confirmar']) && isset($_POST['token_enviado'])) {
    
    $token_para_update = $_POST['token_enviado']; 

    if ($_POST['senha'] === $_POST['confirmar']) {

        $nova_senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE token_recuperacao = ?");
        $stmt_update->bind_param("ss", $nova_senha_hash, $token_para_update);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            $mensagem_feedback = "Senha redefinida com sucesso! <a href='login.php'>Entrar agora</a>";
            $token_valido = false; 
        } else {
            $mensagem_feedback = "Erro ao atualizar a senha. Token inválido ou já utilizado.";
        }
        $stmt_update->close();
    } else {
        $mensagem_feedback = "As senhas não coincidem.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="form-container" style="max-width: 450px;">
        <h1>Redefinir Senha</h1>

        <?php if (!empty($mensagem_feedback)): ?>
            <p style="color: <?php echo (strpos($mensagem_feedback, 'sucesso') !== false) ? 'green' : 'red'; ?>;">
                <?php echo $mensagem_feedback; ?>
            </p>
        <?php endif; ?>

        <?php if ($token_valido): ?>
        <form method="POST">
            <input type="hidden" name="token_enviado" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-control">
                <label for="senha">Nova Senha *</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <div class="form-control">
                <label for="confirmar">Confirmar Nova Senha *</label>
                <input type="password" id="confirmar" name="confirmar" required>
            </div>

            <button type="submit" class="action-button primary">Redefinir Senha</button>
        </form>
        <?php endif; ?>
    </main>
</body>
</html>
