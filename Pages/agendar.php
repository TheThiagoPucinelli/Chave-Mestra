<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

// --- Dados do usuário ---
$cpfUsuario  = $_SESSION['cpf'];
$nomeUsuario = $_SESSION['nome'];
$isAdmin     = $_SESSION['isAdmin'] ?? false;

// --- Função para formatar datas ---
function formatDateTime($dt){
    if(!$dt) return '-';
    $d = new DateTime($dt);
    return $d->format('d/m/Y H:i');
}

// --- Garantir que o usuário exista na tabela solicitante ---
if ($cpfUsuario) {
    $stmtCheck = $conexao->prepare("SELECT cpf FROM solicitante WHERE cpf = ?");
    $stmtCheck->bind_param("s", $cpfUsuario);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows === 0) {
        $stmtInsert = $conexao->prepare("INSERT INTO solicitante (cpf) VALUES (?)");
        $stmtInsert->bind_param("s", $cpfUsuario);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmtCheck->close();
}

// --- Buscar chaves disponíveis ---
$chaves = [];
$sql = "
SELECT c.id_chave, c.nome, c.numero_identificacao, c.descricao
FROM chave c
WHERE c.status = 0
AND c.id_chave NOT IN (
    SELECT id_chave FROM emprestimo
    WHERE hora_data_devolucao IS NULL
)
ORDER BY c.nome
";
$res = $conexao->query($sql);
if($res){
    while($row = $res->fetch_assoc()){
        $descricao = $row['descricao'] ? " - " . $row['descricao'] : "";
        $chaves[] = [
            'id' => $row['id_chave'],
            'texto' => $row['nome'] . " (" . $row['numero_identificacao'].")".$descricao
        ];
    }
}

// --- Processar agendamento ---
$mensagem = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_chave = isset($_POST['chave_id']) ? (int) $_POST['chave_id'] : 0;
    $data_inicio_raw = $_POST['data_inicio'] ?? '';
    $data_fim_raw = $_POST['data_fim'] ?? '';

    if (!$id_chave || !$cpfUsuario || !$data_inicio_raw || !$data_fim_raw) {
        $erro = "Preencha todos os campos!";
    } else {
        $stmtCheck = $conexao->prepare("SELECT id_chave FROM chave WHERE id_chave = ? AND status = 0");
        $stmtCheck->bind_param("i", $id_chave);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if($stmtCheck->num_rows === 0){
            $erro = "Chave inválida ou indisponível!";
        } else {
            $data_inicio = date('Y-m-d H:i:s', strtotime($data_inicio_raw));
            $data_fim    = date('Y-m-d H:i:s', strtotime($data_fim_raw));

            $stmtInsert = $conexao->prepare("
                INSERT INTO emprestimo 
                (data_reserva, data_inicio_reserva, data_fim_reserva, hora_data_retirada, hora_data_devolucao, id_chave, cpf_solicitante)
                VALUES (NOW(), ?, ?, NULL, NULL, ?, ?)
            ");
            if($stmtInsert){
                $stmtInsert->bind_param("ssis", $data_inicio, $data_fim, $id_chave, $cpfUsuario);
                if($stmtInsert->execute()){
                    $mensagem = "Agendamento realizado com sucesso!";
                } else {
                    $erro = "Erro ao agendar: ".$stmtInsert->error;
                }
                $stmtInsert->close();
            } else {
                $erro = "Erro na preparação da consulta: ".$conexao->error;
            }
        }
        $stmtCheck->close();
    }
}

// --- Buscar agendamentos ativos ---
$agendamentos = [];
$sql = "
SELECT e.data_inicio_reserva, e.data_fim_reserva, e.hora_data_retirada,
       c.nome AS chave_nome, c.numero_identificacao
FROM emprestimo e
JOIN chave c ON e.id_chave = c.id_chave
WHERE e.hora_data_devolucao IS NULL
ORDER BY e.data_inicio_reserva
";
$res = $conexao->query($sql);
while($row = $res->fetch_assoc()){
    $inicio = $row['hora_data_retirada'] ?? $row['data_inicio_reserva'];
    $fim = $row['data_fim_reserva'];
    $status = $row['hora_data_retirada'] ? 'Retirada' : 'Reservada';

    $agendamentos[] = [
        'chave' => $row['chave_nome'].' ('.$row['numero_identificacao'].')',
        'inicio' => $inicio,
        'fim' => $fim,
        'status' => $status
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agendamento e Calendário de Chaves</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; position: relative; }
.calendar-header { display: grid; grid-template-columns: repeat(7,1fr); text-align:center; font-weight:600; margin-bottom:5px; }
.day { position:relative; padding:14px; min-height:60px; text-align:center; border:1px solid #e5e7eb; border-radius:10px; cursor:pointer; background:#f9fafb; transition:0.3s; font-weight:500; display:flex; align-items:center; justify-content:center; }
.day:hover { background-color: #bfdbfe; transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.dot { position:absolute; bottom:6px; right:6px; width:10px; height:10px; border-radius:50%; background:#ef4444; border:2px solid white; }
.tooltip { display:none; position:absolute; background:white; padding:10px; border:1px solid #d1d5db; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-size:13px; z-index:1000; width:260px; }
</style>
</head>
<body class="bg-blue-100 font-sans min-h-screen flex flex-col">
<?php include '../Includes/header.php'; ?>
<br><br><br><br><br>
<main class="flex flex-col md:flex-row gap-6 px-6 py-8 flex-1">

    <!-- Formulário de agendamento -->
    <div class="w-full md:w-1/3 bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-5 text-blue-700">Agendar Chave</h2>
        <?php if($mensagem): ?><div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
        <?php if($erro): ?><div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 shadow"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <form method="post" class="flex flex-col gap-4">
            <div>
                <label for="chave" class="font-medium mb-1 block">Chave:</label>
                <input list="chavesDisponiveis" name="chave_texto" id="chave" 
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400" 
                       placeholder="Digite para buscar..." required>
                <input type="hidden" name="chave_id" id="chave_id">
                <datalist id="chavesDisponiveis">
                    <?php foreach($chaves as $ch): ?>
                        <option data-id="<?= $ch['id'] ?>" value="<?= htmlspecialchars($ch['texto']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div>
                <label for="data_inicio" class="font-medium mb-1 block">Data e Hora Início:</label>
                <input type="datetime-local" name="data_inicio" id="data_inicio" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div>
                <label for="data_fim" class="font-medium mb-1 block">Data e Hora Fim:</label>
                <input type="datetime-local" name="data_fim" id="data_fim" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400" required>
            </div>
            <button type="submit" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition-all">Agendar</button>
            <button type="button" id="toggleTabela" class="mt-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg shadow transition">Ver Tabela de Chaves</button>
        </form>
    </div>

    <!-- Calendário -->
    <div class="w-full md:w-2/3 bg-white p-6 rounded-2xl shadow-lg relative overflow-x-auto">
        <div class="month-nav flex justify-between items-center mb-4">
            <button id="prevMonth" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">&lt;</button>
            <h2 id="monthYear" class="text-xl font-bold text-gray-700"></h2>
            <button id="nextMonth" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">&gt;</button>
        </div>
        <div class="calendar-header grid grid-cols-7 text-gray-600">
            <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
        </div>
        <div class="calendar grid grid-cols-7 gap-2"></div>
    </div>
</main>

<!-- Modal da tabela -->
<div id="tabelaModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden z-50 p-4 items-center justify-center">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[80vh] overflow-auto p-6 shadow-lg">
        <h2 class="text-2xl font-bold mb-5 text-blue-700">Tabela de Chaves e Reservas</h2>
        <button id="closeTabela" class="mb-4 text-red-600 hover:text-red-800 font-semibold">Fechar</button>
        <table class="w-full table-auto border-collapse text-sm">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="border px-3 py-2">Chave</th>
                    <th class="border px-3 py-2">Início</th>
                    <th class="border px-3 py-2">Fim</th>
                    <th class="border px-3 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($agendamentos as $ag):
                    $statusColor = match($ag['status']){
                        'Reservada' => 'bg-yellow-100 text-yellow-800',
                        'Retirada'  => 'bg-blue-100 text-blue-800',
                        'Devolvida' => 'bg-green-100 text-green-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                ?>
                <tr>
                    <td class="border px-3 py-2"><?= htmlspecialchars($ag['chave']) ?></td>
                    <td class="border px-3 py-2"><?= formatDateTime($ag['inicio']) ?></td>
                    <td class="border px-3 py-2"><?= formatDateTime($ag['fim']) ?></td>
                    <td class="border px-3 py-2"><span class="px-2 py-1 rounded-full <?= $statusColor ?> font-semibold"><?= $ag['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="bg-gray-900 text-gray-400 py-6 mt-auto w-full">
  <div class="max-w-7xl mx-auto text-center text-sm">
    <p>&copy; 2025 <span class="text-white font-semibold">Chave Mestra</span>. Todos os direitos reservados. | 
      <a href="../Pages/contato.php" class="text-blue-400 hover:text-white transition">Contato</a>
    </p>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const agendamentosJS = <?= json_encode($agendamentos) ?>;
    let currentDate = new Date();

    function formatDateTime(dt){
        if(!dt) return '-';
        const d = new Date(dt);
        if(isNaN(d)) return '-';
        return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});
    }

    function daysInMonth(month, year){ return new Date(year, month+1,0).getDate(); }

    function buildCalendar(date){
        const calendar = document.querySelector('.calendar');
        calendar.innerHTML = '';
        const month = date.getMonth();
        const year = date.getFullYear();
        const monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        document.getElementById('monthYear').textContent = monthNames[month] + ' ' + year;
        const firstDay = new Date(year, month, 1).getDay();
        const totalDays = daysInMonth(month, year);

        for(let i=0;i<firstDay;i++){ calendar.appendChild(document.createElement('div')); }

        for(let day=1; day<=totalDays; day++){
            const dayDiv = document.createElement('div');
            dayDiv.className='day';
            dayDiv.textContent = day;

            const dayString = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            const eventosDoDia = agendamentosJS.filter(a => dayString >= a.inicio.slice(0,10) && dayString <= a.fim.slice(0,10));

            if(eventosDoDia.length > 0){
                const dot = document.createElement('div');
                dot.className='dot';
                dayDiv.appendChild(dot);

                const tooltip = document.createElement('div');
                tooltip.className='tooltip';
                const closeBtn = document.createElement('button');
                closeBtn.textContent = '×';
                closeBtn.style.cssText = 'position:absolute; top:4px; right:8px; background:none; border:none; font-size:18px; cursor:pointer; color:#555;';
                closeBtn.onclick = () => tooltip.style.display = 'none';
                tooltip.appendChild(closeBtn);

                eventosDoDia.forEach(ev => {
                    const info = document.createElement('div');
                    info.innerHTML = `<strong>${ev.chave}</strong><br>Início: ${formatDateTime(ev.inicio)}<br>Fim: ${formatDateTime(ev.fim)}<hr>`;
                    tooltip.appendChild(info);
                });

                document.body.appendChild(tooltip);
                dayDiv.addEventListener('click', ()=>
                                {
                    const isVisible = tooltip.style.display === 'block';
                    document.querySelectorAll('.tooltip').forEach(t => t.style.display = 'none');
                    if(!isVisible){
                        const rect = dayDiv.getBoundingClientRect();
                        tooltip.style.top = (window.scrollY + rect.top - tooltip.offsetHeight - 10) + 'px';
                        tooltip.style.left = (window.scrollX + rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                        tooltip.style.display = 'block';
                    }
                });
            }

            calendar.appendChild(dayDiv);
        }
    }

    document.getElementById('prevMonth').addEventListener('click', ()=> { 
        currentDate.setMonth(currentDate.getMonth()-1); 
        buildCalendar(currentDate); 
    });

    document.getElementById('nextMonth').addEventListener('click', ()=> { 
        currentDate.setMonth(currentDate.getMonth()+1); 
        buildCalendar(currentDate); 
    });

    buildCalendar(currentDate);

    const tabelaModal = document.getElementById('tabelaModal');
    document.getElementById('toggleTabela').addEventListener('click', ()=> {
        tabelaModal.classList.remove('hidden');
        tabelaModal.classList.add('flex');
    });
    document.getElementById('closeTabela').addEventListener('click', ()=> {
        tabelaModal.classList.add('hidden');
        tabelaModal.classList.remove('flex');
    });

    // --- Ajuste do datalist para enviar o ID correto ---
    const inputChave = document.getElementById('chave');
    const inputChaveId = document.getElementById('chave_id');
    const datalistOptions = document.querySelectorAll('#chavesDisponiveis option');

    inputChave.addEventListener('input', () => {
        const val = inputChave.value;
        const option = Array.from(datalistOptions).find(o => o.value === val);
        if(option){
            inputChaveId.value = option.dataset.id;
        } else {
            inputChaveId.value = '';
        }
    });
});
</script>
</body>
</html>
