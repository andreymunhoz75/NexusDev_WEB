<?php

class medicamento {

    public $Nome;
    public $Codigo;
    public $Descricao;
    public $DataValidade;
    public $Quantidade;
    public $Valor;
    public $CodCategoria;

    private $bd;

    public function __construct($bd) {
        $this->bd = $bd;
    }

    public function lerTodos() {
        $sql = "SELECT * FROM medicamento";
        $stmt = $this->bd->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarMedicamento($codMed) {
        $sql = "SELECT * FROM medicamento WHERE Cod_Med = :codMed";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":codMed", $codMed, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function cadastrar() {
        $sql = "INSERT INTO medicamento (Nome_Med, Desc_Med, DataVal_Med, Qtd_Med, Valor_Med, Cod_CatMed) 
                VALUES (:nomeMed, :descMed, :dataVal, :qtdMed, :valorMed, :codCat)";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nomeMed",  $this->Nome,         PDO::PARAM_STR);
        $stmt->bindParam(":descMed",  $this->Descricao,    PDO::PARAM_STR);
        $stmt->bindParam(":dataVal",  $this->DataValidade,  PDO::PARAM_STR);
        $stmt->bindParam(":qtdMed",   $this->Quantidade,   PDO::PARAM_INT);
        $stmt->bindParam(":valorMed", $this->Valor,        PDO::PARAM_STR);
        $stmt->bindParam(":codCat",   $this->CodCategoria, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function atualizar() {
        $sql = "UPDATE medicamento 
                SET Nome_Med = :nomeMed, Desc_Med = :descMed, 
                    DataVal_Med = :dataVal, Qtd_Med = :qtdMed, 
                    Valor_Med = :valorMed, Cod_CatMed = :codCat
                WHERE Cod_Med = :codMed";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":codMed",   $this->Codigo,       PDO::PARAM_INT);
        $stmt->bindParam(":nomeMed",  $this->Nome,         PDO::PARAM_STR);
        $stmt->bindParam(":descMed",  $this->Descricao,    PDO::PARAM_STR);
        $stmt->bindParam(":dataVal",  $this->DataValidade,  PDO::PARAM_STR);
        $stmt->bindParam(":qtdMed",   $this->Quantidade,   PDO::PARAM_INT);
        $stmt->bindParam(":valorMed", $this->Valor,        PDO::PARAM_STR);
        $stmt->bindParam(":codCat",   $this->CodCategoria, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluir($codMed) {
        $sql = "DELETE FROM medicamento WHERE Cod_Med = :codMed";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":codMed", $codMed, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function codMedExiste($codMed) {
        $sql = "SELECT COUNT(*) FROM medicamento WHERE Cod_Med = :codMed";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":codMed", $codMed, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}