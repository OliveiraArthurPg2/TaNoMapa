



let dadosUsuarios = [];
let dadosRoteiros = [];
let dadosPontos = [];





function mostrarAba(aba) {
    
    document.querySelectorAll('.aba-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    
    document.getElementById(`aba-${aba}`).classList.add('active');
    event.target.classList.add('active');
    
    
    if (aba === 'usuarios') {
        carregarUsuarios();
    } else if (aba === 'roteiros') {
        carregarRoteiros();
    } else if (aba === 'pontos') {
        carregarPontos();
    }
}





async function carregarUsuarios() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_usuarios');

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            dadosUsuarios = result.usuarios;
            renderizarUsuarios(dadosUsuarios);
        } else {
            document.getElementById('tabelaUsuarios').innerHTML = 
                `<tr><td colspan="6" class="loading">${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        document.getElementById('tabelaUsuarios').innerHTML = 
            '<tr><td colspan="6" class="loading">Erro ao carregar dados</td></tr>';
    }
}

function renderizarUsuarios(usuarios) {
    const tbody = document.getElementById('tabelaUsuarios');
    
    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading">Nenhum usuário encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = usuarios.map(user => `
        <tr>
            <td>
                <div class="user-cell">
                    <img src="${user.fotoPerfil || 'img/default_avatar.png'}" 
                         alt="${user.nome}" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name">${user.nome}</span>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>
                <span class="badge ${user.tipo === 'Fornecedor' ? 'badge-fornecedor' : 'badge-turista'}">
                    ${user.tipo}
                </span>
            </td>
            <td>${formatarData(user.dataCriacao)}</td>
            <td>${user.totalRoteiros} roteiros, ${user.totalPontos} pontos</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" 
                            onclick="visualizarUsuario(${user.id})"
                            title="Visualizar">
                        👁️
                    </button>
                    <button class="action-btn btn-delete" 
                            onclick="excluirUsuario(${user.id})"
                            title="Excluir">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}





async function carregarRoteiros() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_roteiros');

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            dadosRoteiros = result.roteiros;
            renderizarRoteiros(dadosRoteiros);
        } else {
            document.getElementById('tabelaRoteiros').innerHTML = 
                `<tr><td colspan="6" class="loading">${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Erro ao carregar roteiros:', error);
        document.getElementById('tabelaRoteiros').innerHTML = 
            '<tr><td colspan="6" class="loading">Erro ao carregar dados</td></tr>';
    }
}

function renderizarRoteiros(roteiros) {
    const tbody = document.getElementById('tabelaRoteiros');
    
    if (roteiros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading">Nenhum roteiro encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = roteiros.map(roteiro => `
        <tr>
            <td>
                <div class="user-cell">
                    <div class="user-info">
                        <span class="user-name">${roteiro.nome}</span>
                        <span class="user-email-mini">${(roteiro.bio || '').substring(0, 50)}...</span>
                    </div>
                </div>
            </td>
            <td>${roteiro.criador}</td>
            <td>${roteiro.totalLocais} locais</td>
            <td>⭐ ${roteiro.avaliacao ? parseFloat(roteiro.avaliacao).toFixed(1) : '5.0'}</td>
            <td>${formatarData(roteiro.dataCriacao)}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" 
                            onclick="window.location.href='visualizar-roteiro.php?id=${roteiro.id}'"
                            title="Visualizar">
                        👁️
                    </button>
                    <button class="action-btn btn-delete" 
                            onclick="excluirRoteiro(${roteiro.id})"
                            title="Excluir">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}





async function carregarPontos() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_pontos');

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            dadosPontos = result.pontos;
            renderizarPontos(dadosPontos);
        } else {
            document.getElementById('tabelaPontos').innerHTML = 
                `<tr><td colspan="6" class="loading">${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Erro ao carregar pontos:', error);
        document.getElementById('tabelaPontos').innerHTML = 
            '<tr><td colspan="6" class="loading">Erro ao carregar dados</td></tr>';
    }
}

function renderizarPontos(pontos) {
    const tbody = document.getElementById('tabelaPontos');
    
    if (pontos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="loading">Nenhum ponto encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = pontos.map(ponto => `
        <tr>
            <td>
                <div class="user-cell">
                    <img src="${ponto.fotoPerfil || 'img/default_avatar.png'}" 
                         alt="${ponto.nome}" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name">${ponto.nome}</span>
                    </div>
                </div>
            </td>
            <td>${ponto.tipo}</td>
            <td>${ponto.localidade}</td>
            <td>${ponto.fornecedor}</td>
            <td>⭐ ${ponto.avaliacao ? parseFloat(ponto.avaliacao).toFixed(1) : '5.0'}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" 
                            onclick="window.location.href='visualizar-ponto.php?id=${ponto.id}'"
                            title="Visualizar">
                        👁️
                    </button>
                    <button class="action-btn btn-delete" 
                            onclick="excluirPonto(${ponto.id})"
                            title="Excluir">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}





function visualizarUsuario(id) {
    window.location.href = `perfil-publico.php?id=${id}`;
}

async function excluirUsuario(id) {
    const confirmacao = confirm('Tem certeza que deseja EXCLUIR este usuário? Esta ação não pode ser desfeita!');
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'excluir_usuario');
        formData.append('id', id);

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Usuário excluído com sucesso!');
            carregarUsuarios();
            atualizarEstatisticas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir usuário');
    }
}





async function excluirRoteiro(id) {
    const confirmacao = confirm('Tem certeza que deseja EXCLUIR este roteiro? Esta ação não pode ser desfeita!');
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'excluir_roteiro');
        formData.append('id', id);

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Roteiro excluído com sucesso!');
            carregarRoteiros();
            atualizarEstatisticas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir roteiro');
    }
}





async function excluirPonto(id) {
    const confirmacao = confirm('Tem certeza que deseja EXCLUIR este ponto? Esta ação não pode ser desfeita!');
    
    if (!confirmacao) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'excluir_ponto');
        formData.append('id', id);

        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Ponto excluído com sucesso!');
            carregarPontos();
            atualizarEstatisticas();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir ponto');
    }
}





function filtrarTabela(tipo) {
    const searchId = `search${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`;
    const termo = document.getElementById(searchId).value.toLowerCase();
    
    if (tipo === 'usuarios') {
        const filtrados = dadosUsuarios.filter(user => 
            user.nome.toLowerCase().includes(termo) ||
            user.email.toLowerCase().includes(termo)
        );
        renderizarUsuarios(filtrados);
    } else if (tipo === 'roteiros') {
        const filtrados = dadosRoteiros.filter(roteiro => 
            roteiro.nome.toLowerCase().includes(termo) ||
            (roteiro.bio && roteiro.bio.toLowerCase().includes(termo))
        );
        renderizarRoteiros(filtrados);
    } else if (tipo === 'pontos') {
        const filtrados = dadosPontos.filter(ponto => 
            ponto.nome.toLowerCase().includes(termo) ||
            ponto.localidade.toLowerCase().includes(termo)
        );
        renderizarPontos(filtrados);
    }
}

function formatarData(dataISO) {
    if (!dataISO) return 'N/A';
    const data = new Date(dataISO);
    return data.toLocaleDateString('pt-BR');
}

async function atualizarEstatisticas() {
    try {
        const response = await fetch('includes/admin_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=estatisticas'
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('totalUsuarios').textContent = result.stats.usuarios;
            document.getElementById('totalRoteiros').textContent = result.stats.roteiros;
            document.getElementById('totalPontos').textContent = result.stats.pontos;
        }
    } catch (error) {
        console.error('Erro ao atualizar estatísticas:', error);
    }
}





document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Admin panel carregado!');
    carregarUsuarios();
});