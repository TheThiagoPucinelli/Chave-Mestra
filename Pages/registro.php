<?php include __DIR__ . '/../PHP/verifica_login.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="CM.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="icon" type="image/png" href="../IMG/cmpage.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Lista de Chaves - Chave Mestra</title>

  <script defer>
    function filtrarChaves() {
      const input = document.getElementById('busca').value.toLowerCase();
      const linhas = document.querySelectorAll('#tabela-chaves tbody tr');
      linhas.forEach(linha => {
        const textoLinha = linha.textContent.toLowerCase();
        linha.style.display = textoLinha.includes(input) ? '' : 'none';
      });
    }
  </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- Header -->
  <?php include '../Includes/header.php'; ?>

  <!-- Conteúdo principal -->
  <main class="flex-grow container mx-auto px-4 py-10 max-w-5xl">
    <h1 class="text-3xl font-bold text-center text-blue-800 mb-8">Lista de Chaves</h1>

    <input type="text" id="busca" onkeyup="filtrarChaves()" placeholder="Buscar por ID, nome, número, descrição, quantidade ou status..."
      class="w-full p-2 border border-gray-300 rounded mb-6" />

    <div class="overflow-x-auto shadow-lg rounded-lg bg-white border border-gray-200">
      <table id="tabela-chaves" class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-blue-600 text-white uppercase text-xs">
          <tr>
            <th scope="col" class="px-6 py-4">ID</th>
            <th scope="col" class="px-6 py-4">Nome</th>
            <th scope="col" class="px-6 py-4">Número Identificação</th>
            <th scope="col" class="px-6 py-4">Descrição</th>
            <th scope="col" class="px-6 py-4">Quantidade</th>
            <th scope="col" class="px-6 py-4">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php
          include_once '../BD/conexao.php';

          // SQL para pegar as chaves com info do empréstimo ativo (se houver)
          $sql = "
            SELECT 
              c.id_chave, c.nome, c.numero_identificacao, c.descricao, c.quantidade,
              e.data_inicio_reserva, e.data_fim_reserva,
              CASE 
                WHEN e.id_emprestimo IS NULL THEN 'disponível'
                ELSE 'indisponível'
              END AS status_dinamico
            FROM chave c
            LEFT JOIN emprestimo e ON c.id_chave = e.id_chave AND e.hora_data_devolucao IS NULL
            GROUP BY c.id_chave
          ";

          $result = $conexao->query($sql);

          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              $statusText = ucfirst($row["status_dinamico"]);

              $statusClass = $statusText === 'Disponível'
                ? "text-green-700 bg-green-100 font-semibold px-2 py-1 rounded"
                : "text-red-700 bg-red-100 font-semibold px-2 py-1 rounded";

              // Montar texto do status com data/hora se indisponível
              if ($statusText === 'Indisponível') {
                $inicio = $row['data_inicio_reserva'] ? date('d/m/Y H:i', strtotime($row['data_inicio_reserva'])) : 'N/A';
                $fim = $row['data_fim_reserva'] ? date('d/m/Y H:i', strtotime($row['data_fim_reserva'])) : 'N/A';
                $statusText .= " (De: $inicio Até: $fim)";
              }

              echo "<tr class='hover:bg-gray-100'>
                      <td class='px-6 py-4'>" . htmlspecialchars($row["id_chave"]) . "</td>
                      <td class='px-6 py-4'>" . htmlspecialchars($row["nome"]) . "</td>
                      <td class='px-6 py-4'>" . htmlspecialchars($row["numero_identificacao"]) . "</td>
                      <td class='px-6 py-4'>" . htmlspecialchars($row["descricao"]) . "</td>
                      <td class='px-6 py-4'>" . htmlspecialchars($row["quantidade"]) . "</td>
                      <td class='px-6 py-4'><span class='{$statusClass}'>" . $statusText . "</span></td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='6' class='text-center px-6 py-4 text-red-500'>Nenhuma chave encontrada.</td></tr>";
          }

          $conexao->close();
          ?>
        </tbody>
      </table>
    </div>
  </main>

 <!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>

</body>
</html>
