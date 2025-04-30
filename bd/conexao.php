<?php
// Configuração de dados do banco de dados
$servername = "localhost";  // Ajuste conforme seu servidor
$username = "root";         // Ajuste conforme seu banco de dados
$password = "";             // Ajuste conforme seu banco de dados
$dbname = "chave_mestra";   // Nome do banco de dados


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


return $conn;
?>
