<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'beneficiario') {
    header("Location: login.php");
    exit();
}

$id_beneficiario = $_SESSION['usuario_id'];
$id_solicitacao = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_solicitacao <= 0) {
    die("Solicitação inválida.");
}

// Verifica se pertence ao usuário e ainda está pendente
$sql_check = "SELECT id FROM solicitacoes WHERE id = ? AND id_beneficiario = ? AND status = 'pendente'";
$stmt = $conexao->prepare($sql_check);
$stmt->bind_param("ii", $id_solicitacao, $id_beneficiario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Solicitação não encontrada ou já processada.");
}

// Cancela a solicitação
$sql_cancel = "UPDATE solicitacoes SET status = 'cancelada', data_atualizacao = NOW() WHERE id = ?";
$stmt = $conexao->prepare($sql_cancel);
$stmt->bind_param("i", $id_solicitacao);
$stmt->execute();

header("Location: gerenciar-solicitacoes.php?msg=cancelada");
exit;
?>