<?php
Class laboratorio{
    public $numerolab;
    public $nome;
    public $email;
    public $telefone;
    public $cnpj;
    public $cep;
    private $bd;

public function __construct($bd){
    $this->bd = $bd;
}

public function lerTodos(){
        $sql = "SELECT * FROM laboratorio WHERE Ativo_Lab = 1";
        $resultado = $this->bd->query($sql);
        $resultado->execute();

        return $resultado->fetchAll(PDO::FETCH_OBJ);
}

public function pesquisarLaboratorio($cnpj){
        $sql = "SELECT * FROM laboratorio WHERE cnpj = ?";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":cnpj", $cnpj);
        $resultado->execute([$cnpj]);
}


    public function cadastrar(){
        $sql = "INSERT INTO laboratorio(Nome_Lab, Email_Lab, Telefone_Lab, Cep_Lab, Num_Lab, CNPJ_Lab) 
        VALUES(:nome, :email, :telefone, :cep, :numerolab, :cnpj)";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":numerolab", $this->numerolab, PDO::PARAM_INT);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);

        return $stmt->execute();
    }

public function excluir($cnpj){
    $sql = "UPDATE laboratorio SET Ativo_Lab = 0 WHERE CNPJ_Lab = :cnpj";
    $stmt = $this->bd->prepare($sql);
    $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);

    if($stmt->execute()){
        return true;
    } else {
        return false;
    }
}

    public function atualizar(){
        $sql = "UPDATE laboratorio 
            SET Nome_Lab=:nome, Num_Lab=:numerolab, Email_Lab=:email, 
                Telefone_Lab=:telefone, Cep_Lab=:cep 
            WHERE CNPJ_Lab=:cnpj";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":numerolab", $this->numerolab, PDO::PARAM_INT);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function buscar($cnpj){
        $sql = "SELECT * FROM laboratorio WHERE CNPJ_Lab = :cnpj";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $resultado->execute();
        return $resultado->fetch(PDO::FETCH_ASSOC);
    }

    // ADICIONE ESTES DOIS MÉTODOS AQUI:

    public function cnpjExiste($cnpj){
        $sql = "SELECT COUNT(*) FROM laboratorio WHERE CNPJ_Lab = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function reativar(){
        $sql = "UPDATE laboratorio SET Ativo_Lab = 1, Nome_Lab=:nome, Email_Lab=:email,
                Telefone_Lab=:telefone, Cep_Lab=:cep, Num_Lab=:numerolab
                WHERE CNPJ_Lab=:cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":numerolab", $this->numerolab, PDO::PARAM_INT);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);
        return $stmt->execute();
    }
    // <- fechamento da classe
}