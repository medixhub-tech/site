<?php
require_once '../conexao.php';
verificarLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($extensao, $extensoes_permitidas)) {
            $nome_arquivo = 'logo_' . time() . '.' . $extensao;
            $caminho = '../uploads/logos/' . $nome_arquivo;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $caminho)) {
                $sql = "UPDATE config SET valor = '$nome_arquivo' WHERE chave = 'logo'";
                mysqli_query($conexao, $sql);
                $mensagem = 'Logo atualizado com sucesso!';
            }
        }
    }
}

$sql = "SELECT valor FROM config WHERE chave = 'logo'";
$result = mysqli_query($conexao, $sql);
$logo = mysqli_fetch_assoc($result)['valor'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo do Site</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-content">
            <h2>Logo do Site</h2>
            
            <?php if (isset($mensagem)): ?>
                <div class="alert alert-success"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Logo Atual:</label><br>
                        <?php if ($logo && file_exists("../uploads/logos/$logo")): ?>
                            <img src="../uploads/logos/<?php echo $logo; ?>" alt="Logo" style="max-width: 200px; margin: 10px 0;">
                        <?php else: ?>
                            <p>Nenhum logo carregado</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Novo Logo:</label>
                        <input type="file" name="logo" accept="image/*">
                        <p class="help">Formatos permitidos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salvar Logo</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>