<header class="relative top-0 left-0 w-full bg-white shadow z-50">
  <style>
    /* Estilo para o logo animado */
    .logo-animada {
      width: 80px; /* largura fixa */
      height: 80px; /* altura fixa */
      background: #0098e3; /* cor inicial de fundo */
      /* Máscara para usar a imagem SVG como forma do logo */
      mask: url('../IMG/d2.svg') center/contain no-repeat;
      -webkit-mask: url('../IMG/d2.svg') center/contain no-repeat; /* para Safari */
      animation: corLoop 6s ease-in-out infinite; /* animação de transição de cores em loop infinito */
      position: relative;
      z-index: 9999; /* garante que o logo fique acima dos outros elementos */
    }

    /* Animação que altera a cor do fundo do logo suavemente */
    @keyframes corLoop {
      0%   { background: #0098e3; }
      25%  { background: #007acc; }
      50%  { background: #00bfff; }
      75%  { background: #66d9ff; }
      100% { background: #0098e3; }
    }
  </style>

  <!-- Container principal do header com espaçamento e layout flex -->
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    
    <!-- Link para a página inicial contendo o logo animado -->
    <a href="../Pages/index.php" class="flex items-center">
      <div class="logo-animada"></div>
    </a>

    <!-- Checkbox escondido que controla o menu móvel -->
    <input type="checkbox" id="menu-toggle" class="hidden peer" />
    
    <!-- Label para o checkbox, que funciona como botão do menu hambúrguer (apenas visível em telas pequenas) -->
    <label for="menu-toggle" class="cursor-pointer md:hidden block">
      <!-- Três linhas que formam o ícone hambúrguer -->
      <div class="space-y-1.5">
        <span class="block w-6 h-0.5 bg-gray-800"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
      </div>
    </label>

    <!-- Navegação principal -->
    <nav class="
      absolute top-full left-0 w-full bg-white shadow-md 
      flex-col space-y-2 px-6 py-4
      hidden peer-checked:flex 
      md:static md:w-auto md:bg-transparent md:shadow-none 
      md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0
    ">
      <!-- Links do menu -->
      <a href="../Pages/index.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Inicio</a>
      <a href="../Pages/contato.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Contato</a>
      <a href="../Pages/cadastro_chave.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Cadastro de Chaves</a>
      <a href="../Pages/registro.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Disponibilidade</a>
      <a href="../Pages/agendar_chave.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Agendar</a>
    </nav>
  </div>
</header>
