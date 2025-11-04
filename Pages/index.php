<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$mensagem = "";
$erro = "";

// --- Buscar chaves agendadas (não retiradas) ---
$chaves = [];
$sqlChaves = "
    SELECT e.id_emprestimo, c.nome, c.numero_identificacao, u.nome AS nome_usuario,
           e.data_inicio_reserva, e.data_fim_reserva
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    JOIN usuario u ON e.cpf_solicitante = u.cpf
    WHERE e.hora_data_retirada IS NULL
    ORDER BY u.nome, c.nome
";
$resultChaves = $conexao->query($sqlChaves);
if ($resultChaves) {
    while ($row = $resultChaves->fetch_assoc()) {
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ") - Reservado por: " . $row['nome_usuario'];
        $chaves[] = [
            'id' => $row['id_emprestimo'],
            'texto' => $texto,
            'inicio' => $row['data_inicio_reserva'],
            'fim' => $row['data_fim_reserva']
        ];
    }
}

// --- Buscar chaves retiradas (não devolvidas) ---
$chavesRetiradas = [];
$sqlChavesRetiradas = "
    SELECT e.id_emprestimo, c.nome, c.numero_identificacao, u.nome AS nome_usuario,
           e.hora_data_retirada, e.hora_data_devolucao, e.data_fim_reserva
    FROM emprestimo e
    JOIN chave c ON e.id_chave = c.id_chave
    JOIN usuario u ON e.cpf_solicitante = u.cpf
    WHERE e.hora_data_retirada IS NOT NULL AND e.hora_data_devolucao IS NULL
    ORDER BY u.nome, c.nome
";
$resultChavesRetiradas = $conexao->query($sqlChavesRetiradas);
if ($resultChavesRetiradas) {
    while ($row = $resultChavesRetiradas->fetch_assoc()) {
        $texto = $row['nome'] . " (" . $row['numero_identificacao'] . ") - Retirado por: " . $row['nome_usuario'];
        $chavesRetiradas[] = [
            'id' => $row['id_emprestimo'],
            'texto' => $texto,
            'retirada' => $row['hora_data_retirada'],
            'devolucao' => $row['hora_data_devolucao'],
            'fim' => $row['data_fim_reserva']
        ];
    }
}

// --- Função auxiliar ---
function encontrarIdPorTexto($lista, $texto) {
    foreach ($lista as $item) {
        if ($item['texto'] === $texto) return $item['id'];
    }
    return null;
}

// --- Processar retirada ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'retirada') {
    $texto_chave = $_POST['chave_texto'] ?? '';
    $cpf_adm = $_SESSION['cpf'] ?? '';
    $emprestimo_id = encontrarIdPorTexto($chaves, $texto_chave);

    if (!$emprestimo_id) $erro = "Selecione a chave para retirada.";
    else {
        $stmt = $conexao->prepare("
            UPDATE emprestimo 
            SET hora_data_retirada = NOW(), cpf_adm = ?
            WHERE id_emprestimo = ? AND hora_data_retirada IS NULL
        ");
        if ($stmt) {
            $stmt->bind_param("si", $cpf_adm, $emprestimo_id);
            $stmt->execute();
            $mensagem = $stmt->affected_rows > 0 ? "Retirada da chave confirmada com sucesso." : "Esta retirada já foi confirmada ou não existe.";
            $stmt->close();
        } else $erro = "Erro na preparação da consulta.";
    }
}

// --- Processar devolução ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'devolucao') {
    $texto_chave_devolucao = $_POST['chave_texto_devolucao'] ?? '';
    $emprestimo_id = encontrarIdPorTexto($chavesRetiradas, $texto_chave_devolucao);

    if (!$emprestimo_id) $erro = "Selecione a chave que está sendo devolvida.";
    else {
        $stmt = $conexao->prepare("
            UPDATE emprestimo 
            SET hora_data_devolucao = NOW()
            WHERE id_emprestimo = ? AND hora_data_devolucao IS NULL
            LIMIT 1
        ");
        if ($stmt) {
            $stmt->bind_param("i", $emprestimo_id);
            $stmt->execute();
            $mensagem = $stmt->affected_rows > 0 ? "Devolução registrada com sucesso." : "Não foi encontrada retirada ativa para essa chave.";
            $stmt->close();
        } else $erro = "Erro na preparação da consulta.";
    }
}

// --- Cancelar retirada (com notificação) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'cancelar') {
    $emprestimo_id = (int)($_POST['id_emprestimo'] ?? 0);
    $cpf_adm = $_SESSION['cpf'] ?? '';

    if ($emprestimo_id) {
        $sqlBusca = "
            SELECT e.cpf_solicitante, c.nome AS chave_nome
            FROM emprestimo e
            JOIN chave c ON e.id_chave = c.id_chave
            WHERE e.id_emprestimo = ?
            LIMIT 1
        ";
        $stmtBusca = $conexao->prepare($sqlBusca);
        $stmtBusca->bind_param("i", $emprestimo_id);
        $stmtBusca->execute();
        $resBusca = $stmtBusca->get_result()->fetch_assoc();
        $stmtBusca->close();

        if ($resBusca) {
            $cpf_usuario = $resBusca['cpf_solicitante'];
            $nome_chave = $resBusca['chave_nome'];

            $stmt = $conexao->prepare("
                UPDATE emprestimo
                SET hora_data_retirada = NOW(),
                    hora_data_devolucao = NOW(),
                    cpf_adm = ?,
                    categoria = 'Cancelado'
                WHERE id_emprestimo = ? AND hora_data_devolucao IS NULL
                LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param("si", $cpf_adm, $emprestimo_id);
                $stmt->execute();
                $mensagem = $stmt->affected_rows > 0 ? "Agendamento cancelado com sucesso." : "Não foi possível cancelar o agendamento.";
                $stmt->close();

                // Inserir notificação
                $stmtNotif = $conexao->prepare("
                    INSERT INTO notificacao_usuario (cpf_usuario, id_emprestimo, mensagem, lida, data_criacao)
                    VALUES (?, ?, ?, 0, NOW())
                ");
                if ($stmtNotif) {
                    $mensagem_notificacao = "⚠️ Seu agendamento da chave '$nome_chave' foi cancelado pelo administrador.";
                    $stmtNotif->bind_param("sis", $cpf_usuario, $emprestimo_id, $mensagem_notificacao);
                    $stmtNotif->execute();
                    $stmtNotif->close();
                }

            } else $erro = "Erro ao cancelar o agendamento.";
        } else $erro = "Empréstimo não encontrado para cancelamento.";
    } else $erro = "Chave inválida para cancelamento.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="../IMG/CM.png">
<title>Gerenciamento de Chaves</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.card { background:white; border-radius:1rem; padding:2rem; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
.suggestions { position:absolute; background:white; border:1px solid #ccc; border-radius:0.5rem; max-height:200px; overflow-y:auto; width:100%; z-index:50; display:none; }
.suggestion-item { padding:0.5rem 1rem; cursor:pointer; }
.suggestion-item:hover { background:#e5e7eb; }
.info-box { background:#f9fafb; border:1px solid #d1d5db; border-radius:0.5rem; padding:1rem; margin-top:0.5rem; display:none; white-space: pre-line; }
</style>
</head>
<body class="bg-gray-100 font-sans flex flex-col min-h-screen">

<?php include '../Includes/header.php'; ?>
<br><br><br><br><br><br><br><br><br><br><br>

<main class="flex-grow">
<?php if($mensagem): ?>
<div class="mx-auto max-w-4xl bg-green-100 text-green-800 p-4 rounded mb-6 shadow"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if($erro): ?>
<div class="mx-auto max-w-4xl bg-red-100 text-red-800 p-4 rounded mb-6 shadow"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="max-w-4xl mx-auto card space-y-8">

<!-- Retirada -->
<div class="relative">
<h2 class="text-2xl font-bold mb-4 text-gray-800">Retirada de Chaves</h2>
<form method="post" id="formRetirada">
<input type="hidden" name="acao" value="retirada">
<input type="hidden" id="id_emprestimo_retirada" name="id_emprestimo">
<div class="grid md:grid-cols-3 gap-4">
  <div class="relative">
    <label class="block font-medium text-gray-700 mb-1">Chave Agendada</label>
    <input id="chave_retirada" name="chave_texto" autocomplete="off" placeholder="Digite ou selecione a chave..." class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400">
    <div id="sugestoesRetirada" class="suggestions"></div>
    <div id="infoRetirada" class="info-box"></div>
  </div>
  <div class="flex items-end">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow w-full">
      Confirmar Retirada
    </button>
  </div>
  <div class="flex items-end">
    <button type="button" id="cancelarRetiradaBtn" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3.5 px-20 rounded-lg shadow text-sm">
      Cancelar
    </button>
  </div>
</div>
</form>
</div>

<!-- Devolução -->
<div class="relative">
<h2 class="text-2xl font-bold mb-4 text-gray-800">Devolução de Chaves</h2>
<form method="post">
<input type="hidden" name="acao" value="devolucao">
<div class="grid md:grid-cols-2 gap-4">
  <div class="relative">
    <label class="block font-medium text-gray-700 mb-1">Chave para Devolução</label>
    <input id="chave_devolucao" name="chave_texto_devolucao" autocomplete="off" placeholder="Digite ou selecione a chave..." class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400">
    <div id="sugestoesDevolucao" class="suggestions"></div>
    <div id="infoDevolucao" class="info-box"></div>
  </div>
  <div class="flex items-end">
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow w-full">
      Confirmar Devolução
    </button>
  </div>
</div>
</form>
</div>

</div>
<br><br><br><br><br><br><br><br><br><br>

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
</main>

<script>
const chaves = <?= json_encode($chaves, JSON_UNESCAPED_UNICODE) ?>;
const chavesRetiradas = <?= json_encode($chavesRetiradas, JSON_UNESCAPED_UNICODE) ?>;

function formatarData(dataStr) {
    if (!dataStr) return '-';
    const dt = new Date(dataStr);
    const dia = String(dt.getDate()).padStart(2,'0');
    const mes = String(dt.getMonth()+1).padStart(2,'0');
    const ano = dt.getFullYear();
    const hora = String(dt.getHours()).padStart(2,'0');
    const min = String(dt.getMinutes()).padStart(2,'0');
    return `${dia}/${mes}/${ano} ${hora}:${min}`;
}

function setupAutoComplete(inputId, suggestionBoxId, infoId, lista, tipo) {
    const input = document.getElementById(inputId);
    const box = document.getElementById(suggestionBoxId);
    const info = document.getElementById(infoId);
    const idInput = document.getElementById('id_emprestimo_retirada');

    input.addEventListener('input', () => {
        const val = input.value.toLowerCase();
        box.innerHTML = '';
        info.style.display = 'none';
        if (!val) { box.style.display = 'none'; idInput.value = ''; return; }

        const filtradas = lista.filter(c => c.texto.toLowerCase().includes(val));
        if (!filtradas.length) { box.style.display = 'none'; idInput.value = ''; return; }

        filtradas.forEach(c => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = c.texto;

            item.addEventListener('click', () => {
                input.value = c.texto;
                box.style.display = 'none';
                info.style.display = 'block';
                if(tipo === 'retirada') idInput.value = c.id;

                let html = `<div style="font-weight:bold; color:#000;">${c.texto}</div>`;
                if (tipo === 'retirada') {
                    html += `<div style="color:#256D85;">Início (Agendamento): ${formatarData(c.inicio)}</div>`;
                    html += `<div style="color:#16A34A;">Fim (Agendamento): ${formatarData(c.fim)}</div>`;
                } else {
                    html += `<div style="color:#D97706;">Hora Retirada: ${formatarData(c.retirada)}</div>`;
                    html += `<div style="color:#16A34A;">Fim (Agendamento): ${c.fim ? formatarData(c.fim) : '-'}</div>`;
                }

                info.innerHTML = html;
            });

            box.appendChild(item);
        });

        box.style.display = 'block';
    });

    document.addEventListener('click', e => {
        if (!box.contains(e.target) && e.target !== input) box.style.display = 'none';
    });
}

// Cancelar retirada
document.getElementById('cancelarRetiradaBtn').addEventListener('click', () => {
    const idEmp = document.getElementById('id_emprestimo_retirada').value;
    if (!idEmp) { alert("Selecione uma chave para cancelar a retirada."); return; }
    if (confirm("Tem certeza que deseja cancelar este agendamento? Ele será marcado como cancelado.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        const acao = document.createElement('input');
        acao.name = 'acao';
        acao.value = 'cancelar';
        form.appendChild(acao);
        const idInput = document.createElement('input');
        idInput.name = 'id_emprestimo';
        idInput.value = idEmp;
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
});

setupAutoComplete('chave_retirada', 'sugestoesRetirada', 'infoRetirada', chaves, 'retirada');
setupAutoComplete('chave_devolucao', 'sugestoesDevolucao', 'infoDevolucao', chavesRetiradas, 'devolucao');
</script>

</body>
</html>
