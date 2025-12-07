<?php
session_start();
require_once '../config/conexao.php';
require_once '../classes/PontoTuristico.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'top5':
            $pontos = PontoTuristico::buscarTop5ComEmpate($conn);
            echo json_encode(['success' => true, 'pontos' => $pontos]);
            break;
            
        case 'listar_por_tipo':
            $tipo = $_POST['tipo'] ?? null;
            $pontos = PontoTuristico::listarPorTipo($conn, $tipo);
            echo json_encode(['success' => true, 'pontos' => $pontos]);
            break;
    }
}

mysqli_close($conn);
?>