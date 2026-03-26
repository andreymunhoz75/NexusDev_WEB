<?php
include_once "../configs/database.php";
include_once "medicamento.php";

class medicamentoController{
    private $bd;
    private $medicamento;

    public function __construct(){
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->medicamento = new medicamento($this->bd);
    }

    public function index(){
        return $this->medicamento->lerTodos();
    }

    public function pesquisarMedicamento($cod_Med){
        return $this->medicamento->buscarMedicamento($cod_Med);
    }

    public function cadastrarMedicamento($dados){
        if(
            empty($dados['Nome_Med']) ||
            empty($dados['Desc_Med']) ||
            empty($dados['DataVal_Med']) ||
            empty($dados['Qtd_Med']) ||
            empty($dados['Valor_Med'])
        ){
            echo "Preencha todos os campos obrigatórios.";
            return;
        }

        $this->medicamento->Nome         = $dados['Nome_Med'];
        $this->medicamento->Descricao    = $dados['Desc_Med'];
        $this->medicamento->DataValidade = $dados['DataVal_Med'];
        $this->medicamento->Quantidade   = $dados['Qtd_Med'];
        $this->medicamento->Valor        = $dados['Valor_Med'];
        $this->medicamento->CodCategoria = $dados['Cod_CatMed'] ?? null;

        if($this->medicamento->cadastrar()){
            header("location: index.php");
            exit();
        }
    }

    public function localizarMedicamento($cod_Med){
        return $this->medicamento->buscarMedicamento($cod_Med);
    }

    public function atualizarMedicamento($dados){
        $this->medicamento->Codigo       = $dados['Cod_Med'] ?? '';
        $this->medicamento->Nome         = $dados['Nome_Med'] ?? '';
        $this->medicamento->Descricao    = $dados['Desc_Med'] ?? '';
        $this->medicamento->DataValidade = $dados['DataVal_Med'] ?? '';
        $this->medicamento->Quantidade   = $dados['Qtd_Med'] ?? 0;
        $this->medicamento->Valor        = $dados['Valor_Med'] ?? '';

        if($this->medicamento->atualizar()){
            header("location: index.php");
            exit();
        }
    }

    public function excluirMedicamento($cod_Med){
        if($this->medicamento->excluir($cod_Med)){
            header("location: index.php");
            exit();
        }
    }
}