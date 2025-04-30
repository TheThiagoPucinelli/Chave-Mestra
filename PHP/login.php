<?php
// login.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados do formulário
    $matricula = $_POST['matricula'];
    $senha = $_POST['senha'];

    // Simulação de validação (substitua com sua lógica de autenticação)
    if ($matricula === '123456789' && $senha === 'senha123') {
        // Redirecionar para a página de dashboard ou área restrita
        header('Location: /dashboard.php');
        exit;
    } else {
        // Caso o login falhe, exibir uma mensagem de erro
        echo "Matrícula/CPF ou senha inválidos!";
    }
}
?>
