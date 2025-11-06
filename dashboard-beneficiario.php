<?php
session_start();
include('conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_perfil'])) {
    header("Location: login.php");
    exit;
}

// Verifica se o perfil é 'beneficiario'
if ($_SESSION['usuario_perfil'] != 'beneficiario') {
    echo "Acesso negado! Esta página é exclusiva para beneficiários.";
    exit;
}

// =====================================
// ESTATÍSTICAS DO BENEFICIÁRIO
// =====================================

// Se a conexão estiver com outro nome ($conexao), normaliza:
if (!isset($conn) && isset($conexao)) {
    $conn = $conexao;
}

// Inicializa variáveis para evitar warnings
$total_recebidos = 0;
$total_pendentes = 0;
$total_aguardando = 0;

// ID do beneficiário logado
$id_beneficiario = (int) $_SESSION['usuario_id'];

// Executa consultas se conexão existir
if (isset($conn) && $conn) {

    // Total de itens recebidos (status = 'finalizado')
    $sql_recebidos = "SELECT COUNT(*) AS total_recebidos FROM solicitacoes WHERE id_beneficiario = $id_beneficiario AND status = 'finalizado'";
    $res_recebidos = $conn->query($sql_recebidos);
    if ($res_recebidos) {
        $row = $res_recebidos->fetch_assoc();
        $total_recebidos = $row ? $row['total_recebidos'] : 0;
    }

    // Solicitações pendentes (status = 'pendente')
    $sql_pendentes = "SELECT COUNT(*) AS total_pendentes FROM solicitacoes WHERE id_beneficiario = $id_beneficiario AND status = 'pendente'";
    $res_pendentes = $conn->query($sql_pendentes);
    if ($res_pendentes) {
        $row = $res_pendentes->fetch_assoc();
        $total_pendentes = $row ? $row['total_pendentes'] : 0;
    }

    // Itens aguardando avaliação (status = 'aguardando_avaliacao')
    $sql_aguardando = "SELECT COUNT(*) AS total_aguardando FROM solicitacoes WHERE id_beneficiario = $id_beneficiario AND status = 'aguardando_avaliacao'";
    $res_aguardando = $conn->query($sql_aguardando);
    if ($res_aguardando) {
        $row = $res_aguardando->fetch_assoc();
        $total_aguardando = $row ? $row['total_aguardando'] : 0;
    }
}

// =====================================
// ÚLTIMAS SOLICITAÇÕES DO BENEFICIÁRIO
// =====================================

$ultimas_solicitacoes = [];

if (isset($conn) && $conn) {
    $sql_status = "
        SELECT nome_item, status, data_solicitacao 
        FROM solicitacoes
        WHERE id_beneficiario = $id_beneficiario
        ORDER BY data_solicitacao DESC
        LIMIT 5
    ";
    $res_status = $conn->query($sql_status);

    if ($res_status && $res_status->num_rows > 0) {
        $ultimas_solicitacoes = $res_status->fetch_all(MYSQLI_ASSOC);
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Beneficiário | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo" >
        <nav class="main-nav">
        <a href="dashboard-beneficiario.php" >Home</a>
        <a href="catalogo-itens.php">Buscar Itens</a>
        <a href="gerenciar-solicitacoes.php">Minhas Solicitações</a>
        <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="dashboard-container">
        <h1>
            <?php 
                $nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : "Usuário";
                echo "Olá, " . htmlspecialchars($nome_usuario) . "!";
            ?>
        </h1>

<section class="stats-section">
    <h2>✨ Minhas Estatísticas</h2>
    <div class="stats-grid">
        
        <div class="stat-card">
            <span class="stat-value"><?= $total_recebidos ?></span>
            <span class="stat-label">Itens Recebidos no Total</span>
        </div>

        <div class="stat-card alert">
            <span class="stat-value"><?= $total_pendentes ?></span>
            <span class="stat-label">Solicitações Pendentes</span>
        </div>

        <div class="stat-card">
            <span class="stat-value"><?= $total_aguardando ?></span>
            <span class="stat-label">Itens Aguardando Avaliação</span>
        </div>

    </div>
</section>


        <section class="actions-section">
            <h2>🔎 Procurando um Item?</h2>
            
            <a href="catalogo-itens.php" class="action-button primary" style="max-width: 500px;">
                Acessar Catálogo e Buscar Doações
                <small>Encontre itens por categoria, estado e localização.</small>
            </a>
            
        </section>
        
        <section class="status-section">
    <h2>🔔 Últimos Status de Solicitação</h2>

    <ul class="notification-list">
        <?php if (!empty($ultimas_solicitacoes)): ?>
            <?php foreach ($ultimas_solicitacoes as $sol): ?>
                <?php
                    // Define classe CSS e mensagem conforme o status
                    $classe = '';
                    $mensagem = '';

                    switch ($sol['status']) {
                        case 'pendente':
                            $classe = 'status-solicitado';
                            $mensagem = "está pendente de aprovação pela ONG.";
                            break;
                        case 'aprovado':
                            $classe = 'status-aprovado';
                            $mensagem = "foi aprovado! A ONG entrará em contato para combinar a entrega.";
                            break;
                        case 'finalizado':
                            $classe = 'status-feedback';
                            $mensagem = "foi entregue. Por favor, confirme o recebimento e avalie.";
                            break;
                        default:
                            $classe = 'status-solicitado';
                            $mensagem = "está em andamento.";
                    }

                    $data = !empty($sol['data_solicitacao']) 
                        ? date('d/m/Y', strtotime($sol['data_solicitacao'])) 
                        : 'data não informada';
                ?>
                <li class="notification-item <?= $classe ?>">
                    Sua solicitação do item <strong><?= htmlspecialchars($sol['nome_item']) ?></strong> <?= $mensagem ?>
                    <time>Solicitado em <?= $data ?></time>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="notification-item status-solicitado">
                Você ainda não possui solicitações recentes.
            </li>
        <?php endif; ?>
    </ul>

    <a href="gerenciar-solicitacoes.html" class="link-more">
        Ver todas as solicitações e acompanhar o processo →
    </a>
</section>

        
    </main>

    <footer class="footer">
        <p>&copy; 2025 Marketplace Solidário. Seu impacto faz a diferença.</p>
    </footer>
    
</body>
</html>