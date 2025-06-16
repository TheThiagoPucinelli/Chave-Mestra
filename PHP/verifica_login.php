<?php
session_start();

$tempoMaximo = 30 * 60; // 30 minutos em segundos (1800 segundos)


if (!isset($_SESSION['cpf']) || !isset($_COOKIE['usuario_logado'])) {
    header("Location: ../PHP/login.php?expired=1");
    exit;
}

if (time() > $_COOKIE['usuario_logado']) {
    setcookie('usuario_logado', '', time() - 3600, "/");
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
    setcookie('usuario_logado', time() + $tempoMaximo, time() + $tempoMaximo, "/");
    $tempoRestante = $tempoMaximo * 1000; // usa o tempo máximo fixo
}
?>
