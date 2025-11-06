<?php
session_start();
include('conexao.php');

// 1️⃣ Verifica login e perfil
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'doador') {
    header("Location: login.php");
    exit;
}

$id_doador = $_SESSION['usuario_id'];

// 2️⃣ Verifica se recebeu o ID do item
if (!isset($_GET['id'])) {
    echo "ID do item não informado.";
    exit;
}

$id_item = (int) $_GET['id'];

// 3️⃣ Busca o item no banco
$sql = "SELECT * FROM itens WHERE id = ? AND id_doador = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $id_item, $id_doador); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Item não encontrado ou você não tem permissão para editá-lo.";
    exit;
}

$item = $result->fetch_assoc();
$stmt->close();

// 4️⃣ Atualiza se formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_item'];
    $categoria = $_POST['categoria'];
    $quantidade = (int) $_POST['quantidade'];
    $estado = $_POST['estado'];
    $descricao = $_POST['descricao'];

    // ⚙️ Upload da imagem (opcional)
    $imagem = $item['imagem']; // mantém a anterior se não mudar
    if (!empty($_FILES['imagem']['name'])) {
        $pastaDestino = "uploads/itens/";
        $nomeArquivo = uniqid() . "-" . basename($_FILES['imagem']['name']);
        $caminhoCompleto = $pastaDestino . $nomeArquivo;

        // Cria pasta se não existir
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $imagem = $nomeArquivo;
        } else {
            echo "<script>alert('Erro ao enviar a imagem.');</script>";
        }
    }

    // 5️⃣ Atualiza no banco
    $sql_update = "UPDATE itens 
                   SET nome_item = ?, categoria = ?, quantidade = ?, estado = ?, descricao = ?, imagem = ? 
                   WHERE id = ? AND id_ong = ?";
    $stmt_up = $conexao->prepare($sql_update);
    $stmt_up->bind_param("ssisssii", $nome, $categoria, $quantidade, $estado, $descricao, $imagem, $id_item, $id_doador);

    if ($stmt_up->execute()) {
        echo "<script>alert('Item atualizado com sucesso!'); window.location='minhas-doacoes.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar o item.');</script>";
    }

    $stmt_up->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Doação | DONATION</title>
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

<main class="form-container">
    <h1>Editar Item Doado</h1>
    <p>Atualize as informações do seu item, caso algo tenha mudado.</p>

    <form method="POST" enctype="multipart/form-data" class="doacao-form">
        <div class="form-control">
            <label for="nome_item">Nome do Item *</label>
            <input type="text" name="nome_item" id="nome_item" required value="<?= htmlspecialchars($item['nome_item']) ?>">
        </div>

        <div class="form-control">
            <label for="categoria">Categoria *</label>
            <select name="categoria" id="categoria" required>
                <?php
                $categorias = ['roupas', 'calcados', 'alimentos', 'moveis', 'eletrodomesticos', 'outros'];
                foreach ($categorias as $cat) {
                    $sel = $item['categoria'] === $cat ? 'selected' : '';
                    echo "<option value='$cat' $sel>" . ucfirst($cat) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-control">
            <label for="quantidade">Quantidade *</label>
            <input type="number" name="quantidade" id="quantidade" min="1" required value="<?= htmlspecialchars($item['quantidade']) ?>">
        </div>

        <div class="form-control">
            <label for="estado">Estado *</label>
            <select name="estado" id="estado" required>
                <option value="novo" <?= $item['estado'] === 'novo' ? 'selected' : '' ?>>Novo</option>
                <option value="usado" <?= $item['estado'] === 'usado' ? 'selected' : '' ?>>Usado</option>
            </select>
        </div>

        <div class="form-control">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="4"><?= htmlspecialchars($item['descricao']) ?></textarea>
        </div>

        <div class="form-control">
            <label for="imagem">Imagem do Item</label>
            <?php if ($item['imagem']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="uploads/itens/<?= htmlspecialchars($item['imagem']) ?>" width="120" style="border-radius:8px;">
                </div>
            <?php endif; ?>
            <input type="file" name="imagem" id="imagem" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-button primary-button">💾 Salvar Alterações</button>
            <a href="minhas-doacoes.php" class="submit-button">↩ Voltar</a>
        </div>
    </form>
</main>

<footer class="footer">
    <p>&copy; 2025 DONATION. Todos os direitos reservados.</p>
</footer>

</body>
</html>