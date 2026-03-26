<?php
include_once "../objetos/vendaController.php";

$controller = new VendaController();
$vendas = $controller->index();

$a = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["pesquisar"])) {
        $a = $controller->pesquisaVenda($_POST["pesquisar"]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["excluir"])) {
        $controller->excluirVenda($_GET["excluir"]);
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Vendas Cadastradas</title>

    <style>
        table,tr,td{
            border:1px solid black;
            border-collapse:collapse;
            padding:5px;
        }
    </style>

</head>

<body>

<h1>Vendas</h1>
<a href="../index.php">Voltar</a><br>
<a href="cadastro.php">Cadastrar Venda</a>

<h3>Pesquisar Venda</h3>

<form method="POST">
    <label>Nota Fiscal</label>
    <input type="number" name="pesquisar">
    <button>Pesquisar</button>
</form>

<table>

    <tr>
        <td>Nota Fiscal</td>
        <td>Data</td>
        <td>Valor</td>
        <td>CNPJ Drogaria</td>
        <td>CPF</td>
    </tr>

    <?php if($a) : ?>

        <tr>

            <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $a->NotaFiscal_Saida ?>"><?= $a->NotaFiscal_Saida ?></a></td>
            <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $a->NotaFiscal_Saida ?>"><?= $a->Data_Venda ?></a></td>
            <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $a->NotaFiscal_Saida ?>"><?= $a->Valor_Venda ?></a></td>
            <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $a->NotaFiscal_Saida ?>"><?= $a->CNPJ_Drog ?></a></td>
            <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $a->NotaFiscal_Saida ?>"><?= $a->CPF ?></a></td>

        </tr>

    <?php endif; ?>

</table>

<h2>Vendas cadastradas</h2>

<table>

    <tr>
        <td>Nota Fiscal</td>
        <td>Data</td>
        <td>Valor</td>
        <td>CNPJ Drogaria</td>
        <td>CPF</td>
    </tr>

    <?php if($vendas) : ?>
        <?php foreach($vendas as $venda) : ?>

            <tr>

                <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>"><?= $venda->NotaFiscal_Saida ?></a></td>
                <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>"><?= $venda->Data_Venda ?></a></td>
                <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>"><?= $venda->Valor_Venda ?></a></td>
                <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>"><?= $venda->CNPJ_Drog ?></a></td>
                <td><a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>"><?= $venda->CPF ?></a></td>

            </tr>

        <?php endforeach; ?>
    <?php endif; ?>

</table>

</body>
</html>