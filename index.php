<?php
require_once 'conexao.php';

// Buscar logo
$sql = "SELECT valor FROM config WHERE chave = 'logo'";
$result = mysqli_query($conexao, $sql);
$logo = mysqli_fetch_assoc($result)['valor'];

// Buscar menus ativos
$sql = "SELECT * FROM menus WHERE status = 'ativo' ORDER BY ordem, titulo";
$result = mysqli_query($conexao, $sql);
$menus = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Empresarial</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header com Navegação -->
    <header class="site-header">
        <nav class="nav-container">
            <!-- Logo -->
            <div class="logo-container">
                <?php if ($logo && file_exists("uploads/logos/$logo")): ?>
                    <img src="uploads/logos/<?php echo $logo; ?>" alt="Logo" class="site-logo">
                <?php else: ?>
                    <span class="site-name">Minha Empresa</span>
                <?php endif; ?>
            </div>
            
            <!-- Menu Desktop -->
            <ul class="nav-menu">
                <?php foreach ($menus as $menu): ?>
                    <li>
                        <a href="#section-<?php echo $menu['id']; ?>">
                            <?php if ($menu['imagem']): ?>
                                <img src="uploads/menu_imagens/<?php echo $menu['imagem']; ?>" 
                                     alt="<?php echo htmlspecialchars($menu['titulo']); ?>"
                                     style="height: 20px; vertical-align: middle; margin-right: 5px;">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($menu['titulo']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><a href="#contato">Contato</a></li>
            </ul>
            
            <!-- Botão Mobile -->
            <button class="mobile-menu-btn" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <!-- Conteúdo Principal -->
    <main>
        <?php foreach ($menus as $index => $menu): ?>
            <section id="section-<?php echo $menu['id']; ?>" class="section">
                <div class="container">
                    <h1><?php echo htmlspecialchars($menu['titulo']); ?></h1>
                    <?php if ($menu['imagem']): ?>
                        <div class="section-image">
                            <img src="uploads/menu_imagens/<?php echo $menu['imagem']; ?>" 
                                 alt="<?php echo htmlspecialchars($menu['titulo']); ?>"
                                 style="max-width: 100%; height: auto; margin: 20px 0;">
                        </div>
                    <?php endif; ?>
                    <div class="section-content">
                        <?php echo nl2br(htmlspecialchars($menu['texto'])); ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- Seção de Contato -->
        <section id="contato" class="section">
            <div class="container">
                <h1>Contato</h1>
                <form id="form-contato" method="POST" action="enviar_contato.php">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="tel" id="telefone" name="telefone">
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem">Mensagem:</label>
                        <textarea id="mensagem" name="mensagem" rows="5" required></textarea>
                    </div>
                    
                    <!-- reCAPTCHA -->
                    <?php
                    $sql = "SELECT valor FROM config WHERE chave = 'recaptcha_site'";
                    $result = mysqli_query($conexao, $sql);
                    $recaptcha_site = mysqli_fetch_assoc($result)['valor'];
                    
                    if ($recaptcha_site): ?>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site; ?>"></div>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer style="background: #4169E1; color: white; text-align: center; padding: 2rem;">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Minha Empresa. Todos os direitos reservados.</p>
            <p>
                <a href="login.php" style="color: white; text-decoration: underline;">
                    Área Administrativa
                </a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <?php if ($recaptcha_site): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <script src="js/scripts.js"></script>
    <script>
    // Menu mobile
    function toggleMenu() {
        const menu = document.querySelector('.nav-menu');
        menu.classList.toggle('active');
    }

    // Smooth scroll para links âncora
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Fechar menu mobile se aberto
                const menu = document.querySelector('.nav-menu');
                menu.classList.remove('active');
            }
        });
    });

    // Formulário de contato com AJAX
    document.getElementById('form-contato')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('enviar_contato.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Mensagem enviada com sucesso!');
                this.reset();
                if(typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao enviar mensagem.');
        });
    });
    </script>
</body>
</html>