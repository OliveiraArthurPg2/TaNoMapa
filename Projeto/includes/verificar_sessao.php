<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function obterUsuarioLogado() {
    if (!estaLogado()) {
        return null;
    }
    
    
    if (isset($_SESSION['usuario_dados']) && !empty($_SESSION['usuario_dados'])) {
        return $_SESSION['usuario_dados'];
    }
    
    
    global $conn;
    
    
    if (!isset($conn)) {
        require_once __DIR__ . '/../config/conexao.php';
    }
    
    $userId = $_SESSION['usuario_id'];
    $sql = "SELECT * FROM usuarios WHERE Id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar query: " . mysqli_error($conn));
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($usuario) {
        
        $dadosUsuario = [
            'id' => $usuario['Id'],
            'nome' => $usuario['Nome'],
            'email' => $usuario['Email'],
            'tipo' => $usuario['Tipo'],
            'fotoPerfil' => $usuario['Foto_Perfil'] ?? null,
            'fotoCapa' => $usuario['Foto_Capa'] ?? null,
            'bio' => $usuario['Bio'] ?? null,
            'dataCadastro' => $usuario['Data_Cadastro'] ?? null,
            'telefone' => $usuario['Telefone'] ?? null,
            'cpf' => $usuario['CPF'] ?? null,
            'cnpj' => $usuario['CNPJ'] ?? null,
            'dataNascimento' => $usuario['Data_Nascimento'] ?? null
        ];
        
        
        $_SESSION['usuario_dados'] = $dadosUsuario;
        
        return $dadosUsuario;
    }
    
    return null;
}

function isTurista() {
    if (!estaLogado()) {
        return false;
    }
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'Turista';
}

function isFornecedor() {
    if (!estaLogado()) {
        return false;
    }
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'Fornecedor';
}

function requireLogin() {
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

function requireTurista() {
    requireLogin();
    if (!isTurista()) {
        header('Location: index.php');
        exit;
    }
}

function requireFornecedor() {
    requireLogin();
    if (!isFornecedor()) {
        header('Location: index.php');
        exit;
    }
}
?>