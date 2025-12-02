<?php
require_once '../conexao.php';
verificarLogin();

// Adicionar menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
        $texto = mysqli_real_escape_string($conexao, $_POST['texto']);
        $ordem = intval($_POST['ordem']);
        
        $imagem = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = 'menu_' . time() . '.' . $extensao;
                $caminho = '../uploads/menu_imagens/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                    $imagem = $nome_arquivo;
                }
            }
        }
        
        $sql = "INSERT INTO menus (titulo, texto, imagem, ordem) 
                VALUES ('$titulo', '$texto', '$imagem', $ordem)";
        mysqli_query($conexao, $sql);
    }
    
    // Editar menu
    elseif ($_POST['acao'] == 'editar') {
        $id = intval($_POST['id']);
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
        $texto = mysqli_real_escape_string($conexao, $_POST['texto']);
        $ordem = intval($_POST['ordem']);
        
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = 'menu_' . time() . '.' . $extensao;
                $caminho = '../uploads/menu_imagens/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                    $imagem = $nome_arquivo;
                    $sql = "UPDATE menus SET imagem = '$imagem' WHERE id = $id";
                    mysqli_query($conexao, $sql);
                }
            }
        }
        
        $sql = "UPDATE menus SET titulo = '$titulo', texto = '$texto', ordem = $ordem 
                WHERE id = $id";
        mysqli_query($conexao, $sql);
    }
    
    // Excluir menu
    elseif ($_POST['acao'] == 'excluir') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM menus WHERE id = $id";
        mysqli_query($conexao, $sql);
    }
}

// Buscar menus
$sql = "SELECT * FROM menus ORDER BY ordem, titulo";
$result = mysqli_query($conexao, $sql);
$menus = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Menus</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-content">
            <h2>Gerenciar Menus</h2>
            
            <div class="form-container">
                <h3>Adicionar Novo Menu</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="adicionar">
                    
                    <div class="form-group">
                        <label>Título do Menu:</label>
                        <input type="text" name="titulo" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Texto:</label>
                        <textarea name="texto" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagem:</label>
                        <input type="file" name="imagem" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Ordem:</label>
                        <input type="number" name="ordem" value="0">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Adicionar Menu</button>
                </form>
            </div>
            
            <div class="table-container">
                <h3>Menus Cadastrados</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Imagem</th>
                            <th>Ordem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?php echo $menu['id']; ?></td>
                            <td><?php echo htmlspecialchars($menu['titulo']); ?></td>
                            <td>
                                <?php if ($menu['imagem']): ?>
                                    <img src="../uploads/menu_imagens/<?php echo $menu['imagem']; ?>" 
                                         alt="Imagem" style="max-width: 50px;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $menu['ordem']; ?></td>
                            <td>
                                <button class="btn btn-small btn-info" 
                                        onclick="editarMenu(<?php echo $menu['id']; ?>)">
                                    Editar
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-danger" 
                                            onclick="return confirm('Excluir este menu?')">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal de Edição -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Editar Menu</h3>
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Título:</label>
                    <input type="text" name="titulo" id="edit_titulo" required>
                </div>
                
                <div class="form-group">
                    <label>Texto:</label>
                    <textarea name="texto" id="edit_texto" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Imagem Atual:</label>
                    <div id="edit_imagem_atual"></div>
                    <label>Nova Imagem:</label>
                    <input type="file" name="imagem" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Ordem:</label>
                    <input type="number" name="ordem" id="edit_ordem">
                </div>
                
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>
    
    <script>
    function editarMenu(id) {
        // Buscar dados do menu via AJAX
        fetch(`get_menu.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_titulo').value = data.titulo;
                document.getElementById('edit_texto').value = data.texto;
                document.getElementById('edit_ordem').value = data.ordem;
                
                let imgHtml = '';
                if (data.imagem) {
                    imgHtml = `<img src="../uploads/menu_imagens/${data.imagem}" 
                                   alt="Imagem" style="max-width: 100px;">`;
                }
                document.getElementById('edit_imagem_atual').innerHTML = imgHtml;
                
                document.getElementById('modalEditar').style.display = 'block';
            });
    }
    
    // Fechar modal
    document.querySelector('.close').onclick = function() {
        document.getElementById('modalEditar').style.display = 'none';
    }
    </script>
</body>
</html>