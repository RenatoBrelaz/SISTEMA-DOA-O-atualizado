<?php
session_start();
$mensagem_erro = "";

if (isset($_SESSION['status_erro'])) {
    $mensagem_erro = $_SESSION['status_erro'];
    unset($_SESSION['status_erro']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <a href="index.html" class="logo-link">
            <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo" >
        </a>
        <nav class="main-nav">
            <a href="index.html" class="btn-primary">Home</a>
            <a href="login.php" class="btn-primary">Já tenho conta</a>
        </nav>
    </header>

    <main class="form-container" style="max-width: 600px;">
        
        <h1>Crie sua conta</h1>

        <?php if (!empty($mensagem_erro)): ?>
            <p style="color: red; font-weight: bold; text-align: center;"><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>

        <form action="cadastro-usuario.php" method="POST">

            <fieldset class="form-group">
                <legend>Tipo de Cadastro *</legend>
                <div class="radio-group type-select">

                <input type="radio" id="perfil-doador" name="perfil" value="doador" required>
                <label for="perfil-doador">Doador</label>

                <input type="radio" id="perfil-beneficiario" name="perfil" value="beneficiario">
                <label for="perfil-beneficiario">Beneficiário</label>

                <input type="radio" id="perfil-ong" name="perfil" value="ong">
                <label for="perfil-ong">ONG</label>
 
                </div>
            </fieldset>

            <fieldset class="form-group">
                <legend>Seus Dados</legend>
                
                <div class="form-control">
                    <label for="nome">Nome Completo / Razão Social *</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-control">
                    <label for="cpf_cnpj">CPF / CNPJ *</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" required placeholder="Apenas números">
                </div>
                
                <div class="form-control">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-control">
                    <label for="telefone">Telefone *</label>
                    <input type="tel" id="telefone" name="telefone" required placeholder="(XX) XXXXX-XXXX">
                </div>
            </fieldset>

            <fieldset class="form-group">
                <legend>Endereço Principal</legend>
                
                <div class="form-control">
                    <label for="endereco">Endereço Completo *</label>
                    <input type="text" id="endereco" name="endereco" required placeholder="Rua, Número, Bairro e Cidade">
                </div>
            </fieldset>

            <fieldset class="form-group">
                <legend>Definir Senha</legend>
                <div class="form-control">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <div class="form-control">
                    <label for="confirma-senha">Confirmar Senha *</label>
                    <input type="password" id="confirma-senha" name="confirmaSenha" required>
                </div>

                <input type="submit" name="submit" id="submit" value="Cadastrar">

            </fieldset>
    
            <div style="text-align: center; margin-top: 1.5rem;">
                <p>Ao se cadastrar, você concorda com nossos Termos de Uso.</p>
            </div>

        </form>
    </main>
    
    <footer class="footer">
        <p>&copy; 2025 DONATION.</p>
    </footer>

    <script src="cadastro.js"></script>
</body>
</html>