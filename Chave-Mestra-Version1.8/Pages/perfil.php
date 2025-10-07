<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$cpf_logado = $_SESSION['cpf'] ?? '';

if (!$cpf_logado) {
    header('Location: ../Pages/login.php');
    exit();
}

// --- Buscar dados do usuário ---
$usuario = [];
$stmt = mysqli_prepare($conexao, "SELECT cpf, nome, email FROM usuario WHERE cpf = ?");
mysqli_stmt_bind_param($stmt, "s", $cpf_logado);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
} else {
    $erroMsg = "Erro ao carregar dados do usuário.";
}

// --- Verificar se é administrador ---
$usuario['nivel'] = 'Usuário';
$stmtAdm = mysqli_prepare($conexao, "SELECT cpf FROM usuario_adm WHERE cpf = ?");
mysqli_stmt_bind_param($stmtAdm, "s", $cpf_logado);
mysqli_stmt_execute($stmtAdm);
$resAdm = mysqli_stmt_get_result($stmtAdm);
if ($resAdm && mysqli_num_rows($resAdm) > 0) {
    $usuario['nivel'] = 'Administrador';
}

// --- Buscar histórico de empréstimos ---
$historico = [];
$stmtHist = mysqli_prepare($conexao, "
    SELECT 
        c.nome AS nome_chave, 
        c.numero_identificacao, 
        c.descricao, 
        c.quantidade, 
        e.data_inicio_reserva, 
        e.data_fim_reserva, 
        e.hora_data_retirada, 
        e.hora_data_devolucao,
        CASE 
            WHEN e.hora_data_retirada IS NULL THEN 'Reservado'
            WHEN e.hora_data_retirada IS NOT NULL AND e.hora_data_devolucao IS NULL THEN 'Em uso'
            WHEN e.hora_data_devolucao IS NOT NULL THEN 'Devolvido'
            ELSE 'Desconhecido'
        END AS status
    FROM emprestimo e
    INNER JOIN chave c ON e.id_chave = c.id_chave
    WHERE e.cpf_solicitante = ?
    ORDER BY e.data_inicio_reserva DESC
");
mysqli_stmt_bind_param($stmtHist, "s", $cpf_logado);
mysqli_stmt_execute($stmtHist);
$resHist = mysqli_stmt_get_result($stmtHist);
if ($resHist && mysqli_num_rows($resHist) > 0) {
    while ($row = mysqli_fetch_assoc($resHist)) {
        $historico[] = $row;
    }
}

// --- Processar atualização do perfil ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome  = trim($_POST['nome'] ?? '');
    $novoEmail = trim($_POST['email'] ?? '');
    $novaSenha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        $erroMsg = "Por favor, insira um email válido.";
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT cpf FROM usuario WHERE email = ? AND cpf != ?");
        mysqli_stmt_bind_param($stmt, "ss", $novoEmail, $cpf_logado);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultado) > 0) {
            $erroMsg = "Este email já está em uso por outro usuário.";
        }

        if (!empty($novaSenha) || !empty($confirmarSenha)) {
            if ($novaSenha === $confirmarSenha) {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            } else {
                $erroMsg = "As senhas não conferem.";
            }
        }

        if (empty($erroMsg)) {
            if (!empty($senhaHash)) {
                $sql = "UPDATE usuario SET nome = ?, email = ?, senha = ? WHERE cpf = ?";
            } else {
                $sql = "UPDATE usuario SET nome = ?, email = ? WHERE cpf = ?";
            }

            if ($stmt = mysqli_prepare($conexao, $sql)) {
                if (!empty($senhaHash)) {
                    mysqli_stmt_bind_param($stmt, "ssss", $novoNome, $novoEmail, $senhaHash, $cpf_logado);
                } else {
                    mysqli_stmt_bind_param($stmt, "sss", $novoNome, $novoEmail, $cpf_logado);
                }
                if (mysqli_stmt_execute($stmt)) {
                    $sucessoMsg = "Perfil atualizado com sucesso!";
                    $_SESSION['nome']  = $novoNome;
                    $_SESSION['email'] = $novoEmail;
                    $usuario['nome']  = $novoNome;
                    $usuario['email'] = $novoEmail;
                } else {
                    $erroMsg = "Erro ao atualizar o perfil: " . mysqli_error($conexao);
                }
                mysqli_stmt_close($stmt);
            } else {
                $erroMsg = "Erro na preparação da query: " . mysqli_error($conexao);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="CM.png" type="image/x-icon">
<link rel="icon" type="image/png" href="../IMG/cmpage.png">
<script src="https://cdn.tailwindcss.com"></script>
<title>Perfil - Chave Mestra</title>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php include '../Includes/header.php'; ?>
<br><br><br><br>

<main class="flex-1 flex flex-col items-center justify-start p-6 space-y-6">

    <!-- Mensagens de feedback -->
    <?php if (!empty($erroMsg)): ?>
        <div class="w-full max-w-3xl p-4 bg-red-100 text-red-700 border border-red-300 rounded-lg shadow">
            <?= htmlspecialchars($erroMsg) ?>
        </div>
    <?php elseif (!empty($sucessoMsg)): ?>
        <div class="w-full max-w-3xl p-4 bg-green-100 text-green-700 border border-green-300 rounded-lg shadow">
            <?= htmlspecialchars($sucessoMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Card do Perfil -->
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl transition-all">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Meu Perfil</h2>
            <button id="editarBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Editar
            </button>
        </div>

        <!-- Visualização do perfil -->
        <div id="perfilView" class="space-y-2">
            <p><span class="font-semibold text-gray-700">Nome:</span> <?= htmlspecialchars($usuario['nome'] ?? '-') ?></p>
            <p><span class="font-semibold text-gray-700">Email:</span> <?= htmlspecialchars($usuario['email'] ?? '-') ?></p>
            <p><span class="font-semibold text-gray-700">Nível:</span> <?= htmlspecialchars($usuario['nivel'] ?? 'Usuário') ?></p>
            <p>
                <span class="font-semibold text-gray-700">CPF:</span> 
                <span id="cpfText">***********</span>
                <button id="mostrarCpfBtn" class="ml-2 px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition text-sm">Mostrar</button>
            </p>
        </div>

        <!-- Formulário de edição escondido inicialmente -->
        <form id="perfilEdit" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                <input type="text" name="nome" maxlength="60"
                    placeholder="<?= htmlspecialchars($usuario['nome'] ?? 'Não informado') ?>"
                    value="<?= htmlspecialchars($_POST['nome'] ?? $usuario['nome'] ?? '') ?>"
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" maxlength="60"
                    placeholder="<?= htmlspecialchars($usuario['email'] ?? 'Não informado') ?>"
                    value="<?= htmlspecialchars($_POST['email'] ?? $usuario['email'] ?? '') ?>"
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                <input type="password" name="senha" maxlength="30" placeholder="Digite sua nova senha"
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                <input type="password" name="confirmar_senha" minlength="12" maxlength="30" placeholder="Confirme sua nova senha"
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="flex justify-between">
                <button type="button" id="cancelarBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Salvar</button>
            </div>
        </form>
    </div>

    <!-- Histórico de Empréstimos -->
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl transition-all mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Histórico de Empréstimos</h2>

        <?php if(empty($historico)): ?>
            <p class="text-gray-600">Nenhum histórico encontrado.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse text-sm">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="border px-3 py-2">Chave</th>
                            <th class="border px-3 py-2">Identificação</th>
                            <th class="border px-3 py-2">Descrição</th>
                            <th class="border px-3 py-2">Início</th>
                            <th class="border px-3 py-2">Fim</th>
                            <th class="border px-3 py-2">Retirada</th>
                            <th class="border px-3 py-2">Devolução</th>
                            <th class="border px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($historico as $h): ?>
                            <tr>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['nome_chave']) ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['numero_identificacao']) ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['descricao']) ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['data_inicio_reserva']) ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['data_fim_reserva']) ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['hora_data_retirada'] ?? '-') ?></td>
                                <td class="border px-3 py-2"><?= htmlspecialchars($h['hora_data_devolucao'] ?? '-') ?></td>
                                <td class="border px-3 py-2">
                                    <span class="px-2 py-1 rounded-full 
                                        <?php 
                                            echo $h['status'] === 'Reservado' ? 'bg-yellow-100 text-yellow-800' :
                                                 ($h['status'] === 'Em uso' ? 'bg-blue-100 text-blue-800' :
                                                 ($h['status'] === 'Devolvido' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')); 
                                        ?>">
                                        <?= htmlspecialchars($h['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</main>

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

<script>
// Alternar entre visualização e edição do perfil
document.addEventListener('DOMContentLoaded', () => {
    const editarBtn = document.getElementById('editarBtn');
    const cancelarBtn = document.getElementById('cancelarBtn');
    const perfilView = document.getElementById('perfilView');
    const perfilEdit = document.getElementById('perfilEdit');

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($erroMsg)): ?>
        perfilView.classList.add('hidden');
        perfilEdit.classList.remove('hidden');
    <?php endif; ?>

    editarBtn.addEventListener('click', () => {
        perfilView.classList.add('hidden');
        perfilEdit.classList.remove('hidden');
    });

    cancelarBtn.addEventListener('click', () => {
        perfilEdit.classList.add('hidden');
        perfilView.classList.remove('hidden');
    });

    // Mostrar/esconder CPF
    const mostrarCpfBtn = document.getElementById('mostrarCpfBtn');
    const cpfText = document.getElementById('cpfText');
    let cpfVisivel = false;
    mostrarCpfBtn.addEventListener('click', () => {
        if (!cpfVisivel) {
            cpfText.textContent = '<?= htmlspecialchars($usuario["cpf"] ?? "-") ?>';
            mostrarCpfBtn.textContent = 'Esconder';
        } else {
            cpfText.textContent = '***********';
            mostrarCpfBtn.textContent = 'Mostrar';
        }
        cpfVisivel = !cpfVisivel;
    });
});
</script>

</body>
</html>
