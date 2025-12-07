<?php

session_start();


require_once '../config/conexao.php';


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha']; 
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
    
    
    if (empty($email) || empty($senha) || empty($tipo)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
        exit;
    }
    
    
    $sql = "SELECT * FROM usuarios WHERE Email = '$email' AND Tipo = '$tipo'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        
        
        if (password_verify($senha, $usuario['Senha'])) {
            
            $_SESSION['usuario_id'] = $usuario['ID'];  
            $_SESSION['usuario_nome'] = $usuario['Nome'];
            $_SESSION['usuario_email'] = $usuario['Email'];
            $_SESSION['usuario_tipo'] = $usuario['Tipo'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login realizado com sucesso!',
                'redirect' => 'index.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>