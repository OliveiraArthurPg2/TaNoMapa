<?php
require_once 'includes/verificar_sessao.php';
require_once 'config/conexao.php';
require_once 'Classes/PontoTuristico.php';
require_once 'Classes/Usuario.php';

$logado = estaLogado();
$usuario = obterUsuarioLogado();

$pontoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pontoId === 0) {
    header('Location: index.php');
    exit;
}

$ponto = new PontoTuristico($conn);
if (!$ponto->buscarPorId($pontoId)) {
    header('Location: index.php');
    exit;
}

$fornecedor = new Usuario($conn);
$fornecedor->buscarPorId($ponto->getFornecedor());

$sqlAvaliacao = "SELECT AVG(Nota) as Media, COUNT(*) as Total FROM avaliacao WHERE PontosTuristicos_id = ?";
$stmtAval = mysqli_prepare($conn, $sqlAvaliacao);
mysqli_stmt_bind_param($stmtAval, "i", $pontoId);
mysqli_stmt_execute($stmtAval);
$resultAval = mysqli_stmt_get_result($stmtAval);
$avaliacaoData = mysqli_fetch_assoc($resultAval);

$sqlFotos = "SELECT * FROM fotos_local WHERE PontoTuristico_id = ? ORDER BY DataUpload DESC";
$stmtFotos = mysqli_prepare($conn, $sqlFotos);
mysqli_stmt_bind_param($stmtFotos, "i", $pontoId);
mysqli_stmt_execute($stmtFotos);
$resultFotos = mysqli_stmt_get_result($stmtFotos);
$fotos = [];
while ($foto = mysqli_fetch_assoc($resultFotos)) {
    $fotos[] = $foto;
}

$isAutor = false;
if ($logado && $usuario && isFornecedor()) {
    $fornecedorPonto = (int)$ponto->getFornecedor();
    $usuarioId = (int)$usuario['id'];
    $isAutor = ($fornecedorPonto === $usuarioId);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ponto->getNome()); ?> - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-visualizar-ponto.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-modal.css?v=<?php echo time(); ?>">
</head>
<body>
    
    <!-- Espaçamento para não cobrir o conteúdo -->
    <div style="height: 350px;"></div>
    
    <header>
        <div class="logo">
            <div class="logo-icon">🌍</div>
            <span>Ta no Mapa</span>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="destinos.php">Destinos</a>
            <a href="rotas.php">Rotas</a>
            <?php if ($logado && $usuario): ?>
                <a href="perfil.php" class="perfil-link">
                    <div class="user-avatar">
                        <?php if (!empty($usuario['fotoPerfil'])): ?>
                            <img src="<?php echo htmlspecialchars($usuario['fotoPerfil']); ?>" alt="<?php echo htmlspecialchars($usuario['nome']); ?>" class="user-avatar-img">
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

    <!-- O resto do código continua normalmente... -->
</body>
</html>

    <div class="ponto-hero" style="background-image: url('<?php echo $ponto->getFotoCapa() ?? 'img/default_cover.jpg'; ?>');">
        <div class="ponto-hero-overlay"></div>
        <div class="ponto-hero-content">
            <h1><?php echo htmlspecialchars($ponto->getNome()); ?></h1>
            <div class="ponto-location">
                <span>📍</span>
                <span><?php echo htmlspecialchars($ponto->getLocalidade()); ?></span>
            </div>
            <?php if ($avaliacaoData['Total'] > 0): ?>
                <div class="ponto-rating">
                    <span class="rating-stars">⭐ <?php echo number_format($avaliacaoData['Media'], 1); ?></span>
                    <span class="rating-count">(<?php echo $avaliacaoData['Total']; ?> avaliações)</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container-visualizar">
        <div class="content-main">
            <section class="ponto-info">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2>Sobre</h2>
                    <?php if ($isAutor): ?>
                        <button class="btn-editar-secao" onclick="editarSobre()">✏️ Editar</button>
                    <?php endif; ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars($ponto->getBio() ?: 'Sem descrição disponível.')); ?></p>
                <?php if ($ponto->getEndereco()): ?>
                    <div class="ponto-endereco">
                        <h3>📍 Endereço</h3>
                        <p><?php echo htmlspecialchars($ponto->getEndereco()); ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="galeria-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Galeria de Fotos</h2>
                    <?php if ($isAutor): ?>
                        <button class="btn-editar-secao" onclick="abrirModalAddFotos()">➕ Adicionar</button>
                    <?php endif; ?>
                </div>
                <?php if (count($fotos) > 0): ?>
                    <div class="galeria-container">
                        <div class="galeria-principal">
                            <img id="fotoPrincipal" src="<?php echo htmlspecialchars($fotos[0]['Caminho_Foto']); ?>" alt="Foto" onclick="abrirLightbox('<?php echo htmlspecialchars($fotos[0]['Caminho_Foto']); ?>')">
                            <?php if (count($fotos) > 1): ?>
                                <button class="galeria-nav galeria-nav-prev" onclick="navegarGaleria(-1)">‹</button>
                                <button class="galeria-nav galeria-nav-next" onclick="navegarGaleria(1)">›</button>
                            <?php endif; ?>
                            <div class="galeria-contador"><span id="fotoAtual">1</span> / <?php echo count($fotos); ?></div>
                        </div>
                        <div class="galeria-thumbnails" id="galeriaThumbnails">
                            <?php foreach ($fotos as $index => $foto): ?>
                                <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" data-foto-id="<?php echo $foto['Id']; ?>" data-index="<?php echo $index; ?>" onclick="selecionarFoto(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($foto['Caminho_Foto']); ?>" alt="Miniatura">
                                    <?php if ($isAutor): ?>
                                        <button class="btn-remover-foto-galeria" onclick="event.stopPropagation(); removerFotoGaleria(<?php echo $foto['Id']; ?>)">×</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="galeria-vazia">
                        <p>Nenhuma foto adicionada.</p>
                        <?php if ($isAutor): ?>
                            <button class="btn-add-fotos" onclick="abrirModalAddFotos()">Adicionar Fotos</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="avaliacoes-section">
                <div class="avaliacoes-header">
                    <h2>Avaliações</h2>
                    <?php if ($logado && isTurista()): ?>
                        <button class="btn-avaliar" onclick="abrirModalAvaliacao()">✏️ Avaliar</button>
                    <?php endif; ?>
                </div>
                <div id="listaAvaliacoes" class="lista-avaliacoes">
                    <p class="loading">Carregando...</p>
                </div>
            </section>
        </div>

        <aside class="content-sidebar">
            <div class="fornecedor-card">
                <h3>Fornecedor</h3>
                <div class="fornecedor-info" onclick="window.location.href='perfil-publico.php?id=<?php echo $fornecedor->getId(); ?>'">
                    <?php if ($fornecedor->getFotoPerfil()): ?>
                        <img src="<?php echo htmlspecialchars($fornecedor->getFotoPerfil()); ?>" alt="<?php echo htmlspecialchars($fornecedor->getNome()); ?>" class="fornecedor-avatar">
                    <?php else: ?>
                        <div class="fornecedor-avatar">👤</div>
                    <?php endif; ?>
                    <div class="fornecedor-dados">
                        <p class="fornecedor-nome"><?php echo htmlspecialchars($fornecedor->getNome()); ?></p>
                    </div>
                </div>
            </div>
            <div class="info-card">
                <h3>Informações</h3>
                <div class="info-item">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($ponto->getTipo()); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Localidade:</span>
                    <span class="info-value"><?php echo htmlspecialchars($ponto->getLocalidade()); ?></span>
                </div>
                <?php if ($avaliacaoData['Total'] > 0): ?>
                    <div class="info-item">
                        <span class="info-label">Avaliação:</span>
                        <span class="info-value">⭐ <?php echo number_format($avaliacaoData['Media'], 1); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <?php if ($logado && isTurista()): ?>
        <div id="modalAvaliacao" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Avaliar Local</h2>
                    <button class="modal-close" onclick="fecharModal('modalAvaliacao')">×</button>
                </div>
                <div class="modal-body">
                    <form id="formAvaliacao">
                        <input type="hidden" name="pontoId" value="<?php echo $pontoId; ?>">
                        <div class="form-group">
                            <label>Nota *</label>
                            <div class="rating-input">
                                <input type="radio" name="nota" value="5" id="nota5" required>
                                <label for="nota5">⭐</label>
                                <input type="radio" name="nota" value="4" id="nota4">
                                <label for="nota4">⭐</label>
                                <input type="radio" name="nota" value="3" id="nota3">
                                <label for="nota3">⭐</label>
                                <input type="radio" name="nota" value="2" id="nota2">
                                <label for="nota2">⭐</label>
                                <input type="radio" name="nota" value="1" id="nota1">
                                <label for="nota1">⭐</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Comentário</label>
                            <textarea id="descricao" name="descricao" rows="4" placeholder="Compartilhe sua experiência..."></textarea>
                        </div>
                        <button type="submit" class="btn-salvar">Enviar Avaliação</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isAutor): ?>
        <div id="modalEditarSobre" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Editar Ponto</h2>
                    <button class="modal-close" onclick="fecharModal('modalEditarSobre')">×</button>
                </div>
                <div class="modal-body">
                    <form id="formEditarSobre">
                        <div class="form-group">
                            <label for="nomeEdit">Nome *</label>
                            <input type="text" id="nomeEdit" name="nome" value="<?php echo htmlspecialchars($ponto->getNome()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tipoEdit">Tipo *</label>
                            <select id="tipoEdit" name="tipo" required>
                                <option value="Praia" <?php echo $ponto->getTipo() == 'Praia' ? 'selected' : ''; ?>>Praia</option>
                                <option value="Montanha" <?php echo $ponto->getTipo() == 'Montanha' ? 'selected' : ''; ?>>Montanha</option>
                                <option value="Museu" <?php echo $ponto->getTipo() == 'Museu' ? 'selected' : ''; ?>>Museu</option>
                                <option value="Parque" <?php echo $ponto->getTipo() == 'Parque' ? 'selected' : ''; ?>>Parque</option>
                                <option value="Igreja" <?php echo $ponto->getTipo() == 'Igreja' ? 'selected' : ''; ?>>Igreja</option>
                                <option value="Monumento" <?php echo $ponto->getTipo() == 'Monumento' ? 'selected' : ''; ?>>Monumento</option>
                                <option value="Restaurante" <?php echo $ponto->getTipo() == 'Restaurante' ? 'selected' : ''; ?>>Restaurante</option>
                                <option value="Outro" <?php echo $ponto->getTipo() == 'Outro' ? 'selected' : ''; ?>>Outro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="localidadeEdit">Localidade *</label>
                            <input type="text" id="localidadeEdit" name="localidade" value="<?php echo htmlspecialchars($ponto->getLocalidade()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="enderecoEdit">Endereço *</label>
                            <input type="text" id="enderecoEdit" name="endereco" value="<?php echo htmlspecialchars($ponto->getEndereco()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bioEdit">Descrição</label>
                            <textarea id="bioEdit" name="bio" rows="6"><?php echo htmlspecialchars($ponto->getBio()); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fotoPerfilEdit">Foto Perfil</label>
                            <input type="file" id="fotoPerfilEdit" name="fotoPerfil" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="fotoCapaEdit">Foto Capa</label>
                            <input type="file" id="fotoCapaEdit" name="fotoCapa" accept="image/*">
                        </div>
                        <button type="submit" class="btn-salvar">Salvar</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="modalAddFotos" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Adicionar Fotos</h2>
                    <button class="modal-close" onclick="fecharModal('modalAddFotos')">×</button>
                </div>
                <div class="modal-body">
                    <form id="formAddFotos">
                        <div class="form-group">
                            <label for="fotosGaleria">Fotos</label>
                            <input type="file" id="fotosGaleria" name="fotosGaleria[]" accept="image/*" multiple required>
                        </div>
                        <div id="previewFotos" class="upload-preview"></div>
                        <button type="submit" class="btn-salvar">Adicionar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="lightbox" class="lightbox" onclick="fecharLightbox()">
        <button class="lightbox-close" onclick="fecharLightbox()">×</button>
        <div class="lightbox-content">
            <img id="lightboxImg" src="" alt="Foto">
        </div>
    </div>

    <script>
        const pontoId = <?php echo $pontoId; ?>;
        const isAutor = <?php echo $isAutor ? 'true' : 'false'; ?>;
    </script>
    <script src="js/visualizar-ponto.js"></script>
</body>
</html>