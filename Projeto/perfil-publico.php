<?php
require_once 'includes/verificar_sessao.php';

$usuario = obterUsuarioLogado();
$logado = estaLogado();

$usuarioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($usuarioId <= 0) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css?v=2.0">
    <link rel="stylesheet" href="css/style-perfil.css?v=2.0">
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

    <section class="perfil-header-section">
        <div class="perfil-capa" id="fotoCapaExibicao"></div>
        
        <div class="perfil-info-container">
            <div class="perfil-avatar-wrapper">
                <img src="img/default_avatar.png" alt="Foto do Usuário" class="perfil-avatar-img" id="fotoPerfilExibicao">
            </div>
            
            <div class="perfil-dados">
                <h1 id="nomeUsuario">-</h1>
                <div class="perfil-stats">
                    <span id="contadorRoteiros">🗺️ 0 roteiros</span>
                    <span id="contadorLocais">📍 0 locais</span>
                </div>
                <p class="perfil-bio" id="bio">Sem descrição</p>
            </div>
            
            <div class="perfil-acoes">
                <button class="btn-compartilhar" onclick="compartilharPerfil()">
                    🔗 Compartilhar
                </button>
            </div>
        </div>
    </section>


    <div class="container-perfil">

        <div class="perfil-lista">
            <div class="lista-itens" id="listaItens">
                <p class="aviso-lista">Carregando...</p>
            </div>
        </div>
    </div>

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

    <script src="js/perfil-publico.js"></script>
</body>
</html>