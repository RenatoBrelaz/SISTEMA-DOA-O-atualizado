<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login e perfil
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'doador') {
    header("Location: login.php");
    exit;
}

$id_doador = $_SESSION['usuario_id'];

// 2️⃣ Verifica se recebeu o ID
if (!isset($_GET['id'])) {
    echo "<script>alert('ID do item não informado.'); window.location='minhas-doacoes.php';</script>";
    exit;
}

$id_item = (int) $_GET['id'];

// 3️⃣ Busca o item para confirmar a propriedade e pegar a imagem
// CORRIGIDO: Checando id_doador
$sql = "SELECT imagem FROM itens WHERE id = ? AND id_doador = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $id_item, $id_doador); // <<-- PONTO E VÍRGULA ADICIONADO AQUI
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Item não encontrado ou você não tem permissão para excluí-lo.'); window.location='minhas-doacoes.php';</script>";
    exit;
}

$item = $result->fetch_assoc();
$stmt->close();

// 4️⃣ Exclui imagem (se existir)
if (!empty($item['imagem'])) {
    $caminhoImagem = "uploads/itens/" . $item['imagem'];
    if (file_exists($caminhoImagem)) {
        unlink($caminhoImagem); // apaga a imagem do servidor
    }
}

// 5️⃣ Exclui o item do banco
// CORRIGIDO: Checando id_doador
$sql_del = "DELETE FROM itens WHERE id = ? AND id_doador = ?";
$stmt_del = $conexao->prepare($sql_del);
$stmt_del->bind_param("ii", $id_item, $id_doador);

if ($stmt_del->execute()) {
    echo "<script>alert('Item excluído com sucesso!'); window.location='minhas-doacoes.php';</script>";
} else {
    echo "<script>alert('Erro ao excluir o item.'); window.location='minhas-doacoes.php';</script>";
}

$stmt_del->close();
$conexao->close();
?>