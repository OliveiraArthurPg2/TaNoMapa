<?php
session_start();
require_once '../config/conexao.php';
require_once '../classes/Usuario.php';

header('Content-Type: application/json');


error_log("=== PERFIL HANDLER DEBUG ===");
error_log("Action: " . ($_POST['action'] ?? 'NONE'));
error_log("Session ID: " . ($_SESSION['usuario_id'] ?? 'NONE'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'obter_dados':
            if (!isset($_SESSION['usuario_id'])) {
                error_log("Erro: Não autenticado");
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $usuario = new Usuario($conn);
            if ($usuario->buscarPorId($_SESSION['usuario_id'])) {
                $dados = [
                    'id' => $usuario->getId(),
                    'nome' => $usuario->getNome(),
                    'email' => $usuario->getEmail(),
                    'tipo' => $usuario->getTipo(),
                    'telefone' => $usuario->getTelefone(),
                    'cpf' => $usuario->getCpf(),
                    'cnpj' => $usuario->getCnpj(),
                    'dataNascimento' => $usuario->getDataNascimento(),
                    'bio' => $usuario->getBio(),
                    'fotoPerfil' => $usuario->getFotoPerfil(),
                    'fotoCapa' => $usuario->getFotoCapa(),
                ];
                
                
                if ($usuario->getTipo() === 'Turista' || $usuario->getTipo() === 'Fornecedor') {
                    $sqlRoteiros = "SELECT 
                                        r.Id,
                                        r.Nome,
                                        r.Bio,
                                        r.DataCriacao,
                                        COUNT(DISTINCT rp.Id_PontosTuristicos) as TotalLocais,
                                        ROUND(COALESCE(AVG(a.Nota), 5.0), 1) as Avaliacao,
                                        (SELECT pt.Foto_Capa 
                                         FROM roteiro_pontos rp2 
                                         JOIN pontosturisticos pt ON rp2.Id_PontosTuristicos = pt.Id 
                                         WHERE rp2.Id_Roteiro = r.Id 
                                         LIMIT 1) as Foto_Capa
                                    FROM roteiro r
                                    LEFT JOIN roteiro_pontos rp ON r.Id = rp.Id_Roteiro
                                    LEFT JOIN avaliacao a ON r.Id = a.Roteiro_id
                                    WHERE r.Autor = ?
                                    GROUP BY r.Id
                                    ORDER BY r.DataCriacao DESC";
                    
                    $stmt = mysqli_prepare($conn, $sqlRoteiros);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    $roteiros = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        $avaliacao = (float)$row['Avaliacao'];
                        
                        $row['Avaliacao'] = $avaliacao > 0 ? number_format($avaliacao, 1, '.', '') : '5.0';
                        $roteiros[] = $row;
                    }
                    $dados['roteiros'] = $roteiros;
                }
                
                
                if ($usuario->getTipo() === 'Fornecedor') {
                    $dados['locais'] = $usuario->buscarLocais();
                }
                
                error_log("Dados carregados: " . json_encode($dados));
                echo json_encode(['success' => true, 'dados' => $dados]);
            } else {
                error_log("Erro: Usuário não encontrado - ID: " . $_SESSION['usuario_id']);
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            }
            break;

        case 'obter_dados_publico':
            $usuarioId = (int)$_POST['usuarioId'];
            
            if ($usuarioId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            $usuario = new Usuario($conn);
            if ($usuario->buscarPorId($usuarioId)) {
                $dados = [
                    'id' => $usuario->getId(),
                    'nome' => $usuario->getNome(),
                    'tipo' => $usuario->getTipo(),
                    'bio' => $usuario->getBio(),
                    'fotoPerfil' => $usuario->getFotoPerfil(),
                    'fotoCapa' => $usuario->getFotoCapa(),
                ];
                
                
                if ($usuario->getTipo() === 'Turista' || $usuario->getTipo() === 'Fornecedor') {
                    $sqlRoteiros = "SELECT 
                                        r.Id,
                                        r.Nome,
                                        r.Bio,
                                        r.DataCriacao,
                                        COUNT(DISTINCT rp.Id_PontosTuristicos) as TotalLocais,
                                        ROUND(COALESCE(AVG(a.Nota), 5.0), 1) as Avaliacao,
                                        (SELECT pt.Foto_Capa 
                                         FROM roteiro_pontos rp2 
                                         JOIN pontosturisticos pt ON rp2.Id_PontosTuristicos = pt.Id 
                                         WHERE rp2.Id_Roteiro = r.Id 
                                         LIMIT 1) as Foto_Capa
                                    FROM roteiro r
                                    LEFT JOIN roteiro_pontos rp ON r.Id = rp.Id_Roteiro
                                    LEFT JOIN avaliacao a ON r.Id = a.Roteiro_id
                                    WHERE r.Autor = ?
                                    GROUP BY r.Id
                                    ORDER BY r.DataCriacao DESC";
                    
                    $stmt = mysqli_prepare($conn, $sqlRoteiros);
                    mysqli_stmt_bind_param($stmt, "i", $usuarioId);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    $roteiros = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        $avaliacao = (float)$row['Avaliacao'];
                        
                        $row['Avaliacao'] = $avaliacao > 0 ? number_format($avaliacao, 1, '.', '') : '5.0';
                        $roteiros[] = $row;
                    }
                    $dados['roteiros'] = $roteiros;
                }
                
                
                if ($usuario->getTipo() === 'Fornecedor') {
                    $dados['locais'] = $usuario->buscarLocais();
                }
                
                echo json_encode(['success' => true, 'dados' => $dados]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            }
            break;

        case 'atualizar':
            error_log("=== INICIANDO ATUALIZAÇÃO ===");
            
            if (!isset($_SESSION['usuario_id'])) {
                error_log("Erro: Não autenticado na atualização");
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            error_log("Usuario ID: " . $_SESSION['usuario_id']);
            
            $usuario = new Usuario($conn);
            if (!$usuario->buscarPorId($_SESSION['usuario_id'])) {
                error_log("Erro: Usuário não encontrado para atualização");
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }
            
            error_log("Dados recebidos: " . json_encode($_POST));
            error_log("Arquivos recebidos: " . json_encode($_FILES));
            
            $dados = [
                'nome' => mysqli_real_escape_string($conn, $_POST['nome']),
                'telefone' => mysqli_real_escape_string($conn, $_POST['telefone'] ?? ''),
                'bio' => mysqli_real_escape_string($conn, $_POST['bio'] ?? '')
            ];
            
            if ($usuario->isFornecedor()) {
                $dados['cnpj'] = mysqli_real_escape_string($conn, $_POST['cnpj'] ?? '');
                error_log("Usuário é fornecedor - CNPJ: " . $dados['cnpj']);
            } else {
                $dados['cpf'] = mysqli_real_escape_string($conn, $_POST['cpf'] ?? '');
                $dados['dataNascimento'] = mysqli_real_escape_string($conn, $_POST['dataNascimento'] ?? '');
                error_log("Usuário é turista - CPF: " . $dados['cpf']);
            }
            
            
            if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === 0) {
                error_log("Processando upload de foto de perfil");
                
                $uploadDir = '../uploads/perfil/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                    error_log("Diretório criado: " . $uploadDir);
                }
                
                $ext = pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                error_log("Caminho de upload: " . $uploadPath);
                
                if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $uploadPath)) {
                    $dados['fotoPerfil'] = 'uploads/perfil/' . $filename;
                    error_log("Foto de perfil salva: " . $dados['fotoPerfil']);
                } else {
                    error_log("ERRO ao mover arquivo de foto de perfil");
                }
            }
            
            
            if (isset($_FILES['fotoCapa']) && $_FILES['fotoCapa']['error'] === 0) {
                error_log("Processando upload de foto de capa");
                
                $uploadDir = '../uploads/capa/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                    error_log("Diretório criado: " . $uploadDir);
                }
                
                $ext = pathinfo($_FILES['fotoCapa']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                error_log("Caminho de upload: " . $uploadPath);
                
                if (move_uploaded_file($_FILES['fotoCapa']['tmp_name'], $uploadPath)) {
                    $dados['fotoCapa'] = 'uploads/capa/' . $filename;
                    error_log("Foto de capa salva: " . $dados['fotoCapa']);
                } else {
                    error_log("ERRO ao mover arquivo de foto de capa");
                }
            }
            
            error_log("Dados para atualizar: " . json_encode($dados));
            
            if ($usuario->atualizarPerfil($dados)) {
                
                $_SESSION['usuario_nome'] = $dados['nome'];
                if (!empty($dados['fotoPerfil'])) {
                    $_SESSION['usuario_foto'] = $dados['fotoPerfil'];
                }
                
                error_log("Perfil atualizado com sucesso");
                echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
            } else {
                error_log("ERRO ao atualizar perfil no banco de dados");
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil']);
            }
            break;
            
        case 'atualizar_senha':
            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $senhaAtual = $_POST['senhaAtual'];
            $novaSenha = $_POST['novaSenha'];
            $confirmarSenha = $_POST['confirmarSenha'];
            
            if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos']);
                exit;
            }
            
            if ($novaSenha !== $confirmarSenha) {
                echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
                exit;
            }
            
            if (strlen($novaSenha) < 6) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
                exit;
            }
            
            $usuario = new Usuario($conn);
            if (!$usuario->buscarPorId($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }
            
            if (!$usuario->verificarSenha($senhaAtual)) {
                echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
                exit;
            }
            
            if ($usuario->atualizarSenha($novaSenha)) {
                echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha']);
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