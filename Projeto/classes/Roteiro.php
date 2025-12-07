<?php
class Roteiro {
    private $conn;
    private $id;
    private $nome;
    private $bio;
    private $autor;
    private $dataCriacao;
    
    public function __construct($conexao) {
        $this->conn = $conexao;
    }
    

    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getBio() { return $this->bio; }
    public function getAutor() { return $this->autor; }
    public function getDataCriacao() { return $this->dataCriacao; }

    public function criar($nome, $bio, $autor, $pontosIds = []) {
        $sql = "INSERT INTO roteiro (Nome, Bio, Autor) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $nome, $bio, $autor);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->id = mysqli_insert_id($this->conn);
            $this->nome = $nome;
            $this->bio = $bio;
            $this->autor = $autor;
            

            if (!empty($pontosIds)) {
                foreach ($pontosIds as $pontoId) {
                    $this->adicionarPonto($pontoId);
                }
            }
            
            return $this->id;
        }
        return false;
    }

    public function adicionarPonto($pontoId) {
        $sql = "INSERT INTO roteiro_pontos (Id_Roteiro, Id_PontosTuristicos) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $this->id, $pontoId);
        return mysqli_stmt_execute($stmt);
    }
    

    public function buscarPorId($id) {
        $sql = "SELECT * FROM roteiro WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $this->id = $row['Id'];
            $this->nome = $row['Nome'];
            $this->bio = $row['Bio'];
            $this->autor = $row['Autor'];
            $this->dataCriacao = $row['DataCriacao'];
            return true;
        }
        return false;
    }
    

    public function buscarPontos() {
        $sql = "SELECT pt.* 
                FROM pontosturisticos pt
                INNER JOIN roteiro_pontos rp ON pt.Id = rp.Id_PontosTuristicos
                WHERE rp.Id_Roteiro = ?
                ORDER BY rp.Id_PontosTuristicos";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $pontos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        return $pontos;
    }
    

    public function deletar() {
        $sql = "DELETE FROM roteiro WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        return mysqli_stmt_execute($stmt);
    }
    
    public static function listarPontosTuristicos($conn) {
        $sql = "SELECT * FROM pontosturisticos ORDER BY Nome";
        $result = mysqli_query($conn, $sql);
        
        $pontos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        return $pontos;
    }
    
    public static function buscarPorUsuario($conn, $usuarioId) {
        $sql = "SELECT r.*, 
                COUNT(rp.Id_PontosTuristicos) as Total_Pontos,
                AVG(a.Nota) as Media_Avaliacoes
                FROM roteiro r
                LEFT JOIN roteiro_pontos rp ON r.Id = rp.Id_Roteiro
                LEFT JOIN avaliacao a ON r.Id = a.Roteiro_id
                WHERE r.Autor = ?
                GROUP BY r.Id
                ORDER BY r.DataCriacao DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $roteiros = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $roteiros[] = $row;
        }
        return $roteiros;
    }
}
?>