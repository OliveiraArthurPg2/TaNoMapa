



console.log('🟢 roteiro-universal.js carregado!');

let todosLocais = [];
let pontosSelecionados = [];
let debounceTimer = null;





async function carregarTodosLocais() {
    if (todosLocais.length > 0) {
        console.log('✅ Locais já carregados:', todosLocais.length);
        return true;
    }
    
    try {
        console.log('📥 Carregando todos os locais...');
        
        const formData = new FormData();
        formData.append('action', 'listar_todos');

        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        console.log('📦 Resultado:', result);

        if (result.success && result.pontos) {
            todosLocais = result.pontos;
            console.log('✅ Locais carregados:', todosLocais.length);
            return true;
        } else {
            console.error('❌ Erro ao carregar locais:', result.message);
            return false;
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        return false;
    }
}





window.inicializarBuscaIndex = async function() {
    console.log('🔵 Inicializando busca INDEX');
    await carregarTodosLocais();
    configurarBusca('Index');
}

window.inicializarBuscaDestinos = async function() {
    console.log('🔵 Inicializando busca DESTINOS');
    await carregarTodosLocais();
    configurarBusca('Destinos');
}

window.inicializarBuscaRotas = async function() {
    console.log('🔵 Inicializando busca ROTAS');
    await carregarTodosLocais();
    configurarBusca('Rotas');
}

window.inicializarBuscaPerfil = async function() {
    console.log('🔵 Inicializando busca PERFIL');
    await carregarTodosLocais();
    configurarBusca('');
}


window.buscarPontos = function(termo) {
    buscarLocais(termo, '');
}

window.buscarPontosIndex = function(termo) {
    buscarLocais(termo, 'Index');
}





function configurarBusca(sufixo) {
    const inputId = sufixo ? `buscaPontos${sufixo}` : 'buscaPontos';
    const resultadosId = sufixo ? `resultadosBusca${sufixo}` : 'resultadosBusca';
    const selecionadosId = sufixo ? `pontosSelecionados${sufixo}` : 'pontosSelecionados';
    
    const input = document.getElementById(inputId);
    
    if (!input) {
        console.error('❌ Input não encontrado:', inputId);
        return;
    }
    
    console.log('✅ Configurando input:', inputId);
    
    input.addEventListener('input', function(e) {
        const termo = e.target.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (termo.length >= 2) {
            debounceTimer = setTimeout(() => {
                buscarLocais(termo, sufixo);
            }, 300);
        } else {
            ocultarResultados(sufixo);
        }
    });
    
    document.addEventListener('click', function(e) {
        const modal = e.target.closest('.modal');
        if (!modal || !modal.classList.contains('show')) {
            ocultarResultados(sufixo);
        }
    });
}





function buscarLocais(termo, sufixo) {
    console.log('🔍 Buscando:', termo);
    
    if (todosLocais.length === 0) {
        console.warn('⚠️ Locais não carregados ainda');
        return;
    }
    
    const termoNorm = normalizarTexto(termo);
    
    const resultados = todosLocais.filter(local => {
        const nome = normalizarTexto(local.Nome || '');
        const localidade = normalizarTexto(local.Localidade || '');
        const tipo = normalizarTexto(local.Tipo || '');
        
        return nome.includes(termoNorm) || 
               localidade.includes(termoNorm) ||
               tipo.includes(termoNorm);
    });
    
    resultados.sort((a, b) => {
        const nomeA = normalizarTexto(a.Nome || '');
        const nomeB = normalizarTexto(b.Nome || '');
        
        const matchA = nomeA.startsWith(termoNorm);
        const matchB = nomeB.startsWith(termoNorm);
        
        if (matchA && !matchB) return -1;
        if (!matchA && matchB) return 1;
        return nomeA.localeCompare(nomeB);
    });
    
    console.log('📊 Resultados:', resultados.length);
    exibirResultados(resultados.slice(0, 8), sufixo, termo);
}

function exibirResultados(resultados, sufixo, termo) {
    const resultadosId = sufixo ? `resultadosBusca${sufixo}` : 'resultadosBusca';
    const container = document.getElementById(resultadosId);
    
    if (!container) {
        console.error('❌ Container não encontrado:', resultadosId);
        return;
    }
    
    if (resultados.length === 0) {
        container.innerHTML = '<div class="search-item" style="padding: 20px; text-align: center; color: #999;">Nenhum local encontrado</div>';
        container.classList.add('show');
        return;
    }
    
    container.innerHTML = resultados.map(local => {
        const foto = local.Foto_Capa || local.Foto_Perfil || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23ddd" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" fill="%23999" font-size="20" dy=".3em"%3ESem Imagem%3C/text%3E%3C/svg%3E';
        const nomeDestacado = destacarTermo(local.Nome, termo);
        
        return `
            <div class="search-item" onclick="adicionarPonto(${local.Id}, '${sufixo}')">
                <img src="${foto}" alt="${local.Nome}" class="search-item-foto" onerror="this.style.display='none'">
                <div class="search-item-content">
                    <div class="search-item-nome">${nomeDestacado}</div>
                    <div class="search-item-info">
                        <span class="search-item-tipo">🏷️ ${local.Tipo}</span>
                        <span class="search-item-separador">•</span>
                        <span class="search-item-localidade">📍 ${local.Localidade}</span>
                    </div>
                    ${local.Bio ? `<div class="search-item-bio">${local.Bio.substring(0, 60)}...</div>` : ''}
                </div>
            </div>
        `;
    }).join('');
    
    container.classList.add('show');
}

function ocultarResultados(sufixo) {
    const resultadosId = sufixo ? `resultadosBusca${sufixo}` : 'resultadosBusca';
    const container = document.getElementById(resultadosId);
    
    if (container) {
        container.classList.remove('show');
    }
}





window.adicionarPonto = function(id, sufixo) {
    const local = todosLocais.find(l => l.Id == id);
    
    if (!local) {
        console.error('❌ Local não encontrado:', id);
        return;
    }
    
    if (pontosSelecionados.find(p => p.Id == id)) {
        alert('Este local já foi adicionado!');
        return;
    }
    
    pontosSelecionados.push(local);
    console.log('✅ Local adicionado:', local.Nome);
    
    atualizarListaSelecionados(sufixo);
    ocultarResultados(sufixo);
    
    const inputId = sufixo ? `buscaPontos${sufixo}` : 'buscaPontos';
    const input = document.getElementById(inputId);
    if (input) input.value = '';
}

window.removerPonto = function(id, sufixo) {
    pontosSelecionados = pontosSelecionados.filter(p => p.Id != id);
    console.log('🗑️ Local removido:', id);
    atualizarListaSelecionados(sufixo);
}

function atualizarListaSelecionados(sufixo) {
    const selecionadosId = sufixo ? `pontosSelecionados${sufixo}` : 'pontosSelecionados';
    const container = document.getElementById(selecionadosId);
    
    if (!container) return;
    
    if (pontosSelecionados.length === 0) {
        container.innerHTML = '<p class="aviso-vazio">Nenhum local adicionado ainda. Use a busca acima para adicionar.</p>';
        return;
    }
    
    container.innerHTML = pontosSelecionados.map((ponto, index) => `
        <div class="ponto-selecionado" draggable="true" data-id="${ponto.Id}">
            <span class="ponto-drag-handle">☰</span>
            <span class="ponto-ordem">${index + 1}</span>
            <div class="ponto-info-selecionado">
                <strong class="ponto-nome-selecionado">${ponto.Nome}</strong>
                <small class="ponto-local-selecionado">📍 ${ponto.Localidade}</small>
            </div>
            <button type="button" class="btn-remover-ponto" onclick="removerPonto(${ponto.Id}, '${sufixo}')">×</button>
        </div>
    `).join('');
    
    configurarDragAndDrop(container);
}

function configurarDragAndDrop(container) {
    let draggedElement = null;
    
    const items = container.querySelectorAll('.ponto-selecionado');
    
    items.forEach(item => {
        item.addEventListener('dragstart', function() {
            draggedElement = this;
            this.classList.add('dragging');
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedElement = null;
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            
            if (draggedElement && draggedElement !== this) {
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                
                if (e.clientY < midpoint) {
                    container.insertBefore(draggedElement, this);
                } else {
                    container.insertBefore(draggedElement, this.nextSibling);
                }
                
                atualizarOrdemPontos(container);
            }
        });
    });
}

function atualizarOrdemPontos(container) {
    const items = container.querySelectorAll('.ponto-selecionado');
    const novaOrdem = [];
    
    items.forEach((item, index) => {
        const id = parseInt(item.dataset.id);
        const ponto = pontosSelecionados.find(p => p.Id == id);
        if (ponto) novaOrdem.push(ponto);
        
        const ordem = item.querySelector('.ponto-ordem');
        if (ordem) ordem.textContent = index + 1;
    });
    
    pontosSelecionados = novaOrdem;
}





function configurarFormularios() {
    ['', 'Index', 'Destinos', 'Rotas'].forEach(sufixo => {
        const formId = sufixo ? `formRoteiro${sufixo}` : 'formRoteiro';
        const form = document.getElementById(formId);
        
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                await criarRoteiro(sufixo);
            });
        }
    });
}

async function criarRoteiro(sufixo) {
    const nomeId = sufixo ? `nomeRoteiro${sufixo}` : 'nomeRoteiro';
    const bioId = sufixo ? `bioRoteiro${sufixo}` : 'bioRoteiro';
    
    const nome = document.getElementById(nomeId).value.trim();
    const bio = document.getElementById(bioId).value.trim();
    
    if (!nome) {
        alert('Digite um nome para o roteiro');
        return;
    }
    
    if (pontosSelecionados.length < 2) {
        alert('Adicione pelo menos 2 locais ao roteiro');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'criar');
        formData.append('nome', nome);
        formData.append('bio', bio);
        formData.append('pontos', JSON.stringify(pontosSelecionados.map(p => p.Id)));
        
        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Roteiro criado com sucesso!');
            pontosSelecionados = [];
            fecharModal();
            window.location.href = 'visualizar-roteiro.php?id=' + result.roteiro_id;
        } else {
            alert('❌ Erro: ' + result.message);
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        alert('Erro ao criar roteiro. Tente novamente.');
    }
}





function normalizarTexto(texto) {
    if (!texto) return '';
    return texto.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function destacarTermo(texto, termo) {
    if (!termo || !texto) return texto;
    
    const regex = new RegExp(`(${termo.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return texto.replace(regex, '<mark>$1</mark>');
}

window.abrirModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        
        pontosSelecionados = [];
        
        if (id === 'modalCriarRoteiro') {
            setTimeout(() => {
                const pagina = document.body.dataset.page || '';
                if (pagina === 'index') inicializarBuscaIndex();
                else if (pagina === 'destinos') inicializarBuscaDestinos();
                else if (pagina === 'rotas') inicializarBuscaRotas();
                else inicializarBuscaPerfil();
            }, 100);
        }
    }
}

window.fecharModal = function() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
    
    pontosSelecionados = [];
}





document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 DOM carregado - roteiro-universal.js');
    
    carregarTodosLocais().then(() => {
        configurarFormularios();
        
        const pagina = document.body.dataset.page;
        if (pagina === 'index') inicializarBuscaIndex();
        else if (pagina === 'destinos') inicializarBuscaDestinos();
        else if (pagina === 'rotas') inicializarBuscaRotas();
        else inicializarBuscaPerfil();
    });
});