<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}
include_once("../Objetos/medicamentoController.php");

$controller = new medicamentoController();
$medicamentos = $controller->index();
$a = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["pesquisar"])) {
        $a = $controller->pesquisaMedicamento($_POST["pesquisar"]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["excluir"])) {
        $controller->excluirMedicamento($_GET["excluir"]);
        header("Location: index.php");
        exit();
    }
}

function diasParaVencer(string $dataVal): int {
    $hoje = new DateTime('today');
    $venc = new DateTime($dataVal);
    $venc->setTime(0, 0, 0);
    $diff = $hoje->diff($venc);
    return (int)$diff->format('%r%a');
}

function obterBadgeValidade(string $dataVal): string {
    $dias = diasParaVencer($dataVal);
    $dataExibicao = (new DateTime($dataVal))->format('d/m/Y');
    
    if ($dias < 0) {
        return '<span class="badge bg-danger">Vencido</span> ' . $dataExibicao;
    } elseif ($dias <= 30) {
        return '<span class="badge bg-danger">🔴 ' . $dias . ' dias</span> ' . $dataExibicao;
    } elseif ($dias <= 90) {
        return '<span class="badge bg-warning text-dark">🟡 ' . $dias . ' dias</span> ' . $dataExibicao;
    } else {
        return '<span class="badge bg-success">🟢</span> ' . $dataExibicao;
    }
}

$criticos = 0;
if ($medicamentos) {
    foreach ($medicamentos as $med) {
        if (diasParaVencer($med->DataVal_Med) <= 90) {
            $criticos++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medicamentos – PharmaPulse</title>
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
        <li class="nav-item"><a href="index.php" class="nav-link active" aria-current="page"><span class="fs-5">💊</span> Medicamentos</a></li>
        <?php if (($_SESSION['login']->Funcao ?? '') === 'Administrador'): ?><li class="nav-item"><a href="../funcionario/index.php" class="nav-link"><span class="fs-5">👥</span> Funcionários</a></li><?php endif; ?>
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
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4 gap-3">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"><span class="fs-4">☰</span></button>
        <div>
          <h1 class="display-6 fw-bold m-0" style="color:#1a1c4b;">Medicamentos</h1>
          <p class="text-secondary mb-0">Controle do estoque de medicamentos.</p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="excluidos.php" class="btn btn-outline-danger fw-bold shadow-sm px-4">🗑 Ver Excluídos</a>
        <a href="cadastro.php" class="btn btn-pharma-success fw-bold shadow-sm px-4">+ Novo Medicamento</a>
      </div>
    </div>

    <!-- Busca -->
    <div class="card card-pharma mb-4">
      <div class="card-body p-4">
        <form method="POST" action="index.php" class="row g-3 align-items-end">
          <div class="col-md-9">
            <label for="pesquisar" class="form-label fw-bold">Pesquisar por Nome ou Código</label>
            <input type="text" id="pesquisar" name="pesquisar" class="form-control" placeholder="Digite o nome ou código do medicamento...">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-pharma-success w-100 fw-bold">Filtrar</button>
          </div>
        </form>

        <?php if ($a): ?>
        <hr class="my-4">
        <h5 class="fw-bold mb-3">Resultado encontrado:</h5>
        <div class="table-responsive">
          <table class="table table-pharma mb-0 align-middle">
            <thead><tr>
              <th class="ps-4">Cód.</th><th>Nome</th><th>Qtd.</th><th>Validade</th><th>Valor</th><th class="text-end pe-4">Ações</th>
            </tr></thead>
            <tbody>
              <tr>
                <td class="ps-4 fw-bold text-secondary">#<?= htmlspecialchars($a->Cod_Med) ?></td>
                <td class="fw-bold"><?= htmlspecialchars($a->Nome_Med) ?></td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($a->Qtd_Med) ?> un.</span></td>
                <td><?= obterBadgeValidade($a->DataVal_Med) ?></td>
                <td class="fw-bold text-success">R$ <?= number_format($a->Valor_Med, 2, ',', '.') ?></td>
                <td class="text-end pe-4">
                  <a href="atualizar.php?alterar=<?= $a->Cod_Med ?>" class="btn btn-sm btn-pharma-success me-1">✏ Editar</a>
                  <a href="#" class="btn btn-sm btn-outline-danger"
                     onclick="abrirModalExcluir(event, 'index.php?excluir=<?= $a->Cod_Med ?>', 'Excluir Medicamento', 'Deseja excluir o medicamento <?= htmlspecialchars(addslashes($a->Nome_Med)) ?>? Esta ação não pode ser desfeita.')">🗑</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Indicadores -->
    <div class="row mb-4">
      <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 bg-white shadow-sm h-100" style="border-left: 4px solid #1a1c4b !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Total em Estoque</h6>
            <h2 class="fw-bold mb-0" style="color:#1a1c4b;"><?= $medicamentos ? count($medicamentos) : 0 ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Medicamentos Críticos (≤ 90 dias)</h6>
            <h2 class="fw-bold mb-0" style="color:#dc3545;"><?= $criticos ?></h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista -->
    <h3 class="fw-bold mb-3" style="color:#1a1c4b;">Estoque Completo</h3>
    <?php if ($criticos > 0): ?>
    <div class="alert alert-warning shadow-sm border-0 mb-3" role="alert">
      ⚠️ Atenção: Há <strong><?= $criticos ?></strong> <?= $criticos === 1 ? 'medicamento' : 'medicamentos' ?> com 90 dias ou menos para vencer (ou já vencidos).
    </div>
    <?php endif; ?>
    <div class="card card-pharma">
      <div class="card-body p-0">
        <?php if ($medicamentos): ?>
        <div class="table-responsive">
          <table class="table table-pharma mb-0 align-middle">
            <thead>
              <tr>
                <th class="ps-4">Cód.</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Qtd.</th>
                <th>Validade</th>
                <th>Valor</th>
                <th class="text-end pe-4">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($medicamentos as $med): ?>
              <tr>
                <td class="ps-4 fw-bold text-secondary">#<?= htmlspecialchars($med->Cod_Med) ?></td>
                <td class="fw-bold"><?= htmlspecialchars($med->Nome_Med) ?></td>
                <td class="text-secondary"><?= htmlspecialchars($med->Desc_Med) ?></td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($med->Qtd_Med) ?> un.</span></td>
                <td><?= obterBadgeValidade($med->DataVal_Med) ?></td>
                <td class="fw-bold text-success">R$ <?= number_format($med->Valor_Med, 2, ',', '.') ?></td>
                <td class="text-end pe-4">
                  <a href="atualizar.php?alterar=<?= $med->Cod_Med ?>" class="btn btn-sm btn-pharma-success me-1">✏</a>
                  <a href="#" class="btn btn-sm btn-outline-danger"
                     onclick="abrirModalExcluir(event, 'index.php?excluir=<?= $med->Cod_Med ?>', 'Excluir Medicamento', 'Deseja excluir o medicamento <?= htmlspecialchars(addslashes($med->Nome_Med)) ?>? Esta ação não pode ser desfeita.')">🗑</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="p-5 text-center">
          <p class="text-secondary fs-5 mb-0">Nenhum medicamento cadastrado.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Modal de Confirmação de Exclusão -->
  <div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#c0392b;">
          <h5 class="modal-title text-white fw-bold" id="modalExclusaoLabel">⚠️ Confirmar Exclusão</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-0 fw-bold" id="modalExclusaoMensagem" style="color:#333;"></p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
          <a href="#" id="modalExclusaoBtnConfirmar" class="btn btn-danger px-4 fw-bold">🗑 Confirmar Exclusão</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function abrirModalExcluir(e, url, titulo, mensagem) {
      e.preventDefault();
      document.getElementById('modalExclusaoLabel').textContent = '⚠️ ' + titulo;
      document.getElementById('modalExclusaoMensagem').textContent = mensagem;
      document.getElementById('modalExclusaoBtnConfirmar').href = url;
      new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
    }
  </script>
</body>
</html>
