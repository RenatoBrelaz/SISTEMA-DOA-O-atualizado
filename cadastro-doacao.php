<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'doador') {
    header("Location: login.php");
    exit;
}

$id_doador = $_SESSION['usuario_id'];
$mensagem = "";

$sql_endereco = "SELECT endereco FROM usuarios WHERE id = ?";
$stmt_end = $conexao->prepare($sql_endereco);

if (!$stmt_end) {
    die("Erro ao preparar SQL de endereço: " . $conexao->error);
}

$stmt_end->bind_param("i", $id_doador);
$stmt_end->execute();
$result_end = $stmt_end->get_result();
$endereco_doador = $result_end->fetch_assoc();
$stmt_end->close();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item = trim($_POST['nomeItem'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $quantidade = (int)($_POST['quantidade'] ?? 1);
    $estado = ($_POST['estadoConservacao'] === 'novo') ? 'novo' : 'usado';

    // Endereço de coleta
    $endereco_coleta = $_POST['enderecoColeta'] ?? 'principal';
    $cep_novo = $_POST['cepNovo'] ?? null;

    // Upload da imagem
    $imagem_url = null;

    if (!empty($_FILES['fotoItem']['name'])) {
        $pasta_upload = __DIR__ . "/uploads/itens/";
        if (!is_dir($pasta_upload)) mkdir($pasta_upload, 0777, true);
    
        $ext = strtolower(pathinfo($_FILES['fotoItem']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = uniqid("item_", true) . "." . $ext;
        $destino = $pasta_upload . $nome_arquivo;
    
        $formatos_validos = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $formatos_validos)) {
            if (move_uploaded_file($_FILES['fotoItem']['tmp_name'], $destino)) {
                $imagem_url = "uploads/itens/" . $nome_arquivo;
            } else {
                $mensagem = "⚠️ Erro ao mover o arquivo. Verifique permissões da pasta.";
            }
        } else {
            $mensagem = "❌ Formato de imagem inválido (use JPG, PNG ou WEBP).";
        }
    }
    

    if (empty($mensagem)) {
        $id_ong= null;


        $sql = "INSERT INTO itens (nome_item, descricao, categoria, estado, quantidade, id_doador, id_ong, imagem_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?,?)";
        $stmt = $conexao->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssiiis", $nome_item, $descricao, $categoria, $estado, $quantidade, $id_doador, $id_ong, $imagem_url);
            if ($stmt->execute()) {
                $mensagem = "✅ Item cadastrado com sucesso!";
            } else {
                $mensagem = "❌ Erro ao cadastrar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensagem = "Erro ao preparar SQL: " . $conexao->error;
        }
    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Nova Doação | DONATION</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="header">
    <img src="img/donation (2) (1).png" alt="Logo Donation" class="logo" >
    <nav class="main-nav">
        <a href="dashboard.php">Home</a>
        <a href="logout.php">Sair</a>
    </nav>
    </nav>
</header>

<main class="form-container">
    <h1>Cadastrar Novo Item para Doação</h1>
    <p>Preencha os detalhes do item que deseja doar.</p>

    <?php if (!empty($mensagem)): ?>
        <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="doacao-form">

        <fieldset class="form-group">
            <legend>Informações do Item</legend>

            <div class="form-control">
                <label for="nome-item">Nome do Item *</label>
                <input type="text" id="nome-item" name="nomeItem" required>
            </div>

            <div class="form-control">
                <label for="categoria">Categoria *</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione</option>
                    <option value="roupas">Roupas</option>
                    <option value="calcados">Calçados</option>
                    <option value="alimentos">Alimentos Não Perecíveis</option>
                    <option value="moveis">Móveis</option>
                    <option value="eletrodomesticos">Eletrodomésticos</option>
                </select>
            </div>

            <div class="form-control">
                <label for="quantidade">Quantidade *</label>
                <input type="number" id="quantidade" name="quantidade"  required>
            </div>
        </fieldset>

        <fieldset class="form-group">
            <legend>Concervação e Descrição</legend>
            <div class="radio-group">
                <input type="radio" id="estado-novo" name="estadoConservacao" value="novo" required>
                <label for="estado-novo">Novo/Lacrado</label>

                <input type="radio" id="estado-usado" name="estadoConservacao" value="usado">
                <label for="estado-usado">Usado</label>
            </div>
<br>
            <div class="form-control">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4"></textarea>
            </div>

            <div class="form-control">
                <label for="fotoItem">Foto do Item *</label>
                <input type="file" id="fotoItem" name="fotoItem" accept="image/*" required>
            </div>
        </fieldset>


        <fieldset class="form-group">
    <legend>Endereço de Coleta</legend>

    <div class="form-control">
        <label>Selecione o endereço:</label>
        <div class="radio-group">
            <input type="radio" id="endereco-cadastrado" name="enderecoColeta" value="principal" checked>
            <label for="endereco-cadastrado">Usar endereço cadastrado</label>
            
            <input type="radio" id="endereco-novo" name="enderecoColeta" value="novo">
            <label for="endereco-novo">Informar novo endereço</label>
        </div>
    </div>

    <!-- Endereço do doador -->
    <div id="endereco-principal" class="endereco-box">
    <p><strong>Endereço cadastrado:</strong></p>
    <p><?= htmlspecialchars($endereco_doador['endereco'] ?? 'Endereço não informado') ?></p>
</div>


    <!-- Novo endereço -->
    <div id="novo-endereco-fields" class="form-grid" style="display:none;">
        <div class="form-control">
            <label for="cep">CEP *</label>
            <input type="text" id="cep" name="cepNovo" placeholder="00000-000" maxlength="9" required>
        </div>

        <div class="form-control">
            <label for="rua">Rua *</label>
            <input type="text" id="rua" name="ruaNovo">
        </div>

        <div class="form-control">
            <label for="numero">Número *</label>
            <input type="text" id="numero" name="numeroNovo">
        </div>

        <div class="form-control">
            <label for="bairro">Bairro *</label>
            <input type="text" id="bairro" name="bairroNovo">
        </div>

        <div class="form-control">
            <label for="cidade">Cidade *</label>
            <input type="text" id="cidade" name="cidadeNovo">
        </div>

        <div class="form-control">
            <label for="estado">Estado *</label>
            <input type="text" id="estado" name="estadoNovo" maxlength="2">
        </div>
    </div>
</fieldset>


        <div class="form-actions">
            <button type="submit" class="submit-button primary-button">✅ Confirmar Doação</button>
        </div>
    </form>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
</footer>

<script src="cadastro-doacao.js"></script>

</body>
</html>
