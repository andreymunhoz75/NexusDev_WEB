<?php
Class drogaria{
    public $numerodrog;
    public $nome;
    public $email;
    public $telefone;
    public $cnpj;
    public $cep;
    public $foto;
    private $bd;

public function __construct($bd){
    $this->bd = $bd;
}

public function lerTodos(){
        $sql = "SELECT * FROM drogaria WHERE Ativo_Drog = 1";
        $resultado = $this->bd->query($sql);
        $resultado->execute();

        return $resultado->fetchAll(PDO::FETCH_OBJ);
}

public function pesquisarDrogaria($cnpj){
        $sql = "SELECT * FROM drogaria WHERE cnpj = ?";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":cnpj", $cnpj);
        $resultado->execute([$cnpj]);
}

    public function pesquisarGeral($termo)
    {
        $busca = "%$termo%";
        $valorNumerico = preg_replace('/[^0-9]/', '', $termo);
        
        if (!empty($valorNumerico)) {
            $buscaNumerica = "%$valorNumerico%";
            $sql = "SELECT * FROM drogaria WHERE (Nome_Drog LIKE :busca OR REPLACE(REPLACE(REPLACE(CNPJ_Drog, '.', ''), '/', ''), '-', '') LIKE :buscaNumerica) AND Ativo_Drog = 1";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_STR);
            $stmt->bindParam(':buscaNumerica', $buscaNumerica, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM drogaria WHERE Nome_Drog LIKE :busca AND Ativo_Drog = 1";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    public function cadastrar(){
        $sql = "INSERT INTO drogaria(Nome_Drog, Email_Drog, Telefone_Drog, Cep_Drog, Num_Drog, CNPJ_Drog, Foto_Drog) 
        VALUES(:nome, :email, :telefone, :cep, :numerodrog, :cnpj, :foto)";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":numerodrog", $this->numerodrog, PDO::PARAM_INT);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);
        $stmt->bindParam(":foto",      $this->foto,      PDO::PARAM_STR);

        return $stmt->execute();
    }

public function excluir($cnpj){
    $sql = "UPDATE drogaria SET Ativo_Drog = 0 WHERE CNPJ_Drog = :cnpj";
    $stmt = $this->bd->prepare($sql);
    $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);

    if($stmt->execute()){
        return true;
    } else {
        return false;
    }
}

    public function atualizar(){
        $sql = "UPDATE drogaria 
            SET Nome_Drog=:nome, Num_Drog=:numerodrog, Email_Drog=:email, 
                Telefone_Drog=:telefone, Cep_Drog=:cep, Foto_Drog=:foto 
            WHERE CNPJ_Drog=:cnpj";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":numerodrog", $this->numerodrog, PDO::PARAM_INT);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);
        $stmt->bindParam(":foto",      $this->foto,      PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function buscar($cnpj){
        $sql = "SELECT * FROM drogaria WHERE CNPJ_Drog = :cnpj";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $resultado->execute();
        return $resultado->fetch(PDO::FETCH_ASSOC);
    }


    public function cnpjExiste($cnpj){
        $sql = "SELECT COUNT(*) FROM drogaria WHERE CNPJ_Drog = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function reativar($cnpj){
        $sql = "UPDATE drogaria SET Ativo_Drog = 1 WHERE CNPJ_Drog = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);

        if($stmt->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function lerExcluidos(){
        $sql = "SELECT * FROM drogaria WHERE Ativo_Drog = 0";
        $resultado = $this->bd->query($sql);
        return $resultado->fetchAll(PDO::FETCH_OBJ);
    }
}

