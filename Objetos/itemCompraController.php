<?php
include_once __DIR__ . "/../configs/database.php";
include_once __DIR__ . "/itemCompra.php";

class ItemCompraController {
    private $bd;
    private $item;

    public function __construct() {
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->item = new ItemCompra($this->bd);
    }

    public function cadastrarItemCompra($dados) {
        $this->item->DataVal_Item       = $dados['DataVal_Item'];
        $this->item->Qtd_Item           = $dados['Qtd_Item'];
        $this->item->Valor_Item         = $dados['Valor_Item'];
        $this->item->Data_Venda         = $dados['Data_Venda'];
        $this->item->NotaFiscal_Entrada = $dados['NotaFiscal_Entrada'];
        $this->item->Cod_CatMed         = $dados['Cod_CatMed'];
        $this->item->Cod_Med            = $dados['Cod_Med'] ?? null;

        return $this->item->cadastrar();
    }

    public function lerPorNotaFiscal($notaFiscal) {
        return $this->item->lerPorNotaFiscal($notaFiscal);
    }

    public function excluirItem($cod_item) {
        return $this->item->excluir($cod_item);
    }

    public function atualizarCodMed($cod_item, $cod_med) {
        return $this->item->atualizarCodMed($cod_item, $cod_med);
    }

    public function atualizarQtd($cod_item, $nova_qtd) {
        return $this->item->atualizarQuantidade($cod_item, $nova_qtd);
    }

    /**
     * Calcula o total dos itens de uma compra diretamente do banco.
     */
    public function calcularTotal($NotaFiscal_Entrada) {
        $sql = "SELECT COALESCE(SUM(Qtd_Item * Valor_Item), 0) AS total 
                FROM item WHERE NotaFiscal_Entrada = :nota";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nota", $NotaFiscal_Entrada, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

