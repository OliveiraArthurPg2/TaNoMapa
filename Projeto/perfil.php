<?php

require_once 'includes/verificar_sessao.php';
require_once 'config/conexao.php';


if (!estaLogado()) {
    header('Location: login.php');
    exit;
}

$logado = true;
$usuario = obterUsuarioLogado();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}


$isFornecedor = isFornecedor();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-perfil.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-modal.css?v=2.0">
    <link rel="stylesheet" href="css/style-admin.css?v=<?php echo time(); ?>">
</head>
<body>
 
    <header>
        <div class="logo">
            <div class="logo-icon">🌍</div>
            <span>Ta no Mapa</span>
        </div>
        
        <nav>
            <a href="index.php">Home</a>
            <a href="destinos.php">Destinos</a>
            <a href="rotas.php">Rotas</a>
            
            <?php if ($usuario['email'] === 'tanomapa@gmail.com'): ?>
            <a href="admin.php" class="btn-admin">🔧 Admin</a>
            <?php endif; ?>
            
            <a href="perfil.php" class="perfil-link">
                <div class="user-avatar">
                    <?php if (!empty($usuario['fotoPerfil'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario['fotoPerfil']); ?>" 
                             alt="<?php echo htmlspecialchars($usuario['nome']); ?>"
                             class="user-avatar-img">
                    <?php else: ?>
                        👤
                    <?php endif; ?>
                </div>
                <span class="user-name"><?php echo htmlspecialchars($usuario['nome']); ?></span>
            </a>
            <a href="includes/logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>


    <div class="perfil-header-section">
        <div class="perfil-capa" id="fotoCapaExibicao"></div>
        <div class="perfil-info-container">
            <div class="perfil-avatar-wrapper">
                <img id="fotoPerfilExibicao" src="img/default_avatar.png" alt="Foto de Perfil" class="perfil-avatar-img">
            </div>
            <div class="perfil-dados">
                <h1 id="nomeUsuario"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                <div class="perfil-stats">
                    <span id="contadorRoteiros">🗺️ 12 roteiros</span>
                    <?php if ($isFornecedor): ?>
                    <span id="contadorLocais">📍 8 locais</span>
                    <?php endif; ?>
                </div>
                <p class="perfil-bio" id="bio">Carregando biografia...</p>
            </div>
            <div class="perfil-acoes">
                <button class="btn-compartilhar" onclick="compartilharPerfil()">Compartilhar</button>
                <button class="btn-editar" onclick="abrirModal('modalEditarPerfil')">Editar Perfil</button>
            </div>
        </div>
    </div>


    <div class="container-perfil">

        <div class="perfil-nav">
            <button class="<?php echo !$isFornecedor ? 'active' : ''; ?>" onclick="mostrarSecao('roteiros')" style="<?php echo !$isFornecedor ? 'flex: 1; max-width: 100%;' : ''; ?>">
                Roteiros Criados
            </button>
            <?php if ($isFornecedor): ?>
            <button onclick="mostrarSecao('locais')">
                Locais Criados
            </button>
            <?php endif; ?>
        </div>


        <div id="secao-roteiros" class="perfil-lista">
            <div id="listaItens" class="lista-itens">
                <p class="aviso-lista">Carregando...</p>
            </div>
        </div>

        <?php if ($isFornecedor): ?>
        <div id="secao-locais" class="perfil-lista" style="display: none;">
            <div id="listaLocais" class="lista-itens">
                <p class="aviso-lista">Carregando...</p>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <div class="fab-container">
        <button class="fab-principal" onclick="toggleFabMenu()">+</button>
        <div class="fab-menu" id="fabMenu">
            <button class="fab-option" onclick="abrirModal('modalCriarRoteiro')">
                🗺️ Criar Roteiro
            </button>
            <?php if ($isFornecedor): ?>
            <button class="fab-option" onclick="abrirModal('modalCriarPonto')">
                📍 Criar Local
            </button>
            <?php endif; ?>
        </div>
    </div>


    <div id="modalEditarPerfil" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Editar Perfil</h2>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formPerfil">
                    <div class="form-group">
                        <label><?php echo $isFornecedor ? 'Nome da Empresa' : 'Nome Completo'; ?> *</label>
                        <input type="text" name="nome" id="editNome" required>
                    </div>

                    <?php if ($isFornecedor): ?>
                    <div class="form-group">
                        <label>CNPJ</label>
                        <input type="text" name="cnpj" id="editCnpj">
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" name="cpf" id="editCpf">
                    </div>
                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" name="dataNascimento" id="editDataNascimento">
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" id="editTelefone">
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" id="editBio" rows="4" placeholder="Conte um pouco sobre você..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Foto de Perfil</label>
                            <input type="file" name="fotoPerfil" id="editFotoPerfil" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label>Foto de Capa</label>
                            <input type="file" name="fotoCapa" id="editFotoCapa" accept="image/*">
                        </div>
                    </div>

                   <button type="button" class="btn-salvar" onclick="salvarPerfil()">💾 Salvar Alterações</button>
                </form>

                <hr style="margin: 30px 0; border: none; border-top: 2px solid #f0f0f0;">

                <h3 style="color: #0095ff; margin-bottom: 20px;">🔒 Alterar Senha</h3>
                <form id="formSenha">
                    <div class="form-group">
                        <label>Senha Atual *</label>
                        <input type="password" name="senhaAtual" required>
                    </div>

                    <div class="form-group">
                        <label>Nova Senha *</label>
                        <input type="password" name="novaSenha" required>
                    </div>

                    <div class="form-group">
                        <label>Confirmar Nova Senha *</label>
                        <input type="password" name="confirmarSenha" required>
                    </div>

                    <button type="submit" class="btn-salvar">🔒 Alterar Senha</button>
                </form>
            </div>
        </div>
    </div>
    

    <div id="modalCriarRoteiro" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>🗺️ Criar Novo Roteiro</h2>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formRoteiro">
                    <div class="form-group">
                        <label for="nomeRoteiro">Nome do Roteiro *</label>
                        <input type="text" id="nomeRoteiro" name="nome" required placeholder="Ex: Tour pelo Centro Histórico">
                    </div>

                    <div class="form-group">
                        <label for="bioRoteiro">Descrição do Roteiro</label>
                        <textarea id="bioRoteiro" name="bio" rows="3" placeholder="Descreva seu roteiro (opcional)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Locais do Roteiro * (mínimo 2)</label>
                        <input type="text" id="buscaPontos" class="search-pontos" placeholder="🔍 Buscar local..." onkeyup="buscarPontos(this.value)">
                        
                        <div id="resultadosBusca" class="search-results"></div>
                        
                        <div id="pontosSelecionados" class="pontos-selecionados">
                            <p class="aviso-vazio">Nenhum local adicionado ainda. Use a busca acima para adicionar.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-salvar">Criar Roteiro</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php if ($isFornecedor): ?>
    <div id="modalCriarPonto" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>📍 Cadastrar Novo Local</h2>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formPonto">
                    <div class="form-group">
                        <label for="nomePonto">Nome do Local *</label>
                        <input type="text" id="nomePonto" name="nome" required placeholder="Ex: Museu de Arte Moderna">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipoPonto">Tipo *</label>
                            <select id="tipoPonto" name="tipo" required>
                                <option value="">Selecione</option>
                                <option value="Museu">Museu</option>
                                <option value="Parque">Parque</option>
                                <option value="Monumento">Monumento</option>
                                <option value="Igreja">Igreja</option>
                                <option value="Restaurante">Restaurante</option>
                                <option value="Hotel">Hotel</option>
                                <option value="Praia">Praia</option>
                                <option value="Teatro">Teatro</option>
                                <option value="Centro Cultural">Centro Cultural</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="localidadePonto">Cidade/Estado *</label>
                            <input type="text" id="localidadePonto" name="localidade" required placeholder="Ex: São Paulo, SP">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="enderecoPonto">Endereço Completo *</label>
                        <input type="text" id="enderecoPonto" name="endereco" required placeholder="Ex: Av. Paulista, 1578">
                    </div>

                    <div class="form-group">
                        <label for="bioPonto">Descrição</label>
                        <textarea id="bioPonto" name="bio" rows="3" placeholder="Descreva o local (opcional)..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fotoPerfilPonto">Foto de Perfil</label>
                            <input type="file" id="fotoPerfilPonto" name="fotoPerfil" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="fotoCapaPonto">Foto de Capa</label>
                            <input type="file" id="fotoCapaPonto" name="fotoCapa" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn-salvar">Cadastrar Local</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    function mostrarSecao(secao) {
        document.querySelectorAll('.perfil-lista').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.perfil-nav button').forEach(btn => btn.classList.remove('active'));
        
        if (secao === 'roteiros') {
            document.getElementById('secao-roteiros').style.display = 'block';
            event.target.classList.add('active');
            carregarRoteiros();
        } else if (secao === 'locais') {
            const secaoLocais = document.getElementById('secao-locais');
            if (secaoLocais) {
                secaoLocais.style.display = 'block';
                event.target.classList.add('active');
                carregarLocais();
            }
        }
    }

    function compartilharPerfil() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copiado para área de transferência!');
        });
    }
    </script>

<script src="js/perfil.js"></script>
<script src="js/roteiro-universal.js"></script>
    <?php if ($isFornecedor): ?>
    <script src="js/local.js"></script>
    <?php endif; ?>
</body>
</html>