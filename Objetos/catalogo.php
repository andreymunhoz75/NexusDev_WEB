<?php

class Catalogo {
    public $Cod_CatMed;
    public $EAN_Med;
    public $Nome_CatMed;
    public $Desc_CatMed;
    public $Valor_CatMed;
    public $datacompraItemCat;
    public $dataValItemCat;
    public $quantidade;
    public $CNPJ_Lab;

    private $bd;

    public function __construct($bd) {
        $this->bd = $bd;
    }

    public function lerPorCnpj($cnpj) {
        $sql = "SELECT * FROM catalogo_medicamento WHERE CNPJ_Lab = :cnpj";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function atualizarQuantidade($cod_catMed, $quantidadeDeduza) {
        // quantidade no bd diminui pela quantidade comprada
        // A lógica do Java era: catalogoDAO.atualizarQuantidade(item.getCodCatMedItem(), item.getQuantidadeItem()); e diz "Atualiza a quantidade no catálogo"
        $sql = "UPDATE catalogo_medicamento SET quantidade = quantidade - :qtd WHERE Cod_CatMed = :codCatMed";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":qtd", $quantidadeDeduza, PDO::PARAM_INT);
        $stmt->bindParam(":codCatMed", $cod_catMed, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function buscarCatalogoPorCod($cod_catMed){
        $sql = "SELECT * FROM catalogo_medicamento WHERE Cod_CatMed = :codCatMed";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":codCatMed", $cod_catMed, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function pesquisarPorTermo($cnpj, $termo) {
        $sql = "SELECT * FROM catalogo_medicamento 
                WHERE CNPJ_Lab = :cnpj 
                AND (Nome_CatMed LIKE :termo OR EAN_Med LIKE :termo)";
        $stmt = $this->bd->prepare($sql);
        $likeTermo = "%$termo%";
        $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
        $stmt->bindParam(":termo", $likeTermo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

