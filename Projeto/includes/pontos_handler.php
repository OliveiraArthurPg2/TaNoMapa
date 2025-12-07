<?php
session_start();
require_once '../config/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'listar_todos' || $action === 'top5' || $action === 'listar_por_tipo') {
        $sql = "SELECT 
                    pt.Id,
                    pt.Nome,
                    pt.Tipo,
                    pt.Localidade,
                    pt.Endereco,
                    pt.Bio,
                    pt.Foto_Perfil,
                    pt.Foto_Capa,
                    AVG(a.Nota) as Avaliacao,
                    COUNT(DISTINCT a.Id) as Total_Avaliacoes,
                    u.Nome as NomeFornecedor,
                    u.Foto_Perfil as FotoFornecedor,
                    u.ID as IdFornecedor
                FROM pontosturisticos pt
                LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                LEFT JOIN usuarios u ON pt.Fornecedor = u.ID";
        
        // ✅ Adiciona filtro por tipo se necessário
        if ($action === 'listar_por_tipo') {
            $tipo = $_POST['tipo'] ?? '';
            if (!empty($tipo)) {
                $sql .= " WHERE pt.Tipo = '" . mysqli_real_escape_string($conn, $tipo) . "'";
            }
        }
        
        $sql .= " GROUP BY pt.Id";
        
        if ($action === 'top5') {
            // ✅ Para top5: ordena por avaliação (pontos com avaliação primeiro)
            $sql .= " ORDER BY Avaliacao DESC, Total_Avaliacoes DESC LIMIT 5";
        } else {
            $sql .= " ORDER BY pt.Nome ASC";
        }
        
        $result = mysqli_query($conn, $sql);
        $pontos = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pontos[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'pontos' => $pontos]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Ação inválida']);