<?php
include __DIR__ . '/../PHP/verifica_login.php';
include_once '../BD/conexao.php';

$cpf_logado = $_SESSION['cpf'] ?? '';

if (!$cpf_logado) {
    header('Location: ../Pages/login.php');
    exit();
}

// --- Buscar dados do usuário (nome, email, categoria) ---
$usuario = [];
$stmt = mysqli_prepare($conexao, "SELECT nome, email, categoria FROM usuario WHERE cpf = ?");
mysqli_stmt_bind_param($stmt, "s", $cpf_logado);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
} else {
    $erroMsg = "Erro ao carregar dados do usuário.";
}

// --- Determinar nível do usuário ---
$usuario['nivel'] = 'Usuário'; // padrão
$stmtAdm = mysqli_prepare($conexao, "SELECT tipo FROM usuario_adm WHERE cpf = ?");
mysqli_stmt_bind_param($stmtAdm, "s", $cpf_logado);
mysqli_stmt_execute($stmtAdm);
$resAdm = mysqli_stmt_get_result($stmtAdm);
if ($resAdm && mysqli_num_rows($resAdm) > 0) {
    $admData = mysqli_fetch_assoc($resAdm);
    $usuario['nivel'] = $admData['tipo'] ?? 'Administrador';
}

// Garantir categoria
$usuario['categoria'] = $usuario['categoria'] ?? 'Não informado';

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
            WHEN e.categoria = 'Cancelado' THEN 'Cancelado'
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
<link rel="icon" type="image/png" href="../IMG/CM.png">
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
            <p><span class="font-semibold text-gray-700">Categoria:</span> <?= htmlspecialchars($usuario['categoria']) ?></p>
            <p><span class="font-semibold text-gray-700">Nível:</span> <?= htmlspecialchars($usuario['nivel']) ?></p>
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

    <!-- Modal de confirmação antes da edição -->
    <div id="confirmacaoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Confirme sua identidade</h3>
            <p class="text-gray-600 mb-4">Para alterar suas informações, insira seu CPF e senha atuais:</p>
            <form id="confirmacaoForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" id="cpfConfirm" class="w-full px-4 py-2 border rounded-lg" placeholder="Seu CPF">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Senha atual</label>
                    <input type="password" id="senhaConfirm" class="w-full px-4 py-2 border rounded-lg" placeholder="Senha atual">
                </div>
                <div class="flex justify-between">
                    <button type="button" id="cancelConfirm" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Cancelar</button>
                    <button type="button" id="okConfirm" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Confirmar</button>
                </div>
                <p id="msgErroConfirm" class="text-red-600 text-sm mt-2 hidden"></p>
            </form>
        </div>
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
                        <?php foreach($historico as $h): 
                            $status = $h['status'] ?? 'Desconhecido';
                            $statusClasses = match($status) {
                                'Reservado'  => 'bg-yellow-100 text-yellow-800',
                                'Em uso'     => 'bg-blue-100 text-blue-800',
                                'Devolvido'  => 'bg-green-100 text-green-800',
                                'Cancelado'  => 'bg-red-200 text-red-900 font-bold',
                                default      => 'bg-gray-200 text-gray-700'
                            };
                            $hora_retirada = $h['hora_data_retirada'] ?? '-';
                            $hora_devolucao = $h['hora_data_devolucao'] ?? '-';
                        ?>
                        <tr>
                            <td class="border px-3 py-2"><?= htmlspecialchars($h['nome_chave']) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($h['numero_identificacao']) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($h['descricao']) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($h['data_inicio_reserva']) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($h['data_fim_reserva']) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($hora_retirada) ?></td>
                            <td class="border px-3 py-2"><?= htmlspecialchars($hora_devolucao) ?></td>
                            <td class="border px-3 py-2">
                                <span class="px-2 py-1 rounded-full <?= $statusClasses ?>">
                                    <?= htmlspecialchars($status) ?>
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
    <!-- Ícones GitHub e LinkedIn -->
  </div>
</footer>

<script>
// Alternar entre visualização e edição do perfil com confirmação
document.addEventListener('DOMContentLoaded', () => {
    const editarBtn = document.getElementById('editarBtn');
    const cancelarBtn = document.getElementById('cancelarBtn');
    const perfilView = document.getElementById('perfilView');
    const perfilEdit = document.getElementById('perfilEdit');
    const modal = document.getElementById('confirmacaoModal');
    const cancelConfirm = document.getElementById('cancelConfirm');
    const okConfirm = document.getElementById('okConfirm');
    const msgErro = document.getElementById('msgErroConfirm');

    editarBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        msgErro.classList.add('hidden');
    });

    cancelConfirm.addEventListener('click', () => {
        modal.classList.add('hidden');
        msgErro.classList.add('hidden');
    });

    okConfirm.addEventListener('click', () => {
        const cpf = document.getElementById('cpfConfirm').value.trim();
        const senha = document.getElementById('senhaConfirm').value.trim();

        if (!cpf || !senha) {
            msgErro.textContent = "CPF e senha são obrigatórios.";
            msgErro.classList.remove('hidden');
            return;
        }

        fetch('validar_cpf_senha.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({cpf, senha})
        })
        .then(res => res.json())
        .then(data => {
            if (data.sucesso) {
                modal.classList.add('hidden');
                perfilView.classList.add('hidden');
                perfilEdit.classList.remove('hidden');
            } else {
                msgErro.textContent = "CPF ou senha incorretos.";
                msgErro.classList.remove('hidden');
            }
        })
        .catch(() => {
            msgErro.textContent = "Erro ao validar. Tente novamente.";
            msgErro.classList.remove('hidden');
        });
    });

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($erroMsg)): ?>
        perfilView.classList.add('hidden');
        perfilEdit.classList.remove('hidden');
    <?php endif; ?>
});
</script>

</body>
</html>
