

let pontoAtualIndex = 0;
let totalPontos = 0;
let pontosArray = [];
let pontosDisponiveis = [];

function inicializarGaleria() {
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    totalPontos = thumbnails.length;
    
    pontosArray = Array.from(thumbnails).map(thumb => {
        const img = thumb.querySelector('img');
        return {
            src: img.src,
            id: thumb.dataset.pontoId,
            nome: thumb.querySelector('.ponto-nome-mini')?.textContent || '',
            localidade: pontosData[thumb.dataset.index]?.Localidade || ''
        };
    });
}

function navegarGaleria(direcao) {
    pontoAtualIndex += direcao;
    
    if (pontoAtualIndex < 0) {
        pontoAtualIndex = totalPontos - 1;
    } else if (pontoAtualIndex >= totalPontos) {
        pontoAtualIndex = 0;
    }
    
    atualizarFotoPrincipal();
}

function selecionarPonto(index) {
    pontoAtualIndex = index;
    atualizarFotoPrincipal();
}

function atualizarFotoPrincipal() {
    const fotoPrincipal = document.getElementById('fotoPrincipal');
    const fotoAtualSpan = document.getElementById('fotoAtual');
    const pontoNome = document.getElementById('pontoNome');
    const pontoLocalidade = document.getElementById('pontoLocalidade');
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    
    if (fotoPrincipal && pontosArray[pontoAtualIndex]) {
        fotoPrincipal.src = pontosArray[pontoAtualIndex].src;
        fotoPrincipal.onclick = () => abrirLightbox(pontosArray[pontoAtualIndex].src);
    }
    
    if (fotoAtualSpan) {
        fotoAtualSpan.textContent = pontoAtualIndex + 1;
    }
    
    if (pontoNome && pontosArray[pontoAtualIndex]) {
        pontoNome.textContent = pontosArray[pontoAtualIndex].nome;
    }
    
    if (pontoLocalidade && pontosArray[pontoAtualIndex]) {
        pontoLocalidade.textContent = '📍 ' + pontosArray[pontoAtualIndex].localidade;
    }
    
    thumbnails.forEach((thumb, index) => {
        if (index === pontoAtualIndex) {
            thumb.classList.add('active');
            thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
            thumb.classList.remove('active');
        }
    });
}

function abrirModalAvaliacao() {
    const modal = document.getElementById('modalAvaliacao');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
    }
}

function editarSobre() {
    const modal = document.getElementById('modalEditarSobre');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        carregarPontosDisponiveisEdit();
    }
}

function abrirModalAdicionarPonto() {
    const modal = document.getElementById('modalAdicionarPonto');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        carregarPontosDisponiveis();
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

let todosOsPontos = [];

async function carregarPontosDisponiveisEdit() {
    console.log('🔄 Carregando pontos disponíveis para edição...');
    console.log('📋 Roteiro ID:', roteiroId);
    
    try {
        const formData = new FormData();
        formData.append('action', 'listar_pontos_disponiveis');
        formData.append('roteiroId', roteiroId);

        console.log('📤 Enviando requisição...');

        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        console.log('📥 Response status:', response.status);
        console.log('📥 Response ok:', response.ok);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erro HTTP:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const text = await response.text();
        console.log('📄 Response text (primeiros 200 chars):', text.substring(0, 200));

        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('❌ Erro ao parsear JSON:', parseError);
            console.error('📄 Texto completo recebido:', text);
            throw new Error('Resposta não é um JSON válido');
        }

        console.log('✅ Resultado:', result);

        if (result.success && result.pontos) {
            todosOsPontos = result.pontos;
            console.log('📊 Total de pontos disponíveis:', todosOsPontos.length);
        } else {
            console.error('❌ Erro ao carregar pontos:', result.message);
            todosOsPontos = [];
            alert('Erro ao carregar pontos: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('❌ Erro ao carregar pontos:', error);
        console.error('❌ Stack:', error.stack);
        todosOsPontos = [];
        alert('Erro ao carregar pontos disponíveis: ' + error.message);
    }
}

function buscarPontosEdit() {
    const inputBusca = document.getElementById('buscarPontoEdit');
    const resultadosDiv = document.getElementById('resultadosBuscaEdit');
    
    if (!inputBusca || !resultadosDiv) {
        console.error('❌ Elementos não encontrados');
        return;
    }
    
    const termo = inputBusca.value.toLowerCase().trim();
    
    console.log('🔍 Buscando:', termo);
    console.log('📊 Total de pontos:', todosOsPontos.length);
    
    if (!termo) {
        resultadosDiv.style.display = 'none';
        return;
    }
    
    const pontosFiltrados = todosOsPontos.filter(ponto => 
        (ponto.Nome && ponto.Nome.toLowerCase().includes(termo)) ||
        (ponto.Localidade && ponto.Localidade.toLowerCase().includes(termo)) ||
        (ponto.Tipo && ponto.Tipo.toLowerCase().includes(termo))
    );
    
    console.log('✅ Pontos filtrados:', pontosFiltrados.length);
    
    if (pontosFiltrados.length === 0) {
        resultadosDiv.innerHTML = '<p class="aviso-vazio">Nenhum ponto encontrado.</p>';
        resultadosDiv.style.display = 'block';
        return;
    }
    
    resultadosDiv.innerHTML = pontosFiltrados.map(ponto => {
        const fotoUrl = escapeHtml(ponto.Foto_Perfil || ponto.Foto_Capa || 'img/default_cover.jpg');
        const nome = escapeHtml(ponto.Nome);
        const localidade = escapeHtml(ponto.Localidade || 'Localização não informada');
        const tipo = escapeHtml(ponto.Tipo || 'Local');
        
        return `
            <div class="resultado-busca-item" 
                 data-ponto-id="${ponto.Id}"
                 data-ponto-nome="${nome}"
                 data-ponto-localidade="${localidade}"
                 data-ponto-foto="${fotoUrl}">
                <div class="resultado-foto">
                    <img src="${fotoUrl}" alt="${nome}" onerror="this.src='img/default_cover.jpg'">
                </div>
                <div class="resultado-info">
                    <h4>${nome}</h4>
                    <p>🏷️ ${tipo} • 📍 ${localidade}</p>
                </div>
                <span class="resultado-add">➕</span>
            </div>
        `;
    }).join('');
    
    resultadosDiv.style.display = 'block';
    
    
    resultadosDiv.querySelectorAll('.resultado-busca-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const pontoId = this.dataset.pontoId;
            const nome = this.dataset.pontoNome;
            const localidade = this.dataset.pontoLocalidade;
            const foto = this.dataset.pontoFoto;
            
            console.log('🎯 Ponto selecionado:', { pontoId, nome, localidade, foto });
            adicionarPontoNoModal(pontoId, nome, localidade, foto);
        });
    });
}

async function adicionarPontoNoModal(pontoId, nome, localidade, foto) {
    console.log('➕ INICIANDO adição de ponto:', { pontoId, nome, localidade });
    console.log('📋 Roteiro ID:', roteiroId);
    
    if (!pontoId || !roteiroId) {
        console.error('❌ IDs inválidos:', { pontoId, roteiroId });
        alert('Erro: IDs inválidos');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'adicionar_ponto');
        formData.append('roteiroId', String(roteiroId));
        formData.append('pontoId', String(pontoId));

        console.log('📤 Dados a enviar:', {
            action: 'adicionar_ponto',
            roteiroId: String(roteiroId),
            pontoId: String(pontoId)
        });

        console.log('🌐 Fazendo requisição para includes/roteiro_handler.php...');
        
        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        console.log('📥 Response recebido');
        console.log('   Status:', response.status);
        console.log('   OK:', response.ok);
        console.log('   Status Text:', response.statusText);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erro HTTP:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const text = await response.text();
        console.log('📄 Response text recebido (primeiros 500 chars):');
        console.log(text.substring(0, 500));

        let result;
        try {
            result = JSON.parse(text);
            console.log('✅ JSON parseado com sucesso:', result);
        } catch (parseError) {
            console.error('❌ ERRO ao parsear JSON:', parseError);
            console.error('📄 Texto completo que falhou:');
            console.error(text);
            throw new Error('Resposta do servidor não é um JSON válido');
        }

        if (result.success) {
            console.log('✅ Ponto adicionado com sucesso no backend!');
            
            
            const container = document.getElementById('pontosRoteiroEdit');
            if (!container) {
                console.error('❌ Container pontosRoteiroEdit não encontrado!');
                alert('✅ Ponto adicionado! Recarregue a página para ver.');
                return;
            }
            
            const avisoVazio = container.querySelector('.aviso-vazio');
            if (avisoVazio) {
                avisoVazio.remove();
                console.log('🗑️ Aviso vazio removido');
            }
            
            const novoPonto = document.createElement('div');
            novoPonto.className = 'ponto-edit-card';
            novoPonto.dataset.pontoId = pontoId;
            novoPonto.innerHTML = `
                <div class="ponto-edit-foto">
                    <img src="${escapeHtml(foto)}" alt="${escapeHtml(nome)}" onerror="this.src='img/default_cover.jpg'">
                </div>
                <div class="ponto-edit-info">
                    <h4>${escapeHtml(nome)}</h4>
                    <p>📍 ${escapeHtml(localidade)}</p>
                </div>
                <button type="button" class="btn-remover-ponto-edit" 
                        data-ponto-id="${pontoId}" 
                        title="Remover ponto">
                    🗑️
                </button>
            `;
            container.appendChild(novoPonto);
            console.log('✅ Card adicionado ao DOM');
            
            
            const btnRemover = novoPonto.querySelector('.btn-remover-ponto-edit');
            if (btnRemover) {
                btnRemover.addEventListener('click', function() {
                    removerPontoDoModal(this.dataset.pontoId);
                });
                console.log('✅ Evento de remoção adicionado');
            }
            
            
            const inputBusca = document.getElementById('buscarPontoEdit');
            const resultadosDiv = document.getElementById('resultadosBuscaEdit');
            
            if (inputBusca) {
                inputBusca.value = '';
                console.log('🧹 Input de busca limpo');
            }
            
            if (resultadosDiv) {
                resultadosDiv.style.display = 'none';
                console.log('🧹 Resultados da busca ocultados');
            }
            
            
            todosOsPontos = todosOsPontos.filter(p => p.Id != pontoId);
            console.log('📊 Pontos disponíveis atualizados:', todosOsPontos.length);
            
            alert('✅ Ponto adicionado com sucesso!');
        } else {
            console.error('❌ Backend retornou erro:', result.message);
            alert('❌ Erro: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('❌ ERRO COMPLETO:', error);
        console.error('❌ Nome:', error.name);
        console.error('❌ Mensagem:', error.message);
        console.error('❌ Stack:', error.stack);
        alert('❌ Erro ao adicionar ponto: ' + error.message);
    }
}

async function removerPontoDoModal(pontoId) {
    if (!confirm('Deseja remover este ponto do roteiro?')) {
        return;
    }

    console.log('🗑️ Removendo ponto:', pontoId);

    try {
        const formData = new FormData();
        formData.append('action', 'remover_ponto');
        formData.append('roteiroId', roteiroId);
        formData.append('pontoId', pontoId);

        const response = await fetch('includes/roteiro_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        console.log('✅ Resultado remoção:', result);

        if (result.success) {
            const pontoCard = document.querySelector(`.ponto-edit-card[data-ponto-id="${pontoId}"]`);
            if (pontoCard) {
                pontoCard.remove();
            }
            
            const container = document.getElementById('pontosRoteiroEdit');
            if (container.children.length === 0) {
                container.innerHTML = '<p class="aviso-vazio">Nenhum ponto adicionado ainda.</p>';
            }
            
            await carregarPontosDisponiveisEdit();
            
            alert('✅ Ponto removido com sucesso!');
        } else {
            alert('❌ Erro: ' + result.message);
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        alert('❌ Erro ao remover ponto');
    }
}

function fecharModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});

function abrirLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('show');
}

function fecharLightbox() {
    document.getElementById('lightbox').classList.remove('show');
}

async function carregarAvaliacoes() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_avaliacoes');
        formData.append('tipo', 'Roteiro');
        formData.append('roteiroId', roteiroId);

        const response = await fetch('includes/avaliacao_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        const container = document.getElementById('listaAvaliacoes');

        if (result.success && result.avaliacoes && result.avaliacoes.length > 0) {
            container.innerHTML = result.avaliacoes.map(aval => {
                const fotoUrl = aval.Foto_Perfil || '';
                const avatarHTML = fotoUrl 
                    ? `<img src="${escapeHtml(fotoUrl)}" alt="${escapeHtml(aval.NomeUsuario)}">`
                    : '👤';
                
                return `
                    <div class="avaliacao-card">
                        <div class="avaliacao-header">
                            <div class="avaliacao-usuario" onclick="event.stopPropagation(); window.location.href='perfil-publico.php?id=${aval.IdUsuario}'" style="cursor: pointer;">
                                <div class="usuario-avatar">${avatarHTML}</div>
                                <div>
                                    <p class="usuario-nome">${escapeHtml(aval.NomeUsuario)}</p>
                                    <p class="avaliacao-data">${formatarData(aval.DataAvaliacao)}</p>
                                </div>
                            </div>
                            <div class="avaliacao-nota">${'⭐'.repeat(Math.round(aval.Nota))}</div>
                        </div>
                        ${aval.Descricao ? `<p class="avaliacao-texto">${escapeHtml(aval.Descricao)}</p>` : ''}
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<p class="aviso-vazio">Nenhuma avaliação ainda. Seja o primeiro a avaliar!</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar avaliações:', error);
        document.getElementById('listaAvaliacoes').innerHTML = '<p class="erro">Erro ao carregar avaliações</p>';
    }
}

const formAvaliacao = document.getElementById('formAvaliacao');
if (formAvaliacao) {
    formAvaliacao.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formAvaliacao);
        formData.append('action', 'criar_avaliacao');
        formData.append('tipo', 'Roteiro');

        try {
            const response = await fetch('includes/avaliacao_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Avaliação enviada com sucesso!');
                fecharModal('modalAvaliacao');
                formAvaliacao.reset();
                carregarAvaliacoes();
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar avaliação');
        }
    });
}

const formEditarSobre = document.getElementById('formEditarSobre');
if (formEditarSobre) {
    formEditarSobre.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formEditarSobre);
        formData.append('action', 'editar');
        formData.append('roteiroId', roteiroId);

        try {
            const response = await fetch('includes/roteiro_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Informações atualizadas com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao atualizar informações');
        }
    });
}

function formatarData(data) {
    const d = new Date(data);
    return d.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

document.querySelectorAll('.rating-input input').forEach((input, index) => {
    input.addEventListener('change', () => {
        const labels = input.closest('.rating-input').querySelectorAll('label');
        labels.forEach((label, i) => {
            if (i >= labels.length - index - 1) {
                label.style.color = '#FFD700';
            } else {
                label.style.color = '#ddd';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    console.log('🟢 visualizar-roteiro.js carregado!');
    console.log('📋 Roteiro ID:', roteiroId);
    console.log('👤 É autor:', isAutor);
    
    carregarAvaliacoes();
    inicializarGaleria();
    
    document.querySelectorAll('.btn-remover-ponto-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            removerPontoDoModal(this.dataset.pontoId);
        });
    });
    
    document.addEventListener('keydown', (e) => {
        if (totalPontos > 1) {
            if (e.key === 'ArrowLeft') {
                navegarGaleria(-1);
            } else if (e.key === 'ArrowRight') {
                navegarGaleria(1);
            }
        }
    });
});