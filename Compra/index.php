<?php
include_once "../Objetos/compraController.php";

$controller = new CompraController();
$compras = $controller->index();

$a = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["pesquisar"])) {
        $a = $controller->pesquisaCompra($_POST["pesquisar"]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["excluir"])) {
        $controller->excluirCompra($_GET["excluir"]);
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Compras Cadastradas</title>

    <style>
        table,tr,td{
            border:1px solid black;
            border-collapse:collapse;
            padding:5px;
        }
    </style>

</head>

<body>

<h1>Compras</h1>
<a href="../index.php">Voltar</a><br>

<a href="cadastrarCompra.php">Cadastrar Compra</a>

<h3>Pesquisar Compra</h3>

<form method="POST">
    <label>Nota Fiscal</label>
    <input type="number" name="pesquisar">
    <button>Pesquisar</button>
</form>

<table>
    <tr>
        <td>Nota Fiscal</td>
        <td>Valor Total</td>
        <td>Data Compra</td>
        <td>CPF</td>
        <td>CNPJ Laboratório</td>
    </tr>

    <?php if($a) : ?>
        <tr>
            <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $a->NotaFiscal_Entrada ?>"><?= $a->NotaFiscal_Entrada ?></a></td>
            <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $a->NotaFiscal_Entrada ?>"><?= $a->Valor_Total ?></a></td>
            <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $a->NotaFiscal_Entrada ?>"><?= $a->Data_Compra ?></a></td>
            <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $a->NotaFiscal_Entrada ?>"><?= $a->CPF ?></a></td>
            <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $a->NotaFiscal_Entrada ?>"><?= $a->CNPJ_Lab ?></a></td>
        </tr>
    <?php endif; ?>
</table>

<h2>Compras cadastradas</h2>

<table>
    <tr>
        <td>Nota Fiscal</td>
        <td>Valor Total</td>
        <td>Data Compra</td>
        <td>CPF</td>
        <td>CNPJ Laboratório</td>
    </tr>

    <?php if($compras) : ?>
        <?php foreach($compras as $compra) : ?>
            <tr>
                <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>"><?= $compra->NotaFiscal_Entrada ?></a></td>
                <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>"><?= $compra->Valor_Total ?></a></td>
                <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>"><?= $compra->Data_Compra ?></a></td>
                <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>"><?= $compra->CPF ?></a></td>
                <td><a href="../Itens/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>"><?= $compra->CNPJ_Lab ?></a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

</table>

</body>
</html>