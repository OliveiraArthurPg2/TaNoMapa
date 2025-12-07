



console.log('🔍 INICIANDO DEBUG DO SISTEMA DE ROTEIROS');
console.log('='.repeat(60));


window.debugSistemaRoteiro = function() {
    console.clear();
    console.log('🔍 DEBUG COMPLETO DO SISTEMA\n');
    
    
    console.log('1️⃣ VERIFICANDO MODAL DE ROTEIRO:');
    const modal = document.getElementById('modalCriarRoteiro');
    if (modal) {
        console.log('   ✅ Modal encontrado:', modal.id);
    } else {
        console.log('   ❌ Modal NÃO encontrado!');
        console.log('   Modais disponíveis:');
        document.querySelectorAll('.modal').forEach(m => {
            console.log('      -', m.id || 'sem ID');
        });
        return;
    }
    
    
    console.log('\n2️⃣ DETECTANDO CONTEXTO (qual página):');
    
    const contextos = [
        { nome: 'INDEX', id: 'nomeRoteiroIndex' },
        { nome: 'DESTINOS', id: 'nomeRoteiroDestinos' },
        { nome: 'PERFIL', id: 'nomeRoteiro' }
    ];
    
    let contextoDetectado = null;
    contextos.forEach(ctx => {
        const el = document.getElementById(ctx.id);
        if (el) {
            console.log(`   ✅ Contexto: ${ctx.nome} (encontrou #${ctx.id})`);
            contextoDetectado = ctx.nome;
        } else {
            console.log(`   ⚪ ${ctx.nome}: não (sem #${ctx.id})`);
        }
    });
    
    if (!contextoDetectado) {
        console.log('   ❌ NENHUM CONTEXTO DETECTADO!');
        return;
    }
    
    
    console.log('\n3️⃣ VERIFICANDO ELEMENTOS DO FORMULÁRIO:');
    
    const sufixos = {
        'INDEX': 'Index',
        'DESTINOS': 'Destinos',
        'PERFIL': ''
    };
    
    const sufixo = sufixos[contextoDetectado];
    
    const elementos = [
        `formRoteiro${sufixo}`,
        `nomeRoteiro${sufixo}`,
        `bioRoteiro${sufixo}`,
        `buscaPontos${sufixo}`,
        `resultadosBusca${sufixo}`,
        `pontosSelecionados${sufixo}`,
        `pontosselecionados${sufixo}` 
    ];
    
    elementos.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            console.log(`   ✅ #${id}`);
        } else {
            console.log(`   ❌ #${id} NÃO ENCONTRADO`);
        }
    });
    
    
    console.log('\n4️⃣ VERIFICANDO FUNÇÕES DISPONÍVEIS:');
    
    const funcoes = [
        'abrirModal',
        'fecharModal',
        'inicializarBuscaUniversal',
        'buscarPontosUniversal',
        'adicionarPontoUniversal',
        'removerPontoUniversal'
    ];
    
    funcoes.forEach(fn => {
        if (typeof window[fn] === 'function') {
            console.log(`   ✅ ${fn}()`);
        } else {
            console.log(`   ❌ ${fn}() NÃO EXISTE`);
        }
    });
    
    
    console.log('\n5️⃣ TESTANDO CARREGAMENTO DE PONTOS:');
    
    fetch('includes/roteiro_handler.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'listar_todos_pontos' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log(`   ✅ API funcionando! ${data.pontos.length} pontos carregados`);
            console.log('   Exemplos:');
            data.pontos.slice(0, 3).forEach(p => {
                console.log(`      - [${p.Id}] ${p.Nome} (${p.Localidade})`);
            });
        } else {
            console.log('   ❌ API retornou erro:', data.message);
        }
    })
    .catch(err => {
        console.log('   ❌ Erro ao chamar API:', err);
    });
    
    
    console.log('\n6️⃣ SCRIPTS JAVASCRIPT CARREGADOS:');
    
    const scripts = document.querySelectorAll('script[src]');
    const scriptNames = [];
    
    scripts.forEach(script => {
        const src = script.src;
        const nome = src.split('/').pop();
        scriptNames.push(nome);
    });
    
    const esperados = [
        'roteiro-universal.js',
        'index.js',
        'destinos.js',
        'perfil.js'
    ];
    
    esperados.forEach(nome => {
        if (scriptNames.includes(nome)) {
            console.log(`   ✅ ${nome}`);
        } else {
            console.log(`   ⚪ ${nome} (não carregado)`);
        }
    });
    
    
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO:');
    console.log(`   Contexto: ${contextoDetectado}`);
    console.log(`   Modal: ${modal ? 'OK' : 'ERRO'}`);
    console.log(`   Formulário: #formRoteiro${sufixo}`);
    console.log(`   Container selecionados: #pontosSelecionados${sufixo}`);
    console.log('='.repeat(60));
    
    console.log('\n💡 PARA TESTAR:');
    console.log('   1. abrirModal("modalCriarRoteiro")');
    console.log('   2. Aguarde 1 segundo');
    console.log('   3. Digite na busca');
    
    return {
        contexto: contextoDetectado,
        modal: !!modal,
        elementos: elementos.filter(id => document.getElementById(id))
    };
};


document.addEventListener('DOMContentLoaded', function() {
    console.log('\n🔍 Para fazer debug completo, digite no console:');
    console.log('   debugSistemaRoteiro()');
    console.log('');
});