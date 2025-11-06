<?php
// dashboard.php (versão robusta com checagens de erro)
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_perfil'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['usuario_perfil'] !== 'doador') {
    echo "Acesso negado! Esta página é exclusiva para doadores.";
    exit;
}

$id_doador = (int) $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// Função auxiliar para preparar statement com checagem de erro
function prepare_or_die($conexao, $sql, $context = '') {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        // Mostra mensagem útil para depuração
        $msg = "Erro ao preparar SQL" . ($context ? " ({$context})" : "") . ": " . $conexao->error;
        // Você pode trocar die() por trigger_error() se preferir logar em vez de morrer
        die($msg);
    }
    return $stmt;
}

/* ---------------------------------------------------------
   1) Total de itens cadastrados (campo id_ong em itens)
   --------------------------------------------------------- */
$sql_itens = "SELECT COUNT(*) AS total_itens FROM itens WHERE id_ong = ?";
$stmt_itens = prepare_or_die($conexao, $sql_itens, 'total_itens');
$stmt_itens->bind_param("i", $id_doador);
$stmt_itens->execute();
$res_itens = $stmt_itens->get_result();
$total_itens = ($res_itens && $row = $res_itens->fetch_assoc()) ? (int)$row['total_itens'] : 0;
$stmt_itens->close();

/* ---------------------------------------------------------
   2) Itens com solicitações pendentes (status = 'pendente')
   --------------------------------------------------------- */
$sql_pendentes = "SELECT COUNT(*) AS pendentes
                  FROM solicitacoes s
                  JOIN itens i ON s.id_item = i.id
                  WHERE i.id_ong = ? AND s.status = 'pendente'";
$stmt_pendentes = prepare_or_die($conexao, $sql_pendentes, 'pendentes_solicitacoes');
$stmt_pendentes->bind_param("i", $id_doador);
$stmt_pendentes->execute();
$res_pend = $stmt_pendentes->get_result();
$pendentes = ($res_pend && $row = $res_pend->fetch_assoc()) ? (int)$row['pendentes'] : 0;
$stmt_pendentes->close();

/* ---------------------------------------------------------
   3) Avaliação média dos itens do doador
   (assume tabela avaliacoes com coluna 'avaliacao' e ligação via solicitacoes)
   --------------------------------------------------------- */
   $sql_media = "SELECT ROUND(AVG(a.nota), 1) AS media
   FROM avaliacoes a
   JOIN solicitacoes s ON a.id_solicitacao = s.id
   JOIN itens i ON s.id_item = i.id
   WHERE i.id_ong = ?";
$stmt_media = prepare_or_die($conexao, $sql_media, 'media_avaliacao');
$stmt_media->bind_param("i", $id_doador);
$stmt_media->execute();
$res_media = $stmt_media->get_result();
$row = $res_media ? $res_media->fetch_assoc() : null;
$media_avaliacao = ($row && $row['media'] !== null) ? $row['media'] : null;
$stmt_media->close();


/* ---------------------------------------------------------
   4) Últimas 3 solicitações relacionadas aos itens do doador
   --------------------------------------------------------- */
$sql_notificacoes = "SELECT s.id, s.status, s.data_solicitacao, i.nome_item, u.nome AS nome_beneficiario
                     FROM solicitacoes s
                     JOIN itens i ON s.id_item = i.id
                     JOIN usuarios u ON s.id_beneficiario = u.id
                     WHERE i.id_ong = ?
                     ORDER BY s.data_solicitacao DESC
                     LIMIT 3";
$stmt_notificacoes = prepare_or_die($conexao, $sql_notificacoes, 'ultimas_solicitacoes');
$stmt_notificacoes->bind_param("i", $id_doador);
$stmt_notificacoes->execute();
$notificacoes_res = $stmt_notificacoes->get_result();
$notificacoes = $notificacoes_res ? $notificacoes_res->fetch_all(MYSQLI_ASSOC) : [];
$stmt_notificacoes->close();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Doador | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo">
    <nav class="main-nav">
        <a href="dashboard.php">Home</a>
        <a href="cadastro-doacao.php">Cadastrar Novo Item</a>
        <a href="minhas-doacoes.php">Minhas Doações</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main class="dashboard-container">
    <h1>👋 Bem-vindo, <?= htmlspecialchars($nome_usuario) ?></h1>

    <section class="stats-section">
        <h2>📊 Seu Impacto Atual</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?= $total_itens ?></span>
                <span class="stat-label">Itens Doados no Total</span>
            </div>

            <div class="stat-card alert">
                <span class="stat-value"><?= $pendentes ?></span>
                <span class="stat-label">Solicitações Pendentes</span>
            </div>

            <div class="stat-card">
                <span class="stat-value"><?= $media_avaliacao !== null ? $media_avaliacao : '—' ?></span>
                <span class="stat-label">Avaliação Média</span>
            </div>
        </div>
    </section>

    <section class="actions-section">
        <h2>🚀 O que você quer fazer hoje?</h2>
        <a href="cadastro-doacao.php" class="action-button primary">
            Cadastrar Nova Doação
            <small>Comece a ajudar em menos de 1 minuto!</small>
        </a>
        <a href="minhas-doacoes.php" class="action-button secondary">
            Gerenciar Minhas Doações
            <small>Ver status e histórico</small>
        </a>
    </section>

    <section class="status-section">
        <h2>🔔 Últimos Status e Notificações</h2>
        <ul class="notification-list">
            <?php if (count($notificacoes) > 0): ?>
                <?php foreach ($notificacoes as $n): ?>
                    <li class="notification-item status-<?= htmlspecialchars($n['status']) ?>">
                        <strong><?= htmlspecialchars($n['nome_item']) ?></strong> foi 
                        <em><?= htmlspecialchars($n['status']) ?></em> por 
                        <?= htmlspecialchars($n['nome_beneficiario']) ?> em 
                        <?= date('d/m/Y H:i', strtotime($n['data_solicitacao'])) ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Nenhuma notificação recente.</li>
            <?php endif; ?>
        </ul>
        <a href="minhas-doacoes.php" class="link-more">Ver todas as solicitações →</a>
    </section>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Seu impacto faz a diferença.</p>
</footer>

</body>
</html>
