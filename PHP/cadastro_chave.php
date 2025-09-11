<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão
include '../BD/conexao.php'; // Inclui o arquivo de conexão com o banco de dados

$erroMsg = "";    // Variável para armazenar mensagem de erro
$successMsg = ""; // Variável para armazenar mensagem de sucesso

// Obtém o CPF do administrador armazenado na sessão, ou string vazia se não existir
$cpf_adm = $_SESSION['cpf'] ?? '';

// Prepara uma consulta para verificar se o usuário é um administrador válido
$admCheck = $conexao->prepare("SELECT * FROM usuario_adm WHERE cpf = ?");
$admCheck->bind_param("s", $cpf_adm); // Liga o CPF como parâmetro da consulta
$admCheck->execute();                 // Executa a consulta
$resAdm = $admCheck->get_result();   // Obtém o resultado da consulta

// Se não encontrar o CPF na tabela de administradores, bloqueia o acesso
if ($resAdm->num_rows === 0) {
    echo "<p style='color:red; text-align:center;'>Você não tem permissão para cadastrar chaves.</p>";
    exit; // Interrompe o script para não continuar o processamentoa
}

// Se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e limpa os dados enviados pelo formulário
    $nome = trim($_POST['nome'] ?? '');
    $numero_identificacao = trim($_POST['numero_identificacao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $quantidade = trim($_POST['quantidade'] ?? '');
    $status = 'disponível'; // Status padrão da chave ao cadastrar

    // Verifica se já existe uma chave com o mesmo número de identificação
    $verifica = $conexao->prepare("SELECT * FROM chave WHERE numero_identificacao = ?");
    $verifica->bind_param("s", $numero_identificacao);
    $verifica->execute();
    $resultado = $verifica->get_result();

    if ($resultado->num_rows > 0) {
        // Já existe uma chave com esse número, retorna erro
        $erroMsg = "Número de identificação já cadastrado.";
    } else {
        // Caso não exista, insere a nova chave no banco
        $stmt = $conexao->prepare("INSERT INTO chave (nome, numero_identificacao, descricao, quantidade, status, cpf_adm) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nome, $numero_identificacao, $descricao, $quantidade, $status, $cpf_adm);

        if ($stmt->execute()) {
            // Cadastro realizado com sucesso
            $successMsg = "Chave cadastrada com sucesso!";
        } else {
            // Erro na execução da query de inserção
            $erroMsg = "Erro ao cadastrar chave: " . $stmt->error;
        }

        $stmt->close(); // Fecha o statement de inserção
    }

    $verifica->close(); // Fecha o statement de verificação
}

$admCheck->close();   // Fecha o statement de verificação do administrador
$conexao->close();    // Fecha a conexão com o banco

// Exibe mensagens para o usuário (erro ou sucesso)
if (!empty($erroMsg)) {
    echo "<p style='color:red; text-align:center;'>$erroMsg</p>";
}

if (!empty($successMsg)) {
    echo "<p style='color:green; text-align:center;'>$successMsg</p>";
}
?>
