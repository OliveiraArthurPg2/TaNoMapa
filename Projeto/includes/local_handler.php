<?php
session_start();
require_once '../config/conexao.php';
require_once '../classes/PontoTuristico.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'Fornecedor') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
       case 'criar':
    
    if (empty($_POST['nome']) || empty($_POST['tipo']) || empty($_POST['localidade']) || empty($_POST['endereco'])) {
        echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
        exit;
    }

    
    $fotoPerfil = null;
    $fotoCapa = null;
    
    if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
        $fotoPerfil = uploadImagem($_FILES['fotoPerfil'], 'pontos');
    }
    
    if (isset($_FILES['fotoCapa']) && $_FILES['fotoCapa']['error'] === UPLOAD_ERR_OK) {
        $fotoCapa = uploadImagem($_FILES['fotoCapa'], 'pontos');
    }
    
    $dados = [
        'tipo' => mysqli_real_escape_string($conn, $_POST['tipo']),
        'nome' => mysqli_real_escape_string($conn, $_POST['nome']),
        'localidade' => mysqli_real_escape_string($conn, $_POST['localidade']),
        'endereco' => mysqli_real_escape_string($conn, $_POST['endereco']),
        'bio' => mysqli_real_escape_string($conn, $_POST['bio'] ?? ''),
        'fornecedor' => $_SESSION['usuario_id'],
        'fotoPerfil' => $fotoPerfil,
        'fotoCapa' => $fotoCapa
    ];
    
    $ponto = new PontoTuristico($conn);
    $id = $ponto->criar($dados);
    
    if ($id) {
        echo json_encode([
            'success' => true,
            'message' => 'Local cadastrado com sucesso!',
            'local_id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar local']);
    }
    break;
    
        case 'deletar':
            $id = (int)$_POST['id'];
            $ponto = new PontoTuristico($conn);
            
            if ($ponto->buscarPorId($id)) {
                if ($ponto->getFornecedor() == $_SESSION['usuario_id']) {
                    if ($ponto->deletar()) {
                        echo json_encode(['success' => true, 'message' => 'Local deletado']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erro ao deletar']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Local não encontrado']);
            }
            break;
    }
}

function uploadImagem($arquivo, $pasta) {
    $dirBase = "../uploads/$pasta/";
    
    if (!is_dir($dirBase)) {
        if (!mkdir($dirBase, 0777, true)) {
            return null;
        }
    }
    
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if ($arquivo['error'] !== UPLOAD_ERR_OK || !in_array($extensao, $permitidos)) {
        return null;
    }
    
    $nomeArquivo = uniqid() . '.' . $extensao;
    $caminhoCompleto = $dirBase . $nomeArquivo;
    
    if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        return "uploads/$pasta/" . $nomeArquivo;
    }
    
    return null;
}

mysqli_close($conn);
?>