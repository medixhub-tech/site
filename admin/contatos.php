<?php
require_once '../conexao.php';
verificarLogin();

$page_title = 'Gerenciar Contatos';
require_once 'header.php';

// Ações: marcar como lido, responder, excluir
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($acao == 'marcar_lido' && $id > 0) {
        $sql = "UPDATE contatos SET status = 'lido' WHERE id = $id";
        mysqli_query($conexao, $sql);
        $mensagem = 'Contato marcado como lido.';
        registrarLog('MARCAR_CONTATO_LIDO', 'contatos', "Contato ID $id marcado como lido");
    }
    elseif ($acao == 'responder' && $id > 0) {
        $resposta = mysqli_real_escape_string($conexao, $_POST['resposta']);
        $usuario_id = $_SESSION['usuario_id'];
        
        $sql = "UPDATE contatos SET 
                status = 'respondido',
                resposta = '$resposta',
                respondido_por = $usuario_id,
                data_resposta = NOW()
                WHERE id = $id";
        
        mysqli_query($conexao, $sql);
        $mensagem = 'Resposta enviada com sucesso!';
        registrarLog('RESPONDER_CONTATO', 'contatos', "Contato ID $id respondido");
    }
    elseif ($acao == 'excluir' && $id > 0) {
        $sql = "DELETE FROM contatos WHERE id = $id";
        mysqli_query($conexao, $sql);
        $mensagem = 'Contato excluído com sucesso!';
        registrarLog('EXCLUIR_CONTATO', 'contatos', "Contato ID $id excluído");
    }
}

// Filtros
$status = $_GET['status'] ?? 'todos';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Construir query com filtros
$where = "1=1";
if ($status != 'todos') {
    $where .= " AND status = '$status'";
}
if ($data_inicio) {
    $where .= " AND DATE(data_criacao) >= '$data_inicio'";
}
if ($data_fim) {
    $where .= " AND DATE(data_criacao) <= '$data_fim'";
}

// Buscar contatos
$sql = "SELECT c.*, u.nome as respondido_por_nome 
        FROM contatos c
        LEFT JOIN usuarios u ON c.respondido_por = u.id
        WHERE $where
        ORDER BY c.data_criacao DESC";
$result = mysqli_query($conexao, $sql);
$contatos = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Contadores para dashboard
$sql_novos = "SELECT COUNT(*) as total FROM contatos WHERE status = 'novo'";
$result_novos = mysqli_query($conexao, $sql_novos);
$total_novos = mysqli_fetch_assoc($result_novos)['total'];

$sql_total = "SELECT COUNT(*) as total FROM contatos";
$result_total = mysqli_query($conexao, $sql_total);
$total_contatos = mysqli_fetch_assoc($result_total)['total'];

$sql_hoje = "SELECT COUNT(*) as total FROM contatos WHERE DATE(data_criacao) = CURDATE()";
$result_hoje = mysqli_query($conexao, $sql_hoje);
$total_hoje = mysqli_fetch_assoc($result_hoje)['total'];
?>

<div class="admin-content">
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <!-- Dashboard de Contatos -->
    <div class="dashboard-cards">
        <div class="card">
            <h3><i class="fas fa-envelope"></i> Novos</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #4169E1; margin: 10px 0;">
                <?php echo $total_novos; ?>
            </p>
            <p>Contatos não lidos</p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-calendar-day"></i> Hoje</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #4169E1; margin: 10px 0;">
                <?php echo $total_hoje; ?>
            </p>
            <p>Contatos recebidos hoje</p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-inbox"></i> Total</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #4169E1; margin: 10px 0;">
                <?php echo $total_contatos; ?>
            </p>
            <p>Todos os contatos</p>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="form-container" style="margin-top: 30px;">
        <h3><i class="fas fa-filter"></i> Filtrar Contatos</h3>
        <form method="GET" class="form-filtros">
            <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="todos" <?php echo $status == 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="novo" <?php echo $status == 'novo' ? 'selected' : ''; ?>>Novos</option>
                        <option value="lido" <?php echo $status == 'lido' ? 'selected' : ''; ?>>Lidos</option>
                        <option value="respondido" <?php echo $status == 'respondido' ? 'selected' : ''; ?>>Respondidos</option>
                        <option value="arquivado" <?php echo $status == 'arquivado' ? 'selected' : ''; ?>>Arquivados</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Data Início:</label>
                    <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>">
                </div>
                
                <div class="form-group">
                    <label>Data Fim:</label>
                    <input type="date" name="data_fim" value="<?php echo $data_fim; ?>">
                </div>
                
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="contatos.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Lista de Contatos -->
    <div class="table-container" style="margin-top: 30px;">
        <h3><i class="fas fa-list"></i> Lista de Contatos</h3>
        
        <?php if (empty($contatos)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum contato encontrado com os filtros atuais.
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Nome</th>
                        <th class="sortable">E-mail</th>
                        <th class="sortable">Data</th>
                        <th>Assunto</th>
                        <th class="sortable">Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contatos as $contato): ?>
                    <tr class="contato-<?php echo $contato['status']; ?>" 
                        style="<?php echo $contato['status'] == 'novo' ? 'background: #fff9e6;' : ''; ?>">
                        <td><?php echo $contato['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($contato['nome']); ?></strong><br>
                            <small><?php echo htmlspecialchars($contato['telefone'] ?? 'Sem telefone'); ?></small>
                        </td>
                        <td>
                            <a href="mailto:<?php echo $contato['email']; ?>">
                                <?php echo htmlspecialchars($contato['email']); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($contato['data_criacao'])); ?>
                        </td>
                        <td>
                            <?php 
                            $assunto = $contato['assunto'] ?: substr($contato['mensagem'], 0, 50) . '...';
                            echo htmlspecialchars($assunto);
                            ?>
                        </td>
                        <td>
                            <span class="status-contato status-<?php echo $contato['status']; ?>">
                                <?php 
                                $status_text = [
                                    'novo' => 'Novo',
                                    'lido' => 'Lido',
                                    'respondido' => 'Respondido',
                                    'arquivado' => 'Arquivado'
                                ];
                                echo $status_text[$contato['status']];
                                ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-small btn-info" 
                                    onclick="verContato(<?php echo $contato['id']; ?>)">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            
                            <?php if ($contato['status'] == 'novo'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="marcar_lido">
                                    <input type="hidden" name="id" value="<?php echo $contato['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-warning">
                                        <i class="fas fa-check"></i> Marcar como Lido
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ver Contato -->
<div id="modalVerContato" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <span class="modal-close">&times;</span>
        <h3><i class="fas fa-envelope"></i> Detalhes do Contato</h3>
        
        <div id="contatoDetalhes">
            <!-- Carregado via AJAX -->
        </div>
        
        <div id="formResponder" style="display: none; margin-top: 30px;">
            <h4><i class="fas fa-reply"></i> Responder Contato</h4>
            <form id="formResposta" method="POST">
                <input type="hidden" name="acao" value="responder">
                <input type="hidden" name="id" id="contato_id_resposta">
                
                <div class="form-group">
                    <label>Resposta:</label>
                    <textarea name="resposta" rows="6" required 
                              placeholder="Digite sua resposta aqui..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Resposta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verContato(id) {
    fetch(`get_contato.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="contato-info">
                    <div class="info-item">
                        <strong><i class="fas fa-user"></i> Nome:</strong>
                        <span>${data.nome}</span>
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-envelope"></i> E-mail:</strong>
                        <a href="mailto:${data.email}">${data.email}</a>
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-phone"></i> Telefone:</strong>
                        <span>${data.telefone || 'Não informado'}</span>
                    </div>
                    <div class="info-item">
                        <strong><i class="fas fa-calendar"></i> Data:</strong>
                        <span>${new Date(data.data_criacao).toLocaleString('pt-BR')}</span>
                    </div>
                    ${data.assunto ? `
                    <div class="info-item">
                        <strong><i class="fas fa-tag"></i> Assunto:</strong>
                        <span>${data.assunto}</span>
                    </div>` : ''}
                    <div class="info-item">
                        <strong><i class="fas fa-comment"></i> Mensagem:</strong>
                        <div class="mensagem-box" style="background: #f9f9f9; padding: 15px; border-radius: 6px; margin-top: 10px; white-space: pre-wrap;">
                            ${data.mensagem}
                        </div>
                    </div>
            `;
            
            if (data.status === 'respondido' && data.resposta) {
                html += `
                    <div class="info-item">
                        <strong><i class="fas fa-reply"></i> Resposta:</strong>
                        <div class="resposta-box" style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-top: 10px; white-space: pre-wrap;">
                            ${data.resposta}
                        </div>
                        <small style="color: #666;">
                            Respondido por: ${data.respondido_por_nome || 'Administrador'} em 
                            ${new Date(data.data_resposta).toLocaleString('pt-BR')}
                        </small>
                    </div>
                `;
            }
            
            html += `</div>`;
            
            document.getElementById('contatoDetalhes').innerHTML = html;
            document.getElementById('contato_id_resposta').value = data.id;
            
            // Mostrar/ocultar formulário de resposta
            const formResponder = document.getElementById('formResponder');
            if (data.status !== 'respondido') {
                formResponder.style.display = 'block';
            } else {
                formResponder.style.display = 'none';
            }
            
            // Abrir modal
            document.getElementById('modalVerContato').classList.add('open');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados do contato.');
        });
}

function fecharModal() {
    document.getElementById('modalVerContato').classList.remove('open');
}

// Fechar modal ao clicar fora ou no X
document.querySelectorAll('.modal-close').forEach(closeBtn => {
    closeBtn.addEventListener('click', fecharModal);
});

document.getElementById('modalVerContato').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});

// Enviar resposta
document.getElementById('formResposta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('Enviar resposta para este contato?')) {
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            alert('Resposta enviada com sucesso!');
            location.reload();
        } else {
            alert('Erro ao enviar resposta.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão.');
    });
});
</script>

<style>
.info-item {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.info-item strong {
    display: block;
    color: #4169E1;
    margin-bottom: 5px;
}

.info-item span, .info-item a {
    display: block;
    color: #333;
}

.mensagem-box, .resposta-box {
    line-height: 1.6;
    font-size: 1rem;
}

.status-contato {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.status-novo {
    background: #fff3cd;
    color: #856404;
}

.status-lido {
    background: #d1ecf1;
    color: #0c5460;
}

.status-respondido {
    background: #d4edda;
    color: #155724;
}

.status-arquivado {
    background: #f8d7da;
    color: #721c24;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
    color: #212529;
}

.numero-grande {
    font-weight: bold;
}

.form-filtros .form-group {
    margin-bottom: 0;
}
</style>

<?php require_once 'footer.php'; ?>