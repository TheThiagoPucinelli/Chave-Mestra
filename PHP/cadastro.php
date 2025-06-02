
<?php
session_start();
include '../BD/conexao.php';


$erroMsg = "";

// Função para validar nome (apenas letras e espaços, até 60 caracteres)
function validarNome($nome) {
    return preg_match("/^[a-zA-ZÀ-ÿ\s]{1,60}$/u", $nome);
}

// Função para validar senha (até 30 caracteres)
function validarSenha($senha) {
    return strlen($senha) <= 30;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (!validarNome($nome)) {
        $erroMsg = "O nome deve conter apenas letras e no máximo 100 caracteres.";
    } elseif (!validarSenha($senha)) {
        $erroMsg = "A senha deve conter no máximo 30 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erroMsg = "As senhas não coincidem.";
    } else {
        // Verifica se CPF ou email já existem
        $verifica = $conexao->prepare("SELECT * FROM usuarios WHERE cpf = ? OR email = ?");
        $verifica->bind_param("ss", $cpf, $email);
        $verifica->execute();
        $resultado = $verifica->get_result();

        if ($resultado->num_rows > 0) {
            $erroMsg = "CPF ou e-mail já cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO usuarios (nome, cpf, email, senha) VALUES (?, ?, ?, ?)");
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
                        type="char" 
                        id="nome" 
                        name="nome" 
                        maxlength="60" 
                        pattern="[A-Za-zÀ-ÿ\s]{1,100}" 
                        title="Apenas letras e espaços, até 100 caracteres" 
                        placeholder="Digite seu nome completo" 
                        required 
                        value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                    >
                </div>

                <div class="input-group">
                <div class="input-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="Digite Seu CPF "maxlength="11" pattern="\d{11}"  title="Digite exatamente 11 números" required value="<?php echo isset($cpf) ? htmlspecialchars($cpf) : ''; ?>">
                </div>
                    
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
                        title="Máximo 30 caracteres" 
                        placeholder="Digite sua senha" 
                        required
                    >
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
</body>
</html>
