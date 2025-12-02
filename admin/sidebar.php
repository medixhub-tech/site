<?php
// Este arquivo está incluso no header.php
// Se precisar de um sidebar separado, use:

if (!isset($_SESSION)) {
    session_start();
}
require_once '../conexao.php';
?>
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
                    echo '<span class="badge">' . $novos . '</span>';
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