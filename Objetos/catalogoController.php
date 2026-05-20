<?php
include_once __DIR__ . "/../configs/database.php";
include_once __DIR__ . "/catalogo.php";

class CatalogoController {
    private $bd;
    private $catalogo;

    public function __construct() {
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->catalogo = new Catalogo($this->bd);
    }

    public function lerPorCnpj($cnpj) {
        return $this->catalogo->lerPorCnpj($cnpj);
    }

    public function buscarCatalogo($cod_catMed){
        return $this->catalogo->buscarCatalogoPorCod($cod_catMed);
    }

    public function atualizarQuantidade($cod_catMed, $quantidadeDeduza) {
        return $this->catalogo->atualizarQuantidade($cod_catMed, $quantidadeDeduza);
    }

    public function pesquisarPorTermo($cnpj, $termo){
        return $this->catalogo->pesquisarPorTermo($cnpj, $termo);
    }
}

