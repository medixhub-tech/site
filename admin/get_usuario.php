<?php
require_once '../conexao.php';
verificarLogin();

// Verificar se é admin
$usuario_atual_id = $_SESSION['usuario_id'];
$sql_nivel = "SELECT nivel FROM usuarios WHERE id = $usuario_atual_id";
$result_nivel = mysqli_query($conexao, $sql_nivel);
$usuario_nivel = mysqli_fetch_assoc($result_nivel)['nivel'];

if ($usuario_nivel != 'admin') {
    echo json_encode(['error' => 'Acesso negado. Apenas administradores.']);
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT id, usuario, nome, nivel, status 
            FROM usuarios 
            WHERE id = $id";
    
    $result = mysqli_query($conexao, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($usuario);
    } else {
        echo json_encode(['error' => 'Usuário não encontrado.']);
    }
} else {
    echo json_encode(['error' => 'ID do usuário não fornecido.']);
}
?>