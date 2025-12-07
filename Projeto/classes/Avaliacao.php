<?php
class Avaliacao {
    private $conn;
    private $id;
    private $nota;
    private $descricao;
    private $avaliador;
    private $tipoAvaliado;
    private $pontoTuristicoId;
    private $roteiroId;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    
    public function getId() { return $this->id; }
    public function getNota() { return $this->nota; }
    public function getDescricao() { return $this->descricao; }
    public function getAvaliador() { return $this->avaliador; }

    
    public function criar($dados) {
        $sql = "INSERT INTO avaliacao (Nota, Descricao, Avaliador, Tipo_Avaliado, PontosTuristicos_id, Roteiro_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "dsisii", 
            $dados['nota'],
            $dados['descricao'],
            $dados['avaliador'],
            $dados['tipoAvaliado'],
            $dados['pontoTuristicoId'],
            $dados['roteiroId']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->id = mysqli_insert_id($this->conn);
            return $this->id;
        }
        return false;
    }

    
    public function atualizar($dados) {
        $sql = "UPDATE avaliacao SET Nota = ?, Descricao = ? WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "dsi", 
            $dados['nota'],
            $dados['descricao'],
            $this->id
        );
        return mysqli_stmt_execute($stmt);
    }

    
    public function deletar() {
        $sql = "DELETE FROM avaliacao WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        return mysqli_stmt_execute($stmt);
    }

    
    public static function jaAvaliou($conn, $avaliador, $tipo, $itemId) {
        if ($tipo === 'Ponto_Turistico') {
            $sql = "SELECT Id FROM avaliacao WHERE Avaliador = ? AND PontosTuristicos_id = ?";
        } else {
            $sql = "SELECT Id FROM avaliacao WHERE Avaliador = ? AND Roteiro_id = ?";
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $avaliador, $itemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }
}
?>