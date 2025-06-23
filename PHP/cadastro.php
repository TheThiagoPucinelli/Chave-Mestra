<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";

// Validação de nome
function validarNome($nome) {
    return preg_match("/^[a-zA-ZÀ-ÿ\s]{1,60}$/u", $nome);
}

// Validação de senha
function validarSenha($senha) {
    return strlen($senha) <= 30 && strlen($senha) >= 12;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (!validarNome($nome)) {
        $erroMsg = "O nome deve conter apenas letras e no máximo 60 caracteres.";
    } elseif (!validarSenha($senha)) {
        $erroMsg = "A senha deve conter entre 12 e 30 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erroMsg = "As senhas não coincidem.";
    } else {
        // Verifica se CPF ou email já estão cadastrados
        $verifica = $conexao->prepare("SELECT * FROM usuario WHERE cpf = ? OR email = ?");
        if (!$verifica) {
            die("Erro ao preparar a consulta: " . $conexao->error);
        }
        $verifica->bind_param("ss", $cpf, $email);
        $verifica->execute();
        $resultado = $verifica->get_result();

        if ($resultado->num_rows > 0) {
            $erroMsg = "CPF ou e-mail já cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO usuario (nome, cpf, email, senha) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                die("Erro ao preparar a inserção: " . $conexao->error);
            }
            $stmt->bind_param("ssss", $nome, $cpf, $email, $senha_hash);

            if ($stmt->execute()) {
                $_SESSION['nome'] = $nome;
                $_SESSION['email'] = $email;
                $_SESSION['cpf'] = $cpf;

                header("Location: index.php");
                exit;
            } else {
                $erroMsg = "Erro ao cadastrar: " . $stmt->error;
            }

            $stmt->close();
        }

        $verifica->close();
        $conexao->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastro - Chave Mestra</title>
    <link rel="stylesheet" href="../css/login.css" />
    <style>
      /* Botão para mostrar senha */
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
            <h2>Cadastro</h2>

            <?php if ($erroMsg): ?>
                <div class="erro-login">
                    <?php echo htmlspecialchars($erroMsg); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php">
                <div class="input-group">
                    <label for="nome">Nome</label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        maxlength="60" 
                        pattern="[A-Za-zÀ-ÿ\s]{1,60}" 
                        title="Apenas letras e espaços, até 60 caracteres" 
                        placeholder="Digite seu nome completo" 
                        required 
                        value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                    >
                </div>

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
                        value="<?php echo isset($cpf) ? htmlspecialchars($cpf) : ''; ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="exemplo@dominio.com" 
                        required 
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        minlength="12"
                        maxlength="30" 
                        title="Entre 12 e 30 caracteres" 
                        placeholder="Digite sua senha" 
                        required
                    >
                    <button type="button" class="toggle-password" onclick="toggleSenha('senha', this)">Mostrar</button>
                </div>

                <div class="input-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input 
                        type="password" 
                        id="confirmar_senha" 
                        name="confirmar_senha" 
                        maxlength="30" 
                        placeholder="Confirme sua senha" 
                        required
                    >
                    <button type="button" class="toggle-password" onclick="toggleSenha('confirmar_senha', this)">Mostrar</button>
                </div>

                <div class="submit-btn">
                    <button type="submit">Cadastrar</button>
                </div>
            </form>

            <p class="signup-link">
                Já tem uma conta? <br />
                <a href="login.php">Faça login</a>
            </p>
        </div>
    </div>

    <script>
      function toggleSenha(idInput, btn) {
        const input = document.getElementById(idInput);
        if (input.type === 'password') {
          input.type = 'text';
          btn.textContent = 'Ocultar';
        } else {
          input.type = 'password';
          btn.textContent = 'Mostrar';
        }
      }
    </script>
</body>
</html>
