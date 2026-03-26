<?php
include_once("../Objetos/laboratorioController.php");

$controller = new LaboratorioController();
$laboratorio = $controller->index();
global $laboratorio;
$a = null;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["pesquisar"])){
        $a = $controller->pesquisarLaboratorio($_POST["pesquisar"]);
    }
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["excluir"])){
        $a = $controller->excluirLaboratorio($_GET["excluir"]);
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" >
    <title>NexusDev</title>
    <style>
        table,tr,td{
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>
<body>

<h1>NexusDev</h1>
<a href="../index.php">Voltar</a><br>
<a href="cadastro.php">Cadastrar Laboratório</a><br>
<a href="excluidos.php">Ver Excluidos</a>

<h3>Pesquisar Laboratório</h3>
<form method="POST" action="index.php">
    <label>CNPJ</label>
    <input Type="number" name="pesquisar">
    <button>Pesquisar</button>
</form>

<table>
    <tr>
        <td>CNPJ</td>
        <td>Nome</td>
    </tr>

    <?php if($a) : ?>
        <tr>
            <td><?= $a->cnpj; ?></td>
            <td><?= $a->nome; ?></td>
        </tr>
    <?php endif; ?>

</table>
<h2>Laboratórios Cadastrados</h2>

<table>
        <tr>
            <td>CNPJ</td>
            <td>Nome</td>
            <td>E-mail</td>
            <td>Telefone</td>
            <td>CEP</td>
            <td>NumeroLab</td>
        </tr>
    <?php if($laboratorio) : ?>
    <?php foreach($laboratorio as $lab) : ?>

    <tr>
        <td><?= $lab->CNPJ_Lab; ?></td>
        <td><?= $lab->Nome_Lab; ?></td>
        <td><?= $lab->Email_Lab; ?></td>
        <td><?= $lab->Telefone_Lab; ?></td>
        <td><?= $lab->Cep_Lab; ?></td>
        <td><?= $lab->Num_Lab; ?></td>

        <td><a href="atualizar.php?alterar=<?= $lab->CNPJ_Lab; ?>">Alterar</a> </td>
        <td><a href="index.php?excluir=<?= $lab->CNPJ_Lab; ?>">Excluir</a> </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>
