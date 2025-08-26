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

// Processar Retirada
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
                $mensagem = $stmt->affected_rows > 0 ? "Retirada da chave confirmada com sucesso." : "Esta retirada já foi confirmada ou não existe.";
            } else $erro = "Erro ao confirmar retirada: " . $stmt->error;
            $stmt->close();
        } else $erro = "Erro na preparação da consulta: " . $conexao->error;
    }
}

// Processar Devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'devolucao') {
    $emprestimo_id = $_POST['emprestimo_id_devolucao'] ?? '';

    if (!$emprestimo_id) $erro = "Selecione a chave que está sendo devolvida.";
    else {
        $stmt = $conexao->prepare("
            UPDATE emprestimo 
            SET hora_data_devolucao = NOW()
            WHERE id_emprestimo = ? AND hora_data_devolucao IS NULL
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $emprestimo_id);
            if ($stmt->execute()) {
                $mensagem = $stmt->affected_rows > 0 ? "Devolução registrada com sucesso." : "Não foi encontrada retirada ativa para essa chave.";
            } else $erro = "Erro ao registrar devolução: " . $stmt->error;
            $stmt->close();
        } else $erro = "Erro na preparação da consulta: " . $conexao->error;
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
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { background: #f0f4f8; font-family: 'Inter', sans-serif; }
  .card { background:white; border-radius:1rem; box-shadow:0 10px 25px rgba(0,0,0,0.1); }
  .select-custom { border:1px solid #cbd5e0; border-radius:0.5rem; padding:0.5rem; transition:0.3s; }
  .select-custom:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.2); }
  .btn-primary { background:#3b82f6; color:white; padding:0.5rem 1rem; border-radius:0.5rem; font-weight:600; transition:0.3s; }
  .btn-primary:hover { background:#2563eb; }
</style>
</head>
<body class="flex flex-col min-h-screen">

<?php include '../Includes/header.php'; ?>

<div class="flex justify-end p-4">
  <form action="../PHP/logout.php" method="post">
    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700">Sair</button>
  </form>
</div>

<?php if($mensagem): ?>
<div class="mx-auto max-w-4xl bg-green-100 text-green-800 p-4 rounded mb-4 shadow"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>

<?php if($erro): ?>
<div class="mx-auto max-w-4xl bg-red-100 text-red-800 p-4 rounded mb-4 shadow"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<!-- Retirada -->
<div class="max-w-4xl mx-auto card p-6 mb-6">
  <h2 class="text-2xl font-semibold mb-4 text-gray-700">Retirada de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="retirada" />
    <div class="grid md:grid-cols-3 gap-4">
      <div>
        <label class="block mb-1 font-medium text-gray-600">Chave Agendada</label>
        <select name="emprestimo_id" required class="w-full select-custom">
          <option value="">--Selecione a chave--</option>
          <?php foreach($chaves as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>" <?= (isset($_POST['emprestimo_id']) && $_POST['emprestimo_id']==$c['id'])?'selected':'' ?>>
            <?= htmlspecialchars($c['texto']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium text-gray-600">Categoria</label>
        <select name="categoria" required class="w-full select-custom">
          <option value="">--Selecione--</option>
          <option value="aluno" <?= (isset($_POST['categoria']) && $_POST['categoria']=='aluno')?'selected':'' ?>>Aluno</option>
          <option value="funcionario" <?= (isset($_POST['categoria']) && $_POST['categoria']=='funcionario')?'selected':'' ?>>Funcionário</option>
          <option value="professor" <?= (isset($_POST['categoria']) && $_POST['categoria']=='professor')?'selected':'' ?>>Professor</option>
        </select>
      </div>
      <div class="flex items-end">
        <button type="submit" class="btn-primary w-full">Confirmar Retirada</button>
      </div>
    </div>
  </form>
</div>

<!-- Devolução -->
<div class="max-w-4xl mx-auto card p-6">
  <h2 class="text-2xl font-semibold mb-4 text-gray-700">Devolução de Chaves</h2>
  <form method="post" action="">
    <input type="hidden" name="acao" value="devolucao" />
    <div class="grid md:grid-cols-2 gap-4 items-end">
      <div>
        <label class="block mb-1 font-medium text-gray-600">Chave para Devolução</label>
        <select name="emprestimo_id_devolucao" required class="w-full select-custom">
          <option value="">--Selecione a chave--</option>
          <?php foreach($chavesRetiradas as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['texto']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <button type="submit" class="btn-primary w-full">Confirmar Devolução</button>
      </div>
    </div>
  </form>
</div>

<div class="flex-grow"></div>

<footer class="bg-gray-800 text-white py-6 mt-8">
  <div class="max-w-7xl mx-auto text-center">
    <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
  </div>
</footer>

</body>
</html>
