<?php
include_once("../Objetos/funcionarioController.php");

session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}
if (($_SESSION["login"]->Funcao ?? "") !== "Administrador") {
    header("location: ../index.php");
    exit();
}
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $controller = new funcionarioController();
    if (isset($_POST["cadastrar"])) {
        $a = $controller->cadastrarfuncionario($_POST["funcionario"], $_FILES["funcionario"]);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastrar Funcionário – PharmaPulse</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="d-flex flex-nowrap" style="font-family: 'Manrope', sans-serif;">

  <aside class="b5-sidebar offcanvas-lg offcanvas-start p-3" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralLabel">
    <div class="offcanvas-header d-lg-none border-bottom border-opacity-25 border-light mb-3">
      <h5 class="offcanvas-title fw-bold text-white text-uppercase" id="menuLateralLabel"><img src="../cfa_logo.png" alt="Distribuidora CFA" class="img-fluid rounded" style="max-height: 70px;"></h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
      <a href="../index.php" class="d-none d-lg-flex align-items-center mb-4 text-white text-decoration-none border-bottom pb-3 border-opacity-25" style="border-color:#fff;">
        <img src="../cfa_logo.png" alt="Distribuidora CFA" class="img-fluid w-100 rounded" style="object-fit: cover;">
      </a>


      <?php include_once __DIR__ . '/../includes/sidebar_user.php'; ?>
      <ul class="nav nav-pills flex-column mb-auto gap-2">
      <li class="nav-item">
        <a href="../index.php" class="nav-link">
          <span class="fs-5">🏠</span> Menu Principal
        </a>
      </li>
        <li class="nav-item"><a href="../Medicamento/index.php" class="nav-link"><span class="fs-5">💊</span> Medicamentos</a></li>
        <li class="nav-item"><a href="index.php" class="nav-link active"><span class="fs-5">👥</span> Funcionários</a></li>
        <li class="nav-item"><a href="../laboratorio/index.php" class="nav-link"><span class="fs-5">🔬</span> Laboratórios</a></li>
        <li class="nav-item"><a href="../drogaria/index.php" class="nav-link"><span class="fs-5">🏪</span> Drogarias</a></li>
        <li class="nav-item"><a href="../Compra/index.php" class="nav-link"><span class="fs-5">🛒</span> Compras</a></li>
        <li class="nav-item"><a href="../Venda/index.php" class="nav-link"><span class="fs-5">📈</span> Vendas</a></li>
      </ul>
      <hr class="border-secondary mt-auto">
      <div class="ph-sidebar-footer">
        <a href="../logout.php" class="ph-btn-exit w-100 mt-2 text-decoration-none"><span class="fs-5">⏻</span> Sair do Sistema</a>
      </div>
    </div>
  </aside>

  <main class="b5-main p-4 p-md-5">
    <div class="d-flex align-items-center gap-3 mb-4">
      <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"><span class="fs-4">☰</span></button>
      <div>
        <h1 class="display-6 fw-bold m-0" style="color:#1a1c4b;">Cadastrar Funcionário</h1>
        <p class="text-secondary mb-0">Registre um novo membro na equipe.</p>
      </div>
    </div>

    <?php if (isset($_SESSION["erro"])): ?>
    <div class="alert alert-danger mb-4"><?= $_SESSION["erro"] ?></div>
    <?php unset($_SESSION["erro"]); endif; ?>

    <div class="card card-pharma" style="max-width: 700px; margin: 0 auto;">
      <div class="card-body p-4">
        <form action="cadastro.php" method="post" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-bold">Nome</label>
              <input type="text" name="funcionario[nome]" class="form-control" value="<?= htmlspecialchars($form_data['nome'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">CPF</label>
              <input type="text" name="funcionario[cpf]" class="form-control" value="<?= htmlspecialchars($form_data['cpf'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Telefone</label>
              <input type="tel" name="funcionario[telefone]" class="form-control" value="<?= htmlspecialchars($form_data['telefone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">CEP</label>
              <input type="text" name="funcionario[cep]" class="form-control" value="<?= htmlspecialchars($form_data['cep'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Número</label>
              <input type="text" name="funcionario[numero]" class="form-control" value="<?= htmlspecialchars($form_data['numero'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">E-mail</label>
              <input type="email" name="funcionario[email]" class="form-control" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Senha</label>
              <input type="password" name="funcionario[senha]" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Função</label>
              <select name="funcionario[funcao]" class="form-select">
                <option value="Usuario" <?= ($form_data['funcao'] ?? '') === 'Usuario' ? 'selected' : '' ?>>Usuário</option>
                <option value="Administrador" <?= ($form_data['funcao'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Foto de Perfil</label>
              <img id="preview" src="#" alt="Preview da Foto" style="display:none; width:100%; max-height:200px; object-fit:cover; border-radius:var(--ph-radius-sm); margin-bottom:12px;">
              <input type="file" name="funcionario[fileToUpload]" id="fotoInput" class="form-control" accept="image/*">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
            <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold">Cancelar</a>
            <button type="submit" name="cadastrar" class="btn btn-pharma-success px-4 fw-bold">Cadastrar</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/validacoes.js"></script>
  <script>
    document.getElementById('fotoInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('preview');
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
      } else {
        preview.src = '#';
        preview.style.display = 'none';
      }
    });
  </script>
</body>
</html>

