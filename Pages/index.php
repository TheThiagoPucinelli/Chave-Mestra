<?php 
include __DIR__ . '/../PHP/verifica_login.php'; 
include_once '../BD/conexao.php';

$mensagem = "";
$erro = "";

// Buscar chaves agendadas (reservadas e aguardando retirada)
$chaves = [];
$sqlChaves = "
    SELECT e.id_emprestimo, c.id_chave, c.nome, c.numero_identificacao, u.nome AS nome_usuario, e.categoria
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    JOIN usuario u ON e.cpf_solicitante = u.cpf
    WHERE e.hora_data_retirada IS NULL
    ORDER BY u.nome, c.nome
";
$resultChaves = $conexao->query($sqlChaves);
if ($resultChaves) {
    while ($row = $resultChaves->fetch_assoc()) {
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ") - Reservado por: " . $row['nome_usuario'];
        if (!empty($row['categoria'])) {
            $texto .= " - Categoria: " . ucfirst($row['categoria']);
        }
        $chaves[] = ['id' => $row['id_emprestimo'], 'texto' => $texto, 'categoria' => $row['categoria']];
    }
}

// Buscar chaves retiradas e não devolvidas para devolução
$chavesRetiradas = [];
$sqlChavesRetiradas = "
    SELECT e.id_emprestimo, c.id_chave, c.nome, c.numero_identificacao, u.nome AS nome_usuario
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    JOIN usuario u ON e.cpf_solicitante = u.cpf
    WHERE e.hora_data_retirada IS NOT NULL
      AND e.hora_data_devolucao IS NULL
    ORDER BY u.nome, c.nome
";
$resultChavesRetiradas = $conexao->query($sqlChavesRetiradas);
if ($resultChavesRetiradas) {
    while ($row = $resultChavesRetiradas->fetch_assoc()) {
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ") - Retirado por: " . $row['nome_usuario'];
        $chavesRetiradas[] = ['id' => $row['id_emprestimo'], 'texto' => $texto];
    }
}

// Processar Retirada (confirmar retirada de chave agendada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'retirada') {
    $emprestimo_id = $_POST['emprestimo_id'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $cpf_adm = $_SESSION['cpf'] ?? '';

    if (!$emprestimo_id || !$categoria) {
        $erro = "Preencha todos os campos para retirada.";
    } else {
        $stmt = $conexao->prepare("
            UPDATE emprestimo 
            SET hora_data_retirada = NOW(), cpf_adm = ?, categoria = ?
            WHERE id_emprestimo = ? AND hora_data_retirada IS NULL
        ");
        if ($stmt) {
            $stmt->bind_param("ssi", $cpf_adm, $categoria, $emprestimo_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $mensagem = "Retirada da chave confirmada com sucesso.";
                } else {
                    $erro = "Esta retirada já foi confirmada ou não existe.";
                }
            } else {
                $erro = "Erro ao confirmar retirada: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $erro = "Erro na preparação da consulta: " . $conexao->error;
        }
    }
}

// Processar Devolução (atualizar empréstimo para registrar devolução)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'devolucao') {
    $emprestimo_id = $_POST['emprestimo_id_devolucao'] ?? '';

    if (!$emprestimo_id) {
        $erro = "Selecione a chave que está sendo devolvida.";
    } else {
        $stmt = $conexao->prepare("
            UPDATE emprestimo 
            SET hora_data_devolucao = NOW()
            WHERE id_emprestimo = ? AND hora_data_devolucao IS NULL
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $emprestimo_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $mensagem = "Devolução registrada com sucesso.";
                } else {
                    $erro = "Não foi encontrada retirada ativa para essa chave.";
                }
            } else {
                $erro = "Erro ao registrar devolução: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $erro = "Erro na preparação da consulta: " . $conexao->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gerenciamento de Chaves</title>
    <link rel="shortcut icon" href="/IMG/2.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/index.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<?php include '../Includes/header.php'; ?>

<form action="../PHP/logout.php" method="post" style="display:inline;">
  <button type="submit"
          style="
            background-color: #e3342f;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
          ">
    Sair
  </button>
</form>

<?php if($mensagem): ?>
  <div class="message success"><?= $mensagem ?></div>
<?php endif; ?>

<?php if($erro): ?>
  <div class="message error"><?= $erro ?></div>
<?php endif; ?>

<div class="container-tab table-desktop">
  <h2>Retirada de Chaves <br><em>(Somente Funcionários)</em></h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="retirada">
    <table>
      <thead>
        <tr>
          <th>Chave Agendada</th>
          <th>Categoria</th>
          <th>Confirmar Retirada</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select name="emprestimo_id" required>
              <option value="">--Selecione a chave agendada--</option>
              <?php foreach($chaves as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>" <?php if(isset($_POST['emprestimo_id']) && $_POST['emprestimo_id'] == $c['id']) echo 'selected'; ?>>
                    <?= htmlspecialchars($c['texto']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <select name="categoria" required>
              <option value="">--Selecione a categoria--</option>
              <option value="aluno" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='aluno') echo 'selected'; ?>>Aluno</option>
              <option value="funcionario" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='funcionario') echo 'selected'; ?>>Funcionário</option>
              <option value="professor" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='professor') echo 'selected'; ?>>Professor</option>
            </select>
          </td>
          <td>
            <button type="submit">Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<div class="container-tab table-desktop mt-8">
  <h2>Devolução de Chaves <br><em>(Somente Funcionários)</em></h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="devolucao">
    <table>
      <thead>
        <tr>
          <th>Chave para Devolução</th>
          <th>Confirmar Devolução</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select name="emprestimo_id_devolucao" required>
              <option value="">--Selecione a chave--</option>
              <?php foreach($chavesRetiradas as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['texto']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <button type="submit">Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<br><br><br>

 <!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>

</body>
</html>
