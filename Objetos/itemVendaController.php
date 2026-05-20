<?php

include_once __DIR__ . "/../configs/database.php";
include_once __DIR__ . "/itemVenda.php";

class ItemVendaController
{
    private $bd;
    private $itemVenda;

    public function __construct()
    {
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->itemVenda = new ItemVenda($this->bd);
    }

    public function index()
    {
        return $this->itemVenda->lerTodos();
    }

    public function localizarItemVenda($NotaFiscal_Saida)
    {
        return $this->itemVenda->buscaItemVenda($NotaFiscal_Saida);
    }

    public function cadastrarItemVenda($dados)
    {
        $this->itemVenda->DataVal_ItemVenda = $dados["DataVal_ItemVenda"] ?? null;
        $this->itemVenda->Qtd_ItemVenda     = $dados["Qtd_ItemVenda"];
        $this->itemVenda->Valor_ItemVenda   = $dados["Valor_ItemVenda"];
        $this->itemVenda->Cod_Med           = $dados["Cod_Med"];
        $this->itemVenda->NotaFiscal_Saida  = $dados["NotaFiscal_Saida"];

        return $this->itemVenda->cadastrarItemVenda();
    }

    public function excluirItem($cod_item)
    {
        $this->itemVenda->Cod_ItemVenda = $cod_item;
        return $this->itemVenda->excluirItemVenda();
    }

    public function atualizarQtd($cod_item, $nova_qtd)
    {
        $this->itemVenda->Cod_ItemVenda = $cod_item;
        $this->itemVenda->Qtd_ItemVenda = $nova_qtd;
        return $this->itemVenda->atualizarQuantidade();
    }

    /**
     * Calcula o total dos itens de uma venda diretamente do banco.
     */
    public function calcularTotal($NotaFiscal_Saida)
    {
        $sql = "SELECT COALESCE(SUM(Qtd_ItemVenda * Valor_ItemVenda), 0) AS total 
                FROM item_venda WHERE NotaFiscal_Saida = :nota";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nota", $NotaFiscal_Saida, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

