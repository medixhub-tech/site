<?php
require_once '../conexao.php';
verificarLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM secoes WHERE id = $id";
    $result = mysqli_query($conexao, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $secao = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($secao);
    } else {
        echo json_encode(['error' => 'Seção não encontrada.']);
    }
}
?>