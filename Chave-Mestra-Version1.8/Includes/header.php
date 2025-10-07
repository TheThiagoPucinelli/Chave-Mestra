<header class="fixed top-0 left-0 w-full bg-white/90 backdrop-blur shadow-md z-50">
  <style>
    /* --- Logo animado --- */
    .logo-animada { width: 70px; height: 70px; background: linear-gradient(135deg, #0098e3, #00bfff); mask: url('../IMG/d2.svg') center/contain no-repeat; -webkit-mask: url('../IMG/d2.svg') center/contain no-repeat; animation: corLoop 6s ease-in-out infinite; transition: transform 0.3s ease; }
    .logo-animada:hover { transform: scale(1.1) rotate(5deg); }
    @keyframes corLoop { 0% { background: #0098e3; } 25% { background: #007acc; } 50% { background: #00bfff; } 75% { background: #66d9ff; } 100% { background: #0098e3; } }

    /* --- Ícone hambúrguer animado --- */
    .hamburger span { transition: all 0.3s ease; }
    #menu-toggle:checked + label .hamburger span:nth-child(1) { transform: rotate(45deg) translateY(8px); }
    #menu-toggle:checked + label .hamburger span:nth-child(2) { opacity: 0; }
    #menu-toggle:checked + label .hamburger span:nth-child(3) { transform: rotate(-45deg) translateY(-8px); }

    /* --- Links do menu --- */
    .nav-link { display: block; padding: 0.5rem 1rem; font-weight: 500; color: #374151; border-radius: 6px; transition: all 0.3s ease; position: relative; }
    .nav-link:hover { color: white; background: #0098e3; }
    .nav-link::after { content: ""; position: absolute; left: 0; bottom: 0; width: 0%; height: 2px; background: #0098e3; transition: width 0.3s ease; }
    .nav-link:hover::after { width: 100%; }

    /* --- Bola de perfil --- */
    .profile-badge { width: 40px; height: 40px; border-radius: 9999px; background: #0098e3; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; }
    .profile-badge:hover { transform: scale(1.1); background: #007acc; }

    /* --- Botão Sair --- */
    .btn-logout { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; transition: 0.3s; margin-left: 0.75rem; }
    .btn-logout:hover { background: #b91c1c; transform: scale(1.05); }
  </style>

  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">

    <!-- Logo -->
    <a href="../Pages/agendar.php" class="flex items-center">
      <div class="logo-animada"></div>
    </a>

    <!-- Toggle menu -->
    <input type="checkbox" id="menu-toggle" class="hidden peer" />
    <label for="menu-toggle" class="cursor-pointer md:hidden block">
      <div class="hamburger space-y-1.5">
        <span class="block w-7 h-0.5 bg-gray-800"></span>
        <span class="block w-7 h-0.5 bg-gray-800"></span>
        <span class="block w-7 h-0.5 bg-gray-800"></span>
      </div>
    </label>

    <!-- Navegação -->
    <nav class="
      absolute top-full left-0 w-full bg-white shadow-md 
      flex-col space-y-2 px-6 py-4
      hidden peer-checked:flex 
      md:static md:w-auto md:bg-transparent md:shadow-none 
      md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0
    ">
      <?php if ($isAdmin): ?>
        <a href="../Pages/index.php" class="nav-link">Início</a>
      <?php endif; ?>
      <a href="../Pages/contato.php" class="nav-link">Contato</a>
       <?php if ($isAdmin): ?>
      <a href="../Pages/cadastro_chave.php" class="nav-link">Cadastro de Chaves</a>
      <?php endif; ?>
      <a href="../Pages/agendar.php" class="nav-link">Agendar</a>
    </nav>

    <!-- Perfil e Sair -->
    <div class="flex items-center ml-4 md:ml-6">
      <a href="../Pages/perfil.php">
        <div class="profile-badge">
          <?php echo strtoupper(substr($_SESSION['nome'] ?? 'U', 0, 1)); ?>
        </div>
      </a>
      <form action="../PHP/logout.php" method="post" class="ml-2">
        <button type="submit" class="btn-logout">Sair</button>
      </form>
    </div>

  </div>
</header>
