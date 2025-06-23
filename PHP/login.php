<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$loginSucesso = false;
$tempoMaximo = 30 * 60; // 30 minutos em segundos

function buscarUsuarioPorCpf($conexao, $cpf) {
    $stmt = $conexao->prepare("SELECT * FROM usuario WHERE cpf = ?");
    if (!$stmt) {
        die("Erro ao preparar a consulta: " . $conexao->error);
    }
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    return $stmt->get_result();
}

function senhaCorreta($senhaInformada, $senhaHash) {
    return password_verify($senhaInformada, $senhaHash);
}

function iniciarSessaoUsuario($usuario, $tempoMaximo) {
    $_SESSION['cpf'] = $usuario['cpf'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];

    // Define um cookie para controlar a inatividade
    setcookie('usuario_logado', time() + $tempoMaximo, time() + $tempoMaximo, "/");
}

function processarLogin($conexao, &$erroMsg, &$loginSucesso, $tempoMaximo) {
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

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (senhaCorreta($senha, $usuario['senha'])) {
            iniciarSessaoUsuario($usuario, $tempoMaximo);
            $loginSucesso = true;
        } else {
            $erroMsg = "Senha incorreta. Tente novamente.";
        }
    } else {
        $erroMsg = "CPF não encontrado. Verifique e tente novamente.";
    }
}

processarLogin($conexao, $erroMsg, $loginSucesso, $tempoMaximo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chave Mestra - Login</title>
    <link rel="stylesheet" href="../css/login.css" />
    <link rel="icon" type="image/png" href="/IMG/cmpage.png" />
    <style>
      /* Ajuste básico para botão de mostrar senha */
      .input-group {
        position: relative;
        margin-bottom: 1rem;
      }
      .toggle-password {
        position: absolute;
        right: 10px;
        top: 38px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        color: #555;
        user-select: none;
      }
    </style>
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
                    <button type="button" class="toggle-password" onclick="toggleSenha()">Mostrar</button>
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

    <?php if ($loginSucesso): ?>
        <script>
            alert("Login realizado com sucesso! Redirecionando...");
            setTimeout(function() {
                window.location.href = "../Pages/index.php";
            }, 200);
        </script>
    <?php endif; ?>

    <script>
      function toggleSenha() {
        const senhaInput = document.getElementById('senha');
        const btn = document.querySelector('.toggle-password');
        if (senhaInput.type === 'password') {
          senhaInput.type = 'text';
          btn.textContent = 'Ocultar';
        } else {
          senhaInput.type = 'password';
          btn.textContent = 'Mostrar';
        }
      }
    </script>
</body>
</html>
