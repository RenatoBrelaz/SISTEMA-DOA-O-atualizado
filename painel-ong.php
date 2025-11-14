<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica se é uma ONG logada
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'ong') {
    header("Location: login.php");
    exit;
}

$id_ong = $_SESSION['usuario_id'];

// 2️⃣ Busca todas as solicitações de itens da ONG
$sql = "SELECT s.*, 
               i.nome_item, 
               i.imagem_url, 
               u.nome AS nome_beneficiario, 
               u.endereco AS endereco_beneficiario
        FROM solicitacoes s
        INNER JOIN itens i ON s.id_item = i.id
        INNER JOIN usuarios u ON s.id_beneficiario = u.id
        WHERE i.id_ong = ?
        ORDER BY s.data_solicitacao DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_ong);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel da ONG | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo">
    <nav class="main-nav">
        <a href="dashboard-ong.php">Home</a>
        <a href="painel-ong.php">Solicitações</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main class="container">
    <h1>Solicitações Recebidas</h1>

    <?php if ($result->num_rows > 0): ?>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Beneficiário</th>
                <th>Endereço</th>
                <th>Mensagem</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nome_item']) ?></td>
                <td><?= htmlspecialchars($row['nome_beneficiario']) ?></td>
                <td><?= htmlspecialchars($row['endereco_beneficiario']) ?></td>
                <td><?= htmlspecialchars($row['observacoes'] ?: '-') ?></td>
                <td><strong><?= ucfirst($row['status']) ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_solicitacao'])) ?></td>
                <td>
                    <?php if ($row['status'] === 'pendente'): ?>
                        <form action="atualizar-solicitacao.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_solicitacao" value="<?= $row['id'] ?>">
                            <input type="hidden" name="acao" value="aprovar">
                            <button type="submit" class="btn-aceitar">Aprovar</button>
                        </form>

                        <form action="atualizar-solicitacao.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_solicitacao" value="<?= $row['id'] ?>">
                            <input type="hidden" name="acao" value="recusar">
                            <button type="submit" class="btn-recusar">Recusar</button>
                        </form>
                    <?php else: ?>
                        <em>Sem ações</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>Não há solicitações no momento.</p>
    <?php endif; ?>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
</footer>

<style>
.btn-aceitar {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 6px;
}
.btn-recusar {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 6px;
}
.styled-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.styled-table th, .styled-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
</style>

</body>
</html>
