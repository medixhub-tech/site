<?php
require_once '../conexao.php';
verificarLogin();

$page_title = 'Gerenciar Usuários';
require_once 'header.php';

// Verificar se o usuário atual é administrador
$usuario_atual_id = $_SESSION['usuario_id'];
$sql_nivel = "SELECT nivel FROM usuarios WHERE id = $usuario_atual_id";
$result_nivel = mysqli_query($conexao, $sql_nivel);
$usuario_nivel = mysqli_fetch_assoc($result_nivel)['nivel'];

if ($usuario_nivel != 'admin') {
    echo '<div class="alert alert-error" style="margin: 20px;">
            <i class="fas fa-exclamation-triangle"></i> 
            Apenas administradores podem acessar esta página.
          </div>';
    require_once 'footer.php';
    exit();
}

// Variáveis para mensagens
$mensagem = '';
$erro = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    switch ($acao) {
        case 'adicionar':
            $usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
            $senha = mysqli_real_escape_string($conexao, $_POST['senha']);
            $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
            $nivel = mysqli_real_escape_string($conexao, $_POST['nivel']);
            $status = mysqli_real_escape_string($conexao, $_POST['status']);
            
            // Verificar se usuário já existe
            $sql_verificar = "SELECT id FROM usuarios WHERE usuario = '$usuario'";
            $result_verificar = mysqli_query($conexao, $sql_verificar);
            
            if (mysqli_num_rows($result_verificar) > 0) {
                $erro = 'Este nome de usuário já está em uso.';
            } else {
                $senha_md5 = md5($senha);
                $sql = "INSERT INTO usuarios (usuario, senha, nome, nivel, status) 
                        VALUES ('$usuario', '$senha_md5', '$nome', '$nivel', '$status')";
                
                if (mysqli_query($conexao, $sql)) {
                    $mensagem = 'Usuário adicionado com sucesso!';
                    registrarLog('ADICIONAR_USUARIO', 'usuarios', "Novo usuário: $usuario");
                } else {
                    $erro = 'Erro ao adicionar usuário: ' . mysqli_error($conexao);
                }
            }
            break;
            
        case 'editar':
            $usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
            $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
            $nivel = mysqli_real_escape_string($conexao, $_POST['nivel']);
            $status = mysqli_real_escape_string($conexao, $_POST['status']);
            
            // Verificar se o novo nome de usuário já existe (exceto para o próprio usuário)
            $sql_verificar = "SELECT id FROM usuarios WHERE usuario = '$usuario' AND id != $id";
            $result_verificar = mysqli_query($conexao, $sql_verificar);
            
            if (mysqli_num_rows($result_verificar) > 0) {
                $erro = 'Este nome de usuário já está em uso por outro usuário.';
            } else {
                $sql = "UPDATE usuarios SET 
                        usuario = '$usuario',
                        nome = '$nome',
                        nivel = '$nivel',
                        status = '$status'
                        WHERE id = $id";
                
                if (mysqli_query($conexao, $sql)) {
                    $mensagem = 'Usuário atualizado com sucesso!';
                    registrarLog('EDITAR_USUARIO', 'usuarios', "Usuário ID $id atualizado");
                } else {
                    $erro = 'Erro ao atualizar usuário: ' . mysqli_error($conexao);
                }
            }
            break;
            
        case 'alterar_senha':
            $nova_senha = mysqli_real_escape_string($conexao, $_POST['nova_senha']);
            $confirmar_senha = mysqli_real_escape_string($conexao, $_POST['confirmar_senha']);
            
            if ($nova_senha !== $confirmar_senha) {
                $erro = 'As senhas não coincidem.';
            } elseif (strlen($nova_senha) < 6) {
                $erro = 'A senha deve ter no mínimo 6 caracteres.';
            } else {
                $senha_md5 = md5($nova_senha);
                $sql = "UPDATE usuarios SET senha = '$senha_md5' WHERE id = $id";
                
                if (mysqli_query($conexao, $sql)) {
                    $mensagem = 'Senha alterada com sucesso!';
                    registrarLog('ALTERAR_SENHA_USUARIO', 'usuarios', "Senha do usuário ID $id alterada");
                } else {
                    $erro = 'Erro ao alterar senha: ' . mysqli_error($conexao);
                }
            }
            break;
            
        case 'excluir':
            // Não permitir excluir a si mesmo
            if ($id == $usuario_atual_id) {
                $erro = 'Você não pode excluir seu próprio usuário.';
            } else {
                $sql = "DELETE FROM usuarios WHERE id = $id";
                
                if (mysqli_query($conexao, $sql)) {
                    $mensagem = 'Usuário excluído com sucesso!';
                    registrarLog('EXCLUIR_USUARIO', 'usuarios', "Usuário ID $id excluído");
                } else {
                    $erro = 'Erro ao excluir usuário: ' . mysqli_error($conexao);
                }
            }
            break;
            
        case 'resetar_senha':
            // Resetar senha para padrão (123456)
            $senha_md5 = md5('123456');
            $sql = "UPDATE usuarios SET senha = '$senha_md5' WHERE id = $id";
            
            if (mysqli_query($conexao, $sql)) {
                $mensagem = 'Senha resetada para o padrão (123456) com sucesso!';
                registrarLog('RESETAR_SENHA_USUARIO', 'usuarios', "Senha do usuário ID $id resetada");
            } else {
                $erro = 'Erro ao resetar senha: ' . mysqli_error($conexao);
            }
            break;
    }
}

// Buscar todos os usuários
$sql = "SELECT id, usuario, nome, nivel, status, 
               DATE_FORMAT(data_cadastro, '%d/%m/%Y %H:%i') as data_cadastro_formatada,
               DATE_FORMAT(ultimo_login, '%d/%m/%Y %H:%i') as ultimo_login_formatada
        FROM usuarios 
        ORDER BY nivel DESC, nome";
$result = mysqli_query($conexao, $sql);
$usuarios = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Contadores
$total_usuarios = count($usuarios);
$total_ativos = 0;
$total_admins = 0;
foreach ($usuarios as $usuario) {
    if ($usuario['status'] == 'ativo') $total_ativos++;
    if ($usuario['nivel'] == 'admin') $total_admins++;
}
?>

<div class="admin-content">
    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
        </div>
    <?php endif; ?>
    
    <!-- Dashboard de Usuários -->
    <div class="dashboard-cards">
        <div class="card">
            <h3><i class="fas fa-users"></i> Total</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #4169E1; margin: 10px 0;">
                <?php echo $total_usuarios; ?>
            </p>
            <p>Usuários cadastrados</p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-user-check"></i> Ativos</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #27ae60; margin: 10px 0;">
                <?php echo $total_ativos; ?>
            </p>
            <p>Usuários ativos</p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-user-shield"></i> Admins</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #e74c3c; margin: 10px 0;">
                <?php echo $total_admins; ?>
            </p>
            <p>Administradores</p>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-user-edit"></i> Editores</h3>
            <p class="numero-grande" style="font-size: 2.5rem; color: #f39c12; margin: 10px 0;">
                <?php echo $total_usuarios - $total_admins; ?>
            </p>
            <p>Editores</p>
        </div>
    </div>
    
    <!-- Formulário para Adicionar Novo Usuário -->
    <div class="form-container" style="margin-top: 30px;">
        <h3><i class="fas fa-user-plus"></i> Adicionar Novo Usuário</h3>
        <form method="POST" id="formAdicionarUsuario" onsubmit="return validarFormulario(this)">
            <input type="hidden" name="acao" value="adicionar">
            
            <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <label>Nome de Usuário: *</label>
                    <input type="text" name="usuario" required 
                           minlength="3" maxlength="50"
                           pattern="[a-zA-Z0-9_]+"
                           title="Apenas letras, números e underscore">
                    <small>Mínimo 3 caracteres. Apenas letras, números e _</small>
                </div>
                
                <div class="form-group" style="flex: 2;">
                    <label>Nome Completo: *</label>
                    <input type="text" name="nome" required 
                           minlength="3" maxlength="100">
                    <small>Nome completo do usuário</small>
                </div>
            </div>
            
            <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <label>Senha: *</label>
                    <div style="position: relative;">
                        <input type="password" name="senha" id="senha" required 
                               minlength="6" 
                               pattern="^(?=.*[A-Za-z])(?=.*\d).{6,}$"
                               title="Mínimo 6 caracteres com pelo menos uma letra e um número">
                        <button type="button" class="btn-mostrar-senha" 
                                onclick="mostrarSenha('senha', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small>Mínimo 6 caracteres com letras e números</small>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Confirmar Senha: *</label>
                    <div style="position: relative;">
                        <input type="password" name="confirmar_senha" id="confirmar_senha" required 
                               minlength="6">
                        <button type="button" class="btn-mostrar-senha" 
                                onclick="mostrarSenha('confirmar_senha', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small>Digite a senha novamente</small>
                </div>
            </div>
            
            <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <label>Nível de Acesso: *</label>
                    <select name="nivel" required>
                        <option value="editor">Editor</option>
                        <option value="admin">Administrador</option>
                    </select>
                    <small>Administradores têm acesso total</small>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Status: *</label>
                    <select name="status" required>
                        <option value="ativo" selected>Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                    <small>Usuários inativos não podem fazer login</small>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Usuário
                </button>
                <button type="button" class="btn btn-secondary" onclick="gerarSenha()">
                    <i class="fas fa-key"></i> Gerar Senha Forte
                </button>
            </div>
        </form>
    </div>
    
    <!-- Lista de Usuários -->
    <div class="table-container" style="margin-top: 40px;">
        <h3><i class="fas fa-list"></i> Usuários Cadastrados</h3>
        
        <?php if (empty($usuarios)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum usuário cadastrado.
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Usuário</th>
                        <th class="sortable">Nome</th>
                        <th class="sortable">Nível</th>
                        <th class="sortable">Status</th>
                        <th>Cadastro</th>
                        <th>Último Login</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): 
                        $is_current_user = ($usuario['id'] == $usuario_atual_id);
                    ?>
                    <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>"
                        style="<?php echo $is_current_user ? 'background: #fff9e6;' : ''; ?>">
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong>
                            <?php if ($is_current_user): ?>
                                <br><small style="color: #4169E1;">(Você)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td>
                            <span class="badge nivel-<?php echo $usuario['nivel']; ?>">
                                <?php echo $usuario['nivel'] == 'admin' ? 'Administrador' : 'Editor'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $usuario['status']; ?>">
                                <?php echo $usuario['status'] == 'ativo' ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo $usuario['data_cadastro_formatada']; ?></small>
                        </td>
                        <td>
                            <small><?php echo $usuario['ultimo_login_formatada'] ?: 'Nunca'; ?></small>
                        </td>
                        <td>
                            <div class="acoes-usuario">
                                <!-- Botão Editar -->
                                <button class="btn btn-small btn-info" 
                                        onclick="editarUsuario(<?php echo $usuario['id']; ?>)"
                                        title="Editar Usuário">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Botão Alterar Senha -->
                                <button class="btn btn-small btn-warning" 
                                        onclick="alterarSenha(<?php echo $usuario['id']; ?>)"
                                        title="Alterar Senha">
                                    <i class="fas fa-key"></i>
                                </button>
                                
                                <!-- Botão Resetar Senha (apenas para admins) -->
                                <?php if ($usuario_nivel == 'admin' && !$is_current_user): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="acao" value="resetar_senha">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-secondary"
                                                onclick="return confirm('Resetar senha para o padrão (123456)?')"
                                                title="Resetar Senha para Padrão">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Botão Excluir (não permitir excluir a si mesmo) -->
                                <?php if (!$is_current_user): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger"
                                                onclick="return confirm('Excluir este usuário permanentemente?')"
                                                title="Excluir Usuário">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div id="modalEditarUsuario" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3><i class="fas fa-edit"></i> Editar Usuário</h3>
        <form id="formEditarUsuario" method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Nome de Usuário: *</label>
                <input type="text" name="usuario" id="edit_usuario" required
                       minlength="3" maxlength="50"
                       pattern="[a-zA-Z0-9_]+">
            </div>
            
            <div class="form-group">
                <label>Nome Completo: *</label>
                <input type="text" name="nome" id="edit_nome" required
                       minlength="3" maxlength="100">
            </div>
            
            <div class="form-row" style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Nível de Acesso: *</label>
                    <select name="nivel" id="edit_nivel" required>
                        <option value="editor">Editor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Status: *</label>
                    <select name="status" id="edit_status" required>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="fecharModal('modalEditarUsuario')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Alterar Senha -->
<div id="modalAlterarSenha" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h3><i class="fas fa-key"></i> Alterar Senha</h3>
        <form id="formAlterarSenha" method="POST">
            <input type="hidden" name="acao" value="alterar_senha">
            <input type="hidden" name="id" id="alterar_senha_id">
            
            <div class="form-group">
                <label>Nova Senha: *</label>
                <div style="position: relative;">
                    <input type="password" name="nova_senha" id="nova_senha" required
                           minlength="6"
                           pattern="^(?=.*[A-Za-z])(?=.*\d).{6,}$">
                    <button type="button" class="btn-mostrar-senha" 
                            onclick="mostrarSenha('nova_senha', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small>Mínimo 6 caracteres com letras e números</small>
            </div>
            
            <div class="form-group">
                <label>Confirmar Nova Senha: *</label>
                <div style="position: relative;">
                    <input type="password" name="confirmar_senha" id="confirmar_senha_modal" required
                           minlength="6">
                    <button type="button" class="btn-mostrar-senha" 
                            onclick="mostrarSenha('confirmar_senha_modal', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small>Digite a senha novamente</small>
            </div>
            
            <div class="forca-senha" style="margin: 15px 0;">
                <div class="barra-forca" style="height: 5px; background: #eee; border-radius: 3px;">
                    <div id="indicador-forca" style="height: 100%; width: 0%; border-radius: 3px; transition: width 0.3s;"></div>
                </div>
                <small id="texto-forca" style="color: #666;">Força da senha</small>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="fecharModal('modalAlterarSenha')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Alterar Senha
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Funções para manipulação de usuários
function editarUsuario(id) {
    fetch(`get_usuario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_usuario').value = data.usuario;
            document.getElementById('edit_nome').value = data.nome;
            document.getElementById('edit_nivel').value = data.nivel;
            document.getElementById('edit_status').value = data.status;
            
            abrirModal('modalEditarUsuario');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar dados do usuário.');
        });
}

function alterarSenha(id) {
    document.getElementById('alterar_senha_id').value = id;
    abrirModal('modalAlterarSenha');
}

// Validação de formulário
function validarFormulario(form) {
    const senha = form.querySelector('[name="senha"]');
    const confirmarSenha = form.querySelector('[name="confirmar_senha"]');
    
    if (senha && confirmarSenha && senha.value !== confirmarSenha.value) {
        alert('As senhas não coincidem!');
        confirmarSenha.focus();
        return false;
    }
    
    return true;
}

// Gerar senha forte
function gerarSenha() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let senha = '';
    
    // Garantir pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial
    senha += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.charAt(Math.floor(Math.random() * 26));
    senha += 'abcdefghijklmnopqrstuvwxyz'.charAt(Math.floor(Math.random() * 26));
    senha += '0123456789'.charAt(Math.floor(Math.random() * 10));
    senha += '!@#$%^&*'.charAt(Math.floor(Math.random() * 8));
    
    // Adicionar mais caracteres aleatórios para completar 10 caracteres
    for (let i = 0; i < 6; i++) {
        senha += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    
    // Embaralhar a senha
    senha = senha.split('').sort(() => Math.random() - 0.5).join('');
    
    // Preencher os campos
    document.getElementById('senha').value = senha;
    document.getElementById('confirmar_senha').value = senha;
    
    // Atualizar indicador de força
    verificarForcaSenha(senha, 'indicador-forca', 'texto-forca');
}

// Mostrar/ocultar senha
function mostrarSenha(inputId, botao) {
    const input = document.getElementById(inputId);
    const icon = botao.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Verificar força da senha
function verificarForcaSenha(senha, indicadorId, textoId) {
    let forca = 0;
    let texto = 'Muito fraca';
    let cor = '#e74c3c';
    
    // Critérios de força
    if (senha.length >= 6) forca += 1;
    if (senha.length >= 8) forca += 1;
    if (/[a-z]/.test(senha) && /[A-Z]/.test(senha)) forca += 1;
    if (/\d/.test(senha)) forca += 1;
    if (/[^A-Za-z0-9]/.test(senha)) forca += 1;
    
    // Definir porcentagem e texto
    switch(forca) {
        case 1:
            porcentagem = 20;
            texto = 'Fraca';
            cor = '#e74c3c';
            break;
        case 2:
            porcentagem = 40;
            texto = 'Razoável';
            cor = '#f39c12';
            break;
        case 3:
            porcentagem = 60;
            texto = 'Boa';
            cor = '#f1c40f';
            break;
        case 4:
            porcentagem = 80;
            texto = 'Forte';
            cor = '#27ae60';
            break;
        case 5:
            porcentagem = 100;
            texto = 'Muito forte';
            cor = '#2ecc71';
            break;
        default:
            porcentagem = 0;
    }
    
    // Atualizar visual
    const indicador = document.getElementById(indicadorId);
    const textoElemento = document.getElementById(textoId);
    
    if (indicador) {
        indicador.style.width = porcentagem + '%';
        indicador.style.backgroundColor = cor;
    }
    
    if (textoElemento) {
        textoElemento.textContent = 'Força: ' + texto;
        textoElemento.style.color = cor;
    }
}

// Event listeners para verificar força da senha em tempo real
document.getElementById('nova_senha')?.addEventListener('input', function() {
    verificarForcaSenha(this.value, 'indicador-forca', 'texto-forca');
});

document.getElementById('senha')?.addEventListener('input', function() {
    verificarForcaSenha(this.value, 'indicador-forca', 'texto-forca');
});

// Funções para modais
function abrirModal(modalId) {
    document.getElementById(modalId).classList.add('open');
}

function fecharModal(modalId) {
    document.getElementById(modalId).classList.remove('open');
}

// Fechar modais ao clicar fora ou no X
document.querySelectorAll('.modal-close').forEach(closeBtn => {
    closeBtn.addEventListener('click', function() {
        this.closest('.modal').classList.remove('open');
    });
});

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('open');
        }
    });
});

// Prevenir envio duplo do formulário
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            
            // Reabilitar após 5 segundos (caso haja erro)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Salvar Usuário';
            }, 5000);
        }
    });
});
</script>

<style>
/* Estilos específicos para a página de usuários */
.current-user {
    border-left: 4px solid #4169E1;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge.nivel-admin {
    background: #e74c3c;
    color: white;
}

.badge.nivel-editor {
    background: #3498db;
    color: white;
}

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

.acoes-usuario {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-mostrar-senha {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
}

.btn-mostrar-senha:hover {
    color: #4169E1;
}

.btn-small {
    padding: 5px 8px;
    font-size: 0.8rem;
    min-width: 32px;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
    color: white;
}

.numero-grande {
    font-weight: bold;
}

.form-row {
    margin-bottom: 20px;
}

.form-container .form-group {
    margin-bottom: 15px;
}

/* Responsividade */
@media (max-width: 768px) {
    .acoes-usuario {
        flex-direction: column;
        gap: 3px;
    }
    
    .btn-small {
        width: 100%;
        margin-bottom: 3px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-row .form-group {
        flex: 1 0 100% !important;
    }
}
</style>

<?php require_once 'footer.php'; ?>