let paginaAtualRotas = 1;
const itensPorPaginaRotas = 6;
let todosRoteiros = [];

function filtrarRoteiros() {
    paginaAtualRotas = 1;
    carregarRoteiros();
}

async function carregarRoteiros() {
    try {
        const ordem = document.getElementById('filtroOrdem').value;
        
        const formData = new FormData();
        formData.append('action', 'listar_todos_roteiros');
        formData.append('ordem', ordem);
        
        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Roteiros carregados:', result);
        
        if (result.success) {
            todosRoteiros = result.roteiros || [];
            exibirRoteiros();
            criarPaginacao();
        } else {
            document.getElementById('gridRoteiros').innerHTML = '<p class="aviso-vazio">Erro ao carregar roteiros</p>';
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('gridRoteiros').innerHTML = '<p class="aviso-vazio">Erro ao carregar roteiros</p>';
    }
}

function exibirRoteiros() {
    const grid = document.getElementById('gridRoteiros');
    
    if (todosRoteiros.length === 0) {
        grid.innerHTML = '<p class="aviso-vazio">Nenhum roteiro encontrado</p>';
        return;
    }
    
    const inicio = (paginaAtualRotas - 1) * itensPorPaginaRotas;
    const fim = inicio + itensPorPaginaRotas;
    const roteirosPagina = todosRoteiros.slice(inicio, fim);
    
    const gradientes = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
    ];
    
    grid.innerHTML = roteirosPagina.map(roteiro => {
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
        const bioTruncada = bio.length > 100 ? bio.substring(0, 100) + '...' : bio;
        
        const totalPontos = parseInt(roteiro.TotalPontos) || 0;
        const textoLocais = totalPontos === 1 ? 'destino' : 'destinos';
        
        const fotoAutor = roteiro.FotoAutor || 'img/default_avatar.png';
        const nomeAutor = roteiro.NomeAutor || 'Usuário';
        const idAutor = roteiro.IdAutor || roteiro.Autor || '';
        
        return `
        <div class="card-rota" onclick="window.location.href='visualizar-roteiro.php?id=${roteiro.Id}'">
            <div class="card-img" style="background: ${backgroundStyle}; background-size: cover; background-position: center;">
                ${badgeHTML}
            </div>
            <div class="card-info">
                <h3>${roteiro.Nome}</h3>
                <p class="card-bio">${bioTruncada}</p>
                <div class="card-footer">
                    <div class="carousel-creator-wrapper" onclick="event.stopPropagation(); window.location.href='perfil-publico.php?id=${idAutor}'">
                        <img src="${fotoAutor}" alt="${nomeAutor}" class="carousel-creator-avatar">
                        <span>Por ${nomeAutor}</span>
                    </div>
                    <span>📍 ${totalPontos} ${textoLocais}</span>
                </div>
            </div>
        </div>
    `}).join('');
}

function criarPaginacao() {
    const totalPaginas = Math.ceil(todosRoteiros.length / itensPorPaginaRotas);
    const paginacao = document.getElementById('paginacao');
    
    if (totalPaginas <= 1) {
        paginacao.innerHTML = '';
        return;
    }
    
    let html = '';
    for (let i = 1; i <= totalPaginas; i++) {
        const ativo = i === paginaAtualRotas ? 'active' : '';
        html += `<button class="btn-pagina ${ativo}" onclick="irParaPaginaRotas(${i})">${i}</button>`;
    }
    
    paginacao.innerHTML = html;
}

function irParaPaginaRotas(pagina) {
    paginaAtualRotas = pagina;
    exibirRoteiros();
    criarPaginacao();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', carregarRoteiros);