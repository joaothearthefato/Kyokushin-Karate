<?php
$page_title = 'Gerenciamento de Usuários';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <h3>Gestão de Praticantes e Administradores</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="userSearch" placeholder="Buscar por Nome ou E-mail...">
            </div>
            <select class="select-filter" id="userFilterTipo">
                <option value="">Todos os Tipos</option>
                <option value="aluno">Aluno / Praticante</option>
                <option value="professor">Professor / Sensei</option>
                <option value="admin">Administrador</option>
            </select>
            <button class="btn-primary" id="btnOpenAddUser">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Cadastrar Usuário
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Faixa Atual</th>
                    <th>Tipo / Permissão</th>
                    <th>Status</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="usersTbody">
                <tr>
                    <td colspan="8" style="text-align:center; padding: 40px;">Carregando Usuários...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: EDIT USER & PERMISSIONS -->
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="userModalTitle">Editar Usuário & Permissões</h3>
            <button class="btn-close-modal" id="btnCloseUserModal">&times;</button>
        </div>
        <form id="userForm">
            <div class="modal-body">
                <input type="hidden" id="userId" name="id" value="0">

                <div class="form-group">
                    <label for="userNome">Nome Completo *</label>
                    <input type="text" id="userNome" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="userEmail">Endereço de E-mail *</label>
                    <input type="email" id="userEmail" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="userNascimento">Data de Nascimento *</label>
                    <input type="date" id="userNascimento" name="nascimento" class="form-control" max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="userTipo">Tipo de Conta / Nível de Acesso *</label>
                        <select id="userTipo" name="tipo" class="form-control" required>
                            <option value="aluno">ALUNO (Área do usuário)</option>
                            <option value="professor">PROFESSOR (Sensei)</option>
                            <option value="admin">ADMIN (Acesso Total)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userFaixa">Faixa / Graduação</label>
                        <select id="userFaixa" name="faixa_id" class="form-control">
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="userAtivo">Status da Conta *</label>
                        <select id="userAtivo" name="ativo" class="form-control" required>
                            <option value="1">Ativo (Permitir Login)</option>
                            <option value="0">Bloqueado (Acesso Suspenso)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userNovaSenha" id="userSenhaLabel">Redefinir Senha (Opcional)</label>
                        <input type="password" id="userNovaSenha" name="nova_senha" class="form-control" placeholder="Deixe em branco para manter" minlength="6">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelUserModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveUser">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let usersData = [];
    let faixasData = [];

    const tbody = document.getElementById('usersTbody');
    const searchInput = document.getElementById('userSearch');
    const filterTipo = document.getElementById('userFilterTipo');
    const selectFaixa = document.getElementById('userFaixa');

    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');

    function loadUsers() {
        adminApi('api/users.php')
            .then(res => {
                usersData = res.data;
                faixasData = res.faixas || [];
                populateFaixasDropdown();
                renderUsers();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--admin-red);">${escapeHtml(err.message)}</td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function populateFaixasDropdown() {
        selectFaixa.innerHTML = '<option value="0">Sem Faixa Definida</option>' + 
            faixasData.map(f => `<option value="${f.id}">${f.nome}</option>`).join('');
    }

    function renderUsers() {
        const q = searchInput.value.toLowerCase().trim();
        const tipoVal = filterTipo.value;

        const filtered = usersData.filter(u => {
            const matchesQ = u.nome.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
            const matchesTipo = !tipoVal || u.tipo === tipoVal;
            return matchesQ && matchesTipo;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum Usuário encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(u => `
            <tr>
                <td>#${u.id}</td>
                <td><strong>${escapeHtml(u.nome)}</strong></td>
                <td>${escapeHtml(u.email)}</td>
                <td>
                    <span class="badge" style="background: rgba(255,255,255,0.06); color: ${u.faixa_cor || 'var(--admin-text-main)'}; border: 1px solid rgba(255,255,255,0.1);">
                        ${escapeHtml(u.faixa_nome || 'Sem Faixa')}
                    </span>
                </td>
                <td>
                    <span class="badge badge-${u.tipo}">${u.tipo.toUpperCase()}</span>
                </td>
                <td>
                    ${u.ativo == 1 
                        ? '<span class="badge badge-active">ATIVO</span>' 
                        : '<span class="badge badge-blocked">BLOQUEADO</span>'}
                </td>
                <td>${u.criado_em ? new Date(u.criado_em).toLocaleDateString('pt-BR') : '-'}</td>
                <td>
                    <button class="btn-action edit" onclick="editUser(${u.id})" title="Editar Usuário">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteUser(${u.id}, '${escapeHtml(u.nome)}')" title="Excluir Usuário">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    searchInput.addEventListener('input', renderUsers);
    filterTipo.addEventListener('change', renderUsers);

    document.getElementById('btnOpenAddUser').addEventListener('click', () => {
        form.reset();
        document.getElementById('userId').value = '0';
        document.getElementById('userModalTitle').textContent = 'Cadastrar Novo Usuário';
        document.getElementById('userSenhaLabel').textContent = 'Senha de Acesso * (mínimo 6 caracteres)';
        document.getElementById('userNovaSenha').placeholder = 'Defina a senha inicial';
        document.getElementById('userNovaSenha').required = true;
        modal.classList.add('active');
    });

    document.getElementById('btnCloseUserModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelUserModal').addEventListener('click', () => modal.classList.remove('active'));

    window.editUser = function(id) {
        const u = usersData.find(x => x.id == id);
        if (!u) return;

        document.getElementById('userId').value = u.id;
        document.getElementById('userNome').value = u.nome;
        document.getElementById('userEmail').value = u.email;
        document.getElementById('userTipo').value = u.tipo;
        document.getElementById('userFaixa').value = u.faixa_id || 0;
        document.getElementById('userAtivo').value = u.ativo;
        document.getElementById('userNascimento').value = u.nascimento || '';
        document.getElementById('userNovaSenha').value = '';
        document.getElementById('userNovaSenha').required = false;
        document.getElementById('userNovaSenha').placeholder = 'Deixe em branco para manter';
        document.getElementById('userModalTitle').textContent = 'Editar Usuário: ' + u.nome;
        document.getElementById('userSenhaLabel').textContent = 'Redefinir Senha (Opcional)';

        modal.classList.add('active');
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('userId').value, 10);
        const payload = {
            id: id,
            nome: document.getElementById('userNome').value,
            email: document.getElementById('userEmail').value,
            nascimento: document.getElementById('userNascimento').value,
            tipo: document.getElementById('userTipo').value,
            faixa_id: document.getElementById('userFaixa').value,
            ativo: document.getElementById('userAtivo').value
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/users.php' + (id > 0 ? '?id=' + id : '');

        if (id > 0) {
            payload.nova_senha = document.getElementById('userNovaSenha').value;
        } else {
            payload.senha = document.getElementById('userNovaSenha').value;
        }

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Usuário salvo com sucesso!', 'success');
                modal.classList.remove('active');
                loadUsers();
            })
            .catch(err => showNotification(err.message, 'error'));
    });

    window.deleteUser = function(id, nome) {
        if (!confirm(`Tem certeza que deseja remover o Usuário "${nome}"?`)) return;

        adminApi('api/users.php?id=' + id, 'DELETE')
            .then(res => {
                showNotification(res.message || 'Usuário excluído com sucesso!', 'success');
                loadUsers();
            })
            .catch(err => showNotification(err.message, 'error'));
    };

    loadUsers();
});
</script>

<?php require_once 'footer.php'; ?>
