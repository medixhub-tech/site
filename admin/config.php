<?php
require_once '../conexao.php';
verificarLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recaptcha_site = mysqli_real_escape_string($conexao, $_POST['recaptcha_site']);
    $recaptcha_secret = mysqli_real_escape_string($conexao, $_POST['recaptcha_secret']);
    
    $sql = "UPDATE config SET valor = '$recaptcha_site' WHERE chave = 'recaptcha_site'";
    mysqli_query($conexao, $sql);
    
    $sql = "UPDATE config SET valor = '$recaptcha_secret' WHERE chave = 'recaptcha_secret'";
    mysqli_query($conexao, $sql);
    
    $mensagem = 'Configurações salvas com sucesso!';
}

// Buscar configurações
$config = array();
$sql = "SELECT chave, valor FROM config";
$result = mysqli_query($conexao, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $config[$row['chave']] = $row['valor'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-content">
            <h2>Configurações do Site</h2>
            
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST">
                    <h3>reCAPTCHA</h3>
                    <div class="form-group">
                        <label>Site Key (reCAPTCHA):</label>
                        <input type="text" name="recaptcha_site" 
                               value="<?php echo htmlspecialchars($config['recaptcha_site']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Secret Key (reCAPTCHA):</label>
                        <input type="text" name="recaptcha_secret" 
                               value="<?php echo htmlspecialchars($config['recaptcha_secret']); ?>">
                        <p class="help">
                            Obtenha suas chaves em: 
                            <a href="https://www.google.com/recaptcha/admin" target="_blank">
                                Google reCAPTCHA
                            </a>
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>