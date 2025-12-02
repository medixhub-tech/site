<?php
require_once '../conexao.php';
verificarLogin();

$page_title = 'Gerenciar Seções';
require_once 'header.php';

// Ações: adicionar, editar, excluir
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($acao == 'adicionar') {
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
        $subtitulo = mysqli_real_escape_string($conexao, $_POST['subtitulo']);
        $conteudo = mysqli_real_escape_string($conexao, $_POST['conteudo']);
        $ordem = intval($_POST['ordem']);
        $tipo = mysqli_real_escape_string($conexao, $_POST['tipo']);
        $classe_css = mysqli_real_escape_string($conexao, $_POST['classe_css']);
        $status = mysqli_real_escape_string($conexao, $_POST['status']);
        
        // Upload de imagem
        $imagem = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = 'secao_' . time() . '_' . uniqid() . '.' . $extensao;
                $caminho = '../uploads/secoes/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                    $imagem = $nome_arquivo;
                }
            }
        }
        
        $sql = "INSERT INTO secoes (titulo, subtitulo, conteudo, imagem, ordem, tipo, classe_css, status) 
                VALUES ('$titulo', '$subtitulo', '$conteudo', '$imagem', $ordem, '$tipo', '$classe_css', '$status')";
        
        if (mysqli_query($conexao, $sql)) {
            $mensagem = 'Seção adicionada com sucesso!';
            registrarLog('ADICIONAR_SECAO', 'secoes', "Nova seção: $titulo");
        } else {
            $erro = 'Erro ao adicionar seção: ' . mysqli_error($conexao);
        }
    }
    elseif ($acao == 'editar' && $id > 0) {
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo']);
        $subtitulo = mysqli_real_escape_string($conexao, $_POST['subtitulo']);
        $conteudo = mysqli_real_escape_string($conexao, $_POST['conteudo']);
        $ordem = intval($_POST['ordem']);
        $tipo = mysqli_real_escape_string($conexao, $_POST['tipo']);
        $classe_css = mysqli_real_escape_string($conexao, $_POST['classe_css']);
        $status = mysqli_real_escape_string($conexao, $_POST['status']);
        
        // Upload de nova imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                // Remover imagem antiga
                $sql_antiga = "SELECT imagem FROM secoes WHERE id = $id";
                $result_antiga = mysqli_query($conexao, $sql_antiga);
                $secao_antiga = mysqli_fetch_assoc($result_antiga);
                
                if ($secao_antiga['imagem'] && file_exists("../uploads/secoes/{$secao_antiga['imagem']}")) {
                    unlink("../uploads/secoes/{$secao_antiga['imagem']}");
                }
                
                $nome_arquivo = 'secao_' . time() . '_' . uniqid() . '.' . $extensao;
                $caminho = '../uploads/secoes/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                    $sql = "UPDATE secoes SET imagem = '$nome_arquivo' WHERE id = $id";
                    mysqli_query($conexao, $sql);
                }
            }
        }
        
        $sql = "UPDATE secoes SET 
                titulo = '$titulo',
                subtitulo = '$subtitulo',
                conteudo = '$conteudo',
                ordem = $ordem,
                tipo = '$tipo',
                classe_css = '$classe_css',
                status = '$status'
                WHERE id = $id";
        
        if (mysqli_query($conexao, $sql)) {
            $mensagem = 'Seção atualizada com sucesso!';
            registrarLog('EDITAR_SECAO', 'secoes', "Seção ID $id atualizada");
        } else {
            $erro = 'Erro ao atualizar seção: ' . mysqli_error($conexao);
        }
    }
    elseif ($acao == 'excluir' && $id > 0) {
        // Buscar imagem para excluir
        $sql_imagem = "SELECT imagem FROM secoes WHERE id = $id";
        $result_imagem = mysqli_query($conexao, $sql_imagem);
        $secao = mysqli_fetch_assoc($result_imagem);
        
        if ($secao['imagem'] && file_exists("../uploads/secoes/{$secao['imagem']}")) {
            unlink("../uploads/secoes/{$secao['imagem']}");
        }
        
        $sql = "DELETE FROM secoes WHERE id = $id";
        
        if (mysqli_query($conexao, $sql)) {
            $mensagem = 'Seção excluída com sucesso!';
            registrarLog('EXCLUIR_SECAO', 'secoes', "Seção ID $id excluída");
        } else {
            $erro = 'Erro ao excluir seção: ' . mysqli_error($conexao);
        }
    }
}

// Buscar todas as seções
$sql = "SELECT * FROM secoes ORDER BY ordem, titulo";
$result = mysqli_query($conexao, $sql);
$secoes = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="admin-content">
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
        <h3><i class="fas fa-plus-circle"></i> Adicionar Nova Seção</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="adicionar">
            
            <div class="form-group">
                <label>Título da Seção:</label>
                <input type="text" name="titulo" required maxlength="200">
            </div>
            
            <div class="form-group">
                <label>Subtítulo:</label>
                <input type="text" name="subtitulo" maxlength="500">
            </div>
            
            <div class="form-group">
                <label>Conteúdo (HTML permitido):</label>
                <textarea name="conteudo" rows="6" class="editor-basico"></textarea>
                <small>Use &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, etc.</small>
            </div>
            
            <div class="form-group">
                <label>Imagem da Seção:</label>
                <input type="file" name="imagem" accept="image/*">
                <small>Formatos: JPG, PNG, GIF, WebP. Máx: 2MB</small>
            </div>
            
            <div class="form-row" style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Ordem de Exibição:</label>
                    <input type="number" name="ordem" value="0" min="0">
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Tipo de Seção:</label>
                    <select name="tipo">
                        <option value="secao">Seção Normal</option>
                        <option value="hero">Hero (Destaque)</option>
                        <option value="servicos">Serviços</option>
                        <option value="sobre">Sobre</option>
                        <option value="contato">Contato</option>
                        <option value="portfolio">Portfólio</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Status:</label>
                    <select name="status">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Classe CSS Personalizada:</label>
                <input type="text" name="classe_css" placeholder="ex: bg-azul texto-branco">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar Seção
            </button>
        </form>
    </div>
    
    <div class="table-container" style="margin-top: 40px;">
        <h3><i class="fas fa-list"></i> Seções Cadastradas</h3>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable">ID</th>
                    <th class="sortable">Título</th>
                    <th>Tipo</th>
                    <th class="sortable">Ordem</th>
                    <th>Status</th>
                    <th>Imagem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($secoes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Nenhuma seção cadastrada</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($secoes as $secao): ?>
                    <tr>
                        <td><?php echo $secao['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($secao['titulo']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars(substr($secao['subtitulo'] ?? '', 0, 50)); ?>...</small>
                        </td>
                        <td>
                            <span class="badge" style="background: #4169E1; color: white; padding: 3px 8px; border-radius: 12px;">
                                <?php echo $secao['tipo']; ?>
                            </span>
                        </td>
                        <td><?php echo $secao['ordem']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $secao['status']; ?>">
                                <?php echo $secao['status'] == 'ativo' ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($secao['imagem']): ?>
                                <img src="../uploads/secoes/<?php echo $secao['imagem']; ?>" 
                                     alt="Imagem" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;"
                                     onclick="previewImage(this.src, '<?php echo htmlspecialchars($secao['titulo']); ?>')">
                            <?php else: ?>
                                <span style="color: #999;">Sem imagem</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-small btn-info" 
                                    onclick="editarSecao(<?php echo $secao['id']; ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?php echo $secao['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger" 
                                        onclick="return confirm('Excluir esta seção? Esta ação não pode ser desfeita.')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Edição -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3><i class="fas fa-edit"></i> Editar Seção</h3>
        <form id="formEditar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="titulo" id="edit_titulo" required>
            </div>
            
            <div class="form-group">
                <label>Subtítulo:</label>
                <input type="text" name="subtitulo" id="edit_subtitulo">
            </div>
            
            <div class="form-group">
                <label>Conteúdo:</label>
                <textarea name="conteudo" id="edit_conteudo" rows="6"></textarea>
            </div>
            
            <div class="form-group">
                <label>Imagem Atual:</label>
                <div id="edit_imagem_atual" style="margin: 10px 0;"></div>
                <label>Nova Imagem (opcional):</label>
                <input type="file" name="imagem" accept="image/*">
            </div>
            
            <div class="form-row" style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Ordem:</label>
                    <input type="number" name="ordem" id="edit_ordem">
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Tipo:</label>
                    <select name="tipo" id="edit_tipo">
                        <option value="secao">Seção Normal</option>
                        <option value="hero">Hero (Destaque)</option>
                        <option value="servicos">Serviços</option>
                        <option value="sobre">Sobre</option>
                        <option value="contato">Contato</option>
                        <option value="portfolio">Portfólio</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Status:</label>
                    <select name="status" id="edit_status">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Classe CSS:</label>
                <input type="text" name="classe_css" id="edit_classe_css">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Preview -->
<div id="modalPreview" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3 id="preview_title"></h3>
        <img id="preview_image" src="" alt="Preview" style="max-width: 100%; border-radius: 8px;">
    </div>
</div>

<script>
function editarSecao(id) {
    fetch(`get_secao.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_titulo').value = data.titulo;
            document.getElementById('edit_subtitulo').value = data.subtitulo || '';
            document.getElementById('edit_conteudo').value = data.conteudo || '';
            document.getElementById('edit_ordem').value = data.ordem;
            document.getElementById('edit_tipo').value = data.tipo;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_classe_css').value = data.classe_css || '';
            
            // Imagem atual
            let imgHtml = '';
            if (data.imagem) {
                imgHtml = `<img src="../uploads/secoes/${data.imagem}" 
                               alt="Imagem" 
                               style="max-width: 200px; border-radius: 4px; margin: 10px 0;">`;
            } else {
                imgHtml = '<p style="color: #999;">Nenhuma imagem</p>';
            }
            document.getElementById('edit_imagem_atual').innerHTML = imgHtml;
            
            // Abrir modal
            document.getElementById('modalEditar').classList.add('open');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados da seção.');
        });
}

function previewImage(src, title) {
    document.getElementById('preview_image').src = src;
    document.getElementById('preview_title').textContent = title;
    document.getElementById('modalPreview').classList.add('open');
}

function closeModal() {
    document.getElementById('modalEditar').classList.remove('open');
    document.getElementById('modalPreview').classList.remove('open');
}

// Fechar modais ao clicar fora ou no X
document.querySelectorAll('.modal-close').forEach(closeBtn => {
    closeBtn.addEventListener('click', closeModal);
});

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
});
</script>

<style>
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-ativo {
    background: #d4edda;
    color: #155724;
}

.status-inativo {
    background: #f8d7da;
    color: #721c24;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    background: #4169E1;
    color: white;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
}

.editor-basico {
    min-height: 150px;
    font-family: monospace;
}
</style>

<?php require_once 'footer.php'; ?>