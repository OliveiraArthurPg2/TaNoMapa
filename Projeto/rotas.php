<?php
require_once 'includes/verificar_sessao.php';
$usuario = obterUsuarioLogado();
$logado = estaLogado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotas - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css">
    <link rel="stylesheet" href="css/style-rotas.css">
    <link rel="stylesheet" href="css/style-modal.css?v=2.0">
</head>
<body>
   <header>
    <div class="logo">
        <div class="logo-icon">🌎</div>
        <span>Ta no Mapa</span>
    </div>
    <nav>
        <a href="index.php">Home</a>
        <a href="destinos.php">Destinos</a>
        <a href="rotas.php">Rotas</a>

        
        <?php if ($logado): ?>
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
        <?php else: ?>
            <a href="login.php" class="btn-login-nav">Entrar</a>
            <a href="cadastro.php" class="btn-cadastro">Cadastre-se</a>
        <?php endif; ?>
    </nav>
</header>

    <section class="hero-rotas">
        <h1>Roteiros de Viagem</h1>
        <p>Descubra roteiros incríveis criados pela nossa comunidade de viajantes</p>
    </section>

    <section class="filtros">
        <div class="container-filtros">
            <select id="filtroOrdem" onchange="filtrarRoteiros()">
                <option value="recente">Mais Recentes</option>
                <option value="avaliacao">Melhor Avaliados</option>
                <option value="pontos">Mais Destinos</option>
            </select>
        </div>
    </section>

    <section class="lista-rotas">
        <div class="container-rotas">
            <div id="gridRoteiros" class="grid-rotas">
                <p class="loading">Carregando...</p>
            </div>
            <div id="paginacao" class="paginacao"></div>
        </div>
    </section>

    <section class="cta-turista">
        <h2>É um viajante aventureiro?</h2>
        <p>Crie seus próprios roteiros e compartilhe com outros viajantes</p>
        <?php if ($logado && isTurista()): ?>
            <button class="cta-btn" onclick="abrirModal('modalCriarRoteiro')">Criar Roteiro</button>
        <?php else: ?>
            <a href="cadastro.php" class="cta-btn">Cadastre-se e Crie Roteiros</a>
        <?php endif; ?>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Ta no Mapa</h4>
                <ul>
                    <li><a href="#">Sobre nós</a></li>
                    <li><a href="#">Nossa equipe</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            © 2025 Ta no Mapa. Todos os direitos reservados.
        </div>
    </footer>
    
    <?php if ($logado): ?>
    <div class="fab-container">
        <button class="fab-button" onclick="toggleFabMenu()">+</button>
        <div class="fab-menu" id="fabMenu">
            <?php if (isTurista()): ?>
            <button class="fab-option" onclick="abrirModal('modalCriarRoteiro')">
                🗺️ Criar Roteiro
            </button>
            <?php endif; ?>
            
            <?php if (isFornecedor()): ?>
            <button class="fab-option" onclick="abrirModal('modalCriarRoteiro')">
                🗺️ Criar Roteiro
            </button>
            <button class="fab-option" onclick="abrirModal('modalCriarPonto')">
                📍 Criar Local
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div id="modalCriarRoteiro" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>🗺️ Criar Novo Roteiro</h2>
                <span class="modal-close" onclick="fecharModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formRoteiroRotas">
                    <div class="form-group">
                        <label for="nomeRoteiroRotas">Nome do Roteiro *</label>
                        <input type="text" id="nomeRoteiroRotas" name="nome" required placeholder="Ex: Tour pelo Centro Histórico">
                    </div>

                    <div class="form-group">
                        <label for="bioRoteiroRotas">Descrição do Roteiro</label>
                        <textarea id="bioRoteiroRotas" name="bio" rows="3" placeholder="Descreva seu roteiro (opcional)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Locais do Roteiro * (mínimo 2)</label>
                        <input type="text" id="buscaPontosRotas" class="search-pontos" placeholder="🔍 Buscar local...">
                        
                        <div id="resultadosBuscaRotas" class="search-results"></div>
                        
                        <div id="pontosSelecionadosRotas" class="pontos-selecionados">
                            <p class="aviso-vazio">Nenhum local adicionado ainda. Use a busca acima para adicionar.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-salvar">Criar Roteiro</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (isFornecedor()): ?>
    <div id="modalCriarPonto" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>📍 Cadastrar Novo Local</h2>
                <span class="modal-close" onclick="fecharModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formPontoRotas">
                    <div class="form-group">
                        <label for="nomePontoRotas">Nome do Local *</label>
                        <input type="text" id="nomePontoRotas" name="nome" required placeholder="Ex: Museu de Arte Moderna">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipoPontoRotas">Tipo *</label>
                            <select id="tipoPontoRotas" name="tipo" required>
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
                            <label for="localidadePontoRotas">Cidade/Estado *</label>
                            <input type="text" id="localidadePontoRotas" name="localidade" required placeholder="Ex: São Paulo, SP">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="enderecoPontoRotas">Endereço Completo *</label>
                        <input type="text" id="enderecoPontoRotas" name="endereco" required placeholder="Ex: Av. Paulista, 1578">
                    </div>

                    <div class="form-group">
                        <label for="bioPontoRotas">Descrição</label>
                        <textarea id="bioPontoRotas" name="bio" rows="3" placeholder="Descreva o local (opcional)..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fotoPerfilPontoRotas">Foto de Perfil</label>
                            <input type="file" id="fotoPerfilPontoRotas" name="fotoPerfil" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="fotoCapaPontoRotas">Foto de Capa</label>
                            <input type="file" id="fotoCapaPontoRotas" name="fotoCapa" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn-salvar">Cadastrar Local</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <script>
    
    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'block';
            
            
            if (id === 'modalCriarRoteiro') {
                setTimeout(() => {
                    if (typeof window.inicializarBuscaRotas === 'function') {
                        window.inicializarBuscaRotas();
                    }
                }, 100);
            }
        }
    }

    window.fecharModal = function() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('show');
            modal.style.display = 'none';
        });
    }

    window.toggleFabMenu = function() {
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

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            fecharModal();
        }
    });
    </script>

    <script src="js/rotas.js"></script>
    <script src="js/roteiro-universal.js"></script>
    <?php if (isFornecedor()): ?>
    <script src="js/local.js"></script>
    <?php endif; ?>
</body>
</html>