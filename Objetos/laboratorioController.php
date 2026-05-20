<?php
include_once "../configs/database.php";
include_once "laboratorio.php";

class laboratorioController{
    private $bd;
    private $laboratorio;
    private $img_name;

    public function __construct(){
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->laboratorio = new Laboratorio($this->bd);
    }

    public function index(){
        return $this->laboratorio->lerTodos();
    }

    public function pesquisarLaboratorio($valor) {
        $busca = "%$valor%";
        $valorNumerico = preg_replace('/[^0-9]/', '', $valor);
        
        if (!empty($valorNumerico)) {
            $buscaNumerica = "%$valorNumerico%";
            $sql = "SELECT * FROM laboratorio 
                    WHERE (Nome_Lab LIKE :busca 
                    OR REPLACE(REPLACE(REPLACE(CNPJ_Lab, '.', ''), '/', ''), '-', '') LIKE :buscaNumerica) 
                    AND Ativo_Lab = 1";
        } else {
            $sql = "SELECT * FROM laboratorio WHERE Nome_Lab LIKE :busca AND Ativo_Lab = 1";
        }
        
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(':busca', $busca);
        if (!empty($valorNumerico)) {
            $resultado->bindParam(':buscaNumerica', $buscaNumerica);
        }
        $resultado->execute();
        
        $dados = $resultado->fetchAll(PDO::FETCH_OBJ);
        return count($dados) > 0 ? $dados : null;
    }

    public function cadastrarLaboratorio($dados, $arquivos = null){
        $this->laboratorio->nome = $dados['nome'] ?? '';
        $this->laboratorio->cnpj = $dados['cnpj'] ?? '';
        $this->laboratorio->telefone = $dados['telefone'] ?? '';
        $this->laboratorio->email = $dados['email'] ?? '';
        $this->laboratorio->numerolab = (!empty($dados['num_lab']) ? (int)$dados['num_lab'] : 0);
        $this->laboratorio->cep = $dados['cep'] ?? '';

        if ($arquivos && isset($arquivos['Foto_Lab']) && $arquivos['Foto_Lab']['error'] === UPLOAD_ERR_OK) {
            $extensao = pathinfo($arquivos['Foto_Lab']['name'], PATHINFO_EXTENSION);
            $foto_nome = uniqid() . '.' . $extensao;
            $destino = "../uploads/laboratorios/" . $foto_nome;
            if (!is_dir("../uploads/laboratorios/")) {
                mkdir("../uploads/laboratorios/", 0777, true);
            }
            move_uploaded_file($arquivos['Foto_Lab']['tmp_name'], $destino);
            $this->laboratorio->foto = $foto_nome;
        } else {
            $this->laboratorio->foto = null;
        }

        if($this->laboratorio->cnpjExiste($this->laboratorio->cnpj)){
            if($this->laboratorio->reativar($this->laboratorio->cnpj)){
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

    public function atualizarLaboratorio($dados, $arquivos = null){
        $this->laboratorio->cnpj      = $dados['CNPJ_Lab'] ?? '';
        $this->laboratorio->nome      = $dados['Nome_Lab'] ?? '';
        $this->laboratorio->email     = $dados['Email_Lab'] ?? '';
        $this->laboratorio->telefone  = $dados['Telefone_Lab'] ?? '';
        $this->laboratorio->cep       = $dados['Cep_Lab'] ?? '';
        $this->laboratorio->numerolab = (!empty($dados['Num_Lab']) ? (int)$dados['Num_Lab'] : 0);

        if ($arquivos && isset($arquivos['Foto_Lab']) && $arquivos['Foto_Lab']['error'] === UPLOAD_ERR_OK) {
            $extensao = pathinfo($arquivos['Foto_Lab']['name'], PATHINFO_EXTENSION);
            $foto_nome = uniqid() . '.' . $extensao;
            $destino = "../uploads/laboratorios/" . $foto_nome;
            if (!is_dir("../uploads/laboratorios/")) {
                mkdir("../uploads/laboratorios/", 0777, true);
            }
            move_uploaded_file($arquivos['Foto_Lab']['tmp_name'], $destino);
            $this->laboratorio->foto = $foto_nome;
        } else {
            $this->laboratorio->foto = null;
        }

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
        }
    }
    public function excluidos(){
        return $this->laboratorio->lerExcluidos();
    }

    public function reativarLaboratorio($cnpj){
        if($this->laboratorio->reativar($cnpj)){
            header("location: index.php");
            exit();
        }
    }

    public function upload($arquivo)
    {
        $target_dir    = "../uploads/laboratorios/";
        $uploadOk      = 1;
        $target_file   = $target_dir . $arquivo['name']['fileToUpload'];
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $random_name    = uniqid('lab_', true) . '.' . $imageFileType;
        $this->img_name = $random_name;
        $upload_file    = $target_dir . $random_name;

        $check = getimagesize($arquivo['tmp_name']['fileToUpload']);
        if ($check === false) {
            $uploadOk = 0;
        }

        if (file_exists($upload_file)) {
            $uploadOk = 0;
        }

        if ($arquivo['size']['fileToUpload'] > 10000000) { // 10MB limit
            $uploadOk = 0;
        }

        if (
            $imageFileType != "jpg"  &&
            $imageFileType != "png"  &&
            $imageFileType != "jpeg" &&
            $imageFileType != "gif"
        ) {
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            return false;
        }

        if (move_uploaded_file($arquivo['tmp_name']['fileToUpload'], $upload_file)) {
            return true;
        }

        return false;
    }
}

