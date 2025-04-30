<?php
// Incluir o arquivo de conexão
require_once 'db/conexao.php';

// Variáveis para exibição de mensagens
$mensagem = '';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Coletar os dados do formulário e escapar as entradas para prevenir SQL Injection
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $sobrenome = mysqli_real_escape_string($conn, $_POST['sobrenome']);
    $matricula = mysqli_real_escape_string($conn, $_POST['matricula']);
    $senha = mysqli_real_escape_string($conn, $_POST['senha']);
    $confirmar_senha = mysqli_real_escape_string($conn, $_POST['confirmar_senha']);

    // Validar se os campos não estão vazios
    if (empty($nome) || empty($sobrenome) || empty($matricula) || empty($senha) || empty($confirmar_senha)) {
        $mensagem = "Todos os campos são obrigatórios!";
    } 
    // Validar se as senhas coincidem
    elseif ($senha !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem. Tente novamente.";
    }
    else {
        // Hash da senha para segurança
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Verificar se já existe um usuário com a matrícula informada
        $check_usuario_sql = "SELECT * FROM usuarios WHERE matricula = '$matricula'";
        $resultado = $conn->query($check_usuario_sql);

        if ($resultado->num_rows > 0) {
            // Se já existir, exibe mensagem
            $mensagem = "Já existe um usuário cadastrado com essa matrícula.";
        } else {
            // Caso contrário, insere os dados no banco de dados
            $sql = "INSERT INTO usuarios (nome, sobrenome, matricula, senha) VALUES ('$nome', '$sobrenome', '$matricula', '$senha_hash')";

            if ($conn->query($sql) === TRUE) {
                $mensagem = "Cadastro realizado com sucesso! <a href='/login.html'>Clique aqui para fazer login</a>";
            } else {
                $mensagem = "Erro ao realizar o cadastro: " . $conn->error;
            }
        }
    }
}

// Fechar a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Chave Mestra</title>
    <link rel="stylesheet" href="/CSS/login.css">
    <link rel="icon" type="image/png" href="/IMG/cmpage.png">
</head>
<body>

    <!-- Container de Cadastro -->
    <div class="login-container">
        <div class="login-box">
            <h2>Cadastro</h2>

            <!-- Exibe a mensagem de erro ou sucesso -->
            <?php if (!empty($mensagem)): ?>
                <div class="alert">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form action="cadastro.php" method="POST">
                <!-- Campo Nome -->
                <div class="input-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
                </div>

                <!-- Campo Sobrenome -->
                <div class="input-group">
                    <label for="sobrenome">Sobrenome</label>
                    <input type="text" id="sobrenome" name="sobrenome" placeholder="Digite seu sobrenome" required>
                </div>

                <!-- Campo Matrícula -->
                <div class="input-group">
                    <label for="matricula">Matrícula/SIEP</label>
                    <input type="text" id="matricula" name="matricula" placeholder="Digite sua matrícula" required>
                </div>

                <!-- Campo Senha -->
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </div>

                <!-- Campo Confirmar Senha -->
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
                </div>

                <!-- Botão de Cadastro -->
                <div class="submit-btn">
                    <button type="submit">Cadastrar</button>
                </div>
            </form>

            <!-- Link para Login -->
            <p class="signup-link">Já tem uma conta? <a href="/login.html">Faça login</a></p>
        </div>
    </div>

</body>
</html>
