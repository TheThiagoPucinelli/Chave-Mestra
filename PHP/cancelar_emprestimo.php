<?php
include_once __DIR__ . '/../BD/conexao.php';
session_start();

if (!isset($_SESSION['cpf'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idEmprestimo = $_POST['id_emprestimo'] ?? null;
    $cpfUsuario   = $_SESSION['cpf'];

    if ($idEmprestimo) {
        // Verifica se o empréstimo pertence ao usuário logado
        $stmt = $conexao->prepare("SELECT * FROM emprestimo WHERE id_emprestimo = ? AND cpf_usuario = ?");
        $stmt->execute([$idEmprestimo, $cpfUsuario]);
        $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($emprestimo) {
            // Exclui o empréstimo — ON DELETE CASCADE cuida das notificações associadas
            $delete = $conexao->prepare("DELETE FROM emprestimo WHERE id_emprestimo = ?");
            $delete->execute([$idEmprestimo]);

            // Mensagem de feedback visual
            echo json_encode([
                'success' => true,
                'message' => 'Empréstimo cancelado e removido com sucesso.'
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Empréstimo não encontrado ou não pertence a este usuário.'
            ]);
            exit;
        }
    }
}
?>
