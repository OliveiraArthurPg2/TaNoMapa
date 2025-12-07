<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ta no Mapa</title>
    <link rel="stylesheet" href="css/style-login.css?v=2.0">
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <div class="logo-icon">🌍</div>
            <span>Ta no Mapa</span>
        </a>
        <nav>
            <a href="index.php">Voltar ao site</a>
        </nav>
    </header>

    <div class="main-content">
        <div class="login-container">
            <div class="login-header">
                <h1>Entrar</h1>
                <p>Acesse sua conta e descubra o mundo</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo de Usuário</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecione o tipo</option>
                        <option value="Turista">Turista</option>
                        <option value="Fornecedor">Fornecedor</option>
                    </select>
                </div>

                <button type="submit" class="btn-login">Entrar</button>
            </form>

            <div class="divider">ou</div>

            <div class="register-link">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>Ta no Mapa - Descubra o mundo com a gente</p>
            <div class="footer-bottom">
                © 2025 Ta no Mapa. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="js/login.js"></script>
</body>
</html>