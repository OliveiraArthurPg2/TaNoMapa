<?php
require_once '../config/conexao.php';
require_once 'verificar_sessao.php';

header('Content-Type: application/json');


if (!estaLogado()) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$usuario = obterUsuarioLogado();

if ($usuario['email'] !== 'tanomapa@gmail.com') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$action = $_POST['action'] ?? '';

try {

    switch ($action) {




        case 'listar_usuarios':
            $query = "
                SELECT 
                    u.ID as id,
                    u.Nome as nome,
                    u.Email as email,
                    u.Tipo as Tipo,
                    u.Foto_Perfil as fotoPerfil,
                    NOW() as dataCriacao,
                    COUNT(DISTINCT r.Id) as totalRoteiros,
                    COUNT(DISTINCT p.Id) as totalPontos
                FROM usuarios u
                LEFT JOIN roteiro r ON u.ID = r.Autor
                LEFT JOIN pontosturisticos p ON u.ID = p.Fornecedor
                GROUP BY u.ID
                ORDER BY u.ID DESC
            ";

            $result = mysqli_query($conn, $query);
            $usuarios = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }

            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            break;




        case 'listar_roteiros':
            $query = "
                SELECT 
                    r.Id as id,
                    r.Nome as nome,
                    r.Bio as bio,
                    r.DataCriacao as dataCriacao,
                    u.Nome as criador,
                    (SELECT COUNT(*) FROM roteiro_pontos WHERE Id_Roteiro = r.Id) as totalLocais,
                    (SELECT AVG(Nota) FROM avaliacao WHERE Roteiro_id = r.Id) as avaliacao
                FROM roteiro r
                INNER JOIN usuarios u ON r.Autor = u.ID
                ORDER BY r.DataCriacao DESC
            ";

            $result = mysqli_query($conn, $query);
            $roteiros = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $roteiros[] = $row;
            }

            echo json_encode([
                'success' => true,
                'roteiros' => $roteiros
            ]);
            break;




        case 'listar_pontos':
            $query = "
                SELECT 
                    p.Id as id,
                    p.Nome as nome,
                    p.Tipo as tipo,
                    p.Localidade as localidade,
                    p.Foto_Perfil as fotoPerfil,
                    u.Nome as fornecedor,
                    (SELECT AVG(Nota) FROM avaliacao WHERE PontosTuristicos_id = p.Id) as avaliacao
                FROM pontosturisticos p
                INNER JOIN usuarios u ON p.Fornecedor = u.ID
                ORDER BY p.Nome ASC
            ";

            $result = mysqli_query($conn, $query);
            $pontos = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $pontos[] = $row;
            }

            echo json_encode([
                'success' => true,
                'pontos' => $pontos
            ]);
            break;




        case 'toggle_status_usuario':
        case 'toggle_status_roteiro':
        case 'toggle_status_ponto':
            echo json_encode([
                'success' => false,
                'message' => 'Função de status não disponível'
            ]);
            break;




        case 'excluir_usuario':
            $id = intval($_POST['id'] ?? 0);


            mysqli_autocommit($conn, false);

            try {

                mysqli_query($conn, "DELETE FROM avaliacao WHERE Avaliador = $id");


                mysqli_query($conn, "DELETE FROM roteiro_pontos WHERE Id_Roteiro IN (SELECT Id FROM roteiro WHERE Autor = $id)");


                mysqli_query($conn, "DELETE FROM roteiro WHERE Autor = $id");


                mysqli_query($conn, "DELETE FROM pontosturisticos WHERE Fornecedor = $id");


                mysqli_query($conn, "DELETE FROM usuarios WHERE ID = $id");


                mysqli_commit($conn);
                mysqli_autocommit($conn, true);

                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário excluído com sucesso'
                ]);
            } catch (Exception $e) {
                mysqli_rollback($conn);
                mysqli_autocommit($conn, true);
                throw $e;
            }
            break;




        case 'excluir_roteiro':
            $id = intval($_POST['id'] ?? 0);

            mysqli_autocommit($conn, false);

            try {

                mysqli_query($conn, "DELETE FROM avaliacao WHERE Roteiro_id = $id");


                mysqli_query($conn, "DELETE FROM roteiro_pontos WHERE Id_Roteiro = $id");


                mysqli_query($conn, "DELETE FROM roteiro WHERE Id = $id");

                mysqli_commit($conn);
                mysqli_autocommit($conn, true);

                echo json_encode([
                    'success' => true,
                    'message' => 'Roteiro excluído com sucesso'
                ]);
            } catch (Exception $e) {
                mysqli_rollback($conn);
                mysqli_autocommit($conn, true);
                throw $e;
            }
            break;




        case 'excluir_ponto':
            $id = intval($_POST['id'] ?? 0);

            mysqli_autocommit($conn, false);

            try {

                mysqli_query($conn, "DELETE FROM avaliacao WHERE PontosTuristicos_id = $id");


                mysqli_query($conn, "DELETE FROM roteiro_pontos WHERE Id_PontosTuristicos = $id");


                mysqli_query($conn, "DELETE FROM pontosturisticos WHERE Id = $id");

                mysqli_commit($conn);
                mysqli_autocommit($conn, true);

                echo json_encode([
                    'success' => true,
                    'message' => 'Ponto excluído com sucesso'
                ]);
            } catch (Exception $e) {
                mysqli_rollback($conn);
                mysqli_autocommit($conn, true);
                throw $e;
            }
            break;




        case 'estatisticas':
            $resultUsuarios = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
            $totalUsuarios = mysqli_fetch_assoc($resultUsuarios)['total'];

            $resultRoteiros = mysqli_query($conn, "SELECT COUNT(*) as total FROM roteiro");
            $totalRoteiros = mysqli_fetch_assoc($resultRoteiros)['total'];

            $resultPontos = mysqli_query($conn, "SELECT COUNT(*) as total FROM pontosturisticos");
            $totalPontos = mysqli_fetch_assoc($resultPontos)['total'];

            echo json_encode([
                'success' => true,
                'stats' => [
                    'usuarios' => $totalUsuarios,
                    'roteiros' => $totalRoteiros,
                    'pontos' => $totalPontos
                ]
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Ação não reconhecida'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
