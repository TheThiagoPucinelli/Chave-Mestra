<?php include __DIR__ . '/../PHP/verifica_login.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <link rel="shortcut icon" href="CM.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chave Mestra - Contato</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="../IMG/cmpage.png">
</head>
<body class="flex flex-col min-h-screen font-sans bg-gradient-to-b from-blue-100 via-white to-gray-100">

  <?php include '../Includes/header.php'; ?>
  <br><br><br>

  <!-- Hero -->
  <section class="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white py-24 text-center shadow-md">
    <div class="max-w-4xl mx-auto px-6 relative z-10">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Fale Conosco</h1>
      <p class="text-lg md:text-xl text-blue-100">Nossa equipe está pronta para ajudar. Envie sua mensagem e responderemos o quanto antes.</p>
    </div>
    <div class="absolute inset-0 bg-[url('../IMG/cmpage.png')] bg-center bg-cover opacity-10"></div>
  </section>

  <!-- Formulário -->
  <section class="flex-grow py-16 px-6">
    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-10 border border-gray-100
                transition-transform transform hover:-translate-y-2 hover:shadow-2xl">

      <!-- Título + Subtítulo -->
      <div class="text-center mb-12">
        <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-black to-indigo-300 bg-clip-text text-transparent drop-shadow-sm">
          Contato
        </h2>
        <p class="mt-3 text-gray-600 text-lg max-w-2xl mx-auto">
          Tem dúvidas, sugestões ou precisa de ajuda? Preencha o formulário abaixo e retornaremos em breve.
        </p>
        <div class="mt-4 w-24 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 mx-auto rounded-full"></div>
      </div>

      <!-- Form -->
      <form action="" method="POST" class="space-y-6">
        <div>
          <label for="name" class="block text-gray-700 font-semibold mb-2">Nome</label>
          <input type="text" id="name" name="name" placeholder="Digite seu nome"
            class="w-full p-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>

        <div>
          <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail</label>
          <input type="email" id="email" name="email" placeholder="Digite seu e-mail"
            class="w-full p-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>

        <div>
          <label for="message" class="block text-gray-700 font-semibold mb-2">Mensagem</label>
          <textarea id="message" name="message" rows="5" placeholder="Digite sua mensagem"
            class="w-full p-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required></textarea>
        </div>

        <div class="flex justify-center">
          <button type="submit"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 px-10 rounded-xl shadow-lg hover:from-blue-700 hover:to-blue-800 hover:shadow-xl transition transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M5 5h14" />
            </svg>
            Enviar Mensagem
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- Toast de Sessão Expirada -->
  <div id="toast" class="hidden fixed bottom-5 right-5 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg animate-bounce z-50">
    Sua sessão expirou! Faça login novamente.
  </div>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400 py-6 mt-auto">
    <div class="max-w-7xl mx-auto text-center text-sm">
      <p>&copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. | 
        <a href="../Pages/contato.php" class="text-blue-400 hover:text-white transition">Contato</a>
      </p>
    </div>
  </footer>

  <script>
    // Toast sessão expirada
    setTimeout(() => {
      const toast = document.getElementById("toast");
      toast.classList.remove("hidden");
      setTimeout(() => {
        window.location.href = '../PHP/login.php';
      }, 2500);
    }, <?= $tempoRestante ?>);
  </script>

</body>
</html>
