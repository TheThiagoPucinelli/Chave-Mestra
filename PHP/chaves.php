<?php
// Importando o arquivo de conexão
require_once 'db/conexao.php'; // Ajuste o caminho conforme sua estrutura

// Variáveis para exibição de mensagens
$mensagem = '';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Coletar os dados do formulário e escapar as entradas para prevenir SQL Injection
    $numero_identificacao = mysqli_real_escape_string($conn, $_POST['numero_identificacao']);
    $local = mysqli_real_escape_string($conn, $_POST['local']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $predio = mysqli_real_escape_string($conn, $_POST['predio']);

    // Validar se os campos não estão vazios
    if (empty($numero_identificacao) || empty($local) || empty($descricao) || empty($predio)) {
        $mensagem = "Todos os campos são obrigatórios!";
    } else {
        // Verificar se já existe uma chave com o número de identificação informado
        $check_chave_sql = "SELECT * FROM chaves WHERE numero_identificacao = '$numero_identificacao'";
        $resultado = $conn->query($check_chave_sql);

        if ($resultado->num_rows > 0) {
            // Se já existir, exibe mensagem
            $mensagem = "Já existe uma chave cadastrada com esse número de identificação.";
        } else {
            // Caso contrário, insere os dados no banco de dados
            $sql = "INSERT INTO chaves (numero_identificacao, local, descricao, predio) VALUES ('$numero_identificacao', '$local', '$descricao', '$predio')";

            if ($conn->query($sql) === TRUE) {
                $mensagem = "Chave cadastrada com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar chave: " . $conn->error;
            }
        }
    }
}

// Fechar a conexão
$conn->close();

// Armazenar a mensagem de erro ou sucesso
session_start();
$_SESSION['mensagem'] = $mensagem;
?>
