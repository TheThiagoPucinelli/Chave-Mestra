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
