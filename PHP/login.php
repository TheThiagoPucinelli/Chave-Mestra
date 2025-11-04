<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$redirectTo = "";

// PROCESSA LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erroMsg = "Por favor, preencha todos os campos.";
    } else {
        $resultado = $conexao->prepare("SELECT * FROM usuario WHERE cpf = ? OR email = ? OR info_categoria = ?");
        $resultado->bind_param("sss", $login, $login, $login);
        $resultado->execute();
        $res = $resultado->get_result();

        if ($res && $res->num_rows === 1) {
            $usuario = $res->fetch_assoc();

            if (password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true);
                $_SESSION['cpf'] = $usuario['cpf'];
                $_SESSION['nome'] = $usuario['nome'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['info_categoria'] = $usuario['info_categoria'];

                $stmt = $conexao->prepare("SELECT 1 FROM usuario_adm WHERE cpf = ?");
                $stmt->bind_param("s", $usuario['cpf']);
                $stmt->execute();
                $isAdmin = $stmt->get_result()->num_rows > 0;
                $_SESSION['isAdmin'] = $isAdmin;

                $redirectTo = $isAdmin ? '../Pages/index.php' : '../Pages/agendar.php';

                // Overlay moderno
                echo "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Redirecionando...</title>
                    <style>
                        body {
                            margin:0;
                            display:flex;
                            justify-content:center;
                            align-items:center;
                            height:100vh;
                            font-family:'Segoe UI', sans-serif;
                            background: linear-gradient(to bottom, #ffffff 0%, #6998d6ff 100%);
                            overflow:hidden;
                        }
                        .overlay-container {
                            text-align:center;
                            backdrop-filter: blur(12px);
                            background: rgba(255,255,255,0.85);
                            padding:50px;
                            border-radius:20px;
                            box-shadow:0 15px 40px rgba(0,0,0,0.25);
                        }
                        .spinner {
                            width:80px;
                            height:80px;
                            border-radius:50%;
                            border:10px solid #d0d6e0;
                            border-top:10px solid #2563EB;
                            animation: spin 1s linear infinite;
                            margin:0 auto 25px auto;
                        }
                        @keyframes spin {0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
                        p {
                            font-size:1.3rem;
                            font-weight:600;
                            color:#2563EB;
                            animation: fadeZoom 0.8s ease-in-out infinite alternate;
                            margin:0;
                        }
                        @keyframes fadeZoom {
                            0% { opacity:0.5; transform: scale(0.95);}
                            100% { opacity:1; transform: scale(1.05);}
                        }
                    </style>
                </head>
                <body>
                    <div class='overlay-container'>
                        <div class='spinner'></div>
                        <p>Redirecionando...</p>
                    </div>
                    <script>
                        setTimeout(function(){ window.location.href='$redirectTo'; }, 1000);
                    </script>
                </body>
                </html>
                ";
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Chave Mestra - Login</title>
<link rel="icon" type="image/png" href="../IMG/CM.png">
<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(to bottom, #ffffff 0%, #6998d6ff 100%);
}
.login-container {
    width:100%;
    max-width:400px;
    padding:20px;
}
.login-box {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    padding:35px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,0.25);
}
h2 {
    text-align:center;
    margin-bottom:25px;
    color:#2563EB;
}
.input-group {
    margin-bottom:15px;
}
.input-group label {
    display:block;
    margin-bottom:5px;
    font-weight:600;
    color:#333;
}
.input-group input {
    width:100%;
    padding:12px;
    border:1.5px solid #ccc;
    border-radius:8px;
    font-size:1rem;
}
.input-group input:focus {
    border-color:#2563EB;
    outline:none;
    box-shadow:0 0 10px rgba(37,99,235,0.4);
}
.erro-login {
    background:#f8d7da;
    color:#842029;
    padding:12px 15px;
    margin-bottom:20px;
    border-radius:6px;
    border:1px solid #f5c2c7;
    font-size:0.95rem;
}
.submit-btn button {
    width:100%;
    padding:14px;
    background:#2563EB;
    border:none;
    color:#fff;
    font-weight:bold;
    font-size:1rem;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
}
.submit-btn button:hover {
    background:#1e3a5f;
}
.forgot-password { text-align:center; margin-top:10px; }
.forgot-password a { color:#2563EB; text-decoration:none; font-weight:bold; }
.signup-link { text-align:center; margin-top:15px; }
.signup-link a { color:#2563EB; text-decoration:none; font-weight:bold; }
@media(max-width:480px){.login-box{padding:25px;}}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>

        <?php if($erroMsg): ?>
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

        <div class="forgot-password">
            <a href="recuperar_senha.php">Esqueci a Senha</a>
        </div>

        <p class="signup-link">
            Não tem uma conta? <br>
            <a href="../PHP/cadastro.php">Cadastre-se</a>
        </p>
    </div>
</div>
</body>
</html>
