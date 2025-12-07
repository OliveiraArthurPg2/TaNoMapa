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
    <title>Ta no Mapa - Descubra o Mundo</title>
<link rel="stylesheet" href="css/style-principal.css?v=2.0">
<link rel="stylesheet" href="css/style-modal.css?v=2.0">
</head>
<body data-page="index">
   <header>
    <div class="logo">
        <div class="logo-icon">🌍</div>
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

    <section class="hero">
    <h1>Descubra o que<br> "Ta no Mapa"</h1>
    <p>Encontre as melhores opções de roteiros e destinos incríveis</p>
    <div class="search-box">
        <input type="text" 
               class="search-input" 
               id="inputBuscaHero"
               placeholder="🔍 Para onde você quer ir?">
        <button class="search-btn">Buscar</button>
    </div>
</section>

    <section class="features">
        <div class="feature">
            <div class="feature-icon">✈️</div>
            <h3>Destinos Exclusivos</h3>
            <p>Mais de 300 destinos ao redor do mundo</p>
        </div>
        <div class="feature">
            <div class="feature-icon">⭐</div>
            <h3>Qualidade Premium</h3>
            <p>Experiências de alta qualidade</p>
        </div>
        <div class="feature">
            <div class="feature-icon">💰</div>
            <h3>Melhores rotas</h3>
            <p>Garantia dos melhores roteiros de viagem</p>
        </div>
        <div class="feature">
            <div class="feature-icon">⏱️</div>
            <h3>Agilidade e Facilidade</h3>
            <p>Buscas em menos de 5 minutos</p>
        </div>
    </section>

<section class="destinations">
    <h2>Destinos Populares</h2>
    <p class="destinations-subtitle">Os 5 pontos turísticos mais bem avaliados</p>
    <div class="carousel-container">
        <button class="carousel-btn carousel-btn-prev" onclick="moverCarousel(-1)">
            &#10094;
        </button>
        <div class="carousel-track" id="carouselTop5">
            <p class="loading-carousel">Carregando...</p>
        </div>
        <button class="carousel-btn carousel-btn-next" onclick="moverCarousel(1)">
            &#10095;
        </button>
    </div>
</section>

    <section class="cta">
        <h2>Pronto para sua próxima aventura?</h2>
        <p>Comece a planejar sua viagem dos sonhos agora mesmo</p>
        <?php if ($logado): ?>
            <a href="perfil.php" class="cta-btn">Ir para Meu Perfil</a>
        <?php else: ?>
            <a href="cadastro.php" class="cta-btn">Começar</a>
        <?php endif; ?>
    </section>

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

    <!-- Modal Criar Roteiro -->
    <div id="modalCriarRoteiro" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>🗺️ Criar Novo Roteiro</h2>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formRoteiroIndex">
                    <div class="form-group">
                        <label for="nomeRoteiroIndex">Nome do Roteiro *</label>
                        <input type="text" id="nomeRoteiroIndex" name="nome" required placeholder="Ex: Tour pelo Centro Histórico">
                    </div>

                    <div class="form-group">
                        <label for="bioRoteiroIndex">Descrição do Roteiro</label>
                        <textarea id="bioRoteiroIndex" name="bio" rows="3" placeholder="Descreva seu roteiro (opcional)..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Locais do Roteiro * (mínimo 2)</label>
                        <input type="text" id="buscaPontosIndex" class="search-pontos" placeholder="🔍 Buscar local..." onkeyup="buscarPontosIndex(this.value)">
                        
                        <div id="resultadosBuscaIndex" class="search-results"></div>
                        
                        <div id="pontosSelecionadosIndex" class="pontos-selecionados">
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
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formPontoIndex">
                    <div class="form-group">
                        <label for="nomePontoIndex">Nome do Local *</label>
                        <input type="text" id="nomePontoIndex" name="nome" required placeholder="Ex: Museu de Arte Moderna">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipoPontoIndex">Tipo *</label>
                            <select id="tipoPontoIndex" name="tipo" required>
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
                            <label for="localidadePontoIndex">Cidade/Estado *</label>
                            <input type="text" id="localidadePontoIndex" name="localidade" required placeholder="Ex: São Paulo, SP">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="enderecoPontoIndex">Endereço Completo *</label>
                        <input type="text" id="enderecoPontoIndex" name="endereco" required placeholder="Ex: Av. Paulista, 1578">
                    </div>

                    <div class="form-group">
                        <label for="bioPontoIndex">Descrição</label>
                        <textarea id="bioPontoIndex" name="bio" rows="3" placeholder="Descreva o local (opcional)..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fotoPerfilPontoIndex">Foto de Perfil</label>
                            <input type="file" id="fotoPerfilPontoIndex" name="fotoPerfil" accept="image/*">
                            <small class="form-hint">Imagem principal do local</small>
                        </div>

                        <div class="form-group">
                            <label for="fotoCapaPontoIndex">Foto de Capa</label>
                            <input type="file" id="fotoCapaPontoIndex" name="fotoCapa" accept="image/*">
                            <small class="form-hint">Imagem de fundo</small>
                        </div>
                    </div>

                    <button type="submit" class="btn-salvar">Cadastrar Local</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Ta no Mapa</h4>
                <ul>
                    <li><a href="#">Sobre nós</a></li>
                    <li><a href="#">Nossa equipe</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Suporte</h4>
                <ul>
                    <li><a href="#">Central de ajuda</a></li>
                    <li><a href="#">Contato</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Jurídico</h4>
                <ul>
                    <li><a href="#">Termos de uso</a></li>
                    <li><a href="#">Política de privacidade</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            © 2025 Ta no Mapa. Todos os direitos reservados.
        </div>
    </footer>

    <script>
    window.toggleFabMenu = function() {
        const menu = document.getElementById('fabMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            
            if (id === 'modalCriarRoteiro') {
                setTimeout(() => {
                    if (typeof window.inicializarBuscaIndex === 'function') {
                        window.inicializarBuscaIndex();
                    }
                }, 100);
            }
        }
    }

    window.fecharModal = function() {
        document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
    }
    </script>

<script src="js/busca-hero.js"></script>
<script src="js/index.js"></script>
<script src="js/roteiro-universal.js"></script>
<?php if (isFornecedor()): ?>
<script src="js/local.js"></script>
<?php endif; ?>


</body>
</html>