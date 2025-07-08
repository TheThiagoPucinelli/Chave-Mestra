<?php include __DIR__ . '/../PHP/verifica_login.php'; ?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="CM.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chave Mestra</title>
    <link rel="stylesheet" href="../CSS/index.css"> 
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../IMG/cmpage.png">
</head>


<?php include '../Includes/header.php'; ?>



    <!-- Área de Contato -->
    <section class="pt-32 max-w-3xl mx-auto px-6 py-10">
        <h2 class="text-2xl font-semibold text-center mb-9">Entre em Contato</h2>

        <!-- Formulário de Contato -->
        <form action="" method="POST" class="max-w-3xl mx-auto bg-white p-8 shadow-lg rounded-lg">
            <div class="mb-6">
                <label for="name" class="block text-gray-700 font-medium mb-2">Nome</label>
                <input type="text" id="name" name="name" placeholder="Digite seu nome" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-6">
                <label for="email" class="block text-gray-700 font-medium mb-2">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-6">
                <label for="message" class="block text-gray-700 font-medium mb-2">Mensagem</label>
                <textarea id="message" name="message" rows="4" placeholder="Digite sua mensagem" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded-md hover:bg-blue-600 transition">Enviar</button>
            </div>
        </form>
    </section>







    <!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-6">
    <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Chave Mestra | <a href="../Pages/contato.php" class="text-blue-400 hover:text-white">Contato</a></p>
    </div>
</footer>

<script>
  setTimeout(() => {
    alert('Sua sessão expirou! Faça Login Novamente!');
    window.location.href = '../PHP/login.php'; // redireciona para login
  }, <?= $tempoRestante ?>);
</script>


</body>
</html>
