<?php
session_start();
require_once '../config/conexao.php';
require_once '../classes/Avaliacao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'criar_avaliacao':
            if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'Turista') {
                echo json_encode(['success' => false, 'message' => 'Apenas turistas podem avaliar']);
                exit;
            }

            $tipo = $_POST['tipo'] ?? '';
            $nota = (float)$_POST['nota'];
            $descricao = mysqli_real_escape_string($conn, $_POST['descricao'] ?? '');

            $pontoId = null;
            $roteiroId = null;

            if ($tipo === 'Ponto_Turistico') {
                $pontoId = (int)$_POST['pontoId'];
                if (Avaliacao::jaAvaliou($conn, $_SESSION['usuario_id'], 'Ponto_Turistico', $pontoId)) {
                    echo json_encode(['success' => false, 'message' => 'Você já avaliou este local']);
                    exit;
                }
            } elseif ($tipo === 'Roteiro') {
                $roteiroId = (int)$_POST['roteiroId'];
                if (Avaliacao::jaAvaliou($conn, $_SESSION['usuario_id'], 'Roteiro', $roteiroId)) {
                    echo json_encode(['success' => false, 'message' => 'Você já avaliou este roteiro']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
                exit;
            }

            $avaliacao = new Avaliacao($conn);
            $dados = [
                'nota' => $nota,
                'descricao' => $descricao,
                'avaliador' => $_SESSION['usuario_id'],
                'tipoAvaliado' => $tipo,
                'pontoTuristicoId' => $pontoId,
                'roteiroId' => $roteiroId
            ];

            if ($avaliacao->criar($dados)) {
                echo json_encode(['success' => true, 'message' => 'Avaliação criada com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao criar avaliação']);
            }
            break;

        case 'listar_avaliacoes':
            $tipo = $_POST['tipo'] ?? '';
            $itemId = (int)($_POST['pontoId'] ?? $_POST['roteiroId'] ?? 0);

            if ($tipo === 'Ponto_Turistico') {
                $sql = "SELECT a.*, u.ID as IdUsuario, u.Nome as NomeUsuario, u.Foto_Perfil
                        FROM avaliacao a
                        INNER JOIN usuarios u ON a.Avaliador = u.ID
                        WHERE a.PontosTuristicos_id = ?
                        ORDER BY a.DataAvaliacao DESC";
            } else {
                $sql = "SELECT a.*, u.ID as IdUsuario, u.Nome as NomeUsuario, u.Foto_Perfil
                        FROM avaliacao a
                        INNER JOIN usuarios u ON a.Avaliador = u.ID
                        WHERE a.Roteiro_id = ?
                        ORDER BY a.DataAvaliacao DESC";
            }

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $itemId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $avaliacoes = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $avaliacoes[] = $row;
            }

            echo json_encode(['success' => true, 'avaliacoes' => $avaliacoes]);
            break;

        case 'deletar':
            if (!isset($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }

            $avaliacaoId = (int)$_POST['id'];

            $sql = "SELECT Avaliador FROM avaliacao WHERE Id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $avaliacaoId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $aval = mysqli_fetch_assoc($result);

            if (!$aval || $aval['Avaliador'] != $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }

            $sqlDel = "DELETE FROM avaliacao WHERE Id = ?";
            $stmtDel = mysqli_prepare($conn, $sqlDel);
            mysqli_stmt_bind_param($stmtDel, "i", $avaliacaoId);

            if (mysqli_stmt_execute($stmtDel)) {
                echo json_encode(['success' => true, 'message' => 'Avaliação deletada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao deletar']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}

mysqli_close($conn);
