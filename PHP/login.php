<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$loginSucesso = false;
$tempoMaximo = 30 * 60; // 30 minutos em segundos

function buscarUsuarioPorCpfOuEmail($conexao, $login) {
    $stmt = $conexao->prepare("SELECT * FROM usuario WHERE cpf = ? OR email = ?");
    if (!$stmt) {
        die("Erro ao preparar a consulta: " . $conexao->error);
    }
    $stmt->bind_param("ss", $login, $login);
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

    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erroMsg = "Por favor, preencha todos os campos.";
        return;
    }

    $resultado = buscarUsuarioPorCpfOuEmail($conexao, $login);

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (senhaCorreta($senha, $usuario['senha'])) {
            iniciarSessaoUsuario($usuario, $tempoMaximo);
            $loginSucesso = true;
        } else {
            $erroMsg = "Senha incorreta. Tente novamente.";
        }
    } else {
        $erroMsg = "Usuário não encontrado. Verifique CPF ou e-mail e tente novamente.";
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
      .erro-login {
          background-color: #f8d7da;
          color: #842029;
          padding: 10px;
          margin-bottom: 15px;
          border-radius: 4px;
          border: 1px solid #f5c2c7;
      }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>

            <?php if ($erroMsg): ?>
                <div class="erro-login">
                    <?= htmlspecialchars($erroMsg) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="login">CPF ou Email</label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        placeholder="Digite seu CPF ou Email"
                        maxlength="100"
                        required
                        value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>"
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
