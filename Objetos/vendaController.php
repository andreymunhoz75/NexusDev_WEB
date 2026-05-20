<?php

include_once __DIR__ . "/../configs/database.php";
include_once __DIR__ . "/venda.php";

class VendaController
{
    private $bd;
    private $venda;

    public function __construct()
    {
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->venda = new Venda($this->bd);
    }

    public function index()
    {
        return $this->venda->lerTodos();
    }
    
    public function listarPorStatus($status)
    {
        return $this->venda->lerPorStatus($status);
    }

    public function filtrarVendas($nf, $status, $cnpj, $data_inicio = null, $data_fim = null)
    {
        return $this->venda->filtrar($nf, $status, $cnpj, $data_inicio, $data_fim);
    }

    public function pesquisaVenda($NotaFiscal_Saida)
    {
        return $this->venda->pesquisarVenda($NotaFiscal_Saida);
    }

    public function iniciarVenda($cpf)
    {
        $this->venda->CPF = $cpf;
        return $this->venda->iniciarRetornandoId();
    }

    /**
     * Salva o estado atual da venda (itens, CNPJ, valor) sem finalizar.
     * Nenhum trigger de estoque é disparado.
     */
    public function salvarRascunhoVenda($notaFiscal, $valorTotal, $cnpj)
    {
        if(empty($cnpj)) $cnpj = null;
        return $this->venda->salvarRascunho($notaFiscal, $valorTotal, $cnpj);
    }

    /**
     * Finaliza a venda: atualiza valor/CNPJ e seta Finalizada=1.
     * O trigger trg_finalizar_venda dispara e decrementa o estoque.
     */
    public function finalizarVenda($notaFiscal, $valorTotal, $cnpj)
    {
        if (empty($cnpj)) {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['erro_venda'] = 'Erro: Selecione uma drogaria válida para finalizar a venda.';
            return false;
        }

        // Verifica Estoque (Task 7)
        $sql = "SELECT i.Cod_Med, i.Qtd_ItemVenda, m.Nome_Med, m.Qtd_Med 
                FROM item_venda i 
                JOIN medicamento m ON i.Cod_Med = m.Cod_Med 
                WHERE i.NotaFiscal_Saida = :nf AND i.Qtd_ItemVenda > m.Qtd_Med";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nf", $notaFiscal, PDO::PARAM_INT);
        $stmt->execute();
        $semEstoque = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($semEstoque)) {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $msg = "<strong>Estoque insuficiente para os seguintes itens:</strong><ul>";
            foreach ($semEstoque as $item) {
                $msg .= "<li>{$item->Nome_Med} (Solicitado: {$item->Qtd_ItemVenda}, Disponível: {$item->Qtd_Med})</li>";
            }
            $msg .= "</ul>";
            $_SESSION['erro_venda_modal'] = $msg;
            return false;
        }

        // Primeiro salva os dados finais
        $this->venda->salvarRascunho($notaFiscal, $valorTotal, $cnpj);
        // Depois seta Finalizada=1 (dispara o trigger)
        return $this->venda->finalizarStatus($notaFiscal);
    }

    /**
     * Cancela uma venda finalizada, estornando o estoque via trigger.
     */
    public function cancelarVenda($NotaFiscal_Saida)
    {
        // A trigger trg_retorno_estoque_cancelamento no banco cuidará de devolver o estoque
        // se Finalizada for 1. O ON DELETE CASCADE cuidará de remover os itens.
        $this->venda->NotaFiscal_Saida = $NotaFiscal_Saida;
        if ($this->venda->excluir()) {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['venda_sucesso'] = "Venda #$NotaFiscal_Saida cancelada e estoque estornado com sucesso!";
            header("location: index.php");
            exit();
        }
    }

    public function excluirVenda($NotaFiscal_Saida)
    {
        $this->venda->NotaFiscal_Saida = $NotaFiscal_Saida;
        $this->venda->excluir();
        header("location: index.php");
        exit();
    }

    public function atualizarVenda($dados)
    {
        $this->venda->NotaFiscal_Saida = $dados["NotaFiscal_Saida"];
        $this->venda->Data_Venda = $dados["Data_Venda"];
        $this->venda->Valor_Venda = $dados["Valor_Venda"];
        $this->venda->CNPJ_Drog = $dados["CNPJ_Drog"];
        $this->venda->CPF = $dados["CPF"];

        if ($this->venda->atualizar()) {
            header("location: index.php");
            exit();
        }
    }

    public function localizarVenda($NotaFiscal_Saida)
    {
        return $this->venda->buscaVenda($NotaFiscal_Saida);
    }
}

