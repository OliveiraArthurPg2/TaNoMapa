
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔵 perfil.js carregado');
    carregarDadosPerfil();
    configurarFormularios();
});

async function carregarDadosPerfil() {
    try {
        const formData = new FormData();
        formData.append('action', 'obter_dados');

        const response = await fetch('includes/perfil_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            preencherDadosPerfil(result.dados);
            preencherFormularioEdicao(result.dados);
            
            
            const numRoteiros = result.dados.roteiros ? result.dados.roteiros.length : 0;
            const numLocais = result.dados.locais ? result.dados.locais.length : 0;
            
            const contadorRoteiros = document.getElementById('contadorRoteiros');
            if (contadorRoteiros) {
                contadorRoteiros.textContent = `🗺️ ${numRoteiros} ${numRoteiros === 1 ? 'roteiro' : 'roteiros'}`;
            }
            
            const contadorLocais = document.getElementById('contadorLocais');
            if (contadorLocais) {
                contadorLocais.textContent = `📍 ${numLocais} ${numLocais === 1 ? 'local' : 'locais'}`;
            }
            
            
            if (result.dados.roteiros) {
                exibirRoteiros(result.dados.roteiros);
            }
        }
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    }
}

function preencherDadosPerfil(dados) {
    
    const nomeUsuarioEl = document.getElementById('nomeUsuario');
    if (nomeUsuarioEl) {
        nomeUsuarioEl.textContent = dados.nome || '-';
    }
    
    
    const bioEl = document.getElementById('bio');
    if (bioEl) {
        bioEl.textContent = dados.bio || 'Sem descrição';
    }
    
    
    const fotoPerfilEl = document.getElementById('fotoPerfilExibicao');
    if (fotoPerfilEl) {
        fotoPerfilEl.src = dados.fotoPerfil || 'img/default_avatar.png';
    }
    
    
    const fotoCapaEl = document.getElementById('fotoCapaExibicao');
    if (fotoCapaEl) {
        if (dados.fotoCapa) {
            fotoCapaEl.style.background = `linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('${dados.fotoCapa}')`;
            fotoCapaEl.style.backgroundSize = 'cover';
            fotoCapaEl.style.backgroundPosition = 'center';
        } else {
            fotoCapaEl.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    }
}

function preencherFormularioEdicao(dados) {
    const editNome = document.getElementById('editNome');
    const editTelefone = document.getElementById('editTelefone');
    const editBio = document.getElementById('editBio');
    
    if (editNome) editNome.value = dados.nome || '';
    if (editTelefone) editTelefone.value = dados.telefone || '';
    if (editBio) editBio.value = dados.bio || '';
    
    if (dados.tipo === 'Fornecedor') {
        const cnpjInput = document.getElementById('editCnpj');
        if (cnpjInput) cnpjInput.value = dados.cnpj || '';
    } else {
        const cpfInput = document.getElementById('editCpf');
        const dataNascInput = document.getElementById('editDataNascimento');
        if (cpfInput) cpfInput.value = dados.cpf || '';
        if (dataNascInput) dataNascInput.value = dados.dataNascimento || '';
    }
}

function configurarFormularios() {
    console.log('🔧 Configurando formulários...');
    
    const formPerfil = document.getElementById('formPerfil');
    if (formPerfil) {
        console.log('✅ Form perfil encontrado');
        
        
        formPerfil.removeEventListener('submit', handleSubmitPerfil);
        
        
        formPerfil.addEventListener('submit', handleSubmitPerfil);
    } else {
        console.error('❌ Form perfil NÃO encontrado!');
    }
    
    const formSenha = document.getElementById('formSenha');
    if (formSenha) {
        formSenha.removeEventListener('submit', handleSubmitSenha);
        formSenha.addEventListener('submit', handleSubmitSenha);
    }
}


async function handleSubmitPerfil(e) {
    e.preventDefault();
    console.log('🚀 handleSubmitPerfil chamado');
    await salvarPerfil();
}

async function handleSubmitSenha(e) {
    e.preventDefault();
    console.log('🔑 handleSubmitSenha chamado');
    await alterarSenha();
}

async function salvarPerfil() {
    console.log('💾 Iniciando salvarPerfil()');
    
    try {
        const formData = new FormData();
        formData.append('action', 'atualizar');
        
        
        const nome = document.getElementById('editNome').value.trim();
        console.log('Nome:', nome);
        
        if (!nome) {
            alert('Nome é obrigatório!');
            return;
        }
        formData.append('nome', nome);
        
        
        const telefone = document.getElementById('editTelefone');
        const bio = document.getElementById('editBio');
        
        if (telefone) {
            formData.append('telefone', telefone.value.trim());
            console.log('Telefone:', telefone.value);
        }
        if (bio) {
            formData.append('bio', bio.value.trim());
            console.log('Bio:', bio.value);
        }
        
        
        const cnpjInput = document.getElementById('editCnpj');
        const cpfInput = document.getElementById('editCpf');
        const dataNascInput = document.getElementById('editDataNascimento');
        
        if (cnpjInput && cnpjInput.value) {
            formData.append('cnpj', cnpjInput.value.trim());
            console.log('CNPJ:', cnpjInput.value);
        }
        if (cpfInput && cpfInput.value) {
            formData.append('cpf', cpfInput.value.trim());
            console.log('CPF:', cpfInput.value);
        }
        if (dataNascInput && dataNascInput.value) {
            formData.append('dataNascimento', dataNascInput.value);
            console.log('Data Nasc:', dataNascInput.value);
        }
        
        
        const fotoPerfilInput = document.getElementById('editFotoPerfil');
        const fotoCapaInput = document.getElementById('editFotoCapa');
        
        if (fotoPerfilInput && fotoPerfilInput.files[0]) {
            formData.append('fotoPerfil', fotoPerfilInput.files[0]);
            console.log('Foto Perfil:', fotoPerfilInput.files[0].name);
        }
        
        if (fotoCapaInput && fotoCapaInput.files[0]) {
            formData.append('fotoCapa', fotoCapaInput.files[0]);
            console.log('Foto Capa:', fotoCapaInput.files[0].name);
        }

        
        console.log('📤 Enviando dados...');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
        }

        const response = await fetch('includes/perfil_handler.php', {
            method: 'POST',
            body: formData
        });

        console.log('📡 Status da resposta:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const text = await response.text();
        console.log('📄 Resposta (text):', text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('❌ Erro ao parsear JSON:', parseError);
            console.error('Texto recebido:', text);
            throw new Error('Resposta inválida do servidor');
        }

        console.log('✅ JSON parseado:', result);

        if (result.success) {
            alert('✅ Perfil atualizado com sucesso!');
            fecharModal();
            location.reload();
        } else {
            alert('❌ Erro: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('💥 Erro completo:', error);
        console.error('Stack:', error.stack);
        alert('❌ Erro ao salvar perfil: ' + error.message);
    }
}

async function alterarSenha() {
    try {
        const formData = new FormData(document.getElementById('formSenha'));
        formData.append('action', 'atualizar_senha');

        const response = await fetch('includes/perfil_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            document.getElementById('formSenha').reset();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao alterar senha. Tente novamente.');
    }
}

function exibirRoteiros(roteiros) {
    const container = document.getElementById('listaItens');
    
    if (!container) return;
    
    if (roteiros.length === 0) {
        container.innerHTML = '<p class="aviso-lista">Você ainda não criou nenhum roteiro</p>';
        return;
    }

    container.innerHTML = roteiros.map(roteiro => {
        const gradientes = [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
        ];
        const gradiente = gradientes[Math.floor(Math.random() * gradientes.length)];
        
        const fotoCapa = roteiro.Foto_Capa || roteiro.foto_capa || roteiro.FotoCapa || roteiro.fotoCapa;
        
        let backgroundStyle = gradiente;
        if (fotoCapa) {
            backgroundStyle = `linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), url('${fotoCapa}')`;
        }
        
        
        const avaliacao = parseFloat(roteiro.Avaliacao || 5.0).toFixed(1);
        
        return `
        <div class="item-lista" onclick="visualizarRoteiro(${roteiro.Id})">
            <div class="item-imagem" style="background: ${backgroundStyle}; background-size: cover; background-position: center;">
                <div class="item-badge">⭐ ${avaliacao}</div>
            </div>
            <div class="item-info">
                <div class="item-nome">${roteiro.Nome}</div>
                <div class="item-detalhe">📅 ${roteiro.Duracao || '7 dias'}</div>
                <div class="item-detalhe">📍 ${roteiro.TotalLocais || '0'} destinos</div>
            </div>
            <div class="item-acoes">
                <button class="btn-item" onclick="event.stopPropagation(); visualizarRoteiro(${roteiro.Id})">👁️ Ver</button>
                <button class="btn-item" onclick="event.stopPropagation(); excluirRoteiro(${roteiro.Id})">🗑️ Excluir</button>
            </div>
        </div>
    `}).join('');
}

function carregarRoteiros() {
    carregarDadosPerfil();
}

function carregarLocais() {
    const container = document.getElementById('listaLocais');
    
    if (!container) return;
    
    fetch('includes/perfil_handler.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'obter_dados' })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success && result.dados.locais) {
            exibirLocais(result.dados.locais);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar locais:', error);
        container.innerHTML = '<p class="aviso-lista">Erro ao carregar locais</p>';
    });
}

function exibirLocais(locais) {
    const container = document.getElementById('listaLocais');
    
    if (!container) return;
    
    if (locais.length === 0) {
        container.innerHTML = '<p class="aviso-lista">Você ainda não cadastrou nenhum local</p>';
        return;
    }

    container.innerHTML = locais.map(local => {
        const fotoCapa = local.Foto_Capa || local.foto_capa || local.FotoCapa || local.fotoCapa || 'img/default_cover.jpg';
        const fotoFornecedor = local.FotoFornecedor || 'img/default_avatar.png';
        const idFornecedor = local.IdFornecedor || local.Fornecedor || '';
        const nomeFornecedor = local.NomeFornecedor || 'Fornecedor';
        
        let backgroundStyle = `url('${fotoCapa}')`;
        
        const avaliacao = local.Avaliacao ? parseFloat(local.Avaliacao).toFixed(1) : 'Novo';
        const badgeTexto = local.Avaliacao ? `⭐ ${avaliacao}` : 'Novo';
        const totalAvaliacoes = local.Total_Avaliacoes || 0;
        const bio = local.Bio || 'Sem descrição disponível';
        const bioTruncada = bio.length > 100 ? bio.substring(0, 100) + '...' : bio;
        
        return `
        <div class="item-lista" onclick="visualizarLocal(${local.Id})">
            <div class="item-imagem" style="background-image: ${backgroundStyle}; background-size: cover; background-position: center;">
                <div class="item-badge">${badgeTexto}</div>
            </div>
            <div class="item-info">
                <h3 class="item-nome">${local.Nome}</h3>
                <p class="card-location">📍 ${local.Localidade}</p>
                <p class="card-bio">${bioTruncada}</p>
                <div class="card-footer">
                    <div class="carousel-creator-wrapper" onclick="event.stopPropagation(); window.location.href='perfil-publico.php?id=${idFornecedor}'">
                        <img src="${fotoFornecedor}" alt="${nomeFornecedor}" class="carousel-creator-avatar">
                        <span>Criado por ${nomeFornecedor}</span>
                    </div>
                    <span class="item-avaliacoes">${totalAvaliacoes} avaliações</span>
                </div>
            </div>
            <div class="item-acoes">
                <button class="btn-item" onclick="event.stopPropagation(); visualizarLocal(${local.Id})">👁️ Ver</button>
                <button class="btn-item" onclick="event.stopPropagation(); excluirLocal(${local.Id})">🗑️ Excluir</button>
            </div>
        </div>
    `}).join('');
}

function abrirModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
    }
}

function fecharModal() {
    document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
}

function toggleFabMenu() {
    const menu = document.getElementById('fabMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

document.addEventListener('click', function(event) {
    const fabContainer = document.querySelector('.fab-container');
    const fabMenu = document.getElementById('fabMenu');
    
    if (fabContainer && !fabContainer.contains(event.target) && fabMenu) {
        fabMenu.classList.remove('show');
    }
});

function visualizarRoteiro(id) {
    window.location.href = 'visualizar-roteiro.php?id=' + id;
}

async function excluirRoteiro(id) {
    if (!confirm('Deseja realmente excluir este roteiro?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'deletar');
        formData.append('id', id);

        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Roteiro excluído com sucesso!');
            carregarDadosPerfil();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir roteiro. Tente novamente.');
    }
}

function visualizarLocal(id) {
    window.location.href = 'visualizar-ponto.php?id=' + id;
}

async function excluirLocal(id) {
    if (!confirm('Deseja realmente excluir este local?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'deletar');
        formData.append('id', id);

        const response = await fetch('includes/local_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Local excluído com sucesso!');
            carregarDadosPerfil();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir local. Tente novamente.');
    }
}