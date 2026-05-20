<?php

class ItemCompra {
    public $Cod_Item;
    public $DataVal_Item;
    public $Qtd_Item;
    public $Valor_Item;
    public $Data_Venda;
    public $NotaFiscal_Entrada;
    public $Cod_CatMed;
    public $Cod_Med;

    private $bd;

    public function __construct($bd) {
        $this->bd = $bd;
    }

    public function cadastrar() {
        $sql = "INSERT INTO item (DataVal_Item, Qtd_Item, Valor_Item, Data_Venda, NotaFiscal_Entrada, Cod_CatMed, Cod_Med) 
                VALUES (:dataVal, :qtd, :valor, :dataVenda, :notaFiscal, :codCatMed, :codMed)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":dataVal",    $this->DataVal_Item,        PDO::PARAM_STR);
        $stmt->bindParam(":qtd",        $this->Qtd_Item,            PDO::PARAM_INT);
        $stmt->bindParam(":valor",      $this->Valor_Item,          PDO::PARAM_STR);
        $stmt->bindParam(":dataVenda",  $this->Data_Venda,          PDO::PARAM_STR);
        $stmt->bindParam(":notaFiscal", $this->NotaFiscal_Entrada,  PDO::PARAM_INT);
        $stmt->bindParam(":codCatMed",  $this->Cod_CatMed,          PDO::PARAM_INT);
        if ($this->Cod_Med === null) {
            $stmt->bindValue(":codMed", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":codMed", $this->Cod_Med, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function lerPorNotaFiscal($notaFiscal) {
        $sql = "SELECT i.*, cm.Nome_CatMed AS Nome_Med, cm.Nome_CatMed, cm.EAN_Med, cm.dataValItemCat, cm.Desc_CatMed, cm.quantidade
                FROM item i 
                JOIN catalogo_medicamento cm ON i.Cod_CatMed = cm.Cod_CatMed
                WHERE i.NotaFiscal_Entrada = :notaFiscal";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":notaFiscal", $notaFiscal, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function excluir($Cod_Item) {
        $sql = "DELETE FROM item WHERE Cod_Item = :Cod_Item";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":Cod_Item", $Cod_Item, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function atualizarCodMed($Cod_Item, $Cod_Med) {
        $sql = "UPDATE item SET Cod_Med = :Cod_Med WHERE Cod_Item = :Cod_Item";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":Cod_Med", $Cod_Med, PDO::PARAM_INT);
        $stmt->bindParam(":Cod_Item", $Cod_Item, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function atualizarQuantidade($Cod_Item, $Nova_Qtd) {
        $sql = "UPDATE item SET Qtd_Item = :qtd WHERE Cod_Item = :cod";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":qtd", $Nova_Qtd, PDO::PARAM_INT);
        $stmt->bindParam(":cod", $Cod_Item, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

