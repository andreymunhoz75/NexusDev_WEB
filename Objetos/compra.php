<?php

class Compra
{
    public $NotaFiscal_Entrada;
    public $Valor_Total;
    public $Data_Compra;
    public $CPF;
    public $CNPJ_Lab;
    public $Finalizada;
    public $bd;

    public function __construct($bd)
    {
        $this->bd = $bd;
    }

    public function lerTodos()
    {
        $sql = "SELECT c.*, l.Nome_Lab
                FROM compra c
                LEFT JOIN laboratorio l ON c.CNPJ_Lab = l.CNPJ_Lab
                ORDER BY c.NotaFiscal_Entrada DESC";
        $resultado = $this->bd->query($sql);

        return $resultado->fetchAll(PDO::FETCH_OBJ);
    }

    public function filtrar($nf, $cnpj_lab, $status, $data_inicio, $data_fim)
    {
        $sql = "SELECT c.*, l.Nome_Lab
                FROM compra c
                LEFT JOIN laboratorio l ON c.CNPJ_Lab = l.CNPJ_Lab
                WHERE 1=1";

        if ($nf)                              $sql .= " AND c.NotaFiscal_Entrada = :nf";
        if ($cnpj_lab)                        $sql .= " AND c.CNPJ_Lab = :cnpj_lab";
        if ($status !== '' && $status !== null) $sql .= " AND c.Finalizada = :status";
        if ($data_inicio)                     $sql .= " AND c.Data_Compra >= :data_inicio";
        if ($data_fim)                        $sql .= " AND c.Data_Compra <= :data_fim";

        $sql .= " ORDER BY c.NotaFiscal_Entrada DESC";

        $stmt = $this->bd->prepare($sql);
        if ($nf)                              $stmt->bindParam(':nf',          $nf,          PDO::PARAM_INT);
        if ($cnpj_lab)                        $stmt->bindParam(':cnpj_lab',    $cnpj_lab,    PDO::PARAM_STR);
        if ($status !== '' && $status !== null) $stmt->bindParam(':status',   $status,      PDO::PARAM_INT);
        if ($data_inicio)                     $stmt->bindParam(':data_inicio', $data_inicio, PDO::PARAM_STR);
        if ($data_fim)                        $stmt->bindParam(':data_fim',    $data_fim,    PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function pesquisarCompra($NotaFiscal_Entrada)
    {
        $sql = "SELECT * FROM compra WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":NotaFiscal_Entrada", $NotaFiscal_Entrada);
        $resultado->execute();

        return $resultado->fetch(PDO::FETCH_OBJ);
    }

    public function cadastrar()
    {
        $sql = "INSERT INTO compra (Valor_Total, Data_Compra, CPF, CNPJ_Lab)
                VALUES (:Valor_Total, :Data_Compra, :CPF, :CNPJ_Lab)";

        $stmt = $this->bd->prepare($sql);

        $stmt->bindParam(":Valor_Total", $this->Valor_Total);
        $stmt->bindParam(":Data_Compra", $this->Data_Compra, PDO::PARAM_STR);
        $stmt->bindParam(":CPF", $this->CPF, PDO::PARAM_STR);
        $stmt->bindParam(":CNPJ_Lab", $this->CNPJ_Lab, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function cadastrarEretornarId()
    {
        $sql = "INSERT INTO compra (Valor_Total, CPF, CNPJ_Lab)
                VALUES (0, :CPF, :CNPJ_Lab)";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":CPF", $this->CPF, PDO::PARAM_STR);
        
        if ($this->CNPJ_Lab === null || $this->CNPJ_Lab === '') {
            $stmt->bindValue(":CNPJ_Lab", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":CNPJ_Lab", $this->CNPJ_Lab, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return $this->bd->lastInsertId();
        } else {
            return null;
        }
    }

    public function atualizarValorTotal($notaFiscalCorreta, $valorTotal)
    {
        $sql = "UPDATE compra SET Valor_Total = :Valor_Total WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":Valor_Total", $valorTotal);
        $stmt->bindParam(":NotaFiscal_Entrada", $notaFiscalCorreta, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function excluir()
    {
        $sql = "DELETE FROM compra WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":NotaFiscal_Entrada", $this->NotaFiscal_Entrada, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function atualizar(){
        $sql = "UPDATE compra SET Valor_Total = :Valor_Total, Data_Compra = :Data_Compra, CPF = :CPF, CNPJ_Lab = :CNPJ_Lab WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";

        $stmt = $this->bd->prepare($sql);

        $stmt->bindParam(":Valor_Total", $this->Valor_Total);
        $stmt->bindParam(":Data_Compra", $this->Data_Compra, PDO::PARAM_STR);
        $stmt->bindParam(":CPF", $this->CPF, PDO::PARAM_STR);
        $stmt->bindParam(":CNPJ_Lab", $this->CNPJ_Lab, PDO::PARAM_STR);
        $stmt->bindParam(":NotaFiscal_Entrada", $this->NotaFiscal_Entrada, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function buscarCompra($NotaFiscal_Entrada)
    {
        $sql = "SELECT * FROM compra WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":NotaFiscal_Entrada", $NotaFiscal_Entrada);
        $resultado->execute();

        return $resultado->fetch(PDO::FETCH_OBJ);
    }

    public function finalizarStatus($NotaFiscal_Entrada)
    {
        $sql = "UPDATE compra SET Finalizada = 1 WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":NotaFiscal_Entrada", $NotaFiscal_Entrada, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function salvarRascunho($NotaFiscal_Entrada, $Valor_Total)
    {
        $sql = "UPDATE compra SET Valor_Total = :Valor_Total WHERE NotaFiscal_Entrada = :NotaFiscal_Entrada";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":Valor_Total", $Valor_Total);
        $stmt->bindParam(":NotaFiscal_Entrada", $NotaFiscal_Entrada, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

