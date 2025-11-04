<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$cpfUsuario  = $_SESSION['cpf'] ?? null;
$nomeUsuario = $_SESSION['nome'] ?? '';
$isAdmin     = $_SESSION['isAdmin'] ?? false;

// --- Funções auxiliares ---
function formatDateTime(?string $dt): string {
    return $dt ? (new DateTime($dt))->format('d/m/Y H:i') : '-';
}

function garantirSolicitante(mysqli $conexao, string $cpf): void {
    $stmt = $conexao->prepare("SELECT 1 FROM solicitante WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $insert = $conexao->prepare("INSERT INTO solicitante (cpf) VALUES (?)");
        $insert->bind_param("s", $cpf);
        $insert->execute();
        $insert->close();
    }
    $stmt->close();
}

function buscarChaves(mysqli $conexao): array {
    $res = $conexao->query("SELECT id_chave, nome, numero_identificacao, descricao, status FROM chave ORDER BY nome");
    $chaves = [];
    while ($row = $res->fetch_assoc()) {
        $descricao = $row['descricao'] ? " - {$row['descricao']}" : "";
        $statusTxt = $row['status'] ? " (Indisponível)" : "";
        $chaves[] = [
            'id' => (int)$row['id_chave'],
            'texto' => "{$row['nome']} ({$row['numero_identificacao']}){$descricao}{$statusTxt}"
        ];
    }
    return $chaves;
}

function notificarAdmGerente(mysqli $conexao, int $id_emprestimo, string $mensagem) {
    $res = $conexao->query("SELECT cpf FROM usuario_adm WHERE tipo IN ('Administrador','Gerente')");
    while ($row = $res->fetch_assoc()) {
        // Evita notificações duplicadas
        $check = $conexao->prepare("SELECT 1 FROM notificacoes_admin WHERE id_emprestimo = ? AND cpf_admin = ?");
        $check->bind_param("is", $id_emprestimo, $row['cpf']);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $stmt = $conexao->prepare("INSERT INTO notificacoes_admin (id_emprestimo, cpf_admin, mensagem) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_emprestimo, $row['cpf'], $mensagem);
            $stmt->execute();
            $stmt->close();
        }
        $check->close();
    }
}

function existeConflitoAgendamento(mysqli $conexao, int $id_chave, string $inicio, string $fim): bool {
    $sql = "
        SELECT 1 FROM emprestimo
        WHERE id_chave = ?
          AND hora_data_devolucao IS NULL
          AND (categoria IS NULL OR categoria <> 'Cancelado')
          AND (
              (? BETWEEN data_inicio_reserva AND data_fim_reserva)
              OR (? BETWEEN data_inicio_reserva AND data_fim_reserva)
              OR (data_inicio_reserva BETWEEN ? AND ?)
              OR (data_fim_reserva BETWEEN ? AND ?)
          )
    ";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("issssss", $id_chave, $inicio, $fim, $inicio, $fim, $inicio, $fim);
    $stmt->execute();
    $stmt->store_result();
    $existe = $stmt->num_rows > 0;
    $stmt->close();
    return $existe;
}

function registrarAgendamento(mysqli $conexao, int $id_chave, string $cpf, string $inicio, string $fim): int {
    $stmt = $conexao->prepare("
        INSERT INTO emprestimo (data_reserva, data_inicio_reserva, data_fim_reserva, id_chave, cpf_solicitante)
        VALUES (NOW(), ?, ?, ?, ?)
    ");
    if (!$stmt) return 0;
    $stmt->bind_param("ssis", $inicio, $fim, $id_chave, $cpf);
    $stmt->execute();
    $id = $conexao->insert_id;
    $stmt->close();
    return $id;
}

function buscarAgendamentosUsuario(mysqli $conexao, string $cpf): array {
    $stmt = $conexao->prepare("
        SELECT e.id_emprestimo, e.data_inicio_reserva, e.data_fim_reserva, e.hora_data_retirada,
               e.id_chave, c.nome AS chave_nome, c.numero_identificacao, e.cpf_solicitante
        FROM emprestimo e
        JOIN chave c ON e.id_chave = c.id_chave
        WHERE e.hora_data_devolucao IS NULL
          AND (e.categoria IS NULL OR e.categoria <> 'Cancelado')
          AND e.cpf_solicitante = ?
        ORDER BY e.data_inicio_reserva
    ");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $res = $stmt->get_result();
    $ag = [];
    while ($r = $res->fetch_assoc()) {
        $ag[] = [
            'id_emprestimo'=> (int)$r['id_emprestimo'],
            'id_chave'     => (int)$r['id_chave'],
            'chave'        => "{$r['chave_nome']} ({$r['numero_identificacao']})",
            'inicio'       => $r['data_inicio_reserva'],
            'fim'          => $r['data_fim_reserva'],
            'status'       => $r['hora_data_retirada'] ? 'Em uso' : 'Reservada',
            'cpf_usuario'  => $r['cpf_solicitante']
        ];
    }
    $stmt->close();
    return $ag;
}

function buscarAgendamentosTodos(mysqli $conexao): array {
    $res = $conexao->query("
        SELECT e.id_emprestimo, e.data_inicio_reserva, e.data_fim_reserva, e.hora_data_retirada,
               e.id_chave, c.nome AS chave_nome, c.numero_identificacao, e.cpf_solicitante
        FROM emprestimo e
        JOIN chave c ON e.id_chave = c.id_chave
        WHERE e.hora_data_devolucao IS NULL
          AND (e.categoria IS NULL OR e.categoria <> 'Cancelado')
        ORDER BY e.data_inicio_reserva
    ");
    $ag = [];
    while ($r = $res->fetch_assoc()) {
        $ag[] = [
            'id_emprestimo'=> (int)$r['id_emprestimo'],
            'id_chave'     => (int)$r['id_chave'],
            'chave'        => "{$r['chave_nome']} ({$r['numero_identificacao']})",
            'inicio'       => $r['data_inicio_reserva'],
            'fim'          => $r['data_fim_reserva'],
            'status'       => $r['hora_data_retirada'] ? 'Em uso' : 'Reservada',
            'cpf_usuario'  => $r['cpf_solicitante']
        ];
    }
    return $ag;
}

// --- Execução principal ---
$mensagem = $erro = "";
if ($cpfUsuario) garantirSolicitante($conexao, $cpfUsuario);
$chaves = buscarChaves($conexao);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Cancelamento de agendamento ---
    if (isset($_POST['acao']) && $_POST['acao'] === 'cancelar' && !empty($_POST['id_emprestimo'])) {
        $idEmp = (int)$_POST['id_emprestimo'];
        $stmt = $conexao->prepare("UPDATE emprestimo SET categoria = 'Cancelado' WHERE id_emprestimo = ? AND cpf_solicitante = ?");
        $stmt->bind_param("is", $idEmp, $cpfUsuario);
        $stmt->execute();
        $stmt->close();
        $mensagem = "Agendamento cancelado com sucesso!";
    } else {
        // --- Agendamento normal ---
        $id_chave   = (int)($_POST['chave_id'] ?? 0);
        $inicio_raw = trim($_POST['data_inicio'] ?? '');
        $fim_raw    = trim($_POST['data_fim'] ?? '');

        if (!$id_chave || !$inicio_raw || !$fim_raw) {
            $erro = "Todos os campos são obrigatórios.";
        } else {
            $inicio = date('Y-m-d H:i:s', strtotime($inicio_raw));
            $fim    = date('Y-m-d H:i:s', strtotime($fim_raw));

            if (date('Y-m-d', strtotime($inicio)) !== date('Y-m-d', strtotime($fim))) {
                $erro = "O agendamento deve estar dentro de um único dia.";
            }

            if (!$isAdmin && !$erro) {
                $stmt = $conexao->prepare("
                    SELECT COUNT(*) FROM emprestimo
                    WHERE cpf_solicitante = ? AND DATE(data_inicio_reserva) = ? AND hora_data_devolucao IS NULL
                      AND (categoria IS NULL OR categoria <> 'Cancelado')
                ");
                $dataAgendamento = date('Y-m-d', strtotime($inicio));
                $stmt->bind_param("ss", $cpfUsuario, $dataAgendamento);
                $stmt->execute();
                $stmt->bind_result($totalAgendamentos);
                $stmt->fetch();
                $stmt->close();
                if ($totalAgendamentos >= 2) {
                    $erro = "Você já atingiu o limite de 2 chaves agendadas para este dia.";
                }
            }

            if (!$erro) {
                if ($fim <= $inicio) {
                    $erro = "A data de término deve ser posterior à de início.";
                } elseif (existeConflitoAgendamento($conexao, $id_chave, $inicio, $fim)) {
                    $erro = "Esta chave já está agendada nesse horário.";
                } else {
                    $idEmp = registrarAgendamento($conexao, $id_chave, $cpfUsuario, $inicio, $fim);
                    if ($idEmp) {
                        $mensagem = "Agendamento realizado com sucesso!";
                        $chaveNome = '';
                        foreach ($chaves as $ch) {
                            if ($ch['id'] === $id_chave) { $chaveNome = $ch['texto']; break; }
                        }
                        $mensagemNotificacao = "Nova reserva realizada: $chaveNome por $nomeUsuario";
                        notificarAdmGerente($conexao, $idEmp, $mensagemNotificacao);
                    } else {
                        $erro = "Erro ao registrar agendamento.";
                    }
                }
            }
        }
    }
}
function buscarAgendamentosFixos(mysqli $conexao, $idChaveFiltro = null): array {
    $sql = "
        SELECT ef.id_fixo, ef.dia_semana, ef.hora_inicio, ef.hora_fim,
               c.nome AS chave_nome, c.numero_identificacao, ef.cpf_solicitante
        FROM emprestimo_fixo ef
        JOIN chave c ON ef.id_chave = c.id_chave
    ";

    if ($idChaveFiltro) {
        $sql .= " WHERE ef.id_chave = " . intval($idChaveFiltro);
    }

    $sql .= " ORDER BY ef.dia_semana, ef.hora_inicio";

    $res = $conexao->query($sql);
    $ag = [];

    // mapa de dias
    $dias = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    while ($r = $res->fetch_assoc()) {
        $ag[] = [
            'id'    => (int)$r['id_fixo'],
            'dia'   => $dias[$r['dia_semana']] ?? $r['dia_semana'],
            'inicio'=> substr($r['hora_inicio'], 0, 5),
            'fim'   => substr($r['hora_fim'], 0, 5),
            'chave' => "{$r['chave_nome']} ({$r['numero_identificacao']})",
        ];
    }
    return $ag;
}



$agUsuario = buscarAgendamentosUsuario($conexao, $cpfUsuario);
$agTodos   = buscarAgendamentosTodos($conexao);
$agFixos   = buscarAgendamentosFixos($conexao);

?>





<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../IMG/CM.png">
<title>Agendar e Visualizar Chaves</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.calendar {display:grid;grid-template-columns:repeat(7,1fr);gap:6px;}
.day {position:relative;padding:14px;min-height:60px;border:1px solid #e5e7eb;border-radius:10px;text-align:center;background:#f9fafb;cursor:pointer;transition:.3s;}
.day:hover{background-color:#bfdbfe;transform:translateY(-2px);box-shadow:0 2px 6px rgba(0,0,0,.15);}
.dot{position:absolute;bottom:6px;right:6px;width:10px;height:10px;border-radius:50%;background:#ef4444;border:2px solid white;}
.tooltip{display:none;position:absolute;background:white;padding:10px;border:1px solid #d1d5db;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.2);font-size:13px;z-index:1000;width:260px;}
</style>
</head>
<body class="bg-blue-100 font-sans min-h-screen flex flex-col">
<?php include '../Includes/header.php'; ?>
<br><br><br><br><br>

<main class="flex flex-col md:flex-row gap-6 px-6 py-8 flex-1">
<!-- Formulário -->
<div class="w-full md:w-1/3 bg-white p-6 rounded-2xl shadow-lg">
<h2 class="text-2xl font-bold mb-5 text-blue-700">Agendar Chave</h2>
<?php if($mensagem): ?><div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if($erro): ?><div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<form method="post" class="flex flex-col gap-4">
    <div>
        <label for="chave" class="font-medium mb-1 block">Chave:</label>
        <input list="chavesDisponiveis" id="chave" name="chave_texto" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400" placeholder="Digite ou selecione..." required>
        <input type="hidden" id="chave_id" name="chave_id">
        <datalist id="chavesDisponiveis">
            <?php foreach($chaves as $ch): ?>
                <option data-id="<?= $ch['id'] ?>" value="<?= htmlspecialchars($ch['texto']) ?>"></option>
            <?php endforeach; ?>
        </datalist>
    </div>
    <div>
        <label for="data_inicio" class="font-medium mb-1 block">Data e Hora Início:</label>
        <input type="datetime-local" id="data_inicio" name="data_inicio" required class="w-full border rounded-lg p-3">
    </div>
    <div>
        <label for="data_fim" class="font-medium mb-1 block">Data e Hora Fim:</label>
        <input type="datetime-local" id="data_fim" name="data_fim" required class="w-full border rounded-lg p-3">
    </div>
    <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow">Agendar</button>
    <button type="button" id="toggleTabela" class="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg shadow">Ver Tabela de Reservas</button>
    
    <button id="toggleAreaNormal">Mostrar Agendamentos Fixos</button>

</button>

<div id="areaNormal" class="hidden mt-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-2">Agendamentos Fixos</h3>

    <!-- 🔍 Campo de filtro -->
    <div class="mb-3">
        <label for="filtroChaveFixa" class="block text-sm font-medium text-gray-600 mb-1">
            Filtrar por chave:
        </label>

        <input 
            type="text" 
            id="filtroChaveFixa" 
            class="border rounded-lg px-3 py-2 w-full md:w-1/2" 
            placeholder="Digite o nome ou número da chave..."
        >  
    </div>

    <?php if (!empty($agFixos)): ?>
        <table id="tabelaFixos" class="w-full border mt-2 text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 text-left">Chave</th>
                    <th class="p-2 text-left">Dia da Semana</th>
                    <th class="p-2 text-left">Início</th>
                    <th class="p-2 text-left">Término</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agFixos as $fx): ?>
                <tr class="border-b linhaFixa">
                    <td class="p-2 chaveFixa"><?= htmlspecialchars($fx['chave']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($fx['dia']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($fx['inicio']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($fx['fim']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-600 mt-2">Nenhum agendamento fixo encontrado.</p>
    <?php endif; ?>
</div>



</form>
<p id="infoSelecionada" class="mt-4 text-sm text-gray-600 italic hidden"></p>
</div>



<!-- Calendário -->
<div class="w-full md:w-2/3 bg-white p-6 rounded-2xl shadow-lg">
    <div class="month-nav flex justify-between items-center mb-4">
        <button id="prevMonth" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">&lt;</button>
        <h2 id="monthYear" class="text-xl font-bold text-gray-700"></h2>
        <button id="nextMonth" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">&gt;</button>
    </div>
    <div class="calendar grid grid-cols-7 gap-2"></div>
</div>
</main>

<!-- Modal da Tabela -->
<div id="tabelaModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden z-50 p-4 items-center justify-center">
    <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[80vh] overflow-auto shadow-xl p-6 relative">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-blue-600">Reservas</h2>
            <button id="closeTabela" class="text-red-600 hover:text-red-800 font-semibold text-lg">×</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-blue-600 text-white rounded-t-xl">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Chave</th>
                        <th class="px-4 py-3 text-left font-medium">Início</th>
                        <th class="px-4 py-3 text-left font-medium">Fim</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                    </tr>
                </thead>
                <tbody id="tbodyTabela" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<footer class="bg-gray-900 text-gray-400 py-3 text-center text-sm">
  &copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados.
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const agUser = <?= json_encode($agUsuario) ?>;
    const agTodos = <?= json_encode($agTodos) ?>;
    let agAtuais = [...agUser];
    let currentDate = new Date();

    const inputChave = document.getElementById('chave');
    const inputChaveId = document.getElementById('chave_id');
    const info = document.getElementById('infoSelecionada');
    const datalist = document.querySelectorAll('#chavesDisponiveis option');
    let chaveSelecionada = null;
    const cpfLogado = "<?= $_SESSION['cpf'] ?>";
// 🔍 Filtro de agendamentos fixos
const filtroInput = document.getElementById('filtroChaveFixa');
const linhas = document.querySelectorAll('#tabelaFixos .linhaFixa');
const btnFiltrar = document.getElementById('btnFiltrarFixos');
const btnLimpar = document.getElementById('btnLimparFiltroFixos');

// Filtro digitando
filtroInput?.addEventListener('input', () => {
    const termo = filtroInput.value.toLowerCase();
    linhas.forEach(linha => {
        const texto = linha.querySelector('.chaveFixa').textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
});

// Botão "Filtrar"
btnFiltrar?.addEventListener('click', () => {
    const termo = filtroInput.value.toLowerCase();
    linhas.forEach(linha => {
        const texto = linha.querySelector('.chaveFixa').textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
});

// Botão "Limpar"
btnLimpar?.addEventListener('click', () => {
    filtroInput.value = "";
    linhas.forEach(linha => linha.style.display = '');
});


    // Cria tooltip único
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.style.position = 'absolute';
    tooltip.style.display = 'none';
    tooltip.style.zIndex = 999;
    document.body.appendChild(tooltip);

    const tooltipClose = document.createElement('button');
    tooltipClose.textContent = '×';
    Object.assign(tooltipClose.style, {
        position: 'absolute',
        top: '4px',
        right: '8px',
        background: 'none',
        border: 'none',
        fontSize: '18px',
        cursor: 'pointer',
        color: '#555'
    });
    tooltipClose.addEventListener('click', () => tooltip.style.display = 'none');
    tooltip.appendChild(tooltipClose);

    // Atualiza agendamentos ao selecionar chave
    inputChave.addEventListener('input', () => {
        const val = inputChave.value;
        const opt = Array.from(datalist).find(o => o.value === val);
        if (opt) {
            chaveSelecionada = parseInt(opt.dataset.id);
            inputChaveId.value = chaveSelecionada;
            agAtuais = agTodos.filter(a => a.id_chave === chaveSelecionada);
            info.textContent = `Mostrando todos os agendamentos da chave: ${val}`;
        } else {
            chaveSelecionada = null;
            inputChaveId.value = '';
            agAtuais = [...agUser];
            info.textContent = `Mostrando apenas seus agendamentos.`;
        }
        info.classList.remove('hidden');
        buildCalendar(currentDate);
    });

    // Formata data/hora para exibição
    function formatDateTime(dt) {
        const d = new Date(dt);
        return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    // Constroi o calendário
    function buildCalendar(date) {
        const cal = document.querySelector('.calendar');
        cal.innerHTML = '';
        const m = date.getMonth(), y = date.getFullYear();
        document.getElementById('monthYear').textContent =
            ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][m] + ' ' + y;

        const first = new Date(y, m, 1).getDay();
        const days = new Date(y, m + 1, 0).getDate();

        for (let i = 0; i < first; i++) cal.appendChild(document.createElement('div'));

        for (let d = 1; d <= days; d++) {
            const el = document.createElement('div');
            el.className = 'day';
            el.textContent = d;

            const str = `${y}-${String(m + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const evts = agAtuais.filter(a => str >= a.inicio.slice(0,10) && str <= a.fim.slice(0,10));

            if (evts.length > 0) {
                const dot = document.createElement('div');
                dot.className = 'dot';
                el.appendChild(dot);

                // Limpa tooltip mantendo apenas botão fechar
                tooltip.innerHTML = '';
                tooltip.appendChild(tooltipClose);

                evts.forEach(ev => {
                    const infoEv = document.createElement('div');
                    infoEv.innerHTML = `<strong>${ev.chave}</strong><br>
                                        Início: ${formatDateTime(ev.inicio)}<br>
                                        Fim: ${formatDateTime(ev.fim)}<br>
                                        Status: ${ev.status}`;

                    // Botão cancelar apenas para o próprio usuário e agendamentos não cancelados
                    if (ev.status === 'Reservada' && ev.cpf_usuario === cpfLogado) {
                        const btnCancel = document.createElement('button');
                        btnCancel.textContent = 'Cancelar';
                        btnCancel.className = 'mt-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs';
                        btnCancel.addEventListener('click', (e) => {
                            e.stopPropagation();
                            if (confirm('Deseja realmente cancelar este agendamento?')) {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.style.display = 'none';

                                const acao = document.createElement('input');
                                acao.name = 'acao';
                                acao.value = 'cancelar';
                                form.appendChild(acao);

                                const idInput = document.createElement('input');
                                idInput.name = 'id_emprestimo';
                                idInput.value = ev.id_emprestimo;
                                form.appendChild(idInput);

                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                        infoEv.appendChild(document.createElement('br'));
                        infoEv.appendChild(btnCancel);
                    }

                    infoEv.appendChild(document.createElement('hr'));
                    tooltip.appendChild(infoEv);
                });

                el.addEventListener('click', () => {
                    const vis = tooltip.style.display === 'block';
                    tooltip.style.display = 'none';
                    if (!vis) {
                        const r = el.getBoundingClientRect();
                        tooltip.style.top = (window.scrollY + r.top - tooltip.offsetHeight - 10) + 'px';
                        tooltip.style.left = (window.scrollX + r.left + r.width/2 - tooltip.offsetWidth/2) + 'px';
                        tooltip.style.display = 'block';
                    }
                });
            }

            cal.appendChild(el);
        }
    }

    // Navegação de meses
    document.getElementById('prevMonth').onclick = () => { currentDate.setMonth(currentDate.getMonth() -1); buildCalendar(currentDate); };
    document.getElementById('nextMonth').onclick = () => { currentDate.setMonth(currentDate.getMonth() +1); buildCalendar(currentDate); };

    buildCalendar(currentDate);

    // Modal tabela
    const modal = document.getElementById('tabelaModal');
    const tbody = document.getElementById('tbodyTabela');
    document.getElementById('toggleTabela').onclick = () => {
        tbody.innerHTML = '';
        const data = chaveSelecionada ? agTodos.filter(a=>a.id_chave===chaveSelecionada) : agUser;
        data.forEach(a=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="border px-3 py-2">${a.chave}</td>
                            <td class="border px-3 py-2">${formatDateTime(a.inicio)}</td>
                            <td class="border px-3 py-2">${formatDateTime(a.fim)}</td>
                            <td class="border px-3 py-2">${a.status}</td>`;
            tbody.appendChild(tr);
        });
        modal.classList.remove('hidden'); 
        modal.classList.add('flex');
    };
    document.getElementById('closeTabela').onclick = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
});
</script>
<script>
document.getElementById("toggleAreaNormal").addEventListener("click", function() {
    const area = document.getElementById("areaNormal");
    if (area.classList.contains("hidden")) {
        area.classList.remove("hidden");
        this.textContent = "Esconder Agendamentos Fixos";
    } else {
        area.classList.add("hidden");
        this.textContent = "Mostrar Agendamentos Fixos";
    }
});
</script>





</body>
</html>
