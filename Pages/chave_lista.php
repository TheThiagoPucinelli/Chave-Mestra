<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <?php include '../Includes/header.php'; ?>


    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../IMG/cmpage.png">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <title>Chave Mestra</title>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Conteúdo principal -->
    <main class="flex-grow container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-center text-blue-800 mb-8">Lista de Chaves</h1>

        <div class="overflow-x-auto shadow-lg rounded-lg bg-white">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-blue-600 text-white uppercase text-xs">
                    <tr>
                        <th scope="col" class="px-6 py-4">ID</th>
                        <th scope="col" class="px-6 py-4">Local</th>
                        <th scope="col" class="px-6 py-4">Descrição</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4">Prédio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">

                <?php
                include_once '../BD/conexao.php';

                $sql = "SELECT id_chave, local, descricao, status, predio FROM chave";
                $result = $conexao->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-100'>
                                <td class='px-6 py-4'>" . htmlspecialchars($row["id_chave"]) . "</td>
                                <td class='px-6 py-4'>" . htmlspecialchars($row["local"]) . "</td>
                                <td class='px-6 py-4'>" . htmlspecialchars($row["descricao"]) . "</td>
                                <td class='px-6 py-4'>" . htmlspecialchars($row["status"]) . "</td>
                                <td class='px-6 py-4'>" . htmlspecialchars($row["predio"]) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center px-6 py-4 text-red-500'>Nenhuma chave disponível.</td></tr>";
                }

                $conexao->close();
                ?>

                </tbody>
            </table>
        </div>
    </main>

    
    <!-- Footer -->
<footer class="bg-gray-900 text-gray-400 py-6 mt-auto">
  <div class="max-w-7xl mx-auto text-center text-sm">
    <p>&copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. | 
      <a href="../Pages/contato.php" class="text-blue-400 hover:text-white transition">Contato</a>
    </p>
  </div>
</footer>

</body>
</html>
