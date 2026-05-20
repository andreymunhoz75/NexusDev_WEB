<?php

class Database
{
    private $host = "localhost:3316";
    private $usuario = "root";
    private $senha = "123456";
    private $banco = "drogariaWEB";
    private $con;

    public function conectar()
    {
        $this->con = null;

        try {
            $this->con = new pdo("mysql:host=$this->host;dbname=$this->banco", $this->usuario, $this->senha);

        } catch (PDOException $e) {
            echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
        }
        return $this->con;
    }
}

