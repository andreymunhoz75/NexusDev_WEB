<?php

class Funcionario
{
    public $nome;
    public $email;
    public $senha;
    public $funcao;
    public $CPF;
    public $cep;
    public $foto;
    public $numero;

    public $telefone;
    private $bd;

    public function __construct($bd)
    {
        $this->bd = $bd;

    }

    public function lerTodos()
    {
        $sql = "SELECT * FROM funcionario";
        $resultado = $this->bd->query($sql);
        return $resultado->fetchAll(PDO::FETCH_OBJ);
    }

    public function pesquisaFuncionario($CPF)
    {
        $sql = "SELECT * FROM funcionario WHERE CPF = :CPF";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(":CPF", $CPF);
        $resultado->execute();
        return $resultado->fetchAll(PDO::FETCH_OBJ);
    }

    public function pesquisarPorNome($nome)
    {
        $like = "%$nome%";
        $sql = "SELECT * FROM funcionario WHERE Nome_Fun LIKE :nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(':nome', $like, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function pesquisarGeral($termo)
    {
        $busca = "%$termo%";
        $valorNumerico = preg_replace('/[^0-9]/', '', $termo);
        
        if (!empty($valorNumerico)) {
            $buscaNumerica = "%$valorNumerico%";
            $sql = "SELECT * FROM funcionario WHERE (Nome_Fun LIKE :busca OR REPLACE(REPLACE(CPF, '.', ''), '-', '') LIKE :buscaNumerica)";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_STR);
            $stmt->bindParam(':buscaNumerica', $buscaNumerica, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM funcionario WHERE Nome_Fun LIKE :busca";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function cadastrar()
    {
        $existente = $this->buscafuncionario($this->CPF);
        if ($existente) {
            return ['sucesso' => false, 'mensagem' => 'CPF já cadastrado.'];
        }

        $existente = $this->buscaPorEmail($this->email);
        if ($existente) {
            return ['sucesso' => false, 'mensagem' => 'E-mail já cadastrado.'];
        }

        $query = "INSERT INTO funcionario (CPF, Nome_Fun, Email_Fun, Senha_Fun, Funcao, Telefone_Fun, Cep_Fun, imagem,Num_Fun) 
              VALUES (:cpf, :nome, :email, :senha, :cargo, :telefone, :cep, :foto,:numero)";
        $senha_hash = password_hash($this->senha, PASSWORD_DEFAULT);
        $stmt = $this->bd->prepare($query);

        $stmt->bindParam(":cpf", $this->CPF, PDO::PARAM_STR);
        $stmt->bindParam(":nome", $this->nome, PDO::PARAM_STR);
        $stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $stmt->bindParam(":senha", $senha_hash, PDO::PARAM_STR);
        $stmt->bindParam(":cargo", $this->funcao, PDO::PARAM_STR);
        $stmt->bindParam(":telefone", $this->telefone, PDO::PARAM_STR);
        $stmt->bindParam(":cep", $this->cep, PDO::PARAM_STR);
        $stmt->bindParam(":foto", $this->foto, PDO::PARAM_STR);
        $stmt->bindParam(":numero", $this->numero, PDO::PARAM_INT);

        $resultado = $stmt->execute();
        return ['sucesso' => $resultado, 'mensagem' => $resultado ? 'Funcionário cadastrado com sucesso.' : 'Erro ao cadastrar.'];


    }


    public function atualizar()
    {
        $senha_hash = password_hash($this->senha, PASSWORD_DEFAULT);
        $sql = "UPDATE funcionario SET Nome_Fun = :nome, Email_Fun = :email,
                Senha_Fun = :senha, Funcao = :funcao, imagem = :foto WHERE CPF = :CPF";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":nome", $this->nome, PDO::PARAM_STR);
        $stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $stmt->bindParam(":senha", $senha_hash, PDO::PARAM_STR);
        $stmt->bindParam(":funcao", $this->funcao, PDO::PARAM_STR);
        $stmt->bindParam(":foto", $this->foto, PDO::PARAM_STR);
        $stmt->bindParam(":CPF", $this->CPF, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function excluir()
    {
        $sql = "DELETE FROM funcionario WHERE CPF = :CPF";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":CPF", $this->CPF, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function buscafuncionario($CPF)
    {
        $sql = "SELECT * FROM funcionario WHERE CPF = :CPF";
        $resultado = $this->bd->prepare($sql);
        $resultado->bindParam(':CPF', $CPF);
        $resultado->execute();
        return $resultado->fetch(PDO::FETCH_OBJ);
    }

    public function buscaPorEmail($email)
    {
        $sql = "SELECT * FROM funcionario WHERE Email_Fun = :email";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function login($redirect = "index.php")
    {
        $login_cred = $this->email;
        $cred_numerico = preg_replace('/[^0-9]/', '', $login_cred);

        if (!empty($cred_numerico)) {
            $sql = "SELECT * FROM funcionario WHERE Email_Fun = :cred OR Nome_Fun = :cred OR REPLACE(REPLACE(CPF, '.', ''), '-', '') = :cred_num";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(":cred", $login_cred, PDO::PARAM_STR);
            $stmt->bindParam(":cred_num", $cred_numerico, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM funcionario WHERE Email_Fun = :cred OR Nome_Fun = :cred";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindParam(":cred", $login_cred, PDO::PARAM_STR);
        }
        
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_OBJ);

        if ($resultado) {
            if (password_verify($this->senha, $resultado->Senha_Fun)) {
                if (session_status() !== PHP_SESSION_ACTIVE)
                    session_start();
                $_SESSION["login"] = $resultado;
                $_SESSION["cpf"] = $resultado->CPF;
                header("Location: " . $redirect);
                exit();
            } else {
                return "Senha incorreta.";
            }
        } else {
            return "Usuário não encontrado.";
        }
    }
}


