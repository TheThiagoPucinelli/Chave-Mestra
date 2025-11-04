<?php
include __DIR__ . '/../BD/conexao.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$cpf = $_SESSION['cpf'] ?? '';
if (!$cpf) die("Acesso negado");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_notificacao'])) {
    $id = intval($_POST['id_notificacao']);

    // Verifica se é admin
    $stmtAdm = $conexao->prepare("SELECT tipo FROM usuario_adm WHERE cpf = ?");
    $isAdmin = false;
    if ($stmtAdm) {
        $stmtAdm->bind_param("s", $cpf);
        $stmtAdm->execute();
        $res = $stmtAdm->get_result()->fetch_assoc();
        $isAdmin = !empty($res['tipo']);
        $stmtAdm->close();
    }

    if ($isAdmin) {
        // Admin pode deletar qualquer notificação de admin
        $stmt = $conexao->prepare("DELETE FROM notificacoes_admin WHERE id_notificacao = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo "Notificação do admin deletada!";
    } else {
        // Usuário comum só pode deletar suas próprias notificações
        $stmt = $conexao->prepare("DELETE FROM notificacao_usuario WHERE id_notificacao = ? AND cpf_usuario = ?");
        $stmt->bind_param("is", $id, $cpf);
        $stmt->execute();
        $rows = $stmt->affected_rows;
        $stmt->close();

        if ($rows > 0) {
            echo "Notificação do usuário deletada!";
        } else {
            echo "Nenhuma notificação encontrada para deletar!";
        }
    }
}

// Redireciona de volta
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
