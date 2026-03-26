<?php
include_once ("../objetos/medicamentoController.php");
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $controller = new medicamentoController();

    if(isset($_POST["cadastrar"])){
        $a = $controller->cadastrarMedicamento($_POST["medicamento"]);
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Medicamento</title>
</head>
<body>
<h1>Cadastro de Medicamento</h1>
<a href="index.php">Voltar</a>

<form action="cadastro.php" method="post">
    <label>Nome Medicamento</label>
    <input type="text" name="medicamento[Nome_Med]"><br><br>

    <label>Descrição Medicamento</label>
    <input type="text" name="medicamento[Desc_Med]"><br><br>

    <label>Data Validade</label>
    <input type="date" name="medicamento[DataVal_Med]"><br><br>

    <label>Quantidade</label>
    <input type="number" name="medicamento[Qtd_Med]"><br><br>

    <label>Valor</label>
    <input type="number" step="0.01" name="medicamento[Valor_Med]"><br><br>

    <button name="cadastrar">Cadastrar</button>
</form>

</form>
</body>
</html>
