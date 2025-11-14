<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'doador') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$acao = $_GET['acao'] ?? null;

if (!$id || !$acao) {
    die("Requisição inválida.");
}

$novo_status = match($acao) {
    'aprovar' => 'aprovado',
    'recusar' => 'recusado',
    'finalizar' => 'finalizado',
    default => die("Ação inválida.")
};

$sql = "UPDATE solicitacoes SET status = ? WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("si", $novo_status, $id);
$stmt->execute();

header("Location: minhas-doacoes.php");
exit;
?>