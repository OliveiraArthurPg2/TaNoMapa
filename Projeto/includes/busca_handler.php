<?php

session_start();



require_once __DIR__ . '/../config/conexao.php';





header('Content-Type: application/json');


error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        
        if (!isset($conn) || !$conn) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro: Falha na conexão com o banco de dados'
            ]);
            exit;
        }

        switch ($action) {
            case 'listar_todos_pontos':
            case 'listar_todos':
                listarTodosLocais($conn);
                break;

            case 'buscar':
                buscarLocais($conn);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Ação inválida']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}

function listarTodosLocais($conn)
{
    $sql = "
        SELECT 
            Id,
            Nome,
            Tipo,
            Localidade,
            Endereco,
            Bio,
            Foto_Perfil,
            Foto_Capa
        FROM pontosturisticos
        ORDER BY Nome ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro na consulta: ' . mysqli_error($conn)
        ]);
        return;
    }

    $locais = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locais[] = $row;
    }

    echo json_encode([
        'success' => true,
        'pontos' => $locais,
        'total' => count($locais)
    ]);
}

function buscarLocais($conn)
{
    $termo = mysqli_real_escape_string($conn, $_POST['termo'] ?? '');

    if (empty($termo)) {
        listarTodosLocais($conn);
        return;
    }

    $sql = "
        SELECT 
            Id,
            Nome,
            Tipo,
            Localidade,
            Endereco,
            Bio,
            Foto_Perfil,
            Foto_Capa
        FROM pontosturisticos
        WHERE 
            Nome LIKE '%{$termo}%' OR
            Localidade LIKE '%{$termo}%' OR
            Tipo LIKE '%{$termo}%' OR
            Bio LIKE '%{$termo}%'
        ORDER BY 
            CASE 
                WHEN Nome LIKE '{$termo}%' THEN 1
                WHEN Nome LIKE '%{$termo}%' THEN 2
                ELSE 3
            END,
            Nome ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro na busca: ' . mysqli_error($conn)
        ]);
        return;
    }

    $locais = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locais[] = $row;
    }

    echo json_encode([
        'success' => true,
        'pontos' => $locais,
        'total' => count($locais),
        'termo' => $termo
    ]);
}

if (isset($conn)) {
    mysqli_close($conn);
}
