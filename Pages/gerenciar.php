<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$cpf_logado = $_SESSION['cpf'] ?? '';
if (!$cpf_logado) header("Location: login.php");

// Verifica gerente
$isGerente = false;
$stmtAdm = mysqli_prepare($conexao, "SELECT tipo FROM usuario_adm WHERE cpf = ?");
mysqli_stmt_bind_param($stmtAdm, "s", $cpf_logado);
mysqli_stmt_execute($stmtAdm);
$resAdm = mysqli_stmt_get_result($stmtAdm);
if ($resAdm && mysqli_num_rows($resAdm) > 0) {
    $adm = mysqli_fetch_assoc($resAdm);
    if (($adm['tipo'] ?? '') === 'Gerente') $isGerente = true;
}
if (!$isGerente) {
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Acesso negado</title>
    <script>setTimeout(()=>window.location.href="agendar.php",3000);</script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    </head><body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-red-100 text-red-700 p-6 rounded-lg shadow text-center">
    <h2 class="text-xl font-bold mb-2">Acesso negado</h2>
    <p>Somente gerentes podem acessar esta página.</p>
    <p class="mt-2 text-sm text-gray-600">Você será redirecionado.</p>
    </div></body></html>'; exit();
}

// Mensagens
$erroMsg = $sucessoMsg = "";

// Promoção a administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_promover'])) {
    $cpfPromover = trim($_POST['cpf_promover']);
    $stmt = mysqli_prepare($conexao, "SELECT cpf FROM usuario WHERE cpf = ?");
    mysqli_stmt_bind_param($stmt, "s", $cpfPromover);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($res) == 0) $erroMsg = "CPF não cadastrado.";
    else {
        $stmt2 = mysqli_prepare($conexao, "SELECT cpf FROM usuario_adm WHERE cpf = ?");
        mysqli_stmt_bind_param($stmt2, "s", $cpfPromover);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        if (mysqli_num_rows($res2) > 0) $erroMsg = "Usuário já é administrador.";
        else {
            $tipo='Administrador';
            $insert = mysqli_prepare($conexao,"INSERT INTO usuario_adm (cpf,tipo) VALUES (?,?)");
            mysqli_stmt_bind_param($insert,"ss",$cpfPromover,$tipo);
            mysqli_stmt_execute($insert) ? $sucessoMsg="Usuário promovido!" : $erroMsg="Erro: ".mysqli_error($conexao);
        }
    }
}

// Mês atual
$mesAtual = date('m');
$anoAtual = date('Y');

// Histórico completo
$historicoFull = mysqli_query($conexao,"SELECT e.id_emprestimo, c.nome AS chave_nome, c.numero_identificacao,
    u.nome AS solicitante, e.data_inicio_reserva, e.data_fim_reserva,
    e.hora_data_retirada, e.hora_data_devolucao, e.categoria,
    adm.nome AS confirmado_por
    FROM emprestimo e
    INNER JOIN chave c ON e.id_chave = c.id_chave
    INNER JOIN usuario u ON u.cpf = e.cpf_solicitante
    LEFT JOIN usuario adm ON adm.cpf = e.cpf_adm
    ORDER BY e.id_emprestimo DESC
");

// Limite por página
$limite = 10;

// Dados para gráficos (mantidos)
$graf1Labels=[]; $graf1Totais=[];
$dados1 = mysqli_query($conexao,"SELECT c.nome, COUNT(*) AS total 
    FROM emprestimo e 
    INNER JOIN chave c ON e.id_chave=c.id_chave
    WHERE MONTH(e.data_inicio_reserva)='$mesAtual' AND YEAR(e.data_inicio_reserva)='$anoAtual'
    GROUP BY c.id_chave");
while($row=mysqli_fetch_assoc($dados1)){ $graf1Labels[]=$row['nome']; $graf1Totais[]=(int)$row['total']; }

$graf2Labels=[]; $graf2Totais=[];
$dados2 = mysqli_query($conexao,"SELECT HOUR(e.data_inicio_reserva) AS hora, COUNT(*) AS total
    FROM emprestimo e
    WHERE MONTH(e.data_inicio_reserva)='$mesAtual' AND YEAR(e.data_inicio_reserva)='$anoAtual'
    GROUP BY HOUR(e.data_inicio_reserva)
    ORDER BY hora");
while($row=mysqli_fetch_assoc($dados2)){ $graf2Labels[] = sprintf("%02d:00", $row['hora']); $graf2Totais[]=(int)$row['total']; }

$graf3Labels=[]; $graf3Totais=[];
$dados3 = mysqli_query($conexao,"SELECT DAY(e.data_inicio_reserva) AS dia, COUNT(*) AS total
    FROM emprestimo e
    WHERE MONTH(e.data_inicio_reserva)='$mesAtual' AND YEAR(e.data_inicio_reserva)='$anoAtual'
    GROUP BY DAY(e.data_inicio_reserva)
    ORDER BY dia");
while($row=mysqli_fetch_assoc($dados3)){ $graf3Labels[]='Dia '.$row['dia']; $graf3Totais[]=(int)$row['total']; }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciar Administradores</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="icon" type="image/png" href="../IMG/CM.png">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<?php include '../Includes/header.php'; ?>

<main class="flex flex-col items-center p-6 space-y-6 w-full mt-32">

<!-- Mensagens -->
<?php if($erroMsg||$sucessoMsg): ?>
<div id="msgToast" class="fixed top-6 z-50 max-w-xl w-full mx-auto px-6">
    <?php if($erroMsg): ?><div class="bg-red-100 text-red-700 p-4 rounded-lg shadow mb-2"><?= htmlspecialchars($erroMsg) ?></div><?php endif; ?>
    <?php if($sucessoMsg): ?><div class="bg-green-100 text-green-700 p-4 rounded-lg shadow mb-2"><?= htmlspecialchars($sucessoMsg) ?></div><?php endif; ?>
</div>
<script>setTimeout(()=>document.getElementById('msgToast').remove(),5000);</script>
<?php endif; ?>

<!-- Promoção -->
<div class="w-full max-w-3xl bg-white rounded-xl shadow-lg p-6">
<h2 class="text-xl font-bold text-gray-700 mb-4">Promover Usuário a Administrador</h2>
<form method="POST" class="flex space-x-3">
    <input type="text" name="cpf_promover" maxlength="11" placeholder="Digite o CPF" class="flex-1 border px-3 py-2 rounded-lg shadow focus:ring-2 focus:ring-blue-500">
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Promover</button>
</form>
</div>

<!-- Histórico com Filtros e Paginação -->
<div class="w-full max-w-6xl bg-white rounded-xl shadow-lg p-6 overflow-x-auto">
    <h2 class="text-xl font-bold text-gray-700 mb-4">Histórico Completo de Empréstimos</h2>

    <!-- 🔍 Filtros -->
    <div class="flex flex-wrap gap-3 mb-4">
        <div>
            <label for="filtroDia" class="block text-sm font-medium text-gray-600">Dia</label>
            <input type="number" id="filtroDia" class="border rounded px-3 py-1 w-24" placeholder="DD" min="1" max="31">
        </div>
        <div>
            <label for="filtroMes" class="block text-sm font-medium text-gray-600">Mês</label>
            <input type="number" id="filtroMes" class="border rounded px-3 py-1 w-24" placeholder="MM" min="1" max="12">
        </div>
        <div>
            <label for="filtroAno" class="block text-sm font-medium text-gray-600">Ano</label>
            <input type="number" id="filtroAno" class="border rounded px-3 py-1 w-28" placeholder="AAAA" min="2000" max="2100">
        </div>
        <div class="flex items-end">
            <button id="btnFiltrar" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filtrar</button>
            <button id="btnLimpar" class="ml-2 bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Limpar</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="tabelaHistorico" class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="border px-3 py-2">ID</th>
                    <th class="border px-3 py-2">Chave</th>
                    <th class="border px-3 py-2">Identificação</th>
                    <th class="border px-3 py-2">Solicitante</th>
                    <th class="border px-3 py-2">Início</th>
                    <th class="border px-3 py-2">Fim</th>
                    <th class="border px-3 py-2">Retirada</th>
                    <th class="border px-3 py-2">Devolução</th>
                    <th class="border px-3 py-2">Confirmado por</th>
                    <th class="border px-3 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($h = mysqli_fetch_assoc($historicoFull)):
                    $status='Reservado'; $class='bg-green-100 text-green-900';
                    if (($h['categoria']??'')==='Cancelado'){$status='Cancelado';$class='bg-red-200 text-red-900 font-bold';}
                    elseif(!empty($h['hora_data_devolucao'])){$status='Devolvido';$class='bg-blue-200 text-blue-900 font-bold';}
                    elseif(!empty($h['hora_data_retirada']) && empty($h['hora_data_devolucao'])){$status='Retirado';$class='bg-yellow-200 text-yellow-900 font-bold';}
                ?>
                <tr class="tabelaLinha">
                    <td class="border px-3 py-2"><?= $h['id_emprestimo'] ?></td>
                    <td class="border px-3 py-2"><?= htmlspecialchars($h['chave_nome']) ?></td>
                    <td class="border px-3 py-2"><?= htmlspecialchars($h['numero_identificacao']) ?></td>
                    <td class="border px-3 py-2"><?= htmlspecialchars($h['solicitante']) ?></td>
                    <td class="border px-3 py-2"><?= $h['data_inicio_reserva'] ?></td>
                    <td class="border px-3 py-2"><?= $h['data_fim_reserva'] ?></td>
                    <td class="border px-3 py-2"><?= $h['hora_data_retirada'] ?: '-' ?></td>
                    <td class="border px-3 py-2"><?= $h['hora_data_devolucao'] ?: '-' ?></td>
                    <td class="border px-3 py-2"><?= $h['confirmado_por'] ?: '-' ?></td>
                    <td class="border px-3 py-2"><span class="px-2 py-1 rounded <?= $class ?>"><?= $status ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="flex justify-between mt-4">
        <button id="btnPrev" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Anterior</button>
        <button id="btnNext" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Próximo</button>
    </div>
</div>


<!-- Gráficos -->
<div class="w-full max-w-6xl flex flex-col md:flex-row gap-6 mt-6">
    <div class="w-full md:w-1/3 bg-white p-4 rounded-xl shadow">
        <h3 class="text-lg font-bold mb-2 text-center">Reservas por Chave (Mês Atual)</h3>
        <canvas id="graf1" height="200"></canvas>
    </div>
    <div class="w-full md:w-1/3 bg-white p-4 rounded-xl shadow">
        <h3 class="text-lg font-bold mb-2 text-center">Horários Mais Comuns (Mês Atual)</h3>
        <canvas id="graf2" height="200"></canvas>
    </div>
    <div class="w-full md:w-1/3 bg-white p-4 rounded-xl shadow">
        <h3 class="text-lg font-bold mb-2 text-center">Reservas por Dia (Mês Atual)</h3>
        <canvas id="graf3" height="200"></canvas>
    </div>
</div>

<script>
// Gráficos
const grafico1 = new Chart(document.getElementById('graf1').getContext('2d'),{
    type:'bar',
    data:{labels: <?= json_encode($graf1Labels) ?>, datasets:[{label:'Total', data: <?= json_encode($graf1Totais) ?>, backgroundColor:'rgba(59,130,246,0.7)'}]},
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
const grafico2 = new Chart(document.getElementById('graf2').getContext('2d'),{
    type:'bar',
    data:{labels: <?= json_encode($graf2Labels) ?>, datasets:[{label:'Total', data: <?= json_encode($graf2Totais) ?>, backgroundColor:'rgba(16,185,129,0.7)'}]},
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
const grafico3 = new Chart(document.getElementById('graf3').getContext('2d'),{
    type:'line',
    data:{labels: <?= json_encode($graf3Labels) ?>, datasets:[{label:'Total', data: <?= json_encode($graf3Totais) ?>, borderColor:'rgba(245,158,11,0.8)', backgroundColor:'rgba(245,158,11,0.3)', fill:true}]},
    options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
// 🔍 Filtro por dia/mês/ano
const inputDia = document.getElementById('filtroDia');
const inputMes = document.getElementById('filtroMes');
const inputAno = document.getElementById('filtroAno');
const btnFiltrar = document.getElementById('btnFiltrar');
const btnLimpar = document.getElementById('btnLimpar');

btnFiltrar.addEventListener('click', () => {
    const dia = inputDia.value.trim();
    const mes = inputMes.value.trim();
    const ano = inputAno.value.trim();

    linhas.forEach(linha => {
        const dataTexto = linha.children[4].textContent.trim(); // coluna "Início"
        const [anoData, mesData, diaData] = dataTexto.split('-'); // formato AAAA-MM-DD
        let visivel = true;

        if (dia && parseInt(dia) != parseInt(diaData)) visivel = false;
        if (mes && parseInt(mes) != parseInt(mesData)) visivel = false;
        if (ano && parseInt(ano) != parseInt(anoData)) visivel = false;

        linha.style.display = visivel ? '' : 'none';
    });
});

btnLimpar.addEventListener('click', () => {
    inputDia.value = '';
    inputMes.value = '';
    inputAno.value = '';
    linhas.forEach(linha => linha.style.display = '');
});


// Paginação tabela
const linhas = document.querySelectorAll('.tabelaLinha');
const limite = <?= $limite ?>;
let paginaAtual = 1;
const totalPaginas = Math.ceil(linhas.length / limite);

function mostrarPagina(pagina) {
    const inicio = (pagina - 1) * limite;
    const fim = inicio + limite;
    linhas.forEach((linha, i) => {
        linha.style.display = (i >= inicio && i < fim) ? '' : 'none';
    });
}
document.getElementById('btnPrev').addEventListener('click', () => {
    if(paginaAtual > 1) { paginaAtual--; mostrarPagina(paginaAtual); }
});
document.getElementById('btnNext').addEventListener('click', () => {
    if(paginaAtual < totalPaginas) { paginaAtual++; mostrarPagina(paginaAtual); }
});
mostrarPagina(paginaAtual);
</script>

</main>
</body>
</html>
