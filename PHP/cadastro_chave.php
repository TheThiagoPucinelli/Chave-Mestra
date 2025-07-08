<?php
session_start();
include '../BD/conexao.php';

$erroMsg = "";
$successMsg = "";

// CPF do administrador
$cpf_adm = $_SESSION['cpf'] ?? '';

// Verifica se usuário é administrador
$admCheck = $conexao->prepare("SELECT * FROM usuario_adm WHERE cpf = ?");
$admCheck->bind_param("s", $cpf_adm);
$admCheck->execute();
$resAdm = $admCheck->get_result();

if ($resAdm->num_rows === 0) {
    // Não é administrador
    echo "<p style='color:red; text-align:center;'>Você não tem permissão para cadastrar chaves.</p>";
    exit; // Para não continuar
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $numero_identificacao = trim($_POST['numero_identificacao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $quantidade = trim($_POST['quantidade'] ?? '');
    $status = 'disponível';

    // Verifica se já existe a chave com o mesmo número de identificação
    $verifica = $conexao->prepare("SELECT * FROM chave WHERE numero_identificacao = ?");
    $verifica->bind_param("s", $numero_identificacao);
    $verifica->execute();
    $resultado = $verifica->get_result();

    if ($resultado->num_rows > 0) {
        $erroMsg = "Número de identificação já cadastrado.";
    } else {
        // Insere no banco
        $stmt = $conexao->prepare("INSERT INTO chave (nome, numero_identificacao, descricao, quantidade, status, cpf_adm) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $nome, $numero_identificacao, $descricao, $quantidade, $status, $cpf_adm);

        if ($stmt->execute()) {
            $successMsg = "Chave cadastrada com sucesso!";
        } else {
            $erroMsg = "Erro ao cadastrar chave: " . $stmt->error;
        }

        $stmt->close();
    }

    $verifica->close();
}

$admCheck->close();
$conexao->close();

if (!empty($erroMsg)) {
    echo "<p style='color:red; text-align:center;'>$erroMsg</p>";
}

if (!empty($successMsg)) {
    echo "<p style='color:green; text-align:center;'>$successMsg</p>";
}
?>
