<?php
include_once("../objetos/laboratorioController.php");

$controller = new laboratorioController();

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["alterar"])){
    $a = $controller->localizarLaboratorio($_GET["alterar"]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["atualizar"])){
    $controller->atualizarLaboratorio($_POST["laboratorio"]);
} else {
    header("Location: index.php");
    exit();
}

?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratório</title>
</head>
<body>
<h1>Labortório</h1>
<a href="index.php">Voltar</a>

<form action="atualizar.php" method="post">
    <input type="hidden" name="laboratorio[CNPJ_Lab]" value="<?= $a["CNPJ_Lab"] ?>">

    <label>Nome</label>
    <input type="text" name="laboratorio[Nome_Lab]" value="<?= $a["Nome_Lab"] ?>"><br><br>

    <label>Email</label>
    <input type="text" name="laboratorio[Email_Lab]" value="<?= $a["Email_Lab"] ?>"><br><br>

    <label>Telefone</label>
    <input type="text" name="laboratorio[Telefone_Lab]" value="<?= $a["Telefone_Lab"] ?>"><br><br>

    <label>CEP</label>
    <input type="text" name="laboratorio[Cep_Lab]" value="<?= $a["Cep_Lab"] ?>"><br><br>

    <label>Número</label>
    <input type="text" name="laboratorio[Num_Lab]" value="<?= $a["Num_Lab"] ?>"><br><br>

    <button name="atualizar">Atualizar</button>
</form>

</body>
</html>

