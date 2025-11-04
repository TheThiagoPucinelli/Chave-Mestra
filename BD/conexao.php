<?php

// Configurações do banco de dados
$servidor = "localhost"; // Endereço do servidor de banco de dados
$usuario = "root";        // Usuário do banco
$senha = "";              // Senha do banco (vazia aqui)
$banco = "chave_mestra4"; // Nome do banco de dados

// Cria uma nova conexão com o banco usando MySQLi
$conexao = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conexao->connect_error) {
    // Encerra o script e exibe mensagem de erro caso a conexão falhe
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
