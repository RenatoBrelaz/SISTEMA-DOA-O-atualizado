<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'beneficiario') {
    header("Location: login.php");
    exit();
}

$id_beneficiario = $_SESSION['usuario_id']; // aqui está a correção

$sql = "SELECT s.*, 
               i.nome_item AS nome_item, 
               d.nome AS nome_doador,
               d.telefone AS telefone_doador, 
               u.nome AS nome_ong, 
               u.telefone AS telefone_ong
        FROM solicitacoes s
        JOIN itens i ON s.id_item = i.id
        JOIN usuarios d ON i.id_doador = d.id                 -- Para ter o Doador
        LEFT JOIN usuarios u ON i.id_ong = u.id               -- LEFT JOIN para ter a ONG (pode ser NULL)
        WHERE s.id_beneficiario = ?
        ORDER BY s.data_solicitacao DESC";
$stmt = $conexao->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar SQL: " . $conexao->error);
}

$stmt->bind_param("i", $id_beneficiario);

$stmt->execute();
$result = $stmt->get_result();

$ativas = [];
$historico = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'finalizado') {
        $historico[] = $row;
    } else {
        $ativas[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Itens | Marketplace Solidário</title>
    <link rel="stylesheet" href="styles.css">
    </head>
<body>

    <header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo" >
        <nav class="main-nav">
        <a href="dashboard-beneficiario.php">Home</a>
        <a href="gerenciar-solicitacoes.php">Minhas Solicitações</a>
        <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="management-container">
        <h1>Minhas Solicitações de Itens</h1>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelada'): ?>
    <div class="alert success">✅ Solicitação cancelada com sucesso.</div>
<?php endif; ?>

        <p>Acompanhe o status e realize a confirmação de recebimento dos itens solicitados.</p>
    
        <div class="tabs-nav">
            <button class="tab-button active">Solicitações Ativas</button>
            <button class="tab-button">Histórico Finalizado</button>
        </div>
    
        <section id="ativas" class="tab-content active">
            
<?php if (count($ativas) > 0): ?>
    <?php foreach ($ativas as $sol): ?>
        <div class="solicitacao-card <?= htmlspecialchars($sol['status']) ?>">
            <div class="card-header">
                <h3>Solicitação #<?= $sol['id'] ?> - <?= htmlspecialchars($sol['nome_item']) ?></h3>
                <span class="status-badge status-<?= $sol['status'] ?>">
                    <?= ucfirst($sol['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <p><strong>Data da Solicitação:</strong> <?= date('d/M/Y', strtotime($sol['data_solicitacao'])) ?></p>
                <p><strong>Doador Responsável:</strong> <?= htmlspecialchars($sol['nome_doador']) ?> (<?= htmlspecialchars($sol['telefone_doador']) ?>)</p>

                <?php if ($sol['status'] == 'pendente'): ?>
                    <p class="log-message">Aguarde o contato da ONG para agendar a entrega.</p>
                    <div class="card-actions">
                        <button class="btn-primary cancelar-recebimento">Ver Detalhes</button>
                        <a href="cancelar-solicitacao.php?id=<?= $sol['id'] ?>"class="btn-cancel secondary-button"onclick="return confirm('Tem certeza que deseja cancelar esta solicitação?');">
                            Cancelar Solicitação</a>
                    </div>
                <?php elseif ($sol['status'] == 'aprovado'): ?>
                    <p><strong>Status:</strong> Entrega agendada.</p>
                    <div class="delivery-confirmation-section">
                        <button class="btn-primary confirmar-recebimento" data-id="<?= $sol['id'] ?>">
                            Confirmar Recebimento
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhuma solicitação ativa.</p>
<?php endif; ?>

            
     </section>
    
     <section id="historico" class="tab-content" style="display:none;">
    <?php if (count($historico) > 0): ?>
        <?php foreach ($historico as $sol): ?>
            <div class="solicitacao-card finalizado">
                <div class="card-header">
                    <h3>Solicitação #<?= $sol['id'] ?> - <?= htmlspecialchars($sol['nome_item']) ?></h3>
                    <span class="status-badge status-finalizado">Entrega Concluída</span>
                </div>
                <div class="card-body">
                    <p><strong>Recebido em:</strong>
                        <?= $sol['data_entrega'] ? date('d/M/Y', strtotime($sol['data_entrega'])) : 'Data não informada' ?>
                    </p>

                    <?php if ($sol['avaliacao']): ?>
                        <div class="avaliacao-info">
                            <h4>Sua Avaliação:</h4>
                            <p class="rating">
                                <?= str_repeat('⭐️', (int)$sol['avaliacao']) ?> (<?= $sol['avaliacao'] ?>/5)
                            </p>
                            <p class="comment">
                                "<?= htmlspecialchars($sol['comentario_avaliacao']) ?>"
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="avaliacao-info">
                            <h4>Avaliação do Item:</h4>
                            <button class="btn-feedback primary-button" data-solicitacao-id="<?= $sol['id'] ?>">
                                Avaliar Qualidade do Item
                            </button>
                            <small>Sua opinião é importante!</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-actions">
                    <button class="btn-details">Ver Detalhes</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Você ainda não possui solicitações finalizadas.</p>
    <?php endif; ?>
</section>

    </main>
    
    <footer class="footer">
        <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>