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


    <!-- Header fixo -->
    <main>
        <header class="relative top-0 left-0 w-full bg-white shadow z-50">
          <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <!-- Logo -->
            <a href="/index.html" class="flex items-center">
              <img src="../IMG/CM (5).png" alt="Logo" class="h-20 w-auto" />
            </a>
      
            <!-- Menu Toggle (mobile) -->
            <input type="checkbox" id="menu-toggle" class="hidden peer" />
            <label for="menu-toggle" class="cursor-pointer md:hidden block">
              <div class="space-y-1.5">
                <span class="block w-6 h-0.5 bg-gray-800"></span>
                <span class="block w-6 h-0.5 bg-gray-800"></span>
                <span class="block w-6 h-0.5 bg-gray-800"></span>
              </div>
            </label>
      
            <!-- Menu feito com Tailwind -->
            <nav class="absolute top-full left-0 w-full bg-white shadow-md 
                        flex-col space-y-2 px-6 py-4
                        hidden peer-checked:flex 
                        md:static md:w-auto md:bg-transparent md:shadow-none 
                        md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0">
              <a href="../Pages/contato.html" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Contato</a>
              <a href="../Pages/agenda.html" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Agenda</a>
              <a href="../Pages/registro.html" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Registro</a>
            </nav>
          </div>
        </header>
      </main>




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







    <!-- Footer fixo -->
    <footer class="bg-gray-800 text-white py-6 mt-6">
        <div class="max-w-7xl mx-auto text-center">
            <p>&copy; 2025 Chave Mestra | <a href="/Pages/contato.html" class="text-blue-400 hover:text-white">Contato</a></p>
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
