<?php
include_once __DIR__ . '/../BD/conexao.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// --- Dados do usuário ---
$cpfUsuario = $_SESSION['cpf'] ?? null;
$nomeUsuario = $_SESSION['nome'] ?? '';
$isAdmin = false;
$isGerente = false;

// --- Inicializar arrays ---
$emprestimosPendentes = [];
$notificacoesAdmin = [];
$notificacoesUsuario = [];
$totalNotificacoes = 0;

// --- Configuração de fuso horário ---
date_default_timezone_set('America/Sao_Paulo');
$agora = new DateTime();

// =====================
// Funções auxiliares
// =====================
function corDoUsuario($cpf) {
    if (!$cpf) return '#3b82f6';
    $hash = crc32($cpf);
    $r = ($hash & 0xFF0000) >> 16;
    $g = ($hash & 0x00FF00) >> 8;
    $b = ($hash & 0x0000FF);
    return sprintf("#%02X%02X%02X", $r, $g, $b);
}

function deletarNotificacao($conexao, $id, $tipo='usuario') {
    $tabela = ($tipo === 'admin') ? 'notificacoes_admin' : 'notificacao_usuario';
    $stmt = $conexao->prepare("DELETE FROM $tabela WHERE id_notificacao = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function notificarAdmins($conexao, $emprestimo) {
    $agora = new DateTime();
    if (!is_null($emprestimo['hora_data_devolucao'])) {
        deletarNotificacao($conexao, $emprestimo['id_emprestimo'], 'admin');
        return;
    }
    if (!empty($emprestimo['categoria']) && strtolower($emprestimo['categoria']) === 'cancelado') return;

    $dataFim = new DateTime($emprestimo['data_fim_reserva']);
    if ($agora <= $dataFim) return;

    $nomeUsuario = '';
    $stmtUser = $conexao->prepare("SELECT nome FROM usuario WHERE cpf = ?");
    $stmtUser->bind_param("s", $emprestimo['cpf_solicitante']);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result()->fetch_assoc();
    $nomeUsuario = $resultUser['nome'] ?? $emprestimo['cpf_solicitante'];
    $stmtUser->close();

    $mensagem = "USUÁRIO: $nomeUsuario não devolveu a chave {$emprestimo['titulo']} dentro do prazo!";

    $stmtCheck = $conexao->prepare("SELECT COUNT(*) as total FROM notificacoes_admin WHERE id_emprestimo = ?");
    $stmtCheck->bind_param("i", $emprestimo['id_emprestimo']);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($result['total'] == 0) {
        $stmtInsert = $conexao->prepare("INSERT INTO notificacoes_admin (mensagem, id_emprestimo) VALUES (?, ?)");
        $stmtInsert->bind_param("si", $mensagem, $emprestimo['id_emprestimo']);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
}

// =====================
// Verificação de admin/gerente
// =====================
if ($cpfUsuario) {
    $stmt = $conexao->prepare("SELECT cpf, tipo FROM usuario_adm WHERE cpf = ?");
    $stmt->bind_param("s", $cpfUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $adm = $result->fetch_assoc();
        $isAdmin = true;
        $isGerente = ($adm['tipo'] === 'Gerente');
    }
    $stmt->close();
}

// Bloqueio de páginas restritas
$paginaAtual = basename($_SERVER['PHP_SELF']);
$paginasRestritas = ['cadastro_chave.php', 'gerenciar.php'];
if (!$isAdmin && in_array($paginaAtual, $paginasRestritas)) {
    echo "<script>alert('Acesso negado!');window.location='../Pages/agendar.php';</script>";
    exit;
}

// =====================
// Buscar dados do usuário
// =====================
if ($cpfUsuario) {
    $stmt = $conexao->prepare("
        SELECT e.id_emprestimo, c.nome AS titulo, e.data_inicio_reserva, e.data_fim_reserva,
               e.hora_data_retirada, e.hora_data_devolucao, e.cpf_solicitante, e.categoria
        FROM emprestimo e
        JOIN chave c ON e.id_chave = c.id_chave
        WHERE e.cpf_solicitante = ? 
          AND (e.categoria IS NULL OR e.categoria != 'Cancelado')
          AND (e.hora_data_retirada IS NULL OR (e.hora_data_retirada IS NOT NULL AND e.hora_data_devolucao IS NULL))
        ORDER BY e.data_fim_reserva ASC
    ");
    $stmt->bind_param("s", $cpfUsuario);
    $stmt->execute();
    $emprestimosPendentes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conexao->prepare("SELECT id_notificacao, mensagem FROM notificacao_usuario WHERE cpf_usuario = ? ORDER BY id_notificacao DESC");
    $stmt->bind_param("s", $cpfUsuario);
    $stmt->execute();
    $notificacoesUsuario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

//NUNCA MAIS IREI FAZER NOTIFICAÇÕES! Não vale a pena...

// =====================
// Verificar atrasos e notificar admins
// =====================
$resultTodos = $conexao->query("
    SELECT e.id_emprestimo, c.nome AS titulo, e.data_fim_reserva, e.hora_data_retirada, 
           e.hora_data_devolucao, e.cpf_solicitante, e.categoria
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    WHERE e.hora_data_retirada IS NOT NULL
      AND (e.categoria IS NULL OR e.categoria != 'Cancelado')
");
if ($resultTodos) {
    while ($emprestimo = $resultTodos->fetch_assoc()) {
        notificarAdmins($conexao, $emprestimo);
    }
}

// Notificações admin
if ($isAdmin) {
    $res = $conexao->query("SELECT id_notificacao, mensagem FROM notificacoes_admin ORDER BY id_notificacao DESC");
    if ($res) $notificacoesAdmin = $res->fetch_all(MYSQLI_ASSOC);
}

// =====================
// Contagem total e cor das notificações
// =====================
$totalNotificacoes = count($emprestimosPendentes) + count($notificacoesAdmin) + count($notificacoesUsuario);
$corBolinha = 'bg-blue-500';
foreach ($emprestimosPendentes as $emprestimo) {
    $dataFim = new DateTime($emprestimo['data_fim_reserva']);
    $diff = $dataFim->getTimestamp() - $agora->getTimestamp();
    if ($diff <= 300 && $diff > 0) $corBolinha = 'bg-yellow-500';
    elseif ($diff < 0) $corBolinha = 'bg-red-600';
}
if ($isAdmin && !empty($notificacoesAdmin)) $corBolinha = 'bg-black';

$corAvatar = corDoUsuario($cpfUsuario);
?>




<!-- HEADER ORIGINAL COM AVATAR COLORIDO -->
<header class="fixed top-0 left-0 w-full bg-white/90 backdrop-blur shadow-md z-50">
<style>
.logo-animada { width:70px; height:70px; background:linear-gradient(135deg,#0098e3,#00bfff); mask:url('../IMG/d2.svg') center/contain no-repeat; -webkit-mask:url('../IMG/d2.svg') center/contain no-repeat; animation:corLoop 6s ease-in-out infinite; transition:transform 0.3s ease; }
.logo-animada:hover { transform:scale(1.1) rotate(5deg); }
@keyframes corLoop { 0%{background:#0098e3;} 25%{background:#007acc;} 50%{background:#00bfff;} 75%{background:#66d9ff;} 100%{background:#0098e3;} }
.hamburger span { transition: all 0.3s ease; }
#menu-toggle:checked + label .hamburger span:nth-child(1){transform:rotate(45deg) translateY(8px);}
#menu-toggle:checked + label .hamburger span:nth-child(2){opacity:0;}
#menu-toggle:checked + label .hamburger span:nth-child(3){transform:rotate(-45deg) translateY(-8px);}
.nav-link{display:block;padding:0.5rem 1rem;font-weight:500;color:#374151;border-radius:6px;transition:all 0.3s ease;}
.nav-link:hover{color:white;background:#0098e3;}
.profile-badge{width:40px;height:40px;border-radius:9999px;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1rem;cursor:pointer;transition:all 0.3s ease;}
.profile-badge:hover{transform:scale(1.1);}
.btn-logout{background:#ef4444;color:white;padding:0.5rem 1rem;border-radius:0.5rem;font-weight:600;transition:0.3s;margin-left:0.75rem;}
.btn-logout:hover{background:#b91c1c;transform:scale(1.05);}
</style>

<div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
    <a href="../Pages/agendar.php" class="flex items-center"><div class="logo-animada"></div></a>

    <input type="checkbox" id="menu-toggle" class="hidden peer"/>
    <label for="menu-toggle" class="cursor-pointer md:hidden block">
        <div class="hamburger space-y-1.5">
            <span class="block w-7 h-0.5 bg-gray-800"></span>
            <span class="block w-7 h-0.5 bg-gray-800"></span>
            <span class="block w-7 h-0.5 bg-gray-800"></span>
        </div>
    </label>

    <nav class="absolute top-full left-0 w-full bg-white shadow-md hidden peer-checked:flex flex-col space-y-2 px-6 py-4 md:static md:w-auto md:bg-transparent md:shadow-none md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0">
    <?php if ($isAdmin): ?>
        <a href="../Pages/index.php" class="nav-link">Início</a>
        <a href="../Pages/cadastro_chave.php" class="nav-link">Cadastro de Chaves</a>
    <?php endif; ?>
    <a href="../Pages/contato.php" class="nav-link">Contato</a>
    <a href="../Pages/agendar.php" class="nav-link">Agendar</a>
    <?php if ($isGerente): ?>
        <a href="../Pages/gerenciar.php" class="nav-link">Área do Gerente</a>
    <?php endif; ?>
    <?php if ($isGerente): ?>
        <a href="../Pages/Agendar_fixo.php" class="nav-link">Emprestimo Fixo</a>
    <?php endif; ?>
</nav>

    <div class="flex items-center ml-4 md:ml-6">
        <a href="../Pages/perfil.php">
            <div class="profile-badge" style="background-color: <?= htmlspecialchars($corAvatar) ?>;">
                <?= strtoupper(substr($nomeUsuario ?? 'U', 0, 1)); ?>
            </div>
        </a>

        <div class="relative ml-4">
            <button id="btnNotificacao" class="relative text-gray-700 hover:text-blue-600 transition-transform transform hover:scale-110 focus:outline-none" type="button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 00-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0h6z"/>
                </svg>
                <?php if ($totalNotificacoes > 0): ?>
                    <span class="absolute -top-2 -right-2 flex items-center justify-center w-5 h-5 text-white text-xs font-bold rounded-full <?= $corBolinha ?> ring-2 ring-white">
                        <?= $totalNotificacoes ?>
                    </span>
                <?php endif; ?>
            </button>

            <div id="notificacaoDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-300 rounded-md shadow-lg z-50 p-4 text-gray-700 max-h-64 overflow-y-auto">
                <?php if (!empty($emprestimosPendentes) || ($isAdmin && !empty($notificacoesAdmin)) || !empty($notificacoesUsuario)): ?>
                    <ul class="space-y-2">
                        <?php foreach ($emprestimosPendentes as $emprestimo): 
                            $statusMsg = '';
                            $classeAlerta = 'text-gray-500';
                            $titulo = htmlspecialchars($emprestimo['titulo']);
                            $dataInicio = new DateTime($emprestimo['data_inicio_reserva']);
                            $dataFim = new DateTime($emprestimo['data_fim_reserva']);
                            if (is_null($emprestimo['hora_data_retirada'])) {
                                $diffSegundos = $dataInicio->getTimestamp() - $agora->getTimestamp();
                                if ($diffSegundos <= 300 && $diffSegundos > 0) { 
                                    $statusMsg = "⚠️ Retirar a chave em breve!"; 
                                    $classeAlerta = 'text-yellow-700 font-bold'; 
                                } elseif ($diffSegundos < 0) { 
                                    $statusMsg = "⛔ Você atrasou o horário de retirada!"; 
                                    $classeAlerta = 'text-red-700 font-bold'; 
                                } else { 
                                    $statusMsg = "Buscar a chave a partir: ".$dataInicio->format('d/m/Y H:i'); 
                                }
                            } elseif (!is_null($emprestimo['hora_data_retirada']) && is_null($emprestimo['hora_data_devolucao'])) {
                                $diffSegundos = $dataFim->getTimestamp() - $agora->getTimestamp();
                                if ($diffSegundos <= 300 && $diffSegundos > 0) { 
                                    $statusMsg = "⚠️ Devolver a chave em breve!"; 
                                    $classeAlerta = 'text-yellow-700 font-bold'; 
                                } elseif ($diffSegundos < 0) { 
                                    $statusMsg = "⛔ URGENTE! Prazo expirado! Entregue agora!"; 
                                    $classeAlerta = 'text-red-700 font-bold'; 
                                } else { 
                                    $statusMsg = "Devolver até: ".$dataFim->format('d/m/Y H:i'); 
                                }
                            }
                        ?>
                        <li class="border-b border-gray-200 pb-1 flex justify-between items-start">
                            <div>
                                <p><strong><?= $titulo ?></strong></p>
                                <p class="text-sm <?= $classeAlerta ?>"><?= $statusMsg ?></p>
                            </div>
                        </li>
                        <?php endforeach; ?>

                        <?php if ($isAdmin): foreach ($notificacoesAdmin as $notif): ?>
                        <li class="border-b border-gray-200 pb-1 flex justify-between items-start">
                            <p class="text-sm font-bold text-black"><?= htmlspecialchars($notif['mensagem']) ?></p>
                            <form method="post" action="../PHP/excluir_notificacao.php">
                                <input type="hidden" name="id_notificacao" value="<?= $notif['id_notificacao'] ?>">
                                <button type="submit" class="ml-2 text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                            </form>
                        </li>
                        <?php endforeach; endif; ?>

                        <?php foreach ($notificacoesUsuario as $notif): ?>
                        <li class="border-b border-gray-200 pb-1 flex justify-between items-start">
                            <p class="text-sm text-black"><?= htmlspecialchars($notif['mensagem']) ?></p>
                            <form method="post" action="../PHP/excluir_notificacao_usuario.php">
                                <input type="hidden" name="id_notificacao" value="<?= $notif['id_notificacao'] ?>">
                                <button type="submit" class="ml-2 text-red-500 hover:text-red-700 font-bold text-sm">&times;</button>
                            </form>
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

<script>
const btnNotificacao = document.getElementById('btnNotificacao');
const notificacaoDropdown = document.getElementById('notificacaoDropdown');
btnNotificacao.addEventListener('click', (e) => {
    e.stopPropagation();
    notificacaoDropdown.classList.toggle('hidden');
});
window.addEventListener('click', (event) => {
    if (!btnNotificacao.contains(event.target) && !notificacaoDropdown.contains(event.target)) {
        notificacaoDropdown.classList.add('hidden');
    }
});
</script>
