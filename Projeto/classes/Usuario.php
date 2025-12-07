<?php

class Usuario {
    private $conn;
    private $id;
    private $nome;
    private $email;
    private $tipo;
    private $telefone;
    private $cpf;
    private $cnpj;
    private $dataNascimento;
    private $bio;
    private $fotoPerfil;
    private $fotoCapa;
    
    public function __construct($conexao) {
        $this->conn = $conexao;
    }
    
    
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getTipo() { return $this->tipo; }
    public function getTelefone() { return $this->telefone; }
    public function getCpf() { return $this->cpf; }
    public function getCnpj() { return $this->cnpj; }
    public function getDataNascimento() { return $this->dataNascimento; }
    public function getBio() { return $this->bio; }
    public function getFotoPerfil() { return $this->fotoPerfil; }
    public function getFotoCapa() { return $this->fotoCapa; }
    
    public function isTurista() { return $this->tipo === 'Turista'; }
    public function isFornecedor() { return $this->tipo === 'Fornecedor'; }
    
    public function buscarPorId($id) {
        error_log("Usuario::buscarPorId - ID: $id");
        
        $sql = "SELECT * FROM usuarios WHERE ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar statement: " . mysqli_error($this->conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $this->id = $row['ID'];
            $this->nome = $row['Nome'];
            $this->email = $row['Email'];
            $this->tipo = $row['Tipo'];
            $this->telefone = $row['Telefone'];
            $this->cpf = $row['CPF'];
            $this->cnpj = $row['CNPJ'];
            $this->dataNascimento = $row['Data_Nascimento'];
            $this->bio = $row['Bio'];
            $this->fotoPerfil = $row['Foto_Perfil'];
            $this->fotoCapa = $row['Foto_Capa'];
            
            error_log("Usuário encontrado: " . $this->nome . " (Tipo: " . $this->tipo . ")");
            return true;
        }
        
        error_log("Usuário não encontrado no banco");
        return false;
    }

    public function atualizarPerfil($dados) {
        error_log("=== Usuario::atualizarPerfil ===");
        error_log("Dados recebidos: " . json_encode($dados));
        error_log("Usuario ID: " . $this->id);
        error_log("Usuario Tipo: " . $this->tipo);

        $campos = [
            'Nome' => $dados['nome'],
            'Telefone' => $dados['telefone'] ?? null,
            'Bio' => $dados['bio'] ?? null,
        ];
        

        if (!empty($dados['dataNascimento'])) {
            $campos['Data_Nascimento'] = $dados['dataNascimento'];
        }
        

        if (!empty($dados['cpf'])) {
            $campos['CPF'] = $dados['cpf'];
        }
        
        if (!empty($dados['fotoPerfil'])) {
            $campos['Foto_Perfil'] = $dados['fotoPerfil'];
        }
        
        if (!empty($dados['fotoCapa'])) {
            $campos['Foto_Capa'] = $dados['fotoCapa'];
        }
        
        if ($this->isFornecedor()) {
            if (!empty($dados['cnpj'])) {
                $campos['CNPJ'] = $dados['cnpj'];
            }
            error_log("Atualizando como FORNECEDOR");
        } else {
            error_log("Atualizando como TURISTA");
        }

        $sqlParts = [];
        $tipos = '';
        $valores = [];

        foreach ($campos as $coluna => $valor) {
            $sqlParts[] = "$coluna = ?";
            $valores[] = $valor;
            $tipos .= 's';
            error_log("Campo: $coluna = $valor");
        }
        
        if (empty($sqlParts)) {
            error_log("AVISO: Nenhum campo para atualizar");
            return true;
        }

        $sql = "UPDATE usuarios SET " . implode(', ', $sqlParts) . " WHERE ID = ?";
        error_log("SQL: $sql");
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("SQL Prepare Error: " . mysqli_error($this->conn));
            return false;
        }

        $valores[] = $this->id;
        $tipos .= 'i';

        $bind_names[] = $tipos;
        for ($i = 0; $i < count($valores); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $valores[$i];
            $bind_names[] = &$$bind_name;
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);

        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            error_log("UPDATE executado com sucesso");
            error_log("Linhas afetadas: " . mysqli_stmt_affected_rows($stmt));
            $this->buscarPorId($this->id);
            return true;
        } else {
            error_log("SQL Execute Error: " . mysqli_error($this->conn));
            error_log("SQL Error Number: " . mysqli_errno($this->conn));
            return false;
        }
    }
    
    public function atualizarSenha($novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET Senha = ? WHERE ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $senhaHash, $this->id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function verificarSenha($senha) {
        $sql = "SELECT Senha FROM usuarios WHERE ID = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return password_verify($senha, $row['Senha']);
        }
        return false;
    }
    
    public function buscarRoteiros() {
        if ($this->isFornecedor()) return [];
        
        if (file_exists(__DIR__ . '/Roteiro.php')) {
            require_once __DIR__ . '/Roteiro.php';
            if (class_exists('Roteiro')) {
                return Roteiro::buscarPorUsuario($this->conn, $this->id);
            }
        }
        return [];
    }
    
    public function buscarLocais() {
        if (!$this->isFornecedor()) return [];
        
        if (file_exists(__DIR__ . '/PontoTuristico.php')) {
            require_once __DIR__ . '/PontoTuristico.php';
            if (class_exists('PontoTuristico')) {
                return PontoTuristico::buscarPorFornecedor($this->conn, $this->id);
            }
        }
        return [];
    }
    
    public function buscarAvaliacoes() {
        if (!$this->isTurista()) return [];
        
        $sql = "SELECT a.*, 
                COALESCE(pt.Nome, r.Nome) as Nome_Avaliado,
                a.Tipo_Avaliado,
                a.DataAvaliacao
                FROM avaliacao a
                LEFT JOIN pontosturisticos pt ON a.PontosTuristicos_id = pt.Id
                LEFT JOIN roteiro r ON a.Roteiro_id = r.Id
                WHERE a.Avaliador = ?
                ORDER BY a.DataAvaliacao DESC";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("SQL Prepare Error (buscarAvaliacoes): " . mysqli_error($this->conn));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $avaliacoes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $avaliacoes[] = $row;
        }
        return $avaliacoes;
    }
}
?>