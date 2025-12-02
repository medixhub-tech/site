<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once '../conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Administração</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="logo-container" style="padding: 20px; text-align: center;">
                <?php
                $sql_logo = "SELECT valor FROM config WHERE chave = 'logo'";
                $result_logo = mysqli_query($conexao, $sql_logo);
                $logo = mysqli_fetch_assoc($result_logo)['valor'];
                
                if ($logo && file_exists("../uploads/logos/$logo")) {
                    echo '<img src="../uploads/logos/' . $logo . '" alt="Logo Admin" class="site-logo" style="max-height: 50px;">';
                } else {
                    echo '<h3 style="color: white; margin: 0;">Admin</h3>';
                }
                ?>
            </div>
            
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a></li>
                    
                    <li><a href="logo.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'logo.php' ? 'active' : ''; ?>">
                        <i class="fas fa-image"></i> Logo do Site
                    </a></li>
                    
                    <li><a href="menus.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'menus.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bars"></i> Gerenciar Menus
                    </a></li>
                    
                    <li><a href="secoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'secoes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-layer-group"></i> Seções da Página
                    </a></li>
                    
                    <li><a href="contatos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contatos.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Contatos
                        <?php
                        $sql_novos = "SELECT COUNT(*) as total FROM contatos WHERE status = 'novo'";
                        $result_novos = mysqli_query($conexao, $sql_novos);
                        $novos = mysqli_fetch_assoc($result_novos)['total'];
                        if ($novos > 0) {
                            echo '<span style="background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.8rem; margin-left: 5px;">' . $novos . '</span>';
                        }
                        ?>
                    </a></li>
                    
                    <li><a href="config.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'config.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Configurações
                    </a></li>
                    
                    <li><a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Usuários
                    </a></li>
                    
                    <li><a href="trocar_senha.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'trocar_senha.php' ? 'active' : ''; ?>">
                        <i class="fas fa-key"></i> Alterar Senha
                    </a></li>
                    
                    <li><a href="../logout.php" onclick="return confirm('Deseja sair do sistema?');">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Conteúdo Principal -->
        <main class="admin-content">
            <!-- Header do conteúdo -->
            <header class="admin-header">
                <h2 style="margin: 0; color: #333;"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
                <div class="user-info">
                    <i class="fas fa-user"></i> 
                    Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong> | 
                    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a> | 
                    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Site</a> | 
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </header>
            
            <!-- Barra de busca (opcional) -->
            <div class="admin-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar...">
            </div>