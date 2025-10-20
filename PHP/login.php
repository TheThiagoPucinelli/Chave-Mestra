<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$tempoMaximo = 30 * 60; // 30 minutos (você pode usar isso para expirar sessão)

function buscarUsuarioPorCpfOuEmail($conexao, $login) {
    $stmt = $conexao->prepare("SELECT * FROM usuario WHERE cpf = ? OR email = ? OR info_categoria = ?");
    $stmt->bind_param("sss", $login, $login, $login); // Corrigido: 3 parâmetros
    $stmt->execute();
    return $stmt->get_result();
}


function senhaCorreta($senhaInformada, $senhaHash) {
    return password_verify($senhaInformada, $senhaHash);
}

function isAdmin($conexao, $cpf) {
    $stmt = $conexao->prepare("SELECT 1 FROM usuario_adm WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return ($resultado->num_rows > 0);
}

function iniciarSessaoUsuario($usuario, $admin) {
    session_regenerate_id(true);
    $_SESSION['cpf'] = $usuario['cpf'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['info_categoria'] = $usuario['info_categoria'];
    $_SESSION['isAdmin'] = $admin ? true : false;
    $_SESSION['last_activity'] = time(); // para controlar tempo da sessão
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erroMsg = "Por favor, preencha todos os campos.";
    } else {
        $resultado = buscarUsuarioPorCpfOuEmail($conexao, $login);

        if ($resultado && $resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            if (senhaCorreta($senha, $usuario['senha'])) {
                $admin = isAdmin($conexao, $usuario['cpf']);
                iniciarSessaoUsuario($usuario, $admin);

                if ($admin) {
                    header("Location: ../Pages/index.php");
                } else {
                    header("Location: ../Pages/agendar.php");
                }
                exit;
            } else {
                $erroMsg = "Senha incorreta.";
            }
        } else {
            $erroMsg = "Usuário não encontrado.";
        }
    }
}
?>







<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chave Mestra - Login</title>
    <link rel="stylesheet" href="../css/login.css" />
    <link rel="icon" type="image/png" href="../IMG/cmpage.png" />
    <style>
        .input-group { position: relative; margin-bottom: 1rem; }
        .toggle-password { position: absolute; right: 10px; top: 38px; background: transparent; border: none; cursor: pointer; font-size: 0.9rem; color: #555; user-select: none; }
        .erro-login { background-color: #f8d7da; color: #842029; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #f5c2c7; }
        .forgot-password a { color: #2563EB; text-decoration: underline; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>

            <?php if ($erroMsg): ?>
                <div class="erro-login"><?= htmlspecialchars($erroMsg) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="login">CPF ou Email/Informação de sua Categoria</label>
                    <input type="text" id="login" name="login" placeholder="Digite seu CPF ou Email" maxlength="150" required
                        value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>" />
                </div>

                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" maxlength="30" required />
                    
                </div>

                <div class="submit-btn">
                    <button type="submit">Entrar</button>
                </div>
            </form>

            <div class="forgot-password" style="text-align:center; margin-top:10px;">
                <a href="recuperar_senha.php">Esqueci a Senha</a>
            </div>

            <p class="signup-link" style="text-align:center; margin-top:15px;">
                Não tem uma conta? <br />
                <a href="../PHP/cadastro.php">Cadastre-se</a>
            </p>
        </div>
    </div>

    
    </script>
</body>
</html>
