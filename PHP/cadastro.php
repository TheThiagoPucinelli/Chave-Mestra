<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";

// Valida nome
function validarNome($nome) {
    return preg_match("/^[a-zA-ZÀ-ÿ\s]{1,60}$/u", $nome);
}

// Valida senha
function validarSenha($senha) {
    return strlen($senha) >= 12 && strlen($senha) <= 30;
}

// Validação real de CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }

    return true;
}

// Valida SIAPE ou matrícula: 7 ou 8 dígitos
function validarInfoCategoria($info, $categoria) {
    if ($categoria === 'aluno') {
        return preg_match('/^\d{6,15}$/', $info); // matrícula com 6 a 15 dígitos
    } else {
        return preg_match('/^\d{7,8}$/', $info); // SIAPE ou identificação com 7 ou 8 dígitos
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
    $info_categoria = isset($_POST['info_categoria']) ? trim($_POST['info_categoria']) : '';

    if (!validarNome($nome)) {
        $erroMsg = "O nome deve conter apenas letras e no máximo 60 caracteres.";
    } elseif (!validarCPF($cpf)) {
        $erroMsg = "CPF inválido. Digite um CPF real com 11 dígitos.";
    } elseif (!validarSenha($senha)) {
        $erroMsg = "A senha deve conter entre 12 e 30 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erroMsg = "As senhas não coincidem.";
    } elseif (empty($categoria)) {
        $erroMsg = "Selecione uma categoria.";
    } elseif (!validarInfoCategoria($info_categoria, $categoria)) {
        $erroMsg = "O campo de informação da categoria deve conter apenas números válidos.";
    } else {
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

            $stmt = $conexao->prepare("INSERT INTO usuario (nome, cpf, email, senha, categoria, info_categoria) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Erro ao preparar a inserção: " . $conexao->error);
            }
            $stmt->bind_param("ssssss", $nome, $cpf, $email, $senha_hash, $categoria, $info_categoria);

            if ($stmt->execute()) {
                $_SESSION['msg_sucesso'] = "Cadastro realizado com sucesso!";
                header("Location: login.php");
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
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
     
      background-color:#3884d0;
    }

    .login-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .login-box {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      max-width: 400px;
      width: 100%;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    .input-group {
      margin-bottom: 15px;
    }

    .input-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .input-group input,
    .input-group select {
      width: 100%;
      padding: 10px;
      border: 1.5px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
    }

    .input-group input:focus,
    .input-group select:focus {
      border-color: #667eea;
      outline: none;
      box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
    }

    .erro-login {
      background: #f8d7da;
      color: #842029;
      padding: 10px 15px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #f5c2c7;
      font-size: 0.95rem;
    }

    .submit-btn button {
      width: 100%;
      padding: 12px;
      background: #667eea;
      border: none;
      color: #fff;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
    }

    .submit-btn button:hover {
      background: #5469d4;
    }

    .signup-link {
      text-align: center;
      margin-top: 20px;
    }

    .signup-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: bold;
    }

    @media (max-width: 480px) {
      .login-box {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <h2>Cadastro</h2>

      <?php if ($erroMsg): ?>
        <div class="erro-login"><?= htmlspecialchars($erroMsg) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-group">
          <label for="nome">Nome</label>
          <input type="text" id="nome" name="nome" maxlength="60" required placeholder="Exemplo: João"
                 value="<?= isset($nome) ? htmlspecialchars($nome) : '' ?>">
        </div>

        <div class="input-group">
          <label for="cpf">CPF</label>
          <input type="text" id="cpf" name="cpf" maxlength="11" required pattern="\d{11}" placeholder="Exemplo: 12345678901"
                 value="<?= isset($cpf) ? htmlspecialchars($cpf) : '' ?>">
        </div>

        <div class="input-group">
          <label for="categoria">Categoria</label>
          <select name="categoria" id="categoria" onchange="atualizarLabelInfo()" required>
            <option value="">Selecione</option>
            <option value="aluno" <?= (isset($categoria) && $categoria == 'aluno') ? 'selected' : '' ?>>Aluno</option>
            <option value="professor" <?= (isset($categoria) && $categoria == 'professor') ? 'selected' : '' ?>>Professor</option>
            <option value="servidor_publico" <?= (isset($categoria) && $categoria == 'servidor_publico') ? 'selected' : '' ?>>Servidor Público</option>
          </select>
        </div>

        <div class="input-group" id="grupo-info" style="display:none;">
          <label for="info_categoria" id="label-info">Informação</label>
          <input type="text" id="info_categoria" name="info_categoria" required
                 value="<?= isset($info_categoria) ? htmlspecialchars($info_categoria) : '' ?>">
        </div>

        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required placeholder="Exemplo: João@gmail.com"
                 value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
        </div>

        <div class="input-group">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" minlength="12" maxlength="30" required placeholder="Ex: 123456789012">
        </div>

        <div class="input-group">
          <label for="confirmar_senha">Confirmar Senha</label>
          <input type="password" id="confirmar_senha" name="confirmar_senha" maxlength="30" required>
        </div>

        <div class="submit-btn">
          <button type="submit">Cadastrar</button>
        </div>
      </form>

      <p class="signup-link">Já tem uma conta? <a href="login.php">Faça login</a></p>
    </div>
  </div>

  <script>
    function atualizarLabelInfo() {
      const categoria = document.getElementById('categoria').value;
      const grupoInfo = document.getElementById('grupo-info');
      const label = document.getElementById('label-info');
      const inputInfo = document.getElementById('info_categoria');

      if (categoria) {
        grupoInfo.style.display = 'block';

        if (categoria === 'aluno') {
          label.textContent = 'Matrícula do aluno';
          inputInfo.placeholder = 'Ex: 202312345';
        } else if (categoria === 'professor') {
          label.textContent = 'SIAPE do professor';
          inputInfo.placeholder = 'Ex: 12345678';
        } else {
          label.textContent = 'Identificação do servidor';
          inputInfo.placeholder = 'Ex: 87654321';
        }

        inputInfo.required = true;
      } else {
        grupoInfo.style.display = 'none';
        inputInfo.required = false;
        inputInfo.value = '';
      }
    }

    // Inicializa exibição ao recarregar a página com categoria já selecionada
    window.onload = atualizarLabelInfo;
  </script>
</body>
</html>
