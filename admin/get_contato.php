<?php
require_once '../conexao.php';
verificarLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT c.*, u.nome as respondido_por_nome 
            FROM contatos c
            LEFT JOIN usuarios u ON c.respondido_por = u.id
            WHERE c.id = $id";
    
    $result = mysqli_query($conexao, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $contato = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($contato);
    } else {
        echo json_encode(['error' => 'Contato não encontrado.']);
    }
}
?>