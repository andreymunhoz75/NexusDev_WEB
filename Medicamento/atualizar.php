<?php
include_once ("../objetos/medicamentoController.php");

$controller = new medicamentoController();
$a = null;

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["alterar"])){
    $a = $controller->localizarMedicamento($_GET["alterar"]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["atualizar"])){
    $controller->atualizarMedicamento($_POST["medicamento"]);

} else {
    header("location: index.php");
    exit();
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atualizar Medicamento</title>
</head>
<body>
<h1>Atualizar Medicamento</h1>
<a href="index.php">Voltar</a>

<form action="atualizar.php" method="post">
    <input type="hidden" name="medicamento[Cod_Med]" value="<?= $a->Cod_Med ?>">

    <label>Nome</label>
    <input type="text" name="medicamento[Nome_Med]" value="<?= $a->Nome_Med ?>"><br><br>

    <label>Descrição</label>
    <input type="text" name="medicamento[Desc_Med]" value="<?= $a->Desc_Med ?>"><br><br>

    <label>Data Validade</label>
    <input type="date" name="medicamento[DataVal_Med]" value="<?= $a->DataVal_Med ?>"><br><br>

    <label>Quantidade</label>
    <input type="number" name="medicamento[Qtd_Med]" value="<?= $a->Qtd_Med ?>"><br><br>

    <label>Valor</label>
    <input type="number" step="0.01" name="medicamento[Valor_Med]" value="<?= $a->Valor_Med ?>"><br><br>

    <button name="atualizar">Atualizar</button>
</form>
</body>
</html>