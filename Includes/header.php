
<header class="relative top-0 left-0 w-full bg-white shadow z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    <a href="../Pages/index.php" class="flex items-center">
      <img src="../IMG/CM (5).png" alt="Logo" class="h-20 w-auto" /> 
    </a>

    <input type="checkbox" id="menu-toggle" class="hidden peer" />
    <label for="menu-toggle" class="cursor-pointer md:hidden block">
      <div class="space-y-1.5">
        <span class="block w-6 h-0.5 bg-gray-800"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
      </div>
    </label>

    <nav class="absolute top-full left-0 w-full bg-white shadow-md 
                flex-col space-y-2 px-6 py-4
                hidden peer-checked:flex 
                md:static md:w-auto md:bg-transparent md:shadow-none 
                md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0">
      <a href="../Pages/contato.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Contato</a>
      <a href="../Pages/cadastro_chave.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Cadastro de Chaves</a>
      <a href="../Pages/registro.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Disponibilidade</a>
      <a href="../Pages/agendar_chave.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Agendar</a>
    </nav>
  </div>
</header>
