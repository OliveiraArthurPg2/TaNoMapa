<?php

session_start();
require_once '../config/conexao.php';
require_once '../classes/PontoTuristico.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'editar':
            if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'Fornecedor') {
                echo json_encode(['success' => false, 'message' => 'Apenas fornecedores podem editar pontos']);
                exit;
            }
            
            $pontoId = (int)$_POST['pontoId'];
            
            
            $sqlCheck = "SELECT Fornecedor FROM pontosturisticos WHERE Id = ?";
            $stmtCheck = mysqli_prepare($conn, $sqlCheck);
            mysqli_stmt_bind_param($stmtCheck, "i", $pontoId);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            $pontoData = mysqli_fetch_assoc($resultCheck);
            
            if (!$pontoData || $pontoData['Fornecedor'] != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar este ponto']);
                exit;
            }
            
            
            $nome = mysqli_real_escape_string($conn, $_POST['nome']);
            $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
            $localidade = mysqli_real_escape_string($conn, $_POST['localidade']);
            $endereco = mysqli_real_escape_string($conn, $_POST['endereco']);
            $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
            
            $campos = [];
            $valores = [];
            $tipos = '';
            
            $campos[] = 'Nome = ?';
            $valores[] = $nome;
            $tipos .= 's';
            
            $campos[] = 'Tipo = ?';
            $valores[] = $tipo;
            $tipos .= 's';
            
            $campos[] = 'Localidade = ?';
            $valores[] = $localidade;
            $tipos .= 's';
            
            $campos[] = 'Endereco = ?';
            $valores[] = $endereco;
            $tipos .= 's';
            
            $campos[] = 'Bio = ?';
            $valores[] = $bio;
            $tipos .= 's';
            
            
            if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === 0) {
                $uploadDir = '../uploads/pontos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $ext = pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $uploadPath)) {
                    $campos[] = 'Foto_Perfil = ?';
                    $valores[] = 'uploads/pontos/' . $filename;
                    $tipos .= 's';
                }
            }
            
            
            if (isset($_FILES['fotoCapa']) && $_FILES['fotoCapa']['error'] === 0) {
                $uploadDir = '../uploads/pontos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $ext = pathinfo($_FILES['fotoCapa']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['fotoCapa']['tmp_name'], $uploadPath)) {
                    $campos[] = 'Foto_Capa = ?';
                    $valores[] = 'uploads/pontos/' . $filename;
                    $tipos .= 's';
                }
            }
            
            $sql = "UPDATE pontosturisticos SET " . implode(', ', $campos) . " WHERE Id = ?";
            $valores[] = $pontoId;
            $tipos .= 'i';
            
            $stmt = mysqli_prepare($conn, $sql);
            
            
            $bind_names = [$stmt, $tipos];
            for ($i = 0; $i < count($valores); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $valores[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array('mysqli_stmt_bind_param', $bind_names);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Ponto atualizado com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar ponto: ' . mysqli_error($conn)]);
            }
            break;
            
        case 'adicionar_fotos':
            if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'Fornecedor') {
                echo json_encode(['success' => false, 'message' => 'Apenas fornecedores podem adicionar fotos']);
                exit;
            }
            
            $pontoId = (int)$_POST['pontoId'];
            
            
            $sqlCheck = "SELECT Fornecedor FROM pontosturisticos WHERE Id = ?";
            $stmtCheck = mysqli_prepare($conn, $sqlCheck);
            mysqli_stmt_bind_param($stmtCheck, "i", $pontoId);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            $pontoData = mysqli_fetch_assoc($resultCheck);
            
            if (!$pontoData || $pontoData['Fornecedor'] != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            
            if (isset($_FILES['fotosGaleria'])) {
                $uploadDir = '../uploads/galeria/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadedCount = 0;
                
                foreach ($_FILES['fotosGaleria']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['fotosGaleria']['error'][$key] === 0) {
                        $ext = pathinfo($_FILES['fotosGaleria']['name'][$key], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $ext;
                        $uploadPath = $uploadDir . $filename;
                        
                        if (move_uploaded_file($tmp_name, $uploadPath)) {
                            $caminhoFoto = 'uploads/galeria/' . $filename;
                            $sqlFoto = "INSERT INTO fotos_local (PontoTuristico_id, Caminho_Foto) VALUES (?, ?)";
                            $stmtFoto = mysqli_prepare($conn, $sqlFoto);
                            mysqli_stmt_bind_param($stmtFoto, "is", $pontoId, $caminhoFoto);
                            if (mysqli_stmt_execute($stmtFoto)) {
                                $uploadedCount++;
                            }
                        }
                    }
                }
                
                if ($uploadedCount > 0) {
                    echo json_encode(['success' => true, 'message' => "$uploadedCount foto(s) adicionada(s) com sucesso"]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nenhuma foto foi carregada']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Nenhuma foto selecionada']);
            }
            break;
            
        case 'remover_foto_galeria':
            if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'Fornecedor') {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            $fotoId = (int)$_POST['fotoId'];
            
            
            $sqlCheck = "SELECT fl.*, pt.Fornecedor 
                        FROM fotos_local fl 
                        INNER JOIN pontosturisticos pt ON fl.PontoTuristico_id = pt.Id 
                        WHERE fl.Id = ?";
            $stmtCheck = mysqli_prepare($conn, $sqlCheck);
            mysqli_stmt_bind_param($stmtCheck, "i", $fotoId);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            $fotoData = mysqli_fetch_assoc($resultCheck);
            
            if (!$fotoData || $fotoData['Fornecedor'] != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            
            if (file_exists('../' . $fotoData['Caminho_Foto'])) {
                unlink('../' . $fotoData['Caminho_Foto']);
            }
            
            
            $sqlDelete = "DELETE FROM fotos_local WHERE Id = ?";
            $stmtDelete = mysqli_prepare($conn, $sqlDelete);
            mysqli_stmt_bind_param($stmtDelete, "i", $fotoId);
            
            if (mysqli_stmt_execute($stmtDelete)) {
                echo json_encode(['success' => true, 'message' => 'Foto removida']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao remover foto']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}

mysqli_close($conn);
?>