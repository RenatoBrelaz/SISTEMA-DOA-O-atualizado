<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login (Beneficiário)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'beneficiario') {
    header("Location: login.php");
    exit;
}

$id_beneficiario = $_SESSION['usuario_id'];
$id_item = $_GET['id'] ?? null;

// 2️⃣ Verifica se o ID do item foi passado
if (!$id_item || !is_numeric($id_item)) {
    echo "<script>alert('ID do item inválido ou ausente.'); window.location='catalogo-itens.php';</script>";
    exit;
}

// 3️⃣ Busca os detalhes do item e o nome do doador
$sql = "SELECT i.*, u.nome AS nome_doador 
        FROM itens i
        INNER JOIN usuarios u ON i.id_doador = u.id
        WHERE i.id = ? AND i.id_ong IS NULL"; // Busca item específico e verifica se está disponível (id_ong é NULL)

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_item);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>Item não encontrado ou indisponível.</h1>";
    echo "<p>Este item pode já ter sido solicitado ou excluído.</p>";
    echo '<p><a href="catalogo-itens.php">Voltar ao Catálogo</a></p>';
    exit;
}

$item = $result->fetch_assoc();
$stmt->close();

// Função auxiliar para formatar a categoria
function formatarCategoria($cat) {
    return ucfirst(str_replace('eletrodomesticos', 'Eletrodomésticos', $cat));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['nome_item']) ?> | DONATION</title>
    <link rel="stylesheet" href="styles.css">
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

<main class="item-details-container">
    <a href="catalogo-itens.php" class="back-link">← Voltar para o Catálogo</a>
    
    <section class="item-header">
        <h1><?= htmlspecialchars($item['nome_item']) ?></h1>
        <p class="category"><?= formatarCategoria($item['categoria']) ?></p>
    </section>
    
    <section class="item-content-grid">
        
        <div class="item-gallery">
            <?php 
            $imagem_url = !empty($item['imagem_url']) ? htmlspecialchars($item['imagem_url']) : 'assets/placeholder.jpg';
            ?>
            <img src="<?= $imagem_url ?>" alt="<?= htmlspecialchars($item['nome_item']) ?>" class="main-image">
            
            <div class="thumbnails">
                <img src="assets/placeholder-thumb.jpg" alt="Miniatura 1">
                <img src="assets/placeholder-thumb.jpg" alt="Miniatura 2">
            </div>
        </div>
        
        <div class="item-info-solicitar">
            <h2>Informações Detalhadas</h2>
            
            <div class="info-box">
                <?php $estado_classe = ($item['estado'] === 'novo') ? 'status-novo' : 'status-usado'; ?>
                <p><strong>Estado de Conservação:</strong> <span class="status-badge <?= $estado_classe ?>"><?= htmlspecialchars(ucfirst($item['estado'])) ?></span></p>
                <p><strong>Quantidade:</strong> <?= htmlspecialchars($item['quantidade']) ?> unidade(s)</p>
                <p><strong>Doador:</strong> <?= htmlspecialchars($item['nome_doador']) ?></p>
                <p><strong>Observações do Doador:</strong> "<?= htmlspecialchars($item['descricao'] ?? 'Nenhuma observação.') ?>"</p>
                <p><strong>Localização Aproximada:</strong> Perto do doador (A ser revelado pela ONG).</p>
            </div>
            
            <hr>
            
            <h2>Interesse em Receber?</h2>
            
            <form action="solicitar-doacao.php" method="POST" class="solicitacao-form">
                
                <p class="solicitacao-text">Ao clicar em "Solicitar Doação", seus dados de contato cadastrados serão enviados à ONG responsável para que ela possa agendar a entrega (RF05).</p>
                
                <input type="hidden" name="id_item" value="<?= htmlspecialchars($item['id']) ?>">
                <input type="hidden" name="id_beneficiario" value="<?= htmlspecialchars($id_beneficiario) ?>">
                
                <div class="form-control">
                    <label for="mensagem-opcional">Mensagem Opcional para a ONG:</label>
                    <textarea id="mensagem-opcional" name="mensagem" rows="3" placeholder="Ex: Sou da família tal e o item seria muito útil..."></textarea>
                </div>
                
                <button type="submit" class="btn-primary large-button">
                    Solicitar Doação (RF05)
                </button>
            </form>
            
            <div class="alert-info" style="margin-top: 15px;">
                <p>⚠️ **Atenção:** A solicitação está sujeita à aprovação da ONG, que avaliará a necessidade e a logística.</p>
            </div>
        </div>
        
    </section>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
</footer>
</body>
</html>