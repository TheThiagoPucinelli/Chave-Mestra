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
    <link rel="icon" type="image/svg+xml" href="../IMG/logo.svg">
    <link rel="stylesheet" href="../css/index.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-250 min-h-screen flex flex-col">

<?php include '../Includes/header.php'; ?>

<div class="flex justify-end p-4">
  <form action="../PHP/logout.php" method="post" style="inline">
    <button type="submit"
            class="bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700">
      Sair
    </button>
  </form>
</div>

<?php if($mensagem): ?>
  <div class="mx-auto max-w-4xl bg-green-100 text-green-800 p-4 rounded mb-4"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<?php if($erro): ?>
  <div class="mx-auto max-w-4xl bg-red-100 text-red-800 p-4 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- Retirada: Desktop -->
<div class="container-tab hidden md:block max-w-4xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-xl font-semibold mb-4">Retirada de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="retirada" />
    <table class="w-full table-auto border-collapse border border-gray-300">
      <thead>
        <tr class="bg-blue-600 text-white">
          <th class="border border-gray-300 px-4 py-2 text-left">Chave Agendada</th>
          <th class="border border-gray-300 px-4 py-2 text-left">Categoria</th>
          <th class="border border-gray-300 px-4 py-2 text-center">Confirmar Retirada</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border border-gray-300 px-4 py-2">
            <select name="emprestimo_id" required class="w-full p-2 border rounded">
              <option value="">--Selecione a chave agendada--</option>
              <?php foreach($chaves as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>" <?php if(isset($_POST['emprestimo_id']) && $_POST['emprestimo_id'] == $c['id']) echo 'selected'; ?>>
                    <?= htmlspecialchars($c['texto']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="border border-gray-300 px-4 py-2">
            <select name="categoria" required class="w-full p-2 border rounded">
              <option value="">--Selecione a categoria--</option>
              <option value="aluno" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='aluno') echo 'selected'; ?>>Aluno</option>
              <option value="funcionario" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='funcionario') echo 'selected'; ?>>Funcionário</option>
              <option value="professor" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='professor') echo 'selected'; ?>>Professor</option>
            </select>
          </td>
          <td class="border border-gray-300 px-4 py-2 text-center">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<!-- Retirada: Mobile -->
<div class="md:hidden max-w-xl mx-auto bg-white p-4 rounded shadow space-y-4 min-h-[320px]">
  <h2 class="text-xl font-semibold mb-2">Retirada de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="retirada" />
    <div class="mb-4">
      <label for="emprestimo_id_mobile" class="block mb-1 font-medium">Chave Agendada</label>
      <select id="emprestimo_id_mobile" name="emprestimo_id" required class="w-full p-2 border rounded">
        <option value="">--Selecione a chave agendada--</option>
        <?php foreach($chaves as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>" <?php if(isset($_POST['emprestimo_id']) && $_POST['emprestimo_id'] == $c['id']) echo 'selected'; ?>>
            <?= htmlspecialchars($c['texto']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-4">
      <label for="categoria_mobile" class="block mb-1 font-medium">Categoria</label>
      <select id="categoria_mobile" name="categoria" required class="w-full p-2 border rounded">
        <option value="">--Selecione a categoria--</option>
        <option value="aluno" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='aluno') echo 'selected'; ?>>Aluno</option>
        <option value="funcionario" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='funcionario') echo 'selected'; ?>>Funcionário</option>
        <option value="professor" <?php if(isset($_POST['categoria']) && $_POST['categoria']=='professor') echo 'selected'; ?>>Professor</option>
      </select>
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">Confirmar</button>
  </form>
</div>

<!-- Devolução: Desktop -->
<div class="container-tab hidden md:block max-w-4xl mx-auto bg-white p-6 rounded shadow mt-8">
  <h2 class="text-xl font-semibold mb-4">Devolução de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="devolucao" />
    <table class="w-full table-auto border-collapse border border-gray-300">
      <thead>
        <tr class="bg-blue-600 text-white">
          <th class="border border-gray-300 px-4 py-2 text-left">Chave para Devolução</th>
          <th class="border border-gray-300 px-4 py-2 text-center">Confirmar Devolução</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border border-gray-300 px-4 py-2">
            <select name="emprestimo_id_devolucao" required class="w-full p-2 border rounded">
              <option value="">--Selecione a chave--</option>
              <?php foreach($chavesRetiradas as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['texto']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="border border-gray-300 px-4 py-2 text-center">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>

<!-- Devolução: Mobile -->
<div class="md:hidden max-w-xl mx-auto bg-white p-4 rounded shadow space-y-4 min-h-[320px] mt-6">
  <h2 class="text-xl font-semibold mb-2">Devolução de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="devolucao" />
    <div class="mb-4">
      <label for="emprestimo_id_devolucao_mobile" class="block mb-1 font-medium">Chave para Devolução</label>
      <select id="emprestimo_id_devolucao_mobile" name="emprestimo_id_devolucao" required class="w-full p-2 border rounded">
        <option value="">--Selecione a chave--</option>
        <?php foreach($chavesRetiradas as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['texto']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">Confirmar</button>
  </form>
</div>

<!-- Espaço para footer -->
<div class="flex-grow"></div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>

</body>
</html>

