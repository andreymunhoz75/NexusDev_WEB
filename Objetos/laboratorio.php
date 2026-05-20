<?php
Class laboratorio{
    public $numerolab;
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
        $sql = "INSERT INTO laboratorio(Nome_Lab, Email_Lab, Telefone_Lab, Cep_Lab, Num_Lab, CNPJ_Lab, Foto_Lab) 
        VALUES(:nome, :email, :telefone, :cep, :numerolab, :cnpj, :foto)";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":numerolab", $this->numerolab, PDO::PARAM_INT);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);
        $stmt->bindParam(":foto",      $this->foto,      PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Check if it's a duplicate entry error (SQLSTATE 23000)
            if ($e->getCode() == 23000) {
                // If it's the email duplicate, we can trigger a JS alert and go back
                if (strpos($e->getMessage(), 'Email_Lab') !== false) {
                    echo "<script>alert('Erro: O e-mail informado já está em uso por outro laboratório.'); window.history.back();</script>";
                    exit();
                }
            }
            // For other errors, return false instead of crashing
            return false;
        }
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
        if ($this->foto) {
            $sql = "UPDATE laboratorio 
                SET Nome_Lab=:nome, Num_Lab=:numerolab, Email_Lab=:email, 
                    Telefone_Lab=:telefone, Cep_Lab=:cep, Foto_Lab=:foto 
                WHERE CNPJ_Lab=:cnpj";
        } else {
            $sql = "UPDATE laboratorio 
                SET Nome_Lab=:nome, Num_Lab=:numerolab, Email_Lab=:email, 
                    Telefone_Lab=:telefone, Cep_Lab=:cep 
                WHERE CNPJ_Lab=:cnpj";
        }

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome",      $this->nome,      PDO::PARAM_STR);
        $stmt->bindParam(":numerolab", $this->numerolab, PDO::PARAM_INT);
        $stmt->bindParam(":email",     $this->email,     PDO::PARAM_STR);
        $stmt->bindParam(":telefone",  $this->telefone,  PDO::PARAM_STR);
        $stmt->bindParam(":cep",       $this->cep,       PDO::PARAM_STR);
        $stmt->bindParam(":cnpj",      $this->cnpj,      PDO::PARAM_STR);
        
        if ($this->foto) {
            $stmt->bindParam(":foto",  $this->foto,      PDO::PARAM_STR);
        }

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'Email_Lab') !== false) {
                    echo "<script>alert('Erro: O e-mail informado já está em uso por outro laboratório.'); window.history.back();</script>";
                    exit();
                }
            }
            return false;
        }
    }

    public function buscar($cnpj){
        $sql = "SELECT * FROM laboratorio WHERE CNPJ_Lab = :cnpj";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $resultado->execute();
        return $resultado->fetch(PDO::FETCH_ASSOC);
    }


    public function cnpjExiste($cnpj){
        $sql = "SELECT COUNT(*) FROM laboratorio WHERE CNPJ_Lab = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function reativar($cnpj){
        $sql = "UPDATE laboratorio SET Ativo_Lab = 1 WHERE CNPJ_Lab = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);

        if($stmt->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function lerExcluidos(){
        $sql = "SELECT * FROM laboratorio WHERE Ativo_Lab = 0";
        $resultado = $this->bd->query($sql);
        return $resultado->fetchAll(PDO::FETCH_OBJ);
    }
}

