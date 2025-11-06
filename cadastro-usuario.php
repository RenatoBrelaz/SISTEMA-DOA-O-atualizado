<?php
session_start();
include("conexao.php");
mysqli_set_charset($conexao, "utf8mb4");


// Receber e limpar dados do formulário
$perfil = mysqli_real_escape_string($conexao, trim($_POST['perfil']));
$nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
$cpf_cnpj = mysqli_real_escape_string($conexao, trim($_POST['cpf_cnpj']));
$email = mysqli_real_escape_string($conexao, trim($_POST['email']));
$telefone = mysqli_real_escape_string($conexao, trim($_POST['telefone']));
$endereco = mysqli_real_escape_string($conexao, trim($_POST['endereco']));
if (empty($_POST['senha'])) {
    die("Erro: senha vazia no POST!");
}
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // senha segura

// Verificar se o usuário já existe pelo email
$sql = "SELECT COUNT(*) AS total FROM usuarios WHERE email = '$email'";
$result = mysqli_query($conexao, $sql);
$row = mysqli_fetch_assoc($result);

if($row['total'] > 0) {
    $_SESSION['usuario_existe'] = true;
    header('Location: cadastro.php');
    exit;
}

// Inserir usuário no banco
$sql = "INSERT INTO usuarios (perfil, nome, cpf_cnpj, email, telefone, endereco, senha, data_cadastro) 
        VALUES ('$perfil', '$nome', '$cpf_cnpj', '$email', '$telefone', '$endereco', '$senha', NOW())";

if ($conexao->query($sql) === TRUE) {
    $_SESSION['cadastro_sucesso'] = "Cadastro realizado com sucesso! Faça login abaixo.";


    // Fecha a conexão antes de redirecionar
    $conexao->close();

    // Redireciona automaticamente para a página de login
    header('Location: login.php');
    exit;
} else {
    $_SESSION['cadastro_erro'] = "Erro ao cadastrar: " . $conexao->error;
    header("Location: cadastro.php");
    exit;
}

?>
