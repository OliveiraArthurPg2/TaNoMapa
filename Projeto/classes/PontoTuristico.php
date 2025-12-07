<?php
class PontoTuristico {
    private $conn;
    private $id;
    private $tipo;
    private $nome;
    private $fotoPerfil;
    private $fotoCapa;
    private $localidade;
    private $endereco;
    private $bio;
    private $fornecedor;
    
    public function __construct($conexao) {
        $this->conn = $conexao;
    }
    
    public function getId() { return $this->id; }
    public function getTipo() { return $this->tipo; }
    public function getNome() { return $this->nome; }
    public function getFotoPerfil() { return $this->fotoPerfil; }
    public function getFotoCapa() { return $this->fotoCapa; }
    public function getLocalidade() { return $this->localidade; }
    public function getEndereco() { return $this->endereco; }
    public function getBio() { return $this->bio; }
    public function getFornecedor() { return $this->fornecedor; }
    
    public function criar($dados) {
        $sql = "INSERT INTO pontosturisticos (Tipo, Nome, Foto_Perfil, Foto_Capa, Localidade, Endereco, Bio, Fornecedor) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", 
            $dados['tipo'], 
            $dados['nome'], 
            $dados['fotoPerfil'],
            $dados['fotoCapa'],
            $dados['localidade'],
            $dados['endereco'],
            $dados['bio'],
            $dados['fornecedor']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->id = mysqli_insert_id($this->conn);
            return $this->id;
        }
        return false;
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT * FROM pontosturisticos WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $this->id = $row['Id'];
            $this->tipo = $row['Tipo'];
            $this->nome = $row['Nome'];
            $this->fotoPerfil = $row['Foto_Perfil'];
            $this->fotoCapa = $row['Foto_Capa'];
            $this->localidade = $row['Localidade'];
            $this->endereco = $row['Endereco'];
            $this->bio = $row['Bio'];
            $this->fornecedor = $row['Fornecedor'];
            return true;
        }
        return false;
    }
    
    public function deletar() {
        if ($this->fotoPerfil && file_exists('../' . $this->fotoPerfil)) {
            unlink('../' . $this->fotoPerfil);
        }
        if ($this->fotoCapa && file_exists('../' . $this->fotoCapa)) {
            unlink('../' . $this->fotoCapa);
        }
        
        $sql = "DELETE FROM pontosturisticos WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        return mysqli_stmt_execute($stmt);
    }
    
    public static function buscarPorFornecedor($conn, $fornecedorId) {
        $sql = "SELECT pt.*, 
                AVG(a.Nota) as Avaliacao,
                COUNT(a.Id) as Total_Avaliacoes,
                u.Nome as NomeFornecedor,
                u.Foto_Perfil as FotoFornecedor,
                u.ID as IdFornecedor
                FROM pontosturisticos pt
                LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                WHERE pt.Fornecedor = ?
                GROUP BY pt.Id
                ORDER BY pt.DataCadastro DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $fornecedorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $pontos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        return $pontos;
    }
    
    public static function listarPorTipo($conn, $tipo = null) {
        if ($tipo) {
            $sql = "SELECT pt.*, 
                    AVG(a.Nota) as Avaliacao,
                    COUNT(a.Id) as Total_Avaliacoes,
                    u.Nome as NomeFornecedor,
                    u.Foto_Perfil as FotoFornecedor,
                    u.ID as IdFornecedor
                    FROM pontosturisticos pt
                    LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                    LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                    WHERE pt.Tipo = ?
                    GROUP BY pt.Id
                    ORDER BY pt.DataCadastro DESC";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $tipo);
        } else {
            $sql = "SELECT pt.*, 
                    AVG(a.Nota) as Avaliacao,
                    COUNT(a.Id) as Total_Avaliacoes,
                    u.Nome as NomeFornecedor,
                    u.Foto_Perfil as FotoFornecedor,
                    u.ID as IdFornecedor
                    FROM pontosturisticos pt
                    LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                    LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                    GROUP BY pt.Id
                    ORDER BY pt.DataCadastro DESC";
            $stmt = mysqli_prepare($conn, $sql);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $pontos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        return $pontos;
    }
    
    public static function buscarTop5($conn) {
        $sql = "SELECT pt.*, 
                AVG(a.Nota) as Avaliacao,
                COUNT(a.Id) as Total_Avaliacoes,
                u.Nome as NomeFornecedor,
                u.Foto_Perfil as FotoFornecedor,
                u.ID as IdFornecedor
                FROM pontosturisticos pt
                LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                GROUP BY pt.Id
                HAVING AVG(a.Nota) IS NOT NULL
                ORDER BY Avaliacao DESC, Total_Avaliacoes DESC
                LIMIT 5";
        
        $result = mysqli_query($conn, $sql);
        
        $pontos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        return $pontos;
    }
    
    public static function buscarTop5ComEmpate($conn) {
        $sql = "SELECT pt.*, 
                AVG(a.Nota) as Avaliacao,
                COUNT(a.Id) as Total_Avaliacoes,
                u.Nome as NomeFornecedor,
                u.Foto_Perfil as FotoFornecedor,
                u.ID as IdFornecedor
                FROM pontosturisticos pt
                LEFT JOIN avaliacao a ON pt.Id = a.PontosTuristicos_id
                LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                GROUP BY pt.Id
                HAVING AVG(a.Nota) IS NOT NULL
                ORDER BY Avaliacao DESC, Total_Avaliacoes DESC
                LIMIT 5";
        
        $result = mysqli_query($conn, $sql);
        $pontos = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $pontos[] = $row;
        }
        
        if (count($pontos) < 5) {
            $sql2 = "SELECT pt.*, 
                    0 as Avaliacao,
                    0 as Total_Avaliacoes,
                    u.Nome as NomeFornecedor,
                    u.Foto_Perfil as FotoFornecedor,
                    u.ID as IdFornecedor
                    FROM pontosturisticos pt
                    LEFT JOIN usuarios u ON pt.Fornecedor = u.ID
                    ORDER BY pt.DataCadastro DESC
                    LIMIT 5";
            
            $result2 = mysqli_query($conn, $sql2);
            $pontos = [];
            
            while ($row = mysqli_fetch_assoc($result2)) {
                $pontos[] = $row;
            }
        }
        
        return $pontos;
    }
}
?>