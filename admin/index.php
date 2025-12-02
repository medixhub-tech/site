<?php
require_once '../conexao.php';
verificarLogin();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Administrativa</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Área Administrativa</h1>
            <div class="user-info">
                Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?> | 
                <a href="trocar_senha.php">Alterar Senha</a> | 
                <a href="../logout.php">Sair</a>
            </div>
        </header>
        
        <nav class="admin-menu">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="logo.php">Logo do Site</a></li>
                <li><a href="menus.php">Gerenciar Menus</a></li>
                <li><a href="config.php">Configurações</a></li>
            </ul>
        </nav>
        
        <main class="admin-content">
            <h2>Bem-vindo ao Painel Administrativo</h2>
            <p>Selecione uma opção no menu para começar.</p>
            
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Logo do Site</h3>
                    <p>Altere o logo do site</p>
                    <a href="logo.php" class="btn btn-primary">Configurar</a>
                </div>
                
                <div class="card">
                    <h3>Menus do Site</h3>
                    <p>Gerencie os menus de navegação</p>
                    <a href="menus.php" class="btn btn-primary">Gerenciar</a>
                </div>
                
                <div class="card">
                    <h3>Configurações</h3>
                    <p>reCAPTCHA e outras configurações</p>
                    <a href="config.php" class="btn btn-primary">Configurar</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>