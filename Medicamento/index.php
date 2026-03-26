<?php
include_once ("../objetos/medicamentoController.php");

$controller = new medicamentoController();
$medicamento = $controller->index();
global $medicamento;
$a = null;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["medicamento"])){
        $a = $controller->pesquisarMedicamento($_POST["pesquisar"]);
    }
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["excluir"])){
        $controller->excluirMedicamento($_GET["excluir"]);
    }
}
?>

<!doctype html>
<html lang=pt-br>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NexusDevMed</title>
</head>
<body>
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
<a href="cadastro.php">Cadastrar Medicamento</a>
<h3>Pesquisar Medicamento</h3>
<form method="POST" action="index.php">
    <label>CodMed</label>
    <input Type="number" name="pesquisar">
    <button>Pesquisar</button>
</form>

<table>
    <tr>
        <td>CodMed</td>
        <td>Nome</td>
    </tr>
    <?php if($a) : ?>
        <tr>
            <td><?= $a->codMed; ?></td>
            <td><?= $a->nome; ?></td>
        </tr>
    <?php endif; ?>

</table>
<h2>Medicamentos Cadastrados</h2>

<table>
    <tr>
        <td>Nome</td>
        <td>Descrição Medicamento</td>
        <td>Datava Validade</td>
        <td>Quantidade</td>
        <td>Valor</td>
    </tr>
    <?php if($medicamento) : ?>
        <?php foreach($medicamento as $med) : ?>

            <tr>
                <td><?= $med->Nome_Med; ?></td>
                <td><?= $med->Desc_Med; ?></td>
                <td><?= $med->DataVal_Med; ?></td>
                <td><?= $med->Qtd_Med; ?></td>
                <td><?= $med->Valor_Med; ?></td>

                <td><a href="atualizar.php?alterar=<?= $med->Cod_Med; ?>">Alterar</a> </td>
                <td><a href="index.php?excluir=<?= $med->Cod_Med; ?>">Excluir</a> </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
</body>
</html>
