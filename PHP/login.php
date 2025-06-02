<?php
session_start();
include '../BD/conexao.php';


$erroMsg = "";


function buscarUsuarioPorCpf($conexao, $cpf) {
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    return $stmt->get_result();
}


function senhaCorreta($senhaInformada, $senhaHash) {
    return password_verify($senhaInformada, $senhaHash);
}


function iniciarSessaoUsuario($usuario) {
    $_SESSION['cpf'] = $usuario['cpf'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];
    header("Location: ../pages/index.html");
    exit;
}


function processarLogin($conexao, &$erroMsg) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $cpf = trim($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($cpf) || empty($senha)) {
        $erroMsg = "Por favor, preencha todos os campos.";
        return;
    }

    $resultado = buscarUsuarioPorCpf($conexao, $cpf);

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (senhaCorreta($senha, $usuario['senha'])) {
            iniciarSessaoUsuario($usuario);
        } else {
            $erroMsg = "Senha incorreta. Tente novamente.";
        }
    } else {
        $erroMsg = "CPF não encontrado. Verifique e tente novamente.";
    }
}

// Chama o processamento do login
processarLogin($conexao, $erroMsg);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chave Mestra - Login</title>
    <link rel="stylesheet" href="../css/login.css" />
    <link rel="icon" type="image/png" href="/IMG/cmpage.png" />
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>

            <?php if ($erroMsg): ?>
                <div class="erro-login">
                    <?php echo htmlspecialchars($erroMsg); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="cpf">CPF</label>
                    <input
                        type="text"
                        id="cpf"
                        name="cpf"
                        placeholder="Digite seu CPF"
                        maxlength="11"
                        pattern="\d{11}"
                        title="Digite exatamente 11 números"
                        required
                    />
                </div>

                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        maxlength="30"
                        required
                    />
                </div>

                <div class="submit-btn">
                    <button type="submit">Entrar</button>
                </div>
            </form>

            <p class="signup-link">
                Não tem uma conta? <br />
                <a href="../PHP/cadastro.php">Cadastre-se</a>
            </p>
        </div>
    </div>
</body>
</html>
