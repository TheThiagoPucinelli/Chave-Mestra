<?php
session_start();
include '../BD/conexao.php'; // conexão com o banco

$tempoMaximo = 30 * 60; // 30 minutos em segundos

// --- Verifica se a sessão está iniciada ---
if (!isset($_SESSION['cpf'])) {
    header("Location: ../PHP/login.php?expired=1");
    exit;
}

// --- Verifica expiração baseada em tempo da sessão ---
if (!isset($_SESSION['ultimo_acesso'])) {
    $_SESSION['ultimo_acesso'] = time();
} else if ((time() - $_SESSION['ultimo_acesso']) > $tempoMaximo) {
    session_unset();
    session_destroy();

    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8" />
        <title>Sessão Expirada</title>
        <script>
            alert("Sua sessão expirou por inatividade.");
            window.location.href = "../PHP/login.php";
        </script>
    </head>
    <body></body>
    </html>';
    exit;
} else {
    // Atualiza último acesso
    $_SESSION['ultimo_acesso'] = time();
}

// --- Verifica se o usuário é administrador ---
$cpfUsuario = $_SESSION['cpf'];
$stmt = $conexao->prepare("SELECT tipo FROM usuario_adm WHERE cpf = ?");
$stmt->bind_param("s", $cpfUsuario);
$stmt->execute();
$result = $stmt->get_result();

$isAdmin = ($result && $result->num_rows === 1);

// --- Bloqueia acesso se não for admin (apenas páginas restritas) ---
if (isset($paginaRestrita) && $paginaRestrita && !$isAdmin) {
    header("Location: ../Pages/agendar.php");
    exit;
}
?>
