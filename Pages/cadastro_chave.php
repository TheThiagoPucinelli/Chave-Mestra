<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$successMsg = "";

// CPF do administrador
$cpf_adm = $_SESSION['cpf'] ?? '';

// Verifica se é administrador
$admCheck = $conexao->prepare("SELECT * FROM usuario_adm WHERE cpf = ?");
$admCheck->bind_param("s", $cpf_adm);
$admCheck->execute();
$resAdm = $admCheck->get_result();

if ($resAdm->num_rows === 0) {
    echo "<p style='color:red; text-align:center;'>Você não tem permissão para cadastrar chaves.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $numero_identificacao = trim($_POST['numero_identificacao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $quantidade = trim($_POST['quantidade'] ?? '');
    $status = 'disponível';

    $verifica = $conexao->prepare("SELECT * FROM chave WHERE numero_identificacao = ?");
    $verifica->bind_param("s", $numero_identificacao);
    $verifica->execute();
    $resultado = $verifica->get_result();

    if ($resultado->num_rows > 0) {
        $erroMsg = "Número de identificação já cadastrado.";
    } else {
        $stmt = $conexao->prepare("INSERT INTO chave (nome, numero_identificacao, descricao, quantidade, status, cpf_adm) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nome, $numero_identificacao, $descricao, $quantidade, $status, $cpf_adm);

        if ($stmt->execute()) {
            $successMsg = "Chave cadastrada com sucesso!";
        } else {
            $erroMsg = "Erro ao cadastrar chave: " . $stmt->error;
        }

        $stmt->close();
    }

    $verifica->close();
}

$admCheck->close();
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../IMG/cmpage.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chave Mestra - Cadastro de Chaveiros</title>

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="../css/chaves.css"> 

     <!-- header -->
  <?php include '../Includes/header.php'; ?>

    <!-- Tailwind (opcional) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="login-container">
        <div class="login-box">
            <h2>Cadastro de Chaveiros</h2>

            <!-- Mensagens -->
            <?php if (!empty($erroMsg)): ?>
                <p class="text-red-600 text-center font-semibold mb-4"><?= $erroMsg ?></p>
            <?php elseif (!empty($successMsg)): ?>
                <p class="text-green-600 text-center font-semibold mb-4"><?= $successMsg ?></p>
            <?php endif; ?>

            <form action="cadastro_chave.php" method="POST">

                <div class="input-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Exemplo: LAB. 1" required>
                </div>

                <div class="input-group">
                    <label for="numero_identificacao">Número de Identificação</label>
                    <input type="number" id="numero_identificacao" name="numero_identificacao" placeholder="Exemplo: 00" required pattern="\d+">
                </div>

                <div class="input-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" id="descricao" name="descricao" placeholder="Opcional">
                </div>

                <div class="input-group">
                    <label for="quantidade">Quantidade de chaves por chaveiro</label>
                    <input type="number" id="quantidade" name="quantidade" placeholder="Exemplo: 2" required min="1">
                </div>

                <div class="submit-btn">
                    <button type="submit">Cadastrar Chaveiro</button>
                </div>

            </form>
        </div>
    </div>

     <!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>
</body>
</html>
