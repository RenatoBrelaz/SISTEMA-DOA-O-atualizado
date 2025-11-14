<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login e perfil
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_perfil'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['usuario_perfil'] != 'beneficiario') {
    echo "Acesso negado! Esta página é exclusiva para beneficiários.";
    exit;
}

// 2️⃣ Filtros de busca
$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';

// 3️⃣ Query base
$sql = "SELECT i.*, u.nome AS nome_doador 
        FROM itens i
        INNER JOIN usuarios u ON i.id_doador = u.id
        WHERE i.id_ong IS NULL";

$params = [];
$tipos = '';

if (!empty($busca)) {
    $sql .= " AND (i.nome_item LIKE ? OR i.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $tipos .= 'ss';
}

if (!empty($categoria)) {
    $sql .= " AND i.categoria = ?";
    $params[] = $categoria;
    $tipos .= 's';
}

if (!empty($estado)) {
    $sql .= " AND i.estado = ?";
    $params[] = $estado;
    $tipos .= 's';
}

$sql .= " ORDER BY i.data_cadastro DESC";

$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$res_itens = $stmt->get_result();
$itens_encontrados = ($res_itens && $res_itens->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Itens | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo">
    <nav class="main-nav">
        <a href="dashboard-beneficiario.php">Home</a>
        <a href="gerenciar-solicitacoes.php">Minhas Solicitações</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main class="catalogo-container">

    <aside class="sidebar-filters">
        <h2>🔎 Filtrar Resultados</h2>
        <form class="filter-form" method="GET" action="catalogo-itens.php">
            <div class="form-control">
                <label for="search-bar">Buscar por Palavra-chave:</label>
                <input type="text" id="search-bar" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Ex:Calça jeans, Guarda roupas, Alimentos" class="search-input">
            </div>

            <div class="form-control">
                <label for="categoria-filter">Categoria:</label>
                <select id="categoria-filter" name="categoria">
                    <option value="">Todas as Categorias</option>
                    <option value="roupas" <?= $categoria=='roupas'?'selected':'' ?>>Roupas</option>
                    <option value="calcados" <?= $categoria=='roupas'?'selected':'' ?>>Calçados</option>
                    <option value="alimentos" <?= $categoria=='alimentos'?'selected':'' ?>>Alimentos Não Perecíveis</option>
                    <option value="moveis" <?= $categoria=='moveis'?'selected':'' ?>>Móveis</option>
                    <option value="eletrodomesticos" <?= $categoria=='moveis'?'selected':'' ?>>Eletrodomésticos</option>
                </select>
            </div>

            <div class="form-control">
                <label for="estado-filter">Estado de Conservação:</label>
                <select id="estado-filter" name="estado">
                    <option value="">Qualquer Estado</option>
                    <option value="novo" <?= $estado=='novo'?'selected':'' ?>>Novo/Lacrado</option>
                    <option value="usado" <?= $estado=='usado'?'selected':'' ?>>Usado</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Aplicar Filtros</button>
            <button type="reset" class="btn-secondary" onclick="window.location='catalogo-itens.php'">Limpar Filtros</button>
        </form>
    </aside>

    <section class="results-grid">
        <h2>Itens Encontrados</h2>

        <?php if ($itens_encontrados): ?>
            <?php while ($item = $res_itens->fetch_assoc()): ?>
                <?php $imagem = !empty($item['imagem_url']) ? htmlspecialchars($item['imagem_url']) : 'assets/placeholder.jpg';
                ?>
                <div class="item-card">
                    <img src="<?= htmlspecialchars($imagem) ?>" alt="Foto de <?= htmlspecialchars($item['nome_item']) ?>" class="item-image">
                    <div class="item-info">
                        <h3><?= htmlspecialchars($item['nome_item']) ?></h3>
                        <p class="category"><?= htmlspecialchars($item['categoria']) ?></p>
                        <p class="status-badge status-<?= htmlspecialchars($item['estado']) ?>">
                            <?= ucfirst($item['estado']) ?>
                        </p>
                        <p class="location">Doador: <?= htmlspecialchars($item['nome_doador']) ?></p>
                        <a href="detalhe-item.php?id=<?= $item['id'] ?>" class="btn-details">Ver Detalhes e Solicitar</a>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-state">Não encontramos itens com esses filtros. Tente simplificar sua busca!</p>
        <?php endif; ?>
    </section>

</main>

<footer class="footer">
    <p>&copy; 2025 Marketplace Solidário.</p>
</footer>

</body>
</html>
