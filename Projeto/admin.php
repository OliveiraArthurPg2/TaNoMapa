<?php
require_once 'includes/verificar_sessao.php';
require_once 'config/conexao.php';


if (!estaLogado()) {
    header('Location: login.php');
    exit;
}

$usuario = obterUsuarioLogado();


if ($usuario['email'] !== 'tanomapa@gmail.com') {
    header('Location: index.php');
    exit;
}


require_once 'config/conexao.php';


$resultUsuarios = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
$totalUsuarios = mysqli_fetch_assoc($resultUsuarios)['total'];


$resultRoteiros = mysqli_query($conn, "SELECT COUNT(*) as total FROM roteiro");
$totalRoteiros = mysqli_fetch_assoc($resultRoteiros)['total'];


$resultPontos = mysqli_query($conn, "SELECT COUNT(*) as total FROM pontosturisticos");
$totalPontos = mysqli_fetch_assoc($resultPontos)['total'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-principal.css?v=<?php echo time(); ?>">
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
            <a href="perfil.php">Meu Perfil</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="includes/logout.php" class="btn-logout">Sair</a>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Painel Administrativo</h1>
            <p>Gerencie usuários, roteiros e pontos turísticos</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card stat-usuarios">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Usuários</div>
                    <div class="stat-value" id="totalUsuarios"><?php echo $totalUsuarios; ?></div>
                </div>
            </div>

            <div class="stat-card stat-roteiros">
                <div class="stat-icon">🗺️</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Roteiros</div>
                    <div class="stat-value" id="totalRoteiros"><?php echo $totalRoteiros; ?></div>
                </div>
            </div>

            <div class="stat-card stat-pontos">
                <div class="stat-icon">📍</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Pontos</div>
                    <div class="stat-value" id="totalPontos"><?php echo $totalPontos; ?></div>
                </div>
            </div>
        </div>

        <!-- Navegação de Abas -->
        <div class="admin-tabs">
            <button class="tab-btn active" onclick="mostrarAba('usuarios')">
                👥 Usuários
            </button>
            <button class="tab-btn" onclick="mostrarAba('roteiros')">
                🗺️ Roteiros
            </button>
            <button class="tab-btn" onclick="mostrarAba('pontos')">
                📍 Pontos Turísticos
            </button>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="admin-content">
            
            <!-- Aba Usuários -->
            <div id="aba-usuarios" class="aba-content active">
                <div class="content-header">
                    <input type="text" id="searchUsuarios" class="search-input" placeholder="🔍 Buscar..." onkeyup="filtrarTabela('usuarios')">
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Tipo de Conta</th>
                                <th>Membro desde</th>
                                <th>Conteúdo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaUsuarios">
                            <tr>
                                <td colspan="6" class="loading">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba Roteiros -->
            <div id="aba-roteiros" class="aba-content">
                <div class="content-header">
                    <input type="text" id="searchRoteiros" class="search-input" placeholder="🔍 Buscar..." onkeyup="filtrarTabela('roteiros')">
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Roteiro</th>
                                <th>Criador</th>
                                <th>Nº de Locais</th>
                                <th>Avaliação</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaRoteiros">
                            <tr>
                                <td colspan="6" class="loading">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba Pontos -->
            <div id="aba-pontos" class="aba-content">
                <div class="content-header">
                    <input type="text" id="searchPontos" class="search-input" placeholder="🔍 Buscar..." onkeyup="filtrarTabela('pontos')">
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Local</th>
                                <th>Tipo</th>
                                <th>Localidade</th>
                                <th>Fornecedor</th>
                                <th>Avaliação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaPontos">
                            <tr>
                                <td colspan="6" class="loading">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>