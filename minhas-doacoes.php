<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'doador') {
    header("Location: login.php");
    exit;
}

$id_doador = $_SESSION['usuario_id'];

// Busca as doações do usuário
$sql = "SELECT id, nome_item, categoria, quantidade, estado, imagem_url, data_cadastro 
        FROM itens 
        WHERE id_doador = ?  
        ORDER BY data_cadastro DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_doador);
$stmt->execute();
$result = $stmt->get_result();

$itens = [];
while ($row = $result->fetch_assoc()) {
    $itens[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Doações | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo" >
    <nav class="main-nav">
        <a href="dashboard.php">Home</a>
        <a href="cadastro-doacao.php">Cadastrar Novo Item</a>
        <a href="minhas-doacoes.php" class="active">Minhas Doações</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main class="management-container">
    <h1>Minhas Doações e Histórico</h1>
    <p>Acompanhe o status dos itens que você doou e visualize os feedbacks da ONG.</p>

    <div class="tabs-nav">
        <button class="tab-button active" data-tab="ativos">Itens Ativos</button>
        <button class="tab-button" data-tab="historico">Histórico</button>
    </div>

    <!-- ABA DE ITENS ATIVOS -->
    <section id="ativos" class="tab-content active">
        <h2>Doações Pendentes e em Andamento</h2>

        <?php if (empty($itens)): ?>
            <p class="empty-state">Você não tem itens ativos no momento. Que tal <a href="cadastro-doacao.php">cadastrar uma nova doação</a>?</p>
        <?php else: ?>
            <table class="doacoes-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome do Item</th>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td>
                            <?php if ($item['imagem_url']): ?>
                                <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="Item" width="60" style="border-radius:8px;">
                                <?php else: ?>
                                    <span>Sem imagem</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['nome_item']) ?></td>
                            <td><?= htmlspecialchars($item['categoria']) ?></td>
                            <td><?= htmlspecialchars($item['quantidade']) ?></td>
                            <td><?= date("d/m/Y", strtotime($item['data_cadastro'])) ?></td>
                            <td>
                            <a href="editar-doacao.php?id=<?= $item['id'] ?>" class="btn-edit">✏️ Editar</a>
                            <a href="excluir-doacao.php?id=<?= $item['id'] ?>" class="btn-delete" 
                            onclick="return confirm('Tem certeza que deseja excluir este item? Essa ação não pode ser desfeita.');">
                            🗑️ Excluir </a>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- ABA DE HISTÓRICO -->
    <section id="historico" class="tab-content" style="display:none;">
        <h2>Histórico de Doações Finalizadas</h2>
        <p>Aqui você poderá visualizar as doações já entregues e o feedback da ONG (em breve!).</p>
        <p class="empty-state">Nenhuma doação finalizada ainda.</p>
    </section>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
</footer>

<script>
const buttons = document.querySelectorAll('.tab-button');
const contents = document.querySelectorAll('.tab-content');
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        contents.forEach(c => c.style.display = 'none');
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).style.display = 'block';
    });
});
</script>

</body>
</html>
