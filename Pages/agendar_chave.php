<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$mensagem = "";
$erro = "";

// CPF do usuário logado (vindo da sessão)
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

// Buscar chaves disponíveis com descrição
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
        // Converter do formato datetime-local (ex: 2025-07-08T14:00) para formato MySQL datetime
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
  <link rel="stylesheet" href="../css/index.css" />
  <title>Agendar Chave</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

  <?php include '../Includes/header.php'; ?>

  <br><br><br>
  <div class="max-w-xl mx-auto bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-semibold mb-4">Agendamento de Chave</h2>

    <?php if ($mensagem): ?>
      <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <?php if ($erro): ?>
      <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

   <form method="post" action="">
  <label class="block mb-2 font-medium">Chave:</label>
  <select name="chave" required class="w-full p-2 border rounded mb-4">
    <option value="">-- Selecione a chave --</option>
    <?php foreach ($chaves as $ch): ?>
      <option value="<?= $ch['id'] ?>"><?= htmlspecialchars($ch['texto']) ?></option>
    <?php endforeach; ?>
  </select>

  <!-- Campo oculto com o CPF do usuário logado -->
  <input type="hidden" name="cpf" value="<?= htmlspecialchars($cpfLogado) ?>">

  <label class="block mb-2 font-medium">Data e Hora Início:</label>
  <input type="datetime-local" name="data_inicio" required class="w-full p-2 border rounded mb-4">

  <label class="block mb-2 font-medium">Data e Hora Fim:</label>
  <input type="datetime-local" name="data_fim" required class="w-full p-2 border rounded mb-6">

  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Agendar</button>
</form>

  </div>

  <br><br><br><br><br><br><br><br><br>

 <!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>
</body>
</html>
