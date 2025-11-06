<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login e perfil (apenas beneficiário pode solicitar)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'beneficiario') {
    header("Location: login.php");
    exit;
}

$id_beneficiario = $_SESSION['usuario_id'];

// 2️⃣ Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: catalogo-itens.php");
    exit;
}

// 3️⃣ Coleta e valida dados
$id_item = $_POST['id_item'] ?? null;
$id_beneficiario_form = $_POST['id_beneficiario'] ?? null;
$mensagem = trim($_POST['mensagem'] ?? '');

// Validação básica
if (empty($id_item) || !is_numeric($id_item) || $id_beneficiario != $id_beneficiario_form) {
    echo "<script>alert('Erro de segurança ou dados inválidos.'); window.location='catalogo-itens.php';</script>";
    exit;
}

// 4️⃣ Checa se o item está disponível e se já não foi solicitado pelo mesmo beneficiário
$sql_check = "SELECT id_item 
              FROM solicitacoes 
              WHERE id_item = ? AND id_beneficiario = ?";
$stmt_check = $conexao->prepare($sql_check);
$stmt_check->bind_param("ii", $id_item, $id_beneficiario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "<script>alert('Você já enviou uma solicitação para este item.'); window.location='gerenciar-solicitacoes.php';</script>";
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// Adicionalmente, verifica se o item ainda está no catálogo (id_ong IS NULL)
$sql_disponivel = "SELECT id FROM itens WHERE id = ? AND id_ong IS NULL";
$stmt_disp = $conexao->prepare($sql_disponivel);
$stmt_disp->bind_param("i", $id_item);
$stmt_disp->execute();
$result_disp = $stmt_disp->get_result();

if ($result_disp->num_rows === 0) {
    echo "<script>alert('Este item não está mais disponível no catálogo.'); window.location='catalogo-itens.php';</script>";
    $stmt_disp->close();
    exit;
}
$stmt_disp->close();

// 5️⃣ Insere a nova solicitação no banco
$status = 'pendente';
$sql_insert = "INSERT INTO solicitacoes (id_item, id_beneficiario, mensagem_solicitacao, status) 
               VALUES (?, ?, ?, ?)";
$stmt_insert = $conexao->prepare($sql_insert);

if ($stmt_insert) {
    $stmt_insert->bind_param("iiss", $id_item, $id_beneficiario, $mensagem, $status);
    
    if ($stmt_insert->execute()) {
        echo "<script>alert('✅ Solicitação enviada com sucesso! Acompanhe o status em Minhas Solicitações.'); window.location='gerenciar-solicitacoes.php';</script>";
    } else {
        echo "<script>alert('❌ Erro ao registrar a solicitação: " . $stmt_insert->error . "'); window.location='detalhe_item.php?id=" . $id_item . "';</script>";
    }
    $stmt_insert->close();
} else {
    echo "<script>alert('Erro ao preparar a query de inserção.'); window.location='detalhe_item.php?id=" . $id_item . "';</script>";
}

$conexao->close();
?>