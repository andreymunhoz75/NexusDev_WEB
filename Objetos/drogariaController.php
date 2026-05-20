<?php
include_once "../configs/database.php";
include_once "drogaria.php";

class drogariaController{
    private $bd;
    private $drogaria;
    private $img_name;

    public function __construct(){
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->drogaria = new Drogaria($this->bd);
    }

    public function index(){
        return $this->drogaria->lerTodos();
    }

    public function pesquisarDrogaria($termo){
        return $this->drogaria->pesquisarGeral($termo);
    }

    public function cadastrarDrogaria($dados, $arquivo = null){
        $this->drogaria->nome = $dados['nome'] ?? '';
        $this->drogaria->cnpj = $dados['cnpj'] ?? '';
        $this->drogaria->telefone = $dados['telefone'] ?? '';
        $this->drogaria->email = $dados['email'] ?? '';
        $this->drogaria->numerodrog = $dados['numerodrog'] ?? 0;
        $this->drogaria->cep = $dados['cep'] ?? '';

        $temArquivo = isset($arquivo['name']['Foto_Drog'])
            && $arquivo['name']['Foto_Drog'] !== ""
            && isset($arquivo['error']['Foto_Drog'])
            && $arquivo['error']['Foto_Drog'] === UPLOAD_ERR_OK;

        if ($temArquivo && !$this->upload($arquivo)) {
            return false;
        }
        
        $this->drogaria->foto = $temArquivo ? $this->img_name : null;

        if($this->drogaria->cnpjExiste($this->drogaria->cnpj)){
            if($this->drogaria->reativar($this->drogaria->cnpj)){
                header("location: index.php");
                exit();
            }
        } else {
            if($this->drogaria->cadastrar()){
                header("location: index.php");
                exit();
            }
        }
    }

    public function atualizarDrogaria($dados, $arquivo = null){
        $temArquivo = isset($arquivo['name']['Foto_Drog'])
            && $arquivo['name']['Foto_Drog'] !== ""
            && isset($arquivo['error']['Foto_Drog'])
            && $arquivo['error']['Foto_Drog'] === UPLOAD_ERR_OK;

        if ($temArquivo && !$this->upload($arquivo)) {
            return false;
        }

        $this->drogaria->cnpj      = $dados['CNPJ_Drog'] ?? '';
        $this->drogaria->nome      = $dados['Nome_Drog'] ?? '';
        $this->drogaria->email     = $dados['Email_Drog'] ?? '';
        $this->drogaria->telefone  = $dados['Telefone_Drog'] ?? '';
        $this->drogaria->cep       = $dados['Cep_Drog'] ?? '';
        $this->drogaria->numerodrog = $dados['Num_Drog'] ?? '';
        
        if ($temArquivo) {
            $this->drogaria->foto = $this->img_name;
        } else {
            $existing = $this->localizarDrogaria($dados['CNPJ_Drog']);
            $this->drogaria->foto = $existing['Foto_Drog'] ?? null;
        }

        if($this->drogaria->atualizar()){
            header("location: index.php");
            exit();
        }
    }

public function localizarDrogaria($cnpj){
        return $this->drogaria->buscar($cnpj);
}

    public function excluirDrogaria($cnpj){
        if($this->drogaria->excluir($cnpj)){
            header("location: index.php");
            exit();
        }
    }
    public function excluidos(){
        return $this->drogaria->lerExcluidos();
    }

    public function reativarDrogaria($cnpj){
        if($this->drogaria->reativar($cnpj)){
            header("location: index.php");
            exit();
        }
    }

    public function upload($arquivo)
    {
        $target_dir    = "../uploads/drogarias/";
        $uploadOk      = 1;
        $target_file   = $target_dir . $arquivo['name']['Foto_Drog'];
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $random_name    = uniqid('drog_', true) . '.' . $imageFileType;
        $this->img_name = $random_name;
        $upload_file    = $target_dir . $random_name;

        $check = getimagesize($arquivo['tmp_name']['Foto_Drog']);
        if ($check === false) {
            $uploadOk = 0;
        }

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if ($uploadOk == 0) {
            return false;
        }

        if (move_uploaded_file($arquivo['tmp_name']['Foto_Drog'], $upload_file)) {
            return true;
        }

        return false;
    }
}

