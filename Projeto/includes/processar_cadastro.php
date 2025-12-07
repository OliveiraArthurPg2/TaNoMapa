<?php

session_start();


require_once '../config/conexao.php';


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha']; 
    $telefone = isset($_POST['telefone']) ? mysqli_real_escape_string($conn, $_POST['telefone']) : '';
    $dataNascimento = isset($_POST['dataNascimento']) ? mysqli_real_escape_string($conn, $_POST['dataNascimento']) : '';
    $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
    
    
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    
    $cpf = isset($_POST['cpf']) && !empty($_POST['cpf']) ? mysqli_real_escape_string($conn, $_POST['cpf']) : null;
    $cnpj = isset($_POST['cnpj']) && !empty($_POST['cnpj']) ? mysqli_real_escape_string($conn, $_POST['cnpj']) : null;
    
    
    if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
        exit;
    }
    
    
    if (strlen($senha) < 6) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
        exit;
    }
    
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    
    $checkEmail = "SELECT ID FROM usuarios WHERE Email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
        exit;
    }
    
    
    if ($cpf) {
        $checkCPF = "SELECT ID FROM usuarios WHERE CPF = '$cpf'";
        $resultCPF = mysqli_query($conn, $checkCPF);
        if (mysqli_num_rows($resultCPF) > 0) {
            echo json_encode(['success' => false, 'message' => 'Este CPF já está cadastrado']);
            exit;
        }
    }
    
    
    if ($cnpj) {
        $checkCNPJ = "SELECT ID FROM usuarios WHERE CNPJ = '$cnpj'";
        $resultCNPJ = mysqli_query($conn, $checkCNPJ);
        if (mysqli_num_rows($resultCNPJ) > 0) {
            echo json_encode(['success' => false, 'message' => 'Este CNPJ já está cadastrado']);
            exit;
        }
    }
    
    
    $sql = "INSERT INTO usuarios (Nome, Email, Senha, Telefone, Data_Nascimento, CPF, CNPJ, Tipo) 
            VALUES ('$nome', '$email', '$senhaHash', " . 
            ($telefone ? "'$telefone'" : "NULL") . ", " .
            ($dataNascimento ? "'$dataNascimento'" : "NULL") . ", " .
            ($cpf ? "'$cpf'" : "NULL") . ", " .
            ($cnpj ? "'$cnpj'" : "NULL") . ", '$tipo')";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cadastro realizado com sucesso!',
            'user_id' => mysqli_insert_id($conn)
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao cadastrar: ' . mysqli_error($conn)
        ]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}
?>