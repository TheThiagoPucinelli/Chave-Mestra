<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$erroMsg = '';
$successMsg = '';

// Pegar CPF do usuário logado
$cpf_adm = $_SESSION['cpf'] ?? '';
if (!$cpf_adm) {
    header('Location: ../Pages/login.php');
    exit();
}

// --- VERIFICAÇÃO DE ADMINISTRADOR ---
$stmtAdm = mysqli_prepare($conexao, "SELECT cpf FROM usuario_adm WHERE cpf = ?");
mysqli_stmt_bind_param($stmtAdm, "s", $cpf_adm);
mysqli_stmt_execute($stmtAdm);
$resAdm = mysqli_stmt_get_result($stmtAdm);
if (!$resAdm || mysqli_num_rows($resAdm) === 0) {
    // Redireciona para a agenda se não for administrador
    header('Location: ../Pages/agendar.php');
    exit();
}

// --- PROCESSAR FORMULÁRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $numero_identificacao = trim($_POST['numero_identificacao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    if (!$nome || !$numero_identificacao || $quantidade < 1) {
        $erroMsg = "Preencha todos os campos obrigatórios corretamente!";
    } else {
        // Verifica se já existe a chave
        $verifica = $conexao->prepare("SELECT * FROM chave WHERE numero_identificacao = ?");
        $verifica->bind_param("s", $numero_identificacao);
        $verifica->execute();
        $resVerifica = $verifica->get_result();

        if ($resVerifica->num_rows > 0) {
            $erroMsg = "Número de identificação já cadastrado.";
        } else {
            $stmt = $conexao->prepare("
                INSERT INTO chave (nome, numero_identificacao, descricao, quantidade, cpf_adm)
                VALUES (?, ?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("sssis", $nome, $numero_identificacao, $descricao, $quantidade, $cpf_adm);
                if ($stmt->execute()) {
                    $successMsg = "Chaveiro cadastrado com sucesso!";
                } else {
                    $erroMsg = "Erro ao cadastrar: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $erroMsg = "Erro na preparação da consulta: " . $conexao->error;
            }
        }

        $verifica->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../IMG/cmpage.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chave Mestra - Cadastro de Chaveiros</title>

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="../css/chaves.css">

    <!-- Header -->
    <?php include '../Includes/header.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="flex justify-center items-center min-h-screen p-4">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
            <h2 class="text-2xl font-bold text-center text-black mb-6">Cadastro de Chaveiros</h2>

            <!-- Mensagens -->
            <?php if (!empty($erroMsg)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($erroMsg) ?></div>
            <?php elseif (!empty($successMsg)): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($successMsg) ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="nome" class="block font-medium mb-1">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Exemplo: LAB. 1" required
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label for="numero_identificacao" class="block font-medium mb-1">Número de Identificação</label>
                    <input type="number" id="numero_identificacao" name="numero_identificacao" placeholder="Exemplo: 00" required pattern="\d+"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label for="descricao" class="block font-medium mb-1">Descrição</label>
                    <input type="text" id="descricao" name="descricao" placeholder="Opcional"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label for="quantidade" class="block font-medium mb-1">Quantidade de chaves por chaveiro</label>
                    <input type="number" id="quantidade" name="quantidade" placeholder="Exemplo: 2" required min="1"
                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition-all">
                        Cadastrar Chaveiro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-6 mt-auto">
        <div class="max-w-7xl mx-auto text-center text-sm">
            <p>&copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. |
                <a href="../Pages/contato.php" class="text-blue-400 hover:text-white transition">Contato</a>
            </p>
        </div>
    </footer>
</body>
</html>
