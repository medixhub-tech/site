<?php
require_once '../conexao.php';
verificarLogin();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = md5($_POST['senha_atual']);
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    $usuario_id = $_SESSION['usuario_id'];
    
    // Verificar senha atual
    $sql = "SELECT id FROM usuarios WHERE id = $usuario_id AND senha = '$senha_atual'";
    $result = mysqli_query($conexao, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        if ($nova_senha == $confirmar_senha) {
            $nova_senha_md5 = md5($nova_senha);
            $sql = "UPDATE usuarios SET senha = '$nova_senha_md5' WHERE id = $usuario_id";
            mysqli_query($conexao, $sql);
            $mensagem = 'Senha alterada com sucesso!';
        } else {
            $erro = 'As senhas não coincidem!';
        }
    } else {
        $erro = 'Senha atual incorreta!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-content">
            <h2>Alterar Senha</h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label>Senha Atual:</label>
                        <input type="password" name="senha_atual" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nova Senha:</label>
                        <input type="password" name="nova_senha" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Nova Senha:</label>
                        <input type="password" name="confirmar_senha" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Alterar Senha</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>