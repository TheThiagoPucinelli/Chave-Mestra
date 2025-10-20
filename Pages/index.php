<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$mensagem = "";
$erro = "";

// Buscar chaves agendadas
$chaves = [];
$sqlChaves = "
    SELECT e.id_emprestimo, c.nome, c.numero_identificacao, u.nome AS nome_usuario, e.categoria
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
        $chaves[] = ['id' => $row['id_emprestimo'], 'texto' => $texto];
    }
}

// Buscar chaves retiradas
$chavesRetiradas = [];
$sqlChavesRetiradas = "
    SELECT e.id_emprestimo, c.nome, c.numero_identificacao, u.nome AS nome_usuario
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    JOIN usuario u ON e.cpf_solicitante = u.cpf
    WHERE e.hora_data_retirada IS NOT NULL AND e.hora_data_devolucao IS NULL
    ORDER BY u.nome, c.nome
";
$resultChavesRetiradas = $conexao->query($sqlChavesRetiradas);
if ($resultChavesRetiradas) {
    while ($row = $resultChavesRetiradas->fetch_assoc()) {
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ") - Retirado por: " . $row['nome_usuario'];
        $chavesRetiradas[] = ['id' => $row['id_emprestimo'], 'texto' => $texto];
    }
}

// Função para encontrar o ID com base no texto
function encontrarIdPorTexto($lista, $texto) {
    foreach ($lista as $item) {
        if ($item['texto'] === $texto) {
            return $item['id'];
        }
    }
    return null;
}

// Processar retirada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'retirada') {
    $texto_chave = $_POST['chave_texto'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $cpf_adm = $_SESSION['cpf'] ?? '';
    $emprestimo_id = encontrarIdPorTexto($chaves, $texto_chave);

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
            $stmt->execute();
            $mensagem = $stmt->affected_rows > 0 ? "Retirada da chave confirmada com sucesso." : "Esta retirada já foi confirmada ou não existe.";
            $stmt->close();
        } else $erro = "Erro na preparação da consulta.";
    }
}

// Processar devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'devolucao') {
    $texto_chave_devolucao = $_POST['chave_texto_devolucao'] ?? '';
    $emprestimo_id = encontrarIdPorTexto($chavesRetiradas, $texto_chave_devolucao);

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
            $stmt->execute();
            $mensagem = $stmt->affected_rows > 0 ? "Devolução registrada com sucesso." : "Não foi encontrada retirada ativa para essa chave.";
            $stmt->close();
        } else $erro = "Erro na preparação da consulta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciamento de Chaves</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .card { background:white; border-radius:1rem; padding:2rem; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
    .btn-primary { background:#3b82f6; color:white; padding:0.75rem 1.5rem; border-radius:0.5rem; font-weight:600; }
    .btn-primary:hover { background:#2563eb; transform:scale(1.05); }
    input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
  </style>
</head>
<body class="bg-gray-100 font-sans flex flex-col min-h-screen">

<?php include '../Includes/header.php'; ?>
<br><br><br><br>

<br><br><br><br><br><br><br>
<main class="flex-grow">

<!-- Mensagens -->
<?php if($mensagem): ?>
<div class="mx-auto max-w-4xl bg-green-100 text-green-800 p-4 rounded mb-6 shadow"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if($erro): ?>
<div class="mx-auto max-w-4xl bg-red-100 text-red-800 p-4 rounded mb-6 shadow"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="max-w-4xl mx-auto card space-y-8">

 <!-- Retirada -->
<div class="mb-12">
  <h2 class="text-2xl font-bold mb-4 text-gray-800">Retirada de Chaves</h2>
  <form method="post">
    <input type="hidden" name="acao" value="retirada">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label for="chave_retirada" class="block font-medium text-gray-700 mb-1">Chave Agendada</label>
        <input list="chaves_agendadas" id="chave_retirada" name="chave_texto" required
               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400"
               placeholder="Digite ou selecione a chave...">
        <datalist id="chaves_agendadas">
          <?php foreach($chaves as $c): ?>
            <option value="<?= htmlspecialchars($c['texto']) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="flex items-end">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow w-full">
          Confirmar Retirada
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Devolução -->
<div>
  <h2 class="text-2xl font-bold mb-4 text-gray-800">Devolução de Chaves</h2>
  <form method="post">
    <input type="hidden" name="acao" value="devolucao">
    <div class="grid md:grid-cols-2 gap-4 items-end">
      <div>
        <label for="chave_devolucao" class="block font-medium text-gray-700 mb-1">Chave para Devolução</label>
        <input list="chaves_devolucao" id="chave_devolucao" name="chave_texto_devolucao" required
               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400"
               placeholder="Digite ou selecione a chave retirada...">
        <datalist id="chaves_devolucao">
          <?php foreach($chavesRetiradas as $c): ?>
            <option value="<?= htmlspecialchars($c['texto']) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div>
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow w-full">
          Confirmar Devolução
        </button>
      </div>
    </div>
  </form>
</div>


</main>

<footer class="bg-gray-900 text-gray-400 py-3">
  <div class="text-center text-sm mb-4">
    &copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. | 
    <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a>
  </div>
  <div class="flex justify-center space-x-6">
    <a href="https://github.com/TheThiagoPucinelli" target="_blank" aria-label="GitHub" class="hover:text-white transition-colors duration-300">
      <!-- Ícone GitHub SVG -->
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.263.82-.582 0-.288-.01-1.05-.015-2.06-3.338.726-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.73.083-.73 1.205.085 1.838 1.237 1.838 1.237 1.07 1.835 2.807 1.305 3.492.997.108-.775.418-1.305.76-1.605-2.665-.3-5.466-1.334-5.466-5.932 0-1.31.468-2.38 1.236-3.22-.124-.303-.536-1.523.117-3.176 0 0 1.008-.322 3.3 1.23a11.5 11.5 0 0 1 3-.404c1.02.005 2.045.138 3 .404 2.29-1.552 3.297-1.23 3.297-1.23.655 1.653.243 2.873.12 3.176.77.84 1.235 1.91 1.235 3.22 0 4.61-2.804 5.628-5.475 5.922.43.37.823 1.103.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .32.217.698.825.58C20.565 21.796 24 17.297 24 12c0-6.63-5.37-12-12-12z"/>
      </svg>
    </a>
    <a href="https://br.linkedin.com/in/thiagopucinelli" target="_blank" aria-label="LinkedIn" class="hover:text-white transition-colors duration-300">
      <!-- Ícone LinkedIn SVG -->
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M4.98 3.5C3.34 3.5 2 4.82 2 6.45c0 1.56 1.27 2.94 3.05 2.94h.03c1.7 0 3.04-1.38 3.04-2.94-.03-1.63-1.35-2.95-3.14-2.95zM2.4 21.5h5.17V9H2.4v12.5zM9.57 9h4.95v1.7h.07c.69-1.3 2.38-2.67 4.9-2.67 5.24 0 6.2 3.45 6.2 7.93v9.27h-5.17v-8.23c0-1.97-.04-4.5-2.74-4.5-2.75 0-3.17 2.14-3.17 4.36v8.37H9.57V9z"/>
      </svg>
    </a>
  </div>
</footer>



</body>
</html>
