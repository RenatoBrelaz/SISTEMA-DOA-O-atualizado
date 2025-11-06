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
        -- Relaciona o item com o USUÁRIO QUE O CADASTROU (o doador)
        INNER JOIN usuarios u ON i.id_doador = u.id
        -- Filtra itens disponíveis (id_ong ainda não foi definido)
        WHERE i.id_ong IS NULL";

// 🔍 Aplica filtros dinamicamente
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

// 4️⃣ Prepara e executa
$stmt = $conexao->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$res_itens = $stmt->get_result();
$itens_encontrados = ($res_itens && $res_itens->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Itens | DONATION</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <header class="header">
        <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo">
        <nav class="main-nav">
            <a href="dashboard-beneficiario.php">Home</a>
            <a href="catalogo-itens.php">Buscar Itens</a>
            <a href="gerenciar-solicitacoes.php">Minhas Solicitações</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="dashboard-container">
        <h1>Itens Disponíveis</h1>
    
        <div class="catalogo-container">
            
            <!-- 🧭 SIDEBAR DE FILTROS -->
            <aside class="sidebar-filters">
                <h2><i class="fas fa-filter"></i> Filtrar Itens</h2>

                <form action="catalogo-itens.php" method="GET" class="filter-form">
                    <div class="form-control">
                        <label for="busca">Pesquisar por Nome/Descrição</label>
                        <input type="search" id="busca" name="busca" 
                               placeholder="Ex: Geladeira, roupas..." 
                               value="<?= htmlspecialchars($busca) ?>">
                    </div>

                    <div class="form-control">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria">
                            <option value="">Todas</option>
                            <option value="roupas" <?= $categoria == 'roupas' ? 'selected' : '' ?>>Roupas</option>
                            <option value="calcados" <?= $categoria == 'calcados' ? 'selected' : '' ?>>Calçados</option>
                            <option value="alimentos" <?= $categoria == 'alimentos' ? 'selected' : '' ?>>Alimentos</option>
                            <option value="moveis" <?= $categoria == 'moveis' ? 'selected' : '' ?>>Móveis</option>
                            <option value="eletrodomesticos" <?= $categoria == 'eletrodomesticos' ? 'selected' : '' ?>>Eletrodomésticos</option>
                            <option value="outros" <?= $categoria == 'outros' ? 'selected' : '' ?>>Outros</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="novo" <?= $estado == 'novo' ? 'selected' : '' ?>>Novo</option>
                            <option value="usado" <?= $estado == 'usado' ? 'selected' : '' ?>>Usado</option>
                        </select>
                    </div>

                    <button type="submit" class="large-button btn-primary">Filtrar</button>
                    <a href="catalogo-itens.php" class="large-button btn-secondary" style="text-align:center;">Limpar Filtros</a>
                </form>
            </aside>

            <!-- 🧱 ÁREA DE RESULTADOS -->
            <section class="results-area">
                <?php if ($itens_encontrados): ?>
                    <div class="results-grid">
                        <?php while ($item = $res_itens->fetch_assoc()): ?>
                            <?php
                                $imagem = !empty($item['imagem_url']) ? htmlspecialchars($item['imagem_url']) : 'assets/placeholder.jpg';
                            ?>
                            <div class="item-card">
                                <div class="item-image" style="background-image: url('<?= $imagem ?>'); background-size: cover; background-position: center;"></div>
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nome_item']) ?></h3>
                                    <p><?= htmlspecialchars($item['descricao'] ?? 'Sem descrição') ?></p>
                                    <p><strong>Categoria:</strong> <?= htmlspecialchars($item['categoria']) ?></p>
                                    <p><strong>Estado:</strong> <?= htmlspecialchars($item['estado']) ?></p>
                                    <p><strong>Quantidade:</strong> <?= htmlspecialchars($item['quantidade']) ?></p>
                                    <p><strong>Doador:</strong> <?= htmlspecialchars($item['nome_doador']) ?></p>
                                    <p><small>Adicionado em: <?= date('d/m/Y', strtotime($item['data_cadastro'])) ?></small></p>
                                    
                                    <a href="detalhe-item.php?id=<?= $item['id'] ?>" class="btn-details">
                                        Ver Detalhes →
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem; display:block;"></i>
                        <p>Nenhum item encontrado com os filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 DONATION. Seu impacto faz a diferença.</p>
    </footer>

</body>
</html>
