<?php
session_start();

$tempoMaximo = 30 * 60; // 30 minutos em segundos (1800 segundos)

// Verifica se a sessão está iniciada com CPF e se o cookie de controle existe
if (!isset($_SESSION['cpf']) || !isset($_COOKIE['usuario_logado'])) {
    header("Location: ../PHP/login.php?expired=1");
    exit;
}

// Verifica se o cookie de controle de sessão expirou
if (time() > $_COOKIE['usuario_logado']) {
    // Apaga o cookie, encerra a sessão e avisa o usuário
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
    // Renova o cookie para mais 30 minutos
    setcookie('usuario_logado', time() + $tempoMaximo, time() + $tempoMaximo, "/");
    $tempoRestante = $tempoMaximo * 1000; 
}
?>
