<?php
require_once '../conexao.php';
verificarLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM menus WHERE id = $id";
    $result = mysqli_query($conexao, $sql);
    $menu = mysqli_fetch_assoc($result);
    
    header('Content-Type: application/json');
    echo json_encode($menu);
}
?>