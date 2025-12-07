<?php
session_start();
require_once 'config/conexao.php';
require_once 'Classes/Roteiro.php';
require_once 'Classes/Usuario.php';

function estaLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function isTurista() {
    return estaLogado() && isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'Turista';
}

function isFornecedor() {
    return estaLogado() && isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'Fornecedor';
}

function obterUsuarioLogado() {
    global $conn;
    if (!estaLogado()) return null;
    if (isset($_SESSION['usuario_dados'])) return $_SESSION['usuario_dados'];
    
    $userId = $_SESSION['usuario_id'];
    $sql = "SELECT * FROM usuarios WHERE Id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return null;
    
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($usuario) {
        $dadosUsuario = [
            'id' => (int)$usuario['Id'],
            'nome' => $usuario['Nome'],
            'email' => $usuario['Email'],
            'tipo' => $usuario['Tipo'],
            'fotoPerfil' => $usuario['Foto_Perfil'] ?? null,
            'fotoCapa' => $usuario['Foto_Capa'] ?? null,
            'bio' => $usuario['Bio'] ?? null,
            'dataCadastro' => null
        ];
        $_SESSION['usuario_dados'] = $dadosUsuario;
        return $dadosUsuario;
    }
    return null;
}

$logado = estaLogado();
$usuario = obterUsuarioLogado();
$roteiroId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($roteiroId === 0) {
    header('Location: index.php');
    exit;
}

$roteiro = new Roteiro($conn);
if (!$roteiro->buscarPorId($roteiroId)) {
    header('Location: index.php');
    exit;
}

$autor = new Usuario($conn);
$autor->buscarPorId($roteiro->getAutor());
$pontos = $roteiro->buscarPontos();

$sqlAvaliacao = "SELECT AVG(Nota) as Media, COUNT(*) as Total FROM avaliacao WHERE Roteiro_id = ?";
$stmtAval = mysqli_prepare($conn, $sqlAvaliacao);
mysqli_stmt_bind_param($stmtAval, "i", $roteiroId);
mysqli_stmt_execute($stmtAval);
$resultAval = mysqli_stmt_get_result($stmtAval);
$avaliacaoData = mysqli_fetch_assoc($resultAval);

$isAutor = false;
if ($logado && $usuario) {
    $autorId = (int)$roteiro->getAutor();
    $sessionId = (int)$usuario['id'];
    $isAutor = ($sessionId === $autorId);
}

$fotoCapa = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
if (count($pontos) > 0 && !empty($pontos[0]['Foto_Capa'])) {
    $fotoCapa = 'url(' . htmlspecialchars($pontos[0]['Foto_Capa']) . ')';
} elseif (count($pontos) > 0 && !empty($pontos[0]['Foto_Perfil'])) {
    $fotoCapa = 'url(' . htmlspecialchars($pontos[0]['Foto_Perfil']) . ')';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($roteiro->getNome()); ?> - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-visualizar-ponto.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-visualizar-roteiro.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style-modal.css?v=<?php echo time(); ?>">
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

    <div class="ponto-hero" style="background: <?php echo $fotoCapa; ?>; background-size: cover; background-position: center;">
        <div class="ponto-hero-overlay"></div>
        <div class="ponto-hero-content">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🗺️</div>
            <h1><?php echo htmlspecialchars($roteiro->getNome()); ?></h1>
            <div class="ponto-location">
                <span>📍</span>
                <span><?php echo count($pontos); ?> pontos turísticos</span>
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
                    <h2>Sobre este Roteiro</h2>
                    <?php if ($isAutor): ?>
                        <button class="btn-editar-secao" onclick="editarSobre()">✏️ Editar</button>
                    <?php endif; ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars($roteiro->getBio() ?: 'Sem descrição disponível.')); ?></p>
            </section>

            <section class="galeria-section">
                <h2>Pontos Turísticos</h2>
                <?php if (count($pontos) > 0): ?>
                    <div class="galeria-container">
                        <div class="galeria-principal">
                            <img id="fotoPrincipal" src="<?php echo htmlspecialchars($pontos[0]['Foto_Capa'] ?? $pontos[0]['Foto_Perfil'] ?? 'img/default_cover.jpg'); ?>" alt="<?php echo htmlspecialchars($pontos[0]['Nome']); ?>" onclick="abrirLightbox('<?php echo htmlspecialchars($pontos[0]['Foto_Capa'] ?? $pontos[0]['Foto_Perfil'] ?? 'img/default_cover.jpg'); ?>')">
                            <?php if (count($pontos) > 1): ?>
                                <button class="galeria-nav galeria-nav-prev" onclick="navegarGaleria(-1)">‹</button>
                                <button class="galeria-nav galeria-nav-next" onclick="navegarGaleria(1)">›</button>
                            <?php endif; ?>
                            <div class="galeria-contador"><span id="fotoAtual">1</span> / <?php echo count($pontos); ?></div>
                            <div class="ponto-info-overlay">
                                <h3 id="pontoNome"><?php echo htmlspecialchars($pontos[0]['Nome']); ?></h3>
                                <p id="pontoLocalidade">📍 <?php echo htmlspecialchars($pontos[0]['Localidade']); ?></p>
                            </div>
                        </div>
                        <div class="galeria-thumbnails" id="galeriaThumbnails">
                            <?php foreach ($pontos as $index => $ponto): ?>
                                <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" data-ponto-id="<?php echo $ponto['Id']; ?>" data-index="<?php echo $index; ?>" onclick="selecionarPonto(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($ponto['Foto_Capa'] ?? $ponto['Foto_Perfil'] ?? 'img/default_cover.jpg'); ?>" alt="<?php echo htmlspecialchars($ponto['Nome']); ?>">
                                    <div class="ponto-numero"><?php echo $index + 1; ?></div>
                                    <div class="ponto-nome-mini"><?php echo htmlspecialchars($ponto['Nome']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="pontos-detalhados">
                            <h3>Roteiro Detalhado</h3>
                            <?php foreach ($pontos as $index => $ponto): ?>
                                <div class="ponto-detalhado-card" onclick="window.location.href='visualizar-ponto.php?id=<?php echo $ponto['Id']; ?>'">
                                    <div class="ponto-numero-badge"><?php echo $index + 1; ?></div>
                                    <div class="ponto-detalhado-foto">
                                        <img src="<?php echo htmlspecialchars($ponto['Foto_Perfil'] ?? $ponto['Foto_Capa'] ?? 'img/default_cover.jpg'); ?>" alt="<?php echo htmlspecialchars($ponto['Nome']); ?>">
                                    </div>
                                    <div class="ponto-detalhado-info">
                                        <h4><?php echo htmlspecialchars($ponto['Nome']); ?></h4>
                                        <p class="ponto-tipo">🏷️ <?php echo htmlspecialchars($ponto['Tipo']); ?></p>
                                        <p class="ponto-local">📍 <?php echo htmlspecialchars($ponto['Localidade']); ?></p>
                                        <?php if (!empty($ponto['Bio'])): ?>
                                            <p class="ponto-desc"><?php echo htmlspecialchars(mb_substr($ponto['Bio'], 0, 150)); ?>...</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ponto-arrow">→</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="galeria-vazia"><p>Nenhum ponto turístico.</p></div>
                <?php endif; ?>
            </section>

            <section class="avaliacoes-section">
                <div class="avaliacoes-header">
                    <h2>Avaliações</h2>
                    <?php if ($logado && isTurista()): ?>
                        <button class="btn-avaliar" onclick="abrirModalAvaliacao()">✏️ Avaliar</button>
                    <?php endif; ?>
                </div>
                <div id="listaAvaliacoes" class="lista-avaliacoes"><p class="loading">Carregando...</p></div>
            </section>
        </div>

        <aside class="content-sidebar">
            <div class="fornecedor-card">
                <h3>Criado por</h3>
                <div class="fornecedor-info" onclick="window.location.href='perfil-publico.php?id=<?php echo $autor->getId(); ?>'">
                    <?php if ($autor->getFotoPerfil()): ?>
                        <img src="<?php echo htmlspecialchars($autor->getFotoPerfil()); ?>" alt="<?php echo htmlspecialchars($autor->getNome()); ?>" class="fornecedor-avatar">
                    <?php else: ?>
                        <div class="fornecedor-avatar">👤</div>
                    <?php endif; ?>
                    <div class="fornecedor-dados">
                        <p class="fornecedor-nome"><?php echo htmlspecialchars($autor->getNome()); ?></p>
                    </div>
                </div>
            </div>
            <div class="info-card">
                <h3>Informações</h3>
                <div class="info-item">
                    <span class="info-label">Pontos:</span>
                    <span class="info-value"><?php echo count($pontos); ?> locais</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Criado:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($roteiro->getDataCriacao())); ?></span>
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

    <?php if ($isAutor): ?>
        <div id="modalEditarSobre" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Editar Roteiro</h2>
                    <button class="modal-close" onclick="fecharModal('modalEditarSobre')">×</button>
                </div>
                <div class="modal-body">
                    <form id="formEditarSobre">
                        <div class="form-group">
                            <label for="nomeEdit">Nome *</label>
                            <input type="text" id="nomeEdit" name="nome" value="<?php echo htmlspecialchars($roteiro->getNome()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bioEdit">Descrição</label>
                            <textarea id="bioEdit" name="bio" rows="6"><?php echo htmlspecialchars($roteiro->getBio()); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Pontos do Roteiro</label>
                            <div id="pontosRoteiroEdit" class="pontos-roteiro-edit">
                                <?php if (count($pontos) > 0): ?>
                                    <?php foreach ($pontos as $ponto): ?>
                                        <div class="ponto-edit-card" data-ponto-id="<?php echo $ponto['Id']; ?>">
                                            <div class="ponto-edit-foto">
                                                <img src="<?php echo htmlspecialchars($ponto['Foto_Perfil'] ?? $ponto['Foto_Capa'] ?? 'img/default_cover.jpg'); ?>" alt="<?php echo htmlspecialchars($ponto['Nome']); ?>">
                                            </div>
                                            <div class="ponto-edit-info">
                                                <h4><?php echo htmlspecialchars($ponto['Nome']); ?></h4>
                                                <p>📍 <?php echo htmlspecialchars($ponto['Localidade']); ?></p>
                                            </div>
                                            <button type="button" class="btn-remover-ponto-edit" data-ponto-id="<?php echo $ponto['Id']; ?>">🗑️</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="aviso-vazio">Nenhum ponto.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Adicionar Pontos</label>
                            <input type="text" id="buscarPontoEdit" placeholder="Buscar..." onkeyup="buscarPontosEdit()">
                            <div id="resultadosBuscaEdit" class="resultados-busca" style="display: none;"></div>
                        </div>
                        <button type="submit" class="btn-salvar">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="lightbox" class="lightbox" onclick="fecharLightbox()">
        <button class="lightbox-close" onclick="fecharLightbox()">×</button>
        <div class="lightbox-content"><img id="lightboxImg" src="" alt="Foto"></div>
    </div>

    <?php if ($logado && isTurista()): ?>
        <div id="modalAvaliacao" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Avaliar Roteiro</h2>
                    <button class="modal-close" onclick="fecharModal('modalAvaliacao')">×</button>
                </div>
                <div class="modal-body">
                    <form id="formAvaliacao">
                        <input type="hidden" name="roteiroId" value="<?php echo $roteiroId; ?>">
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
                            <textarea id="descricao" name="descricao" rows="4" placeholder="Compartilhe..."></textarea>
                        </div>
                        <button type="submit" class="btn-salvar">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const roteiroId = <?php echo $roteiroId; ?>;
        const isAutor = <?php echo $isAutor ? 'true' : 'false'; ?>;
        const pontosData = <?php echo json_encode($pontos); ?>;
    </script>
    <script src="js/visualizar-roteiro.js"></script>
</body>
</html>