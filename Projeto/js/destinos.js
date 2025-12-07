
let paginaAtualDestinos = 1;
const itensPorPagina = 6;

function filtrarPontos() {
    paginaAtualDestinos = 1;
    carregarPontos();
}

function carregarPontos() {
    var tipo = document.getElementById('filtroTipo').value;
    var formData = new FormData();
    formData.append('action', 'listar_por_tipo');
    formData.append('tipo', tipo);
    
    fetch('includes/pontos_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        if (result.success) {
            exibirPontos(result.pontos);
            criarPaginacao(result.pontos.length);
        }
    })
    .catch(function(error) {
        console.error('Erro:', error);
    });
}

function exibirPontos(pontos) {
    var grid = document.getElementById('gridPontos');
    
    if (pontos.length === 0) {
        grid.innerHTML = '<p class="aviso-vazio">Nenhum ponto turistico encontrado</p>';
        return;
    }
    
    var inicio = (paginaAtualDestinos - 1) * itensPorPagina;
    var fim = inicio + itensPorPagina;
    var pontosPagina = pontos.slice(inicio, fim);
    var html = '';
    
    for (var i = 0; i < pontosPagina.length; i++) {
        var ponto = pontosPagina[i];
        var nota = ponto.Avaliacao ? parseFloat(ponto.Avaliacao).toFixed(1) : 'Novo';
        var bio = ponto.Bio || 'Sem descricao';
        var bioTruncada = bio.length > 100 ? bio.substring(0, 100) + '...' : bio;
        var fotoCapa = ponto.Foto_Capa || 'img/default_cover.jpg';
        var fornecedor = ponto.NomeFornecedor || 'Desconhecido';
        var avaliacoes = ponto.Total_Avaliacoes || 0;
        var fotoFornecedor = ponto.FotoFornecedor || 'img/default_avatar.png';
        var idFornecedor = ponto.IdFornecedor || '';
        
        html += '<div class="card-ponto" onclick="window.location.href=\'visualizar-ponto.php?id=' + ponto.Id + '\'">';
        html += '<div class="card-img" style="background-image: url(\'' + fotoCapa + '\')">';
        html += '<span class="badge">⭐ ' + nota + '</span>';
        html += '</div>';
        html += '<div class="card-info">';
        html += '<h3>' + ponto.Nome + '</h3>';
        html += '<p class="card-location">📍 ' + ponto.Localidade + '</p>';
        html += '<p class="card-bio">' + bioTruncada + '</p>';
        html += '<div class="card-footer">';
        html += '<div class="carousel-creator-wrapper" onclick="event.stopPropagation(); window.location.href=\'perfil-publico.php?id=' + idFornecedor + '\'">';
        html += '<img src="' + fotoFornecedor + '" alt="' + fornecedor + '" class="carousel-creator-avatar">';
        html += '<span>Criado por ' + fornecedor + '</span>';
        html += '</div>';
        html += '<span>' + avaliacoes + ' avaliações</span>';
        html += '</div>'; 
        html += '</div>'; 
        html += '</div>'; 
    }
    
    grid.innerHTML = html;
}

function criarPaginacao(total) {
    var totalPaginas = Math.ceil(total / itensPorPagina);
    var paginacao = document.getElementById('paginacao');
    
    if (totalPaginas <= 1) {
        paginacao.innerHTML = '';
        return;
    }
    
    var html = '';
    for (var i = 1; i <= totalPaginas; i++) {
        var ativo = i === paginaAtualDestinos ? 'active' : '';
        html += '<button class="btn-pagina ' + ativo + '" onclick="irParaPagina(' + i + ')">' + i + '</button>';
    }
    
    paginacao.innerHTML = html;
}

function irParaPagina(pagina) {
    paginaAtualDestinos = pagina;
    carregarPontos();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', carregarPontos);




