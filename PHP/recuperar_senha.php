<?php
// Inclui o arquivo de conexão com o banco de dados
include __DIR__ . '/../BD/conexao.php'; // conexão com o banco

// Array para armazenar possíveis mensagens de erro
$erro = [];
$sucessoMsg = "";

// 'ok' é o gatilho para executar o PHP apenas quando o formulário for enviado.
// Se o usuário clicar no botão de enviar, $_POST['ok'] estará definido
if (isset($_POST['ok'])) {

    // Recebe o email enviado pelo formulário e protege contra SQL Injection
    $email = $conexao->real_escape_string($_POST['email']);

    // Valida se o formato do email é correto
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = "Email inválido"; // Adiciona erro ao array
    }

    // Consulta o banco para verificar se o email existe
    $sql_code = "SELECT senha FROM usuario WHERE email = '$email'";
    $sql_query = $conexao->query($sql_code) or die($conexao->error); // Executa a consulta ou exibe erro
    $total = $sql_query->num_rows; // Conta quantos registros foram encontrados

    // Se não encontrar nenhum registro, adiciona mensagem de erro
    if ($total == 0) {
        $erro[] = "O Email informado não existe no banco de dados";
    }

    // Se não houver erros, prossegue com a recuperação de senha
    if (count($erro) == 0) {

        // Gerar nova senha aleatória com 6 caracteres
        // Usa md5 do timestamp atual e pega os 6 primeiros caracteres
        $novaSenha = substr(md5(time()), 0, 6);

        // Criptografa a nova senha usando password_hash (recomendado para segurança)
        $senhacrypt = password_hash($novaSenha, PASSWORD_DEFAULT);

        // Atualiza a senha do usuário no banco de dados
        $sql_code = "UPDATE usuario SET senha = '$senhacrypt' WHERE email = '$email'";
        $sql_query = $conexao->query($sql_code) or die($conexao->error);

        // Tenta enviar um email para o usuário com a nova senha
        if (mail($email, "Sua nova senha", "Sua nova senha: " . $novaSenha)) {
            $sucessoMsg = "Uma nova senha foi enviada para o seu e-mail.";
        } else {
            // Caso o envio de email falhe, exibe mensagem de erro
            $sucessoMsg = "Não foi possível enviar o e-mail. Sua senha temporária é: <b>$novaSenha</b>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/CM.png">
    <title>Recuperar Senha</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-blue-50 min-h-screen flex items-center justify-center font-sans">

    <!-- Container centralizado do formulário -->
    <div class="bg-white shadow-2xl rounded-2xl p-10 max-w-md w-full">

        <!-- Título da página -->
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Recuperar Senha</h2>

        <!-- Exibe todas as mensagens de erro armazenadas no array $erro -->
        <?php if (!empty($erro)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 border border-red-300 rounded">
                <?php foreach ($erro as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Exibe mensagem de sucesso caso a senha tenha sido gerada/enviada -->
        <?php if ($sucessoMsg): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 border border-green-300 rounded">
                <?= $sucessoMsg ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para o usuário digitar seu email -->
        <form action="recuperar_senha.php" method="post" class="space-y-4">

            <!-- Campo de email -->
            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">Digite seu email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                    placeholder="seuemail@exemplo.com">
            </div>

            <!-- Botão de envio -->
            <button type="submit" name="ok"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition transform hover:scale-105">
                Gerar nova senha
            </button>
        </form>

        <!-- Link para voltar ao login -->
        <p class="text-center mt-6 text-gray-600 text-sm">
            Lembrou sua senha? <a href="login.php" class="text-blue-600 hover:underline">Voltar ao login</a>
        </p>
    </div>

</body>
</html>
