<?php
session_start();

// Limpa todas as variáveis de sessão
session_unset();

// Destroi a sessão
session_destroy();

// Remove o cookie de controle de inatividade (se existir)
setcookie('usuario_logado', '', time() - 3600, "/");

// Redireciona para a página de login
header("Location: ../PHP/login.php");
exit;
