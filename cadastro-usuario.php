<?php
session_start();
include("conexao.php");
mysqli_set_charset($conexao, "utf8mb4");

$perfil = trim(mysqli_real_escape_string($conexao, $_POST['perfil'] ?? ''));
$nome = trim(mysqli_real_escape_string($conexao, $_POST['nome'] ?? ''));
$cpf_cnpj = trim(mysqli_real_escape_string($conexao, $_POST['cpf_cnpj'] ?? ''));
$email = trim(mysqli_real_escape_string($conexao, $_POST['email'] ?? ''));
$telefone = trim(mysqli_real_escape_string($conexao, $_POST['telefone'] ?? ''));
$rua = trim(mysqli_real_escape_string($conexao, $_POST['rua'] ?? ''));
$bairro = trim(mysqli_real_escape_string($conexao, $_POST['bairro'] ?? ''));
$cidade = trim(mysqli_real_escape_string($conexao, $_POST['cidade'] ?? ''));
$estado = trim(mysqli_real_escape_string($conexao, $_POST['estado'] ?? ''));
$pais = trim(mysqli_real_escape_string($conexao, $_POST['pais'] ?? ''));
$cep = trim(mysqli_real_escape_string($conexao, $_POST['cep'] ?? ''));

if (empty($perfil) || empty($nome) || empty($cpf_cnpj) || empty($email) || empty($telefone) || empty($rua) || empty($bairro) || empty($cidade) || empty($estado) || empty($pais) || empty($_POST['senha'])) {
    $_SESSION['cadastro_erro'] = "Preencha todos os campos obrigatórios!";
    header("Location: cadastro.php");
    exit;
}

$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

$endereco = "$rua, $bairro, $cidade - $estado, $pais, CEP: $cep";

$sql_check = "SELECT COUNT(*) AS total FROM usuarios WHERE email = '$email'";
$result = mysqli_query($conexao, $sql_check);
$row = mysqli_fetch_assoc($result);

if($row['total'] > 0) {
    $_SESSION['usuario_existe'] = true;
    header('Location: cadastro.php');
    exit;
}

$sql_insert = "INSERT INTO usuarios (perfil, nome, cpf_cnpj, email, telefone, endereco, senha, data_cadastro) 
               VALUES ('$perfil', '$nome', '$cpf_cnpj', '$email', '$telefone', '$endereco', '$senha', NOW())";

if ($conexao->query($sql_insert) === TRUE) {
    $_SESSION['cadastro_sucesso'] = "Cadastro realizado com sucesso!";

    $conexao->close();

    switch (strtolower($perfil)) {
        case 'doador':
            header('Location: dashboard.php');
            break;
        case 'beneficiario':
            header('Location: dashboard-beneficiario.php');
            break;
        case 'ong':
            header('Location: ong.php');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit;
} else {
    $_SESSION['cadastro_erro'] = "Erro ao cadastrar: " . $conexao->error;
    header("Location: cadastro.php");
    exit;
}
?>
