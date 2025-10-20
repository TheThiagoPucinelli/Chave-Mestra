<?php
include_once __DIR__ . '/../BD/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cpfUsuario = $_SESSION['cpf'] ?? null;

$emprestimosPendentes = [];

if ($cpfUsuario) {
    $sql = "
        SELECT e.id_emprestimo, c.nome AS titulo, 
               e.data_inicio_reserva,
               e.data_fim_reserva,
               e.hora_data_retirada,
               e.hora_data_devolucao
        FROM emprestimo e
        JOIN chave c ON e.id_chave = c.id_chave
        WHERE e.cpf_solicitante = ?
          AND (e.hora_data_retirada IS NULL OR (e.hora_data_retirada IS NOT NULL AND e.hora_data_devolucao IS NULL))
        ORDER BY e.data_fim_reserva ASC
    ";

    if ($stmt = $conexao->prepare($sql)) {
        $stmt->bind_param("s", $cpfUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $emprestimosPendentes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        echo '<p class="text-red-500">Erro na consulta ao banco: ' . $conexao->error . '</p>';
    }
}

// Exibindo as notificações
if (empty($emprestimosPendentes)) {
    echo '<p>Você não possui chaves pendentes para buscar ou devolver.</p>';
} else {
    echo '<ul class="space-y-2">';
    foreach ($emprestimosPendentes as $emprestimo) {
        if (is_null($emprestimo['hora_data_retirada'])) {
            // Ainda não retirou -> precisa buscar
            $statusMsg = "Buscar a chave a partir de:";
            $dataHora = $emprestimo['data_inicio_reserva'];

            // Se quiser, pode mostrar o horário previsto para retirada (se data_inicio_reserva for só data, e hora_data_retirada for NULL)
            $dataHoraExibir = date('d/m/Y', strtotime($dataHora));
        } elseif (!is_null($emprestimo['hora_data_retirada']) && is_null($emprestimo['hora_data_devolucao'])) {
            // Retirou, mas não devolveu -> precisa devolver
            $statusMsg = "Devolver até:";
            $dataHora = $emprestimo['data_fim_reserva'];

            $dataHoraExibir = date('d/m/Y', strtotime($dataHora));
        } else {
            continue;
        }

        echo '<li class="p-3 bg-yellow-100 text-yellow-900 rounded shadow">';
        echo '<strong>' . htmlspecialchars($emprestimo['titulo']) . '</strong>: ' . $statusMsg . ' ' . $dataHoraExibir;

        // Se quiser adicionar hora da retirada e devolução, só descomentar abaixo:

        /*
        if ($emprestimo['hora_data_retirada']) {
            echo '<br>Hora da retirada: ' . date('d/m/Y H:i', strtotime($emprestimo['hora_data_retirada']));
        }
        if ($emprestimo['hora_data_devolucao']) {
            echo '<br>Hora da devolução: ' . date('d/m/Y H:i', strtotime($emprestimo['hora_data_devolucao']));
        }
        */

        echo '</li>';
    }
    echo '</ul>';
}
?>





<!-- Resto do seu HTML e scripts -->



<header class="fixed top-0 left-0 w-full bg-white/90 backdrop-blur shadow-md z-50">
  <style>
    /* === Logo Animada === */
    .logo-animada {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, #0098e3, #00bfff);
      mask: url('../IMG/d2.svg') center/contain no-repeat;
      -webkit-mask: url('../IMG/d2.svg') center/contain no-repeat;
      animation: corLoop 6s ease-in-out infinite;
      transition: transform 0.3s ease;
    }

    .logo-animada:hover {
      transform: scale(1.1) rotate(5deg);
    }

    @keyframes corLoop {
      0% { background: #0098e3; }
      25% { background: #007acc; }
      50% { background: #00bfff; }
      75% { background: #66d9ff; }
      100% { background: #0098e3; }
    }

    /* === Menu Hambúrguer === */
    .hamburger span {
      transition: all 0.3s ease;
    }

    #menu-toggle:checked + label .hamburger span:nth-child(1) {
      transform: rotate(45deg) translateY(8px);
    }

    #menu-toggle:checked + label .hamburger span:nth-child(2) {
      opacity: 0;
    }

    #menu-toggle:checked + label .hamburger span:nth-child(3) {
      transform: rotate(-45deg) translateY(-8px);
    }

    /* === Links de Navegação === */
    .nav-link {
      display: block;
      padding: 0.5rem 1rem;
      font-weight: 500;
      color: #374151;
      border-radius: 6px;
      transition: all 0.3s ease;
      position: relative;
    }

    .nav-link:hover {
      color: white;
      background: #0098e3;
    }

    .nav-link::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0%;
      height: 2px;
      background: #0098e3;
      transition: width 0.3s ease;
    }

    .nav-link:hover::after {
      width: 100%;
    }

    /* === Perfil === */
    .profile-badge {
      width: 40px;
      height: 40px;
      border-radius: 9999px;
      background: #0098e3;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .profile-badge:hover {
      transform: scale(1.1);
      background: #007acc;
    }

    /* === Botão Sair === */
    .btn-logout {
      background: #ef4444;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: 0.3s;
      margin-left: 0.75rem;
    }

    .btn-logout:hover {
      background: #b91c1c;
      transform: scale(1.05);
    }
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
    <nav class="absolute top-full left-0 w-full bg-white shadow-md hidden peer-checked:flex 
                flex-col space-y-2 px-6 py-4 
                md:static md:w-auto md:bg-transparent md:shadow-none 
                md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0">
      <?php if ($isAdmin): ?>
        <a href="../Pages/index.php" class="nav-link">Início</a>
        <a href="../Pages/cadastro_chave.php" class="nav-link">Cadastro de Chaves</a>
      <?php endif; ?>
      <a href="../Pages/contato.php" class="nav-link">Contato</a>
      <a href="../Pages/agendar.php" class="nav-link">Agendar</a>
    </nav>

    

    <!-- Perfil, Sair, Notificações -->
    <div class="flex items-center ml-4 md:ml-6">
      <a href="../Pages/perfil.php">
        <div class="profile-badge">
          <?php echo strtoupper(substr($_SESSION['nome'] ?? 'U', 0, 1)); ?>
        </div>
      </a>
       <!-- Notificações -->
       <div class="relative ml-4">
        <button
          id="btnNotificacao"
          class="relative text-gray-700 hover:text-blue-600 transition-transform transform hover:scale-110 focus:outline-none"
          aria-label="Notificações"
          type="button"
        >
          <!-- Ícone sino -->
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 00-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0h6z" />
          </svg>

          <?php if (!empty($emprestimosPendentes)): ?>
            <span class="absolute top-0 right-0 block w-2.5 h-2.5 bg-red-600 rounded-full ring-2 ring-white"></span>
          <?php endif; ?>
        </button>

        <!-- Dropdown de Notificações -->
        <div
          id="notificacaoDropdown"
          class="hidden absolute right-0 mt-2 w-72 bg-white border border-gray-300 rounded-md shadow-lg z-50 p-4 text-gray-700"
        >
          <?php if (!empty($emprestimosPendentes)): ?>
            <h4 class="font-semibold mb-2">Empréstimos Pendentes</h4>
            <ul class="max-h-48 overflow-y-auto space-y-2">
              <?php foreach ($emprestimosPendentes as $emprestimo): ?>
                <li class="border-b border-gray-200 pb-1">
                  <p><strong><?= htmlspecialchars($emprestimo['titulo']) ?></strong></p>
                  <p class="text-sm text-gray-500">
    <?php
        if (is_null($emprestimo['hora_data_retirada'])) {
            echo "Buscar a chave a partir de: " . date('d/m/Y, H:i:s', strtotime($emprestimo['data_inicio_reserva']));
        } elseif (!is_null($emprestimo['hora_data_retirada']) && is_null($emprestimo['hora_data_devolucao'])) {
            echo "Devolver até: " . date('d/m/Y, H:i:s', strtotime($emprestimo['data_fim_reserva']));
        }
    ?>
</p>


                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-center text-gray-500">Não há notificações.</p>
          <?php endif; ?>
        </div>
      </div>

      <form action="../PHP/logout.php" method="post" class="ml-2">
        <button type="submit" class="btn-logout">Sair</button>
      </form>

     
    </div>
  </div>
</header>

<!-- Script: Dropdown de Notificações -->
<script>
  const btnNotificacao = document.getElementById('btnNotificacao');
  const notificacaoDropdown = document.getElementById('notificacaoDropdown');

  btnNotificacao.addEventListener('click', (e) => {
    e.stopPropagation(); // Evita conflito com o click fora
    notificacaoDropdown.classList.toggle('hidden');
  });

  window.addEventListener('click', (event) => {
    if (!btnNotificacao.contains(event.target) && !notificacaoDropdown.contains(event.target)) {
      notificacaoDropdown.classList.add('hidden');
    }
  });
</script>
