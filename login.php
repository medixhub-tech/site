<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
    $senha = md5($_POST['senha']);
    
    $sql = "SELECT id, nome FROM usuarios WHERE usuario = '$usuario' AND senha = '$senha'";
    $result = mysqli_query($conexao, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nome'] = $row['nome'];
        header('Location: admin/index.php');
        exit();
    } else {
        $erro = 'Usuário ou senha inválidos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Área Administrativa</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>Área Administrativa</h2>
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Usuário:</label>
                <input type="text" name="usuario" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
        <p>Usuários: ttatim / telma | Senha: 123456</p>
    </div>
</body>
</html>