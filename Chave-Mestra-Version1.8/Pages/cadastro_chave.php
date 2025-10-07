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
<body class="bg-gray-100">
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

    <footer class="bg-gray-900 text-gray-400 py-3">
  <div class="text-center text-sm mb-4">
    &copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. | 
    <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a>
  </div>
  <div class="flex justify-center space-x-6">
    <a href="https://github.com/TheThiagoPucinelli" target="_blank" aria-label="GitHub" class="hover:text-white transition-colors duration-300">
      <!-- Ícone GitHub SVG -->
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.263.82-.582 0-.288-.01-1.05-.015-2.06-3.338.726-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.73.083-.73 1.205.085 1.838 1.237 1.838 1.237 1.07 1.835 2.807 1.305 3.492.997.108-.775.418-1.305.76-1.605-2.665-.3-5.466-1.334-5.466-5.932 0-1.31.468-2.38 1.236-3.22-.124-.303-.536-1.523.117-3.176 0 0 1.008-.322 3.3 1.23a11.5 11.5 0 0 1 3-.404c1.02.005 2.045.138 3 .404 2.29-1.552 3.297-1.23 3.297-1.23.655 1.653.243 2.873.12 3.176.77.84 1.235 1.91 1.235 3.22 0 4.61-2.804 5.628-5.475 5.922.43.37.823 1.103.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .32.217.698.825.58C20.565 21.796 24 17.297 24 12c0-6.63-5.37-12-12-12z"/>
      </svg>
    </a>
    <a href="https://br.linkedin.com/in/thiagopucinelli" target="_blank" aria-label="LinkedIn" class="hover:text-white transition-colors duration-300">
      <!-- Ícone LinkedIn SVG -->
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M4.98 3.5C3.34 3.5 2 4.82 2 6.45c0 1.56 1.27 2.94 3.05 2.94h.03c1.7 0 3.04-1.38 3.04-2.94-.03-1.63-1.35-2.95-3.14-2.95zM2.4 21.5h5.17V9H2.4v12.5zM9.57 9h4.95v1.7h.07c.69-1.3 2.38-2.67 4.9-2.67 5.24 0 6.2 3.45 6.2 7.93v9.27h-5.17v-8.23c0-1.97-.04-4.5-2.74-4.5-2.75 0-3.17 2.14-3.17 4.36v8.37H9.57V9z"/>
      </svg>
    </a>
  </div>
</footer>
</body>
</html>
