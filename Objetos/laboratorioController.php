<?php
include_once "../configs/database.php";
include_once "laboratorio.php";

class laboratorioController{
    private $bd;
    private $laboratorio;

    public function __construct(){
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->laboratorio = new Laboratorio($this->bd);
    }

    public function index(){
        return $this->laboratorio->lerTodos();
    }

    public function pesquisarLaboratorio($cnpj){
        return $this->laboratorio->buscar($cnpj);
    }

    public function cadastrarLaboratorio($dados){
        $this->laboratorio->nome      = $dados['nome']      ?? '';
        $this->laboratorio->cnpj      = $dados['cnpj']      ?? '';
        $this->laboratorio->telefone  = $dados['telefone']  ?? '';
        $this->laboratorio->email     = $dados['email']     ?? '';
        $this->laboratorio->numerolab = $dados['numerolab'] ?? 0;
        $this->laboratorio->cep       = $dados['cep']       ?? '';


        if($this->laboratorio->cnpjExiste($this->laboratorio->cnpj)){
            if($this->laboratorio->reativar()){
                header("location: index.php");
                exit();
            }
        } else {
            if($this->laboratorio->cadastrar()){
                header("location: index.php");
                exit();
            }
        }
    }

    public function atualizarLaboratorio($dados){
        $this->laboratorio->nome      = $dados['Nome_Lab']     ?? '';
        $this->laboratorio->cnpj      = $dados['CNPJ_Lab']     ?? '';
        $this->laboratorio->telefone  = $dados['Telefone_Lab'] ?? '';
        $this->laboratorio->email     = $dados['Email_Lab']    ?? '';
        $this->laboratorio->numerolab = $dados['Num_Lab']      ?? 0;
        $this->laboratorio->cep       = $dados['Cep_Lab']      ?? '';

        if($this->laboratorio->atualizar()){
            header("location: index.php");
            exit();
        }
    }

public function localizarLaboratorio($cnpj){
        return $this->laboratorio->buscar($cnpj);
}

    public function excluirLaboratorio($cnpj){
        if($this->laboratorio->excluir($cnpj)){
            header("location: index.php");
            exit();
        }else{
            echo"Erro ao excluir";
        }
    }

}