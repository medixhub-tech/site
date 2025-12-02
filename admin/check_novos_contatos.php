<?php
require_once '../conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['novos' => 0]);
    exit();
}

$sql = "SELECT COUNT(*) as novos FROM contatos WHERE status = 'novo'";
$result = mysqli_query($conexao, $sql);
$dados = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode($dados);
?>