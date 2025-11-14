<?php
include('conexao.php');
session_start();

// Variável para armazenar a mensagem de erro ou sucesso
$mensagem_feedback = ""; 

// 1. Exibir mensagem de sucesso do cadastro (se houver)
if (isset($_SESSION['cadastro_sucesso'])) {
    $mensagem_feedback = $_SESSION['cadastro_sucesso'];
    // Remove a mensagem da sessão após exibir
    unset($_SESSION['cadastro_sucesso']);
}

if (isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['perfil'])) {

    $mensagem_feedback = "";

    if (strlen($_POST['email']) == 0) {
        $mensagem_feedback = "Preencha seu email";
    } else if (strlen($_POST['senha']) == 0) {
        $mensagem_feedback = "Preencha sua senha";
    } else if (strlen($_POST['perfil']) == 0) {
        $mensagem_feedback = "Informe o tipo de perfil";
    } else {

        $email = $conexao->real_escape_string($_POST['email']);
        $senha_digitada = $_POST['senha'];
        $perfil_digitado = $conexao->real_escape_string($_POST['perfil']);
        
        $sql_code = "SELECT * FROM usuarios WHERE email = '$email'";
        $sql_query = $conexao->query($sql_code) or die("Falha na execução do código SQL: " . $conexao->error);
        
        if ($sql_query->num_rows == 1) {
            $usuario = $sql_query->fetch_assoc();
            $senha_criptografada = $usuario['senha'];
        
            if (password_verify($senha_digitada, $senha_criptografada)) {
                if ($usuario['perfil'] === $perfil_digitado) {
        
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_perfil'] = $usuario['perfil'];
                    $_SESSION['usuario_email'] = $usuario['email'];
        
                    if ($usuario['perfil'] == 'doador') {
                        header("Location: dashboard.php");
                    } elseif ($usuario['perfil'] == 'beneficiario') {
                        header("Location: dashboard-beneficiario.php");
                    } elseif ($usuario['perfil'] == 'ong') {
                        header("Location: painel-ong.php");
                    }
                    exit;
                } else {
                    $mensagem_feedback = "Perfil incorreto para este usuário.";
                }
            } else {
                $mensagem_feedback = "Falha ao logar! Senha incorreta.";
            }
        } else {
            $mensagem_feedback = "Falha ao logar! E-mail não encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
<header class="header">
        <a href="index.html" class="logo-link">
            <img src="img/donation (2) (1).png" class="logo">
        </a>
        
        <nav class="main-nav">
        </nav>
    </header>
    <main class="form-container" style="max-width: 450px;">
        
        <h1>Acessar Plataforma</h1>
        <p>Insira seus dados para continuar como Doador ou Beneficiário.</p>

        <?php if (!empty($mensagem_feedback)) : ?>
            <p style="color: <?php echo (strpos($mensagem_feedback, 'sucesso') !== false) ? 'green' : 'red'; ?>; 
                      font-weight: bold; margin-bottom: 1rem;">
                <?php echo $mensagem_feedback; ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">

            <div class="form-control">
                <label for="email">E-mail ou Usuário *</label>
                <input type="text" id="email" name="email" placeholder="seu.email@exemplo.com" required>
            </div>

            <div class="form-control">
                <label for="password">Senha *</label>
                <input type="password" id="password" name="senha" placeholder="••••••••" required>
            </div>
            
            <div class="form-control">
                <label for="perfil">Entrar como:</label>
                <select id="perfil" name="perfil" required>
                    <option value="doador">Doador</option>
                    <option value="beneficiario">Beneficiário</option>
                    <option value="ong">ONG / Administrador</option>
                </select>
            </div>

            <div class="form-actions" style="margin-top: 2rem;">
                <button type="submit" class="action-button primary" style="max-width: none;">
                    Entrar
                </button>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.95rem;">
                <p>
                    <a href="recuperar-senha.php" class="link-more">Esqueci minha senha</a>
                </p>
                <p style="margin-top: 0.8rem;">
                    Ainda não tem conta? <a href="cadastro.php" class="link-more">Cadastre-se aqui</a>.
                </p>
            </div>

        </form>
    </main>
    
    <footer class="footer">
        <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
    </footer>

</body>
</html>