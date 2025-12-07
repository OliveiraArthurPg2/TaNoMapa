



let todosPontos = [];
let pontosSelecionados = [];


async function inicializarBusca() {
    console.log('🔍 Inicializando busca de pontos para roteiro (PERFIL)...');
    
    try {
        const formData = new FormData();
        formData.append('action', 'listar_todos_pontos');

        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        console.log('📦 Pontos carregados (PERFIL):', result);

        if (result.success && result.pontos) {
            todosPontos = result.pontos || [];
            console.log('✅ Total de pontos disponíveis (PERFIL):', todosPontos.length);
            
            
            exibirResultadosBusca(todosPontos.slice(0, 5));
        } else {
            console.error('❌ Erro ao carregar pontos:', result.message);
            const resultadosDiv = document.getElementById('resultadosBusca');
            if (resultadosDiv) {
                resultadosDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: #ef4444;">Erro ao carregar locais</div>';
                resultadosDiv.classList.add('show');
            }
        }
    } catch (error) {
        console.error('❌ Erro ao carregar pontos:', error);
        const resultadosDiv = document.getElementById('resultadosBusca');
        if (resultadosDiv) {
            resultadosDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: #ef4444;">Erro ao carregar locais</div>';
            resultadosDiv.classList.add('show');
        }
    }
}


window.buscarPontos = function(termo) {
    console.log('⌨️ Buscando (PERFIL):', termo);
    const resultadosDiv = document.getElementById('resultadosBusca');
    
    if (!resultadosDiv) {
        console.error('❌ Container resultadosBusca não encontrado!');
        return;
    }
    
    if (termo.length === 0) {
        exibirResultadosBusca(todosPontos.slice(0, 5));
        return;
    }

    const termoLower = termo.toLowerCase();
    const resultados = todosPontos.filter(ponto => 
        ponto.Nome.toLowerCase().includes(termoLower) ||
        (ponto.Localidade && ponto.Localidade.toLowerCase().includes(termoLower)) ||
        (ponto.Tipo && ponto.Tipo.toLowerCase().includes(termoLower))
    );

    console.log('📍 Resultados encontrados (PERFIL):', resultados.length);
    exibirResultadosBusca(resultados);
}

function exibirResultadosBusca(pontos) {
    const resultadosDiv = document.getElementById('resultadosBusca');
    
    if (!resultadosDiv) {
        console.error('❌ Container resultadosBusca não encontrado!');
        return;
    }
    
    if (pontos.length === 0) {
        resultadosDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: #888;">Nenhum local encontrado</div>';
        resultadosDiv.classList.add('show');
        return;
    }

    resultadosDiv.innerHTML = pontos.map(ponto => `
        <div class="search-item" onclick="window.adicionarPonto(${ponto.Id})">
            <img src="${ponto.Foto_Capa || ponto.Foto_Perfil || 'img/default_cover.jpg'}" 
                 alt="${ponto.Nome}" 
                 class="search-item-foto"
                 onerror="this.src='img/default_cover.jpg'">
            <div class="search-item-content">
                <div class="search-item-nome">${ponto.Nome}</div>
                <div class="search-item-info">
                    <span class="search-item-tipo">🏷️ ${ponto.Tipo || 'Local'}</span>
                    <span class="search-item-separador">•</span>
                    <span class="search-item-localidade">📍 ${ponto.Localidade || 'Localização não informada'}</span>
                </div>
            </div>
        </div>
    `).join('');
    
    resultadosDiv.classList.add('show');
    console.log('✅ Resultados exibidos (PERFIL)');
}


window.adicionarPonto = function(id) {
    console.log('➕ Adicionando ponto (PERFIL):', id);
    
    if (pontosSelecionados.find(p => p.Id === id)) {
        alert('Este local já foi adicionado ao roteiro');
        return;
    }

    const ponto = todosPontos.find(p => p.Id === id);
    if (ponto) {
        pontosSelecionados.push(ponto);
        atualizarListaPontosSelecionados();
        
        
        const inputBusca = document.getElementById('buscaPontos');
        if (inputBusca) {
            inputBusca.value = '';
        }
        
        const resultadosDiv = document.getElementById('resultadosBusca');
        if (resultadosDiv) {
            resultadosDiv.classList.remove('show');
        }
        
        console.log('✅ Ponto adicionado (PERFIL):', ponto.Nome);
        console.log('📊 Total de pontos selecionados:', pontosSelecionados.length);
    }
}


window.removerPonto = function(id) {
    console.log('➖ Removendo ponto (PERFIL):', id);
    pontosSelecionados = pontosSelecionados.filter(p => p.Id !== id);
    atualizarListaPontosSelecionados();
    console.log('📊 Total de pontos selecionados:', pontosSelecionados.length);
}


function atualizarListaPontosSelecionados() {
    const container = document.getElementById('pontosSelecionados');
    
    if (!container) {
        console.error('❌ Container pontosSelecionados não encontrado!');
        return;
    }
    
    if (pontosSelecionados.length === 0) {
        container.innerHTML = '<p class="aviso-vazio">Nenhum local adicionado ainda. Use a busca acima para adicionar.</p>';
        return;
    }

    container.innerHTML = pontosSelecionados.map((ponto, index) => `
        <div class="ponto-selecionado" draggable="true" data-id="${ponto.Id}" data-index="${index}">
            <span class="ponto-drag-handle">☰</span>
            <div class="ponto-info-selecionado">
                <span class="ponto-ordem">${index + 1}</span>
                <span class="ponto-nome-selecionado">${ponto.Nome}</span>
                <div class="ponto-local-selecionado">${ponto.Localidade || 'Localização não informada'}</div>
            </div>
            <button type="button" class="btn-remover-ponto" onclick="window.removerPonto(${ponto.Id})">✕</button>
        </div>
    `).join('');

    habilitarDragAndDrop();
    console.log('✅ Lista atualizada com', pontosSelecionados.length, 'pontos');
}


function habilitarDragAndDrop() {
    const pontos = document.querySelectorAll('#pontosSelecionados .ponto-selecionado');
    let draggedElement = null;

    pontos.forEach(ponto => {
        ponto.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
        });

        ponto.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
        });

        ponto.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        ponto.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement !== this) {
                const fromIndex = parseInt(draggedElement.dataset.index);
                const toIndex = parseInt(this.dataset.index);
                
                const [removed] = pontosSelecionados.splice(fromIndex, 1);
                pontosSelecionados.splice(toIndex, 0, removed);
                
                atualizarListaPontosSelecionados();
            }
        });
    });
}


document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 roteiro.js carregado!');
    
    const formRoteiro = document.getElementById('formRoteiro');
    if (formRoteiro) {
        console.log('✅ Form de roteiro encontrado (PERFIL)');
        
        formRoteiro.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const nome = document.getElementById('nomeRoteiro').value;
            const bio = document.getElementById('bioRoteiro').value;
            
            if (!nome.trim()) {
                alert('Por favor, informe o nome do roteiro');
                return;
            }

            if (pontosSelecionados.length < 2) {
                alert('Selecione pelo menos 2 locais para o roteiro');
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
                    alert('Roteiro criado com sucesso!');
                    
                    
                    if (typeof fecharModal === 'function') {
                        fecharModal();
                    }
                    
                    
                    formRoteiro.reset();
                    pontosSelecionados = [];
                    atualizarListaPontosSelecionados();
                    
                    
                    if (typeof carregarDadosPerfil === 'function') {
                        carregarDadosPerfil();
                    }
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao criar roteiro. Tente novamente.');
            }
        });
    }
});


window.abrirModalRoteiro = function() {
    const modal = document.getElementById('modalCriarRoteiro');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        
        
        pontosSelecionados = [];
        atualizarListaPontosSelecionados();
        
        
        setTimeout(() => {
            inicializarBusca();
        }, 100);
    }
}