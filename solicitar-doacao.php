<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login (beneficiário)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'beneficiario') {
    header("Location: login.php");
    exit;
}

$id_beneficiario = $_SESSION['usuario_id'];
$id_item = $_POST['id_item'] ?? null;
$mensagem = trim($_POST['mensagem'] ?? '');

// 2️⃣ Verifica se o item foi passado corretamente
if (!$id_item || !is_numeric($id_item)) {
    echo "<script>alert('Item inválido.'); window.location='catalogo-itens.php';</script>";
    exit;
}

// 3️⃣ Busca informações do item e do doador
$sql_item = "SELECT id_doador, nome_item FROM itens WHERE id = ? AND id_ong IS NULL";
$stmt_item = $conexao->prepare($sql_item);
$stmt_item->bind_param("i", $id_item);
$stmt_item->execute();
$result_item = $stmt_item->get_result();

if ($result_item->num_rows === 0) {
    echo "<script>alert('Item não encontrado ou indisponível.'); window.location='catalogo-itens.php';</script>";
    exit;
}

$item = $result_item->fetch_assoc();
$id_doador = $item['id_doador'];
$nome_item = $item['nome_item'];

// 4️⃣ Evita duplicidade: o mesmo beneficiário solicitar o mesmo item duas vezes
$sql_check = "SELECT id FROM solicitacoes WHERE id_item = ? AND id_beneficiario = ?";
$stmt_check = $conexao->prepare($sql_check);
$stmt_check->bind_param("ii", $id_item, $id_beneficiario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "<script>alert('Você já solicitou este item anteriormente.'); window.location='gerenciar-solicitacoes.php';</script>";
    exit;
}

// 5️⃣ Insere a solicitação
$sql_insert = "INSERT INTO solicitacoes (id_beneficiario, id_item, id_doador, nome_item, status, observacoes, data_solicitacao)
               VALUES (?, ?, ?, ?, 'pendente', ?, NOW())";
$stmt_insert = $conexao->prepare($sql_insert);
$stmt_insert->bind_param("iiiss", $id_beneficiario, $id_item, $id_doador, $nome_item, $mensagem);

if ($stmt_insert->execute()) {
    echo "<script>
            alert('Solicitação enviada com sucesso! O doador será notificado.');
            window.location='gerenciar-solicitacoes.php';
          </script>";
} else {
    echo "<script>alert('Erro ao enviar solicitação.'); history.back();</script>";
}

$stmt_insert->close();
$conexao->close();
?>
