

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../IMG/logo.svg">
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

