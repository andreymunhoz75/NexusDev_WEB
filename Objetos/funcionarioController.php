<?php
include_once __DIR__ . "/../configs/database.php";
include_once __DIR__ . "/funcionario.php"; // ajuste conforme onde o arquivo está

class FuncionarioController
{
    private $bd;
    private $Funcionario;
    private $img_name;

    public function __construct()
    {
        $banco = new Database();
        $this->bd = $banco->conectar();
        $this->Funcionario = new Funcionario($this->bd);
    }

    public function index()
    {
        return $this->Funcionario->lerTodos();
    }

    public function pesquisaFuncionario($CPF)
    {
        return $this->Funcionario->pesquisaFuncionario($CPF);
    }

    public function pesquisarFuncionarioGeral(string $valor): array
    {
        return $this->Funcionario->pesquisarGeral($valor) ?: [];
    }

    public function cadastrarFuncionario($dados, $arquivo)
    {

        $temArquivo = isset($arquivo['name']['fileToUpload'])
            && $arquivo['name']['fileToUpload'] !== ""
            && isset($arquivo['error']['fileToUpload'])
            && $arquivo['error']['fileToUpload'] === UPLOAD_ERR_OK;

        if ($temArquivo && !$this->upload($arquivo)) {
            return false;
        }

        if (!$temArquivo) {
            $this->img_name = null;
        }


        $this->Funcionario->nome = $dados['nome'];
        $this->Funcionario->telefone = $dados['telefone'];
        $this->Funcionario->cep = $dados['cep'];
        $this->Funcionario->numero = $dados['numero'];
        $this->Funcionario->email = $dados['email'];
        $this->Funcionario->senha = $dados['senha'];
        $this->Funcionario->funcao = $dados['funcao'];
        $this->Funcionario->CPF = $dados['cpf'];
        $this->Funcionario->foto = $this->img_name;

        $resultado = $this->Funcionario->cadastrar();

        if (!$resultado['sucesso']) {
            $_SESSION['erro'] = $resultado['mensagem'];
            $_SESSION['form_data'] = $dados;
            header("Location: ../funcionario/cadastro.php");
            exit();
        }

        if ($resultado['sucesso']) {
            $_SESSION['sucesso'] = $resultado['mensagem'];
            header("Location: index.php");
            exit();
        }
//        CREATE TABLE funcionario (
//        CPF VARCHAR(14) UNIQUE PRIMARY KEY,
//Nome_Fun VARCHAR(50) NOT NULL,
//Telefone_Fun VARCHAR(15) NOT NULL,
//Cep_Fun VARCHAR(10) NOT NULL,
//Num_Fun INT NOT NULL,
//Email_Fun VARCHAR(50) UNIQUE NOT NULL,
//Senha_Fun VARCHAR(255) NOT NULL,
//Funcao VARCHAR(50),
//Ativo_Fun TINYINT(1) NOT NULL DEFAULT 1

//)
    }
    public function excluirFuncionario($CPF)
    {
        $this->Funcionario->CPF = $CPF;

        if ($this->Funcionario->excluir()) {
            header("location: index.php");
            exit();
        }
    }

    public function atualizarFuncionario($dados, $arquivo = null)
    {
        $temArquivo = isset($arquivo['name']['fileToUpload'])
            && $arquivo['name']['fileToUpload'] !== ""
            && isset($arquivo['error']['fileToUpload'])
            && $arquivo['error']['fileToUpload'] === UPLOAD_ERR_OK;

        if ($temArquivo && !$this->upload($arquivo)) {
            return false;
        }

        $this->Funcionario->CPF    = $dados['CPF'];
        $this->Funcionario->nome   = $dados['nome'];
        $this->Funcionario->email  = $dados['email'];
        $this->Funcionario->senha  = $dados['senha'];
        $this->Funcionario->funcao = $dados['funcao'] ?? $dados['cargo'] ?? null;
        
        if ($temArquivo) {
            $this->Funcionario->foto = $this->img_name;
        } else {
            // Keep existing photo if no new one uploaded
            $existing = $this->localizarFuncionario($dados['CPF']);
            $this->Funcionario->foto = $existing->imagem ?? null;
        }

        if ($this->Funcionario->atualizar()) {
            header("location: index.php");
            exit();
        }
    }

    public function localizarFuncionario($CPF)
    {
        return $this->Funcionario->buscafuncionario($CPF);
    }

    public function upload($arquivo)
    {
        $target_dir    = "../uploads/funcionarios/";
        $uploadOk      = 1;
        $target_file   = $target_dir . $arquivo['name']['fileToUpload'];
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $random_name    = uniqid('img_', true) . '.' . $imageFileType;
        $this->img_name = $random_name;
        $upload_file    = $target_dir . $random_name;

        $check = getimagesize($arquivo['tmp_name']['fileToUpload']);
        if ($check === false) {
            $uploadOk = 0;
        }

        if (file_exists($upload_file)) {
            $uploadOk = 0;
        }

        if ($arquivo['size']['fileToUpload'] > 100000000) {
            echo "Arquivo muito grande.<br>";
            $uploadOk = 0;
        }

        if (
            $imageFileType != "jpg"  &&
            $imageFileType != "png"  &&
            $imageFileType != "jpeg" &&
            $imageFileType != "gif"
        ) {
            echo "Formato não permitido. Use JPG, JPEG, PNG ou GIF.<br>";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            return false;
        }

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($arquivo['tmp_name']['fileToUpload'], $upload_file)) {
            return true;
        }

        return false;
    }

    public function login($email, $senha, $redirect = "index.php")
    {
        $this->Funcionario->email = $email;
        $this->Funcionario->senha = $senha;
        return $this->Funcionario->login($redirect);
    }
}
