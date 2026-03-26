<?php
include_once ("../objetos/laboratorioController.php");

$controller = new laboratorioController();
$excluidos = $controller->excluidos();
global $excluidos;

if($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["reativar"])){
    $controller->reativarLaboratorio($_GET["reativar"]);
}

?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratórios Excluídos</title>
</head>
<body>
<style>
    table, tr, td {
        border: 1px solid black;
        border-collapse: collapse;
        padding: 5px;
    }
</style>

<h1>Laboratórios Excluídos</h1>
<a href="index.php">Voltar</a>

<table>
    <tr>
        <td>CNPJ</td>
        <td>Nome</td>
        <td>Telefone</td>
        <td>Email</td>
        <td>CEP</td>
        <td>Número</td>
        <td>Ação</td>
    </tr>

    <?php if($excluidos) : ?>
        <?php foreach($excluidos as $lab) : ?>
            <tr>
                <td><?= $lab->CNPJ_Lab ?></td>
                <td><?= $lab->Nome_Lab ?></td>
                <td><?= $lab->Telefone_Lab ?></td>
                <td><?= $lab->Email_Lab ?></td>
                <td><?= $lab->Cep_Lab ?></td>
                <td><?= $lab->Num_Lab ?></td>
                <td><a href="excluidos.php?reativar=<?= $lab->CNPJ_Lab ?>">Reativar</a></td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="7">Nenhum laboratório excluído.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>