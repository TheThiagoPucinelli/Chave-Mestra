<?php
// ==================== INICIALIZAÇÃO ====================
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$cpfUsuario  = $_SESSION['cpf'] ?? null;
$nomeUsuario = $_SESSION['nome'] ?? '';
$isAdmin     = $_SESSION['isAdmin'] ?? false;

// Array de mensagens para feedback
$mensagens = [];

// Dias da semana
$dias = ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"];

// ==================== FUNÇÕES AUXILIARES ====================

/**
 * Garante que o solicitante exista
 */
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

/**
 * Retorna todas as chaves disponíveis
 */
function buscarChaves(mysqli $conexao): array {
    $res = $conexao->query("SELECT id_chave, nome, numero_identificacao FROM chave ORDER BY nome");
    $chaves = [];
    while ($row = $res->fetch_assoc()) {
        $chaves[] = [
            'id' => (int)$row['id_chave'],
            'nome' => $row['nome'],
            'texto' => "{$row['nome']} ({$row['numero_identificacao']})"
        ];
    }
    return $chaves;
}

/**
 * Retorna todos os empréstimos fixos de uma chave
 */
function buscarEmprestimosPorChave(mysqli $conexao, int $id_chave): array {
    $stmt = $conexao->prepare("
        SELECT ef.id_fixo, u.nome AS solicitante, ef.dia_semana, ef.hora_inicio, ef.hora_fim
        FROM emprestimo_fixo ef
        JOIN solicitante s ON ef.cpf_solicitante = s.cpf
        JOIN usuario u ON s.cpf = u.cpf
        WHERE ef.id_chave = ?
        ORDER BY ef.dia_semana, ef.hora_inicio
    ");
    $stmt->bind_param("i", $id_chave);
    $stmt->execute();
    $res = $stmt->get_result();
    $arr = [];
    while ($r = $res->fetch_assoc()) {
        $arr[] = [
            'id_fixo' => (int)$r['id_fixo'],
            'nome' => $r['solicitante'],
            'dia_semana' => (int)$r['dia_semana'],
            'inicio' => $r['hora_inicio'],
            'fim' => $r['hora_fim']
        ];
    }
    $stmt->close();
    return $arr;
}

// ==================== INICIALIZAÇÃO DE DADOS ====================
if ($cpfUsuario) garantirSolicitante($conexao, $cpfUsuario);
$chaves = buscarChaves($conexao);
$chaveSelecionada = (int)($_GET['chave_id'] ?? 0);
$emprestimosFixos = $chaveSelecionada ? buscarEmprestimosPorChave($conexao, $chaveSelecionada) : [];

// ==================== PROCESSAMENTO DO FORMULÁRIO ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave_id = (int)($_POST['chave_id'] ?? 0);
    $dias_semana = $_POST['dias_semana'] ?? [];
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fim = $_POST['hora_fim'] ?? '';

    // Validações básicas
    if (!$chave_id) {
        $mensagens[] = "Selecione uma chave válida.";
    }
    if (empty($dias_semana)) {
        $mensagens[] = "Selecione pelo menos um dia da semana.";
    }
    if ($hora_inicio >= $hora_fim) {
        $mensagens[] = "O horário de início deve ser menor que o horário de fim.";
    }

    // Verifica conflitos de horário
    foreach ($dias_semana as $dia) {
        $dia = (int)$dia;
        foreach (buscarEmprestimosPorChave($conexao, $chave_id) as $ef) {
            if ($ef['dia_semana'] === $dia) {
                if (!($hora_fim <= $ef['inicio'] || $hora_inicio >= $ef['fim'])) {
                    $mensagens[] = "Conflito: a chave já está agendada no dia {$dias[$dia]} das {$ef['inicio']} às {$ef['fim']}.";
                }
            }
        }
    }

    // Inserção se não houver erros
    if (empty($mensagens)) {
        $stmt = $conexao->prepare("INSERT INTO emprestimo_fixo (cpf_solicitante, id_chave, dia_semana, hora_inicio, hora_fim) VALUES (?, ?, ?, ?, ?)");
        foreach ($dias_semana as $dia) {
            $stmt->bind_param("siiss", $cpfUsuario, $chave_id, $dia, $hora_inicio, $hora_fim);
            $stmt->execute();
        }
        $stmt->close();
        $mensagens[] = "Empréstimo(s) fixo(s) registrado(s) com sucesso!";
        $emprestimosFixos = buscarEmprestimosPorChave($conexao, $chave_id);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../IMG/CM.png">
<title>Agendamentos por Chave</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-100 font-sans min-h-screen flex flex-col">
<?php include '../Includes/header.php'; ?>

<main class="flex flex-col md:flex-row gap-6 px-6 py-8 flex-1 pt-32">

   <!-- ==================== Mensagens de feedback ==================== -->
<div id="mensagens-container" class="fixed top-20 left-1/2 -translate-x-1/2 flex flex-col gap-2 z-50">
    <?php foreach ($mensagens as $msg): 
        // Define cor de fundo: vermelho se contém "Conflito" ou "Selecione", verde se sucesso
        $bg = strpos($msg, 'registrado') !== false ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800';
    ?>
        <div class="mensagem px-6 py-3 rounded-lg font-medium <?= $bg ?> shadow-lg"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
</div>

<script>
// Faz as mensagens desaparecerem após 5 segundos
setTimeout(() => {
    const msgs = document.querySelectorAll('#mensagens-container .mensagem');
    msgs.forEach(m => {
        m.classList.add('transition-opacity', 'duration-1000', 'opacity-0');
        setTimeout(() => m.remove(), 1000);
    });
}, 5000);
</script>


    <!-- ==================== Formulário de Cadastro ==================== -->
    <div class="w-full md:w-1/3 bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-5 text-blue-700">Cadastrar Empréstimo Fixo</h2>
        <form method="post" class="flex flex-col gap-4">
            <div>
                <label for="chave" class="font-medium mb-1 block">Chave:</label>
                <input list="chavesDisponiveis" id="chave" name="chave_texto" class="w-full border border-gray-300 rounded-lg p-3" placeholder="Selecione a chave..." required>
                <input type="hidden" id="chave_id" name="chave_id">
                <datalist id="chavesDisponiveis">
                    <?php foreach($chaves as $ch): ?>
                        <option data-id="<?= $ch['id'] ?>" value="<?= htmlspecialchars($ch['texto']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div>
                <label class="font-medium mb-1 block">Dias da Semana:</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach($dias as $idx => $dia): ?>
                        <label class="cursor-pointer flex-1 min-w-[80px] text-center">
                            <input type="checkbox" name="dias_semana[]" value="<?= $idx ?>" class="hidden peer">
                            <span class="px-4 py-2 rounded-lg border border-gray-300 peer-checked:bg-blue-700 peer-checked:text-white transition block">
                                <?= $dia ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label for="hora_inicio" class="font-medium mb-1 block">Hora Início:</label>
                <input type="time" name="hora_inicio" id="hora_inicio" required class="w-full border rounded-lg p-3">
            </div>

            <div>
                <label for="hora_fim" class="font-medium mb-1 block">Hora Fim:</label>
                <input type="time" name="hora_fim" id="hora_fim" required class="w-full border rounded-lg p-3">
            </div>

            <button type="submit" class="bg-blue-700 text-white p-3 rounded-lg font-semibold hover:bg-blue-800 transition">Registrar</button>
        </form>
    </div>

    <!-- ==================== Filtro e Tabela ==================== -->
    <div class="w-full md:w-2/3 bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-5 text-blue-700">Visualizar Agendamentos</h2>
        <form method="get" class="flex flex-col gap-4 mb-4">
            <input list="chavesDisponiveisFiltro" id="chaveFiltro" class="w-full border border-gray-300 rounded-lg p-3" placeholder="Digite o nome da chave...">
            <input type="hidden" name="chave_id" id="chaveFiltroId" value="<?= $chaveSelecionada ?>">
            <datalist id="chavesDisponiveisFiltro">
                <?php foreach($chaves as $ch): ?>
                    <option data-id="<?= $ch['id'] ?>" value="<?= htmlspecialchars($ch['texto']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <button type="submit" class="bg-blue-700 text-white p-3 rounded-lg font-semibold hover:bg-blue-800 transition">Filtrar</button>
        </form>

        <?php if($chaveSelecionada): ?>
            <h3 class="text-xl font-semibold mb-3 text-gray-700">
                Chave: <?= htmlspecialchars(array_filter($chaves, fn($c)=>$c['id']==$chaveSelecionada)[0]['nome'] ?? '') ?>
            </h3>

            <?php if(empty($emprestimosFixos)): ?>
                <p class="text-gray-700">Nenhum agendamento encontrado para esta chave.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-blue-200 text-left">
                                <th class="p-3 border-b">Nome</th>
                                <th class="p-3 border-b">Dia</th>
                                <th class="p-3 border-b">Início</th>
                                <th class="p-3 border-b">Fim</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($emprestimosFixos as $ef): ?>
                                <tr class="border-b hover:bg-blue-50">
                                    <td class="p-3"><?= htmlspecialchars($ef['nome']) ?></td>
                                    <td class="p-3"><?= $dias[$ef['dia_semana']] ?></td>
                                    <td class="p-3"><?= $ef['inicio'] ?></td>
                                    <td class="p-3"><?= $ef['fim'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</main>

<script>
// Preenche ID da chave
const inputChave = document.getElementById('chave');
const inputChaveId = document.getElementById('chave_id');
inputChave.addEventListener('input', () => {
    const opcoes = document.querySelectorAll('#chavesDisponiveis option');
    let id = '';
    opcoes.forEach(o => { if(o.value === inputChave.value) id = o.dataset.id; });
    inputChaveId.value = id;
});

const inputFiltro = document.getElementById('chaveFiltro');
const inputFiltroId = document.getElementById('chaveFiltroId');
inputFiltro.addEventListener('input', () => {
    const opcoes = document.querySelectorAll('#chavesDisponiveisFiltro option');
    let id = '';
    opcoes.forEach(o => { if(o.value === inputFiltro.value) id = o.dataset.id; });
    inputFiltroId.value = id;
});
</script>
</body>
</html>
