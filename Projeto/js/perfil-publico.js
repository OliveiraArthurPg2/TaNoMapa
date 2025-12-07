
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const usuarioId = urlParams.get('id');
    
    if (usuarioId) {
        carregarDadosPerfilPublico(usuarioId);
    } else {
        alert('ID de usuário não fornecido');
        window.location.href = 'index.php';
    }
});

async function carregarDadosPerfilPublico(usuarioId) {
    try {
        const formData = new FormData();
        formData.append('action', 'obter_dados_publico');
        formData.append('usuarioId', usuarioId);

        const response = await fetch('includes/perfil_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            preencherDadosPerfil(result.dados);
            
            
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
            
            
            if (result.dados.tipo === 'Turista') {
                if (result.dados.roteiros) {
                    exibirRoteiros(result.dados.roteiros);
                }
            } else if (result.dados.tipo === 'Fornecedor') {
                if (result.dados.locais) {
                    exibirLocais(result.dados.locais);
                }
            }
        } else {
            alert('Erro: ' + result.message);
            window.location.href = 'index.php';
        }
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        alert('Erro ao carregar perfil');
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
    if (fotoPerfilEl && dados.fotoPerfil) {
        fotoPerfilEl.src = dados.fotoPerfil;
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

function exibirRoteiros(roteiros) {
    const container = document.getElementById('listaItens');
    
    if (!container) return;
    
    if (roteiros.length === 0) {
        container.innerHTML = '<p class="aviso-lista">Este usuário ainda não criou nenhum roteiro</p>';
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
        
        const fotoCapa = roteiro.Foto_Capa;
        
        let backgroundStyle;
        if (fotoCapa) {
            backgroundStyle = `linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('${fotoCapa}')`;
        } else {
            backgroundStyle = gradiente;
        }
        
        const avaliacao = roteiro.Avaliacao ? parseFloat(roteiro.Avaliacao).toFixed(1) : 'Novo';
        const badgeTexto = roteiro.Avaliacao > 0 ? `⭐ ${avaliacao}` : '🆕 Novo';
        
        const bio = roteiro.Bio || 'Sem descrição';
        const bioTruncada = bio.length > 80 ? bio.substring(0, 80) + '...' : bio;
        
        const totalPontos = parseInt(roteiro.TotalPontos) || 0;
        const textoLocais = totalPontos === 1 ? 'local' : 'locais';
        
        return `
        <div class="item-lista" onclick="visualizarRoteiro(${roteiro.Id})">
            <div class="item-imagem" style="background: ${backgroundStyle}; background-size: cover; background-position: center;">
                <div class="item-badge">${badgeTexto}</div>
            </div>
            <div class="item-info">
                <div class="item-nome">${roteiro.Nome}</div>
                <div class="item-bio-roteiro">${bioTruncada}</div>
                <div class="item-detalhe">📍 ${totalPontos} ${textoLocais}</div>
            </div>
            <div class="item-acoes">
                <button class="btn-item" onclick="event.stopPropagation(); visualizarRoteiro(${roteiro.Id})">👁️ Ver Detalhes</button>
            </div>
        </div>
    `}).join('');
}
function exibirRoteiros(roteiros) {
    const container = document.getElementById('listaItens');
    
    if (!container) return;
    
    if (roteiros.length === 0) {
        container.innerHTML = '<p class="aviso-lista">Este usuário ainda não criou nenhum roteiro</p>';
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
        
        const fotoCapa = roteiro.Foto_Capa;
        
        let backgroundStyle;
        if (fotoCapa) {
            backgroundStyle = `linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('${fotoCapa}')`;
        } else {
            backgroundStyle = gradiente;
        }
        
        
        const avaliacao = roteiro.Avaliacao && roteiro.Avaliacao > 0 
            ? parseFloat(roteiro.Avaliacao).toFixed(1) 
            : null;
        
        const badgeHTML = avaliacao 
            ? `<div class="item-badge">⭐ ${avaliacao}</div>`
            : `<div class="item-badge item-badge-novo">🆕 Novo</div>`;
        
        const bio = roteiro.Bio || 'Sem descrição';
        const bioTruncada = bio.length > 80 ? bio.substring(0, 80) + '...' : bio;
        
        
        const totalPontos = parseInt(roteiro.TotalPontos) || 0;
        const textoLocais = totalPontos === 1 ? 'destino' : 'destinos';
        
        return `
        <div class="item-lista" onclick="visualizarRoteiro(${roteiro.Id})">
            <div class="item-imagem" style="background: ${backgroundStyle}; background-size: cover; background-position: center;">
                ${badgeHTML}
            </div>
            <div class="item-info">
                <div class="item-nome">${roteiro.Nome}</div>
                <div class="item-bio-roteiro">${bioTruncada}</div>
                <div class="item-detalhe">📍 ${totalPontos} ${textoLocais}</div>
            </div>
            <div class="item-acoes">
                <button class="btn-item" onclick="event.stopPropagation(); visualizarRoteiro(${roteiro.Id})">👁️ Ver Detalhes</button>
            </div>
        </div>
    `}).join('');
}
function exibirLocais(locais) {
    const container = document.getElementById('listaItens');
    
    if (!container) return;
    
    if (locais.length === 0) {
        container.innerHTML = '<p class="aviso-lista">Este fornecedor ainda não cadastrou nenhum local</p>';
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
                <button class="btn-item" onclick="event.stopPropagation(); visualizarLocal(${local.Id})">👁️ Ver Detalhes</button>
            </div>
        </div>
    `}).join('');
}

function visualizarRoteiro(id) {
    window.location.href = 'visualizar-roteiro.php?id=' + id;
}

function visualizarLocal(id) {
    window.location.href = 'visualizar-ponto.php?id=' + id;
}

function compartilharPerfil() {
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: 'Perfil do Usuário',
            url: url
        }).catch(err => console.log('Erro ao compartilhar:', err));
    } else {
        
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copiado para a área de transferência!');
        }).catch(err => {
            console.error('Erro ao copiar:', err);
        });
    }
}