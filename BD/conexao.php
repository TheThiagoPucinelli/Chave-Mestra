<?php

$servidor = "localhost";
$usuario = "root";
$senha = ""; // 
$banco = "chave-mestra2";


$conexao = new mysqli($servidor, $usuario, $senha, $banco);


if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
