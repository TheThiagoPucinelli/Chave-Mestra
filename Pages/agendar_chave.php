<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$mensagem = "";
$erro = "";

// CPF do usuário logado
$cpfLogado = $_SESSION['cpf'] ?? '';

// Verifica se o CPF existe na tabela solicitante
$verifica = $conexao->prepare("SELECT cpf FROM solicitante WHERE cpf = ?");
$verifica->bind_param("s", $cpfLogado);
$verifica->execute();
$verifica->store_result();

if ($verifica->num_rows == 0) {
    // Se não existir, cria automaticamente
    $insere = $conexao->prepare("INSERT INTO solicitante (cpf) VALUES (?)");
    $insere->bind_param("s", $cpfLogado);
    $insere->execute();
    $insere->close();
}
$verifica->close();

// Buscar chaves disponíveis
$chaves = [];
$sql = "SELECT id_chave, nome, numero_identificacao, descricao FROM chave WHERE status = 0 ORDER BY nome";
$res = $conexao->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $descricao = $row['descricao'] ? " - " . $row['descricao'] : "";
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ")" . $descricao;
        $chaves[] = ['id' => $row['id_chave'], 'texto' => $texto];
    }
}

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_chave = isset($_POST['chave']) ? (int) $_POST['chave'] : 0;
    $data_inicio_raw = $_POST['data_inicio'] ?? '';
    $data_fim_raw = $_POST['data_fim'] ?? '';

    if (!$id_chave || !$cpfLogado || !$data_inicio_raw || !$data_fim_raw) {
        $erro = "Preencha todos os campos!";
    } else {
        $data_inicio = date('Y-m-d H:i:s', strtotime($data_inicio_raw));
        $data_fim = date('Y-m-d H:i:s', strtotime($data_fim_raw));

        $stmt = $conexao->prepare("
            INSERT INTO emprestimo (data_reserva, data_inicio_reserva, data_fim_reserva, id_chave, cpf_solicitante)
            VALUES (NOW(), ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssis", $data_inicio, $data_fim, $id_chave, $cpfLogado);
            if ($stmt->execute()) {
                $mensagem = "Agendamento realizado com sucesso!";
            } else {
                $erro = "Erro ao agendar: " . $stmt->error;
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
  <title>Agendar Chave</title>
  <link rel="stylesheet" href="../css/index.css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-900 min-h-screen flex flex-col">

  <?php include '../Includes/header.php'; ?>

  <main class="flex-grow flex justify-center items-start py-8 px-4">
    <div class="w-full max-w-xl bg-white shadow-md p-6 rounded-md
                sm:p-8
                mx-0
                sm:mx-auto">
      <h2 class="text-2xl font-semibold mb-6 text-center sm:text-left text-white-900">Agendamento de Chave</h2>

      <?php if ($mensagem): ?>
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($mensagem) ?></div>
      <?php endif; ?>

      <?php if ($erro): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <form method="post" action="" class="flex flex-col gap-4">

        <label class="block font-medium" for="chave">Chave:</label>
        <select id="chave" name="chave" required class="w-full p-3 border rounded text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
          <option value="">-- Selecione a chave --</option>
          <?php foreach ($chaves as $ch): ?>
            <option value="<?= $ch['id'] ?>"><?= htmlspecialchars($ch['texto']) ?></option>
          <?php endforeach; ?>
        </select>

        <input type="hidden" name="cpf" value="<?= htmlspecialchars($cpfLogado) ?>">

        <label class="block font-medium" for="data_inicio">Data e Hora Início:</label>
        <input id="data_inicio" type="datetime-local" name="data_inicio" required
          class="w-full p-3 border rounded text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600" />

        <label class="block font-medium" for="data_fim">Data e Hora Fim:</label>
        <input id="data_fim" type="datetime-local" name="data_fim" required
          class="w-full p-3 border rounded text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600" />

        <button type="submit"
          class="mt-4 bg-blue-600 text-white font-semibold px-6 py-3 rounded hover:bg-blue-700 transition-colors">
          Agendar
        </button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-6 mt-auto">
    <div class="max-w-7xl mx-auto text-center px-4">
      <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
  </footer>

</body>
</html>
