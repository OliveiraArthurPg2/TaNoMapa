<?php

error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);


ob_start();

session_start();
require_once '../config/conexao.php';
require_once '../classes/Roteiro.php';


ob_end_clean();

header('Content-Type: application/json; charset=utf-8');


error_log("=== ROTEIRO HANDLER ===");
error_log("Action: " . ($_POST['action'] ?? 'NONE'));
error_log("Session ID: " . ($_SESSION['usuario_id'] ?? 'NONE'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            
            case 'listar_todos_pontos':
                
case 'listar_todos_roteiros':
    $ordem = $_POST['ordem'] ?? 'recente';
    
    $orderBy = "r.DataCriacao DESC"; 
    
    if ($ordem === 'avaliacao') {
        $orderBy = "Avaliacao DESC, r.DataCriacao DESC";
    } elseif ($ordem === 'pontos') {
        $orderBy = "TotalPontos DESC, r.DataCriacao DESC";
    }
    
    $sql = "SELECT 
                r.Id,
                r.Nome,
                r.Bio,
                r.Autor,
                r.DataCriacao,
                COUNT(DISTINCT rp.Id_PontosTuristicos) as TotalPontos,
                COALESCE(AVG(a.Nota), 0) as Avaliacao,
                u.Nome as NomeAutor,
                u.Foto_Perfil as FotoAutor,
                u.ID as IdAutor,
                (SELECT pt.Foto_Capa 
                 FROM roteiro_pontos rp2 
                 JOIN pontosturisticos pt ON rp2.Id_PontosTuristicos = pt.Id 
                 WHERE rp2.Id_Roteiro = r.Id 
                 LIMIT 1) as Foto_Capa
            FROM roteiro r
            LEFT JOIN roteiro_pontos rp ON r.Id = rp.Id_Roteiro
            LEFT JOIN avaliacao a ON r.Id = a.Roteiro_id
            LEFT JOIN usuarios u ON r.Autor = u.ID
            GROUP BY r.Id
            ORDER BY $orderBy";
    
    $result = mysqli_query($conn, $sql);
    $roteiros = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $roteiros[] = $row;
        }
        echo json_encode([
            'success' => true, 
            'roteiros' => $roteiros,
            'total' => count($roteiros)
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao buscar roteiros: ' . mysqli_error($conn)
        ]);
    }
    break;
            case 'listar_todos':
                $sql = "SELECT 
                            pt.Id,
                            pt.Nome,
                            pt.Tipo,
                            pt.Localidade,
                            pt.Endereco,
                            pt.Bio,
                            pt.Foto_Perfil,
                            pt.Foto_Capa,
                            pt.Fornecedor,
                            u.Nome as NomeFornecedor,
                            COALESCE(AVG(a.Nota), 0) as Avaliacao,
                            COUNT(DISTINCT a.Id) as Total_Avaliacoes
                        FROM pontosturisticos pt
                        LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                        LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                        GROUP BY pt.Id
                        ORDER BY pt.Nome ASC";
                
                $result = mysqli_query($conn, $sql);
                $pontos = [];
                
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $pontos[] = $row;
                    }
                    echo json_encode([
                        'success' => true, 
                        'pontos' => $pontos,
                        'total' => count($pontos)
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Erro ao buscar pontos: ' . mysqli_error($conn)
                    ]);
                }
                break;
                
            
            case 'criar':
                if (!isset($_SESSION['usuario_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $nome = mysqli_real_escape_string($conn, $_POST['nome']);
                $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
                $pontosIds = isset($_POST['pontos']) ? json_decode($_POST['pontos'], true) : [];
                
                if (empty($nome)) {
                    echo json_encode(['success' => false, 'message' => 'Nome do roteiro é obrigatório']);
                    exit;
                }
                
                if (empty($pontosIds) || count($pontosIds) < 2) {
                    echo json_encode(['success' => false, 'message' => 'Selecione pelo menos 2 locais']);
                    exit;
                }
                
                $roteiro = new Roteiro($conn);
                $id = $roteiro->criar($nome, $bio, $_SESSION['usuario_id'], $pontosIds);
                
                if ($id) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Roteiro criado com sucesso!',
                        'roteiro_id' => $id
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar roteiro']);
                }
                break;
                
            
            case 'listar_pontos':
                $pontos = Roteiro::listarPontosTuristicos($conn);
                echo json_encode(['success' => true, 'pontos' => $pontos]);
                break;
                
            
            case 'buscar':
                $id = (int)$_POST['id'];
                $roteiro = new Roteiro($conn);
                
                if ($roteiro->buscarPorId($id)) {
                    $pontos = $roteiro->buscarPontos();
                    echo json_encode([
                        'success' => true,
                        'roteiro' => [
                            'id' => $roteiro->getId(),
                            'nome' => $roteiro->getNome(),
                            'bio' => $roteiro->getBio(),
                            'autor' => $roteiro->getAutor(),
                            'dataCriacao' => $roteiro->getDataCriacao(),
                            'pontos' => $pontos
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Roteiro não encontrado']);
                }
                break;
                
            
            case 'editar':
                if (!isset($_SESSION['usuario_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $roteiroId = (int)$_POST['roteiroId'];
                $roteiro = new Roteiro($conn);
                
                if (!$roteiro->buscarPorId($roteiroId)) {
                    echo json_encode(['success' => false, 'message' => 'Roteiro não encontrado']);
                    exit;
                }
                
                
                if ($roteiro->getAutor() != $_SESSION['usuario_id']) {
                    echo json_encode(['success' => false, 'message' => 'Você não tem permissão']);
                    exit;
                }
                
                $nome = mysqli_real_escape_string($conn, $_POST['nome']);
                $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
                
                $sql = "UPDATE roteiro SET Nome = ?, Bio = ? WHERE Id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $nome, $bio, $roteiroId);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode(['success' => true, 'message' => 'Roteiro atualizado']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
                }
                break;
                
            
            case 'deletar':
                if (!isset($_SESSION['usuario_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $id = (int)$_POST['id'];
                $roteiro = new Roteiro($conn);
                
                if ($roteiro->buscarPorId($id)) {
                    
                    if ($roteiro->getAutor() == $_SESSION['usuario_id']) {
                        if ($roteiro->deletar()) {
                            echo json_encode(['success' => true, 'message' => 'Roteiro deletado com sucesso']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Erro ao deletar roteiro']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para deletar este roteiro']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Roteiro não encontrado']);
                }
                break;
            
            
            case 'listar_pontos_disponiveis':
                error_log("📋 Listando pontos disponíveis");
                
                if (!isset($_SESSION['usuario_id'])) {
                    error_log("❌ Usuário não autenticado");
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $roteiroId = isset($_POST['roteiroId']) ? (int)$_POST['roteiroId'] : 0;
                error_log("🎯 Roteiro ID: " . $roteiroId);
                
                if ($roteiroId <= 0) {
                    error_log("❌ Roteiro ID inválido: $roteiroId");
                    echo json_encode(['success' => false, 'message' => 'Roteiro ID inválido']);
                    exit;
                }
                
                
                $sql = "SELECT pt.Id, pt.Nome, pt.Tipo, pt.Localidade, pt.Foto_Perfil, pt.Foto_Capa, pt.Bio
                        FROM pontosturisticos pt
                        WHERE pt.Id NOT IN (
                            SELECT Id_PontosTuristicos 
                            FROM roteiro_pontos 
                            WHERE Id_Roteiro = ?
                        )
                        ORDER BY pt.Nome";
                
                $stmt = mysqli_prepare($conn, $sql);
                
                if (!$stmt) {
                    error_log("❌ Erro ao preparar query: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . mysqli_error($conn)]);
                    exit;
                }
                
                mysqli_stmt_bind_param($stmt, "i", $roteiroId);
                
                if (!mysqli_stmt_execute($stmt)) {
                    error_log("❌ Erro ao executar query: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro ao executar query: ' . mysqli_error($conn)]);
                    exit;
                }
                
                $result = mysqli_stmt_get_result($stmt);
                
                if (!$result) {
                    error_log("❌ Erro ao obter resultado: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro ao obter resultado: ' . mysqli_error($conn)]);
                    exit;
                }
                
                $pontos = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $pontos[] = $row;
                }
                
                error_log("✅ Pontos encontrados: " . count($pontos));
                echo json_encode(['success' => true, 'pontos' => $pontos, 'total' => count($pontos)]);
                break;
            
            
            case 'adicionar_ponto':
                error_log("➕ Adicionando ponto ao roteiro");
                
                if (!isset($_SESSION['usuario_id'])) {
                    error_log("❌ Usuário não autenticado");
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $roteiroId = isset($_POST['roteiroId']) ? (int)$_POST['roteiroId'] : 0;
                $pontoId = isset($_POST['pontoId']) ? (int)$_POST['pontoId'] : 0;
                
                error_log("📊 Dados recebidos: Roteiro=$roteiroId, Ponto=$pontoId");
                
                if ($roteiroId <= 0 || $pontoId <= 0) {
                    error_log("❌ IDs inválidos: Roteiro=$roteiroId, Ponto=$pontoId");
                    echo json_encode(['success' => false, 'message' => "IDs inválidos (Roteiro: $roteiroId, Ponto: $pontoId)"]);
                    exit;
                }
                
                
                $roteiro = new Roteiro($conn);
                if (!$roteiro->buscarPorId($roteiroId)) {
                    error_log("❌ Roteiro não encontrado: $roteiroId");
                    echo json_encode(['success' => false, 'message' => 'Roteiro não encontrado']);
                    exit;
                }
                
                $autorId = $roteiro->getAutor();
                $sessionId = $_SESSION['usuario_id'];
                
                error_log("🔍 Verificação de autoria: Autor=$autorId, Session=$sessionId");
                
                if ($autorId != $sessionId) {
                    error_log("❌ Usuário não é autor. Autor=$autorId, Session=$sessionId");
                    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar este roteiro']);
                    exit;
                }
                
                
                $sqlCheckPonto = "SELECT Id FROM pontosturisticos WHERE Id = ?";
                $stmtCheckPonto = mysqli_prepare($conn, $sqlCheckPonto);
                
                if (!$stmtCheckPonto) {
                    error_log("❌ Erro ao preparar verificação de ponto: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . mysqli_error($conn)]);
                    exit;
                }
                
                mysqli_stmt_bind_param($stmtCheckPonto, "i", $pontoId);
                mysqli_stmt_execute($stmtCheckPonto);
                $resultCheckPonto = mysqli_stmt_get_result($stmtCheckPonto);
                
                if (mysqli_num_rows($resultCheckPonto) === 0) {
                    error_log("❌ Ponto não existe: $pontoId");
                    echo json_encode(['success' => false, 'message' => 'Ponto turístico não encontrado no banco de dados']);
                    exit;
                }
                
                
                $sqlCheck = "SELECT Id_Roteiro FROM roteiro_pontos WHERE Id_Roteiro = ? AND Id_PontosTuristicos = ?";
                $stmtCheck = mysqli_prepare($conn, $sqlCheck);
                
                if (!$stmtCheck) {
                    error_log("❌ Erro ao preparar verificação: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . mysqli_error($conn)]);
                    exit;
                }
                
                mysqli_stmt_bind_param($stmtCheck, "ii", $roteiroId, $pontoId);
                mysqli_stmt_execute($stmtCheck);
                $resultCheck = mysqli_stmt_get_result($stmtCheck);
                
                if (mysqli_num_rows($resultCheck) > 0) {
                    error_log("❌ Ponto já está no roteiro");
                    echo json_encode(['success' => false, 'message' => 'Este ponto já está no roteiro']);
                    exit;
                }
                
                
                $sqlInsert = "INSERT INTO roteiro_pontos (Id_Roteiro, Id_PontosTuristicos) VALUES (?, ?)";
                $stmtInsert = mysqli_prepare($conn, $sqlInsert);
                
                if (!$stmtInsert) {
                    error_log("❌ Erro ao preparar inserção: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro ao preparar inserção: ' . mysqli_error($conn)]);
                    exit;
                }
                
                mysqli_stmt_bind_param($stmtInsert, "ii", $roteiroId, $pontoId);
                
                if (mysqli_stmt_execute($stmtInsert)) {
                    error_log("✅ Ponto adicionado com sucesso! Roteiro=$roteiroId, Ponto=$pontoId");
                    echo json_encode(['success' => true, 'message' => 'Ponto adicionado com sucesso']);
                } else {
                    error_log("❌ Erro ao inserir: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar ponto: ' . mysqli_error($conn)]);
                }
                break;
            
            
            case 'remover_ponto':
                error_log("🗑️ Removendo ponto do roteiro");
                
                if (!isset($_SESSION['usuario_id'])) {
                    error_log("❌ Usuário não autenticado");
                    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                    exit;
                }
                
                $roteiroId = isset($_POST['roteiroId']) ? (int)$_POST['roteiroId'] : 0;
                $pontoId = isset($_POST['pontoId']) ? (int)$_POST['pontoId'] : 0;
                
                error_log("📊 Dados: Roteiro=$roteiroId, Ponto=$pontoId");
                
                
                $roteiro = new Roteiro($conn);
                if (!$roteiro->buscarPorId($roteiroId)) {
                    error_log("❌ Roteiro não encontrado");
                    echo json_encode(['success' => false, 'message' => 'Roteiro não encontrado']);
                    exit;
                }
                
                if ($roteiro->getAutor() != $_SESSION['usuario_id']) {
                    error_log("❌ Usuário não é autor");
                    echo json_encode(['success' => false, 'message' => 'Você não tem permissão']);
                    exit;
                }
                
                
                $sqlCount = "SELECT COUNT(*) as total FROM roteiro_pontos WHERE Id_Roteiro = ?";
                $stmtCount = mysqli_prepare($conn, $sqlCount);
                mysqli_stmt_bind_param($stmtCount, "i", $roteiroId);
                mysqli_stmt_execute($stmtCount);
                $resultCount = mysqli_stmt_get_result($stmtCount);
                $rowCount = mysqli_fetch_assoc($resultCount);
                
                if ($rowCount['total'] <= 1) {
                    error_log("❌ Último ponto do roteiro");
                    echo json_encode(['success' => false, 'message' => 'O roteiro precisa ter pelo menos 1 ponto']);
                    exit;
                }
                
                
                $sqlDelete = "DELETE FROM roteiro_pontos WHERE Id_Roteiro = ? AND Id_PontosTuristicos = ?";
                $stmtDelete = mysqli_prepare($conn, $sqlDelete);
                mysqli_stmt_bind_param($stmtDelete, "ii", $roteiroId, $pontoId);
                
                if (mysqli_stmt_execute($stmtDelete)) {
                    error_log("✅ Ponto removido com sucesso");
                    echo json_encode(['success' => true, 'message' => 'Ponto removido com sucesso']);
                } else {
                    error_log("❌ Erro ao remover: " . mysqli_error($conn));
                    echo json_encode(['success' => false, 'message' => 'Erro ao remover ponto']);
                }
                break;
                
            default:
                error_log("❌ Ação inválida: $action");
                echo json_encode(['success' => false, 'message' => 'Ação inválida: ' . $action]);
        }
        
    } catch (Exception $e) {
        error_log("❌❌❌ EXCEPTION: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}

mysqli_close($conn);
?>