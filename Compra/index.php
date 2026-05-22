<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}
include_once "../Objetos/compraController.php";
include_once "../Objetos/laboratorioController.php";

$controller = new CompraController();
$labController = new laboratorioController();
$laboratorios = $labController->index();

// ── Variáveis de filtro ──
$a                   = null;
$status_selecionado  = "";
$cnpj_lab_selecionado = "";
$data_inicio         = "";
$data_fim            = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Iniciar nova compra
    if (isset($_POST["iniciar_compra"])) {
        if(!isset($_SESSION['cpf']) || empty($_SESSION['cpf'])){
            header("Location: ../login.php");
            exit();
        }
        $cnpj_lab = trim($_POST['cnpj_lab'] ?? '');
        if(empty($cnpj_lab)) {
            $erro_iniciar = 'Selecione um fornecedor/laboratório para iniciar a compra!';
        } else {
            $id_nota_fiscal = $controller->iniciarCompra($_SESSION['cpf'], $cnpj_lab);
            header("Location: itensCompra.php?nota_fiscal_entrada=" . $id_nota_fiscal . "&cnpj_lab=" . urlencode($cnpj_lab));
            exit();
        }
    }

    // Pesquisa por NF
    if (isset($_POST["pesquisa_nf"]) && !empty($_POST["pesquisa_nf"])) {
        $a = $controller->pesquisaCompra($_POST["pesquisa_nf"]);
    }

    // Filtros combinados
    $status_selecionado   = $_POST["filtro_status"] ?? "";
    $cnpj_lab_selecionado = $_POST["filtro_lab"]    ?? "";
    $data_inicio          = $_POST["data_inicio"]   ?? "";
    $data_fim             = $_POST["data_fim"]       ?? "";

    $compras = $controller->filtrarCompras(
        $_POST["pesquisa_nf"]  ?? null,
        $cnpj_lab_selecionado  ?: null,
        $status_selecionado    !== "" ? $status_selecionado : null,
        $data_inicio           ?: null,
        $data_fim              ?: null
    );

} else {

    $compras = $controller->index();

    if (isset($_GET["excluir"])) {
        $controller->excluirCompra($_GET["excluir"]);
        header("Location: index.php");
        exit();
    }

    if (isset($_GET["cancelar"]) && ($_SESSION['login']->Funcao ?? '') === 'Administrador') {
        $controller->cancelarCompra($_GET["cancelar"]);
        // cancelarCompra já redireciona internamente
    }
}

$totalCompras = $compras ? count($compras) : 0;
$valorTotalCompras = 0;
if ($compras) {
    foreach ($compras as $c) {
        $valorTotalCompras += $c->Valor_Total ?? 0;
    }
}

$sucessoCompra = $_SESSION['compra_sucesso'] ?? null;
unset($_SESSION['compra_sucesso']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compras – PharmaPulse</title>
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
        <?php if (($_SESSION['login']->Funcao ?? '') === 'Administrador'): ?><li class="nav-item"><a href="../funcionario/index.php" class="nav-link"><span class="fs-5">👥</span> Funcionários</a></li><?php endif; ?>
        <li class="nav-item"><a href="../laboratorio/index.php" class="nav-link"><span class="fs-5">🔬</span> Laboratórios</a></li>
        <li class="nav-item"><a href="../drogaria/index.php" class="nav-link"><span class="fs-5">🏪</span> Drogarias</a></li>
        <li class="nav-item"><a href="index.php" class="nav-link active" aria-current="page"><span class="fs-5">🛒</span> Compras</a></li>
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
          <h1 class="display-6 fw-bold m-0" style="color:#1a1c4b;">Painel de Compras</h1>
          <p class="text-secondary mb-0">Central de notas fiscais de entrada.</p>
        </div>
      </div>
    </div>

    <!-- Iniciar Nova Compra -->
    <div class="card card-pharma mb-4">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color:#1a1c4b;">🛒 Iniciar Nova Compra</h5>
        <?php if (!empty($erro_iniciar)): ?>
          <div class="alert alert-warning mb-3"><?= htmlspecialchars($erro_iniciar) ?></div>
        <?php endif; ?>
        <form method="POST" class="row g-3 align-items-end">
          <div class="col-md-8">
            <label for="cnpj_lab" class="form-label fw-bold">Selecione o Fornecedor / Laboratório</label>
            <select name="cnpj_lab" id="cnpj_lab" class="form-select" required>
              <option value="">Selecione...</option>
              <?php if($laboratorios): ?>
                <?php foreach($laboratorios as $lab): ?>
                  <option value="<?= $lab->CNPJ_Lab ?>"><?= htmlspecialchars($lab->Nome_Lab) ?> — <?= $lab->CNPJ_Lab ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-4">
            <button type="submit" name="iniciar_compra" class="btn btn-pharma-success w-100 fw-bold">+ Iniciar Compra</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Filtros (espelhando Venda/index.php) -->
    <div class="card card-pharma mb-4">
      <div class="card-body p-4">
        <form method="POST" class="row g-3 align-items-end">
          <!-- NF -->
          <div class="col-md-2">
            <label for="pesquisa_nf" class="form-label fw-bold">Pesquisar NF</label>
            <input type="number" class="form-control" id="pesquisa_nf" name="pesquisa_nf"
                   placeholder="Ex: 12"
                   value="<?= isset($_POST['pesquisa_nf']) ? htmlspecialchars($_POST['pesquisa_nf']) : '' ?>">
          </div>
          <!-- Filtrar por Fornecedor (≡ Drogaria em Venda) -->
          <div class="col-md-2">
            <label for="filtro_lab" class="form-label fw-bold">Filtrar por Fornecedor</label>
            <select name="filtro_lab" id="filtro_lab" class="form-select">
              <option value="">Todos os Fornecedores</option>
              <?php if($laboratorios): ?>
                <?php foreach($laboratorios as $lab): ?>
                  <option value="<?= $lab->CNPJ_Lab ?>" <?= $cnpj_lab_selecionado == $lab->CNPJ_Lab ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lab->Nome_Lab) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <!-- Situação (≡ Situação em Venda) -->
          <div class="col-md-2">
            <label for="filtro_status" class="form-label fw-bold">Situação</label>
            <select name="filtro_status" id="filtro_status" class="form-select">
              <option value="">Todas as Situações</option>
              <option value="1" <?= $status_selecionado === "1" ? "selected" : "" ?>>Finalizada</option>
              <option value="0" <?= $status_selecionado === "0" ? "selected" : "" ?>>Em Aberto / Rascunho</option>
            </select>
          </div>
          <!-- Data Início -->
          <div class="col-md-2">
            <label for="data_inicio_compra" class="form-label fw-bold">Data Início</label>
            <input type="date" id="data_inicio_compra" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
          </div>
          <!-- Data Fim -->
          <div class="col-md-2">
            <label for="data_fim_compra" class="form-label fw-bold">Data Fim</label>
            <input type="date" id="data_fim_compra" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
          </div>
          <!-- Botão -->
          <div class="col-md-2">
            <button type="submit" class="btn btn-pharma-success w-100 fw-bold">Aplicar Filtros</button>
          </div>
        </form>

        <?php if ($a): ?>
        <hr class="my-4">
        <h5 class="fw-bold mb-3 text-success">Resultado Encontrado:</h5>
        <div class="table-responsive">
          <table class="table table-pharma mb-0 align-middle">
            <thead><tr>
              <th class="ps-4">NF</th><th>Valor Total</th><th>Data</th><th>CPF</th><th>CNPJ Lab</th><th>Status</th>
            </tr></thead>
            <tbody>
              <tr>
                <td class="ps-4 fw-bold">#<?= $a->NotaFiscal_Entrada ?></td>
                <td class="text-success fw-bold">R$ <?= number_format($a->Valor_Total ?? 0, 2, ',', '.') ?></td>
                <td><?= $a->Data_Compra ? date('d/m/Y', strtotime($a->Data_Compra)) : '—' ?></td>
                <td><?= htmlspecialchars($a->CPF) ?></td>
                <td><?= htmlspecialchars($a->CNPJ_Lab) ?></td>
                <td>
                  <?php if($a->Finalizada): ?>
                    <span class="badge bg-success px-3 py-2">Finalizada</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-2">Em Aberto</span>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Indicadores -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-sm" style="border-left: 4px solid #1a1c4b !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Total de Compras no Sistema</h6>
            <h2 class="fw-bold mb-0" style="color:#1a1c4b;"><?= $totalCompras ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-sm" style="border-left: 4px solid #27ae60 !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Valor Total de Compras</h6>
            <h2 class="fw-bold mb-0" style="color: #27ae60;">R$ <?= number_format($valorTotalCompras, 2, ',', '.') ?></h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de compras -->
    <h3 class="fw-bold mb-3" style="color:#1a1c4b;">Compras Registradas</h3>
    <div class="card card-pharma">
      <div class="card-body p-0">
        <?php if($compras): ?>
        <div class="table-responsive">
          <table class="table table-pharma mb-0 align-middle">
            <thead>
              <tr>
                <th class="ps-4">Nota Fiscal</th>
                <th>Data da Compra</th>
                <th>Valor Total</th>
                <th>Fornecedor</th>
                <th>Status</th>
                <th class="text-end pe-4">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($compras as $compra): ?>
              <tr>
                <td class="ps-4 fw-bold fs-6">#<?= $compra->NotaFiscal_Entrada ?></td>
                <td><?= $compra->Data_Compra ? date('d/m/Y', strtotime($compra->Data_Compra)) : '—' ?></td>
                <td class="fw-bold text-success">R$ <?= number_format($compra->Valor_Total ?? 0, 2, ',', '.') ?></td>
                <td>
                  <span class="text-secondary small fw-bold text-uppercase">
                    <?= htmlspecialchars($compra->Nome_Lab ?? $compra->CNPJ_Lab ?? 'N/A') ?>
                  </span>
                </td>
                <td>
                  <?php if($compra->Finalizada): ?>
                    <span class="badge bg-success px-3 py-2">Finalizada</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-2">Em Aberto / Rascunho</span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-4">
                  <?php if($compra->Finalizada): ?>
                    <?php if (($_SESSION['login']->Funcao ?? '') === 'Administrador'): ?>
                    <a href="#" class="btn btn-sm btn-outline-danger"
                       onclick="abrirModalExcluir(event, 'index.php?cancelar=<?= $compra->NotaFiscal_Entrada ?>', 'Cancelar Compra', 'Deseja cancelar a compra NF #<?= $compra->NotaFiscal_Entrada ?>? O estoque será estornado automaticamente.')">Cancelar Compra</a>
                    <?php endif; ?>
                  <?php else: ?>
                    <a href="itensCompra.php?nota_fiscal_entrada=<?= $compra->NotaFiscal_Entrada ?>&cnpj_lab=<?= urlencode($compra->CNPJ_Lab ?? '') ?>" class="btn btn-sm btn-outline-secondary me-1">Editar</a>
                    <a href="#" class="btn btn-sm btn-outline-danger"
                       onclick="abrirModalExcluir(event, 'index.php?excluir=<?= $compra->NotaFiscal_Entrada ?>', 'Excluir Compra', 'Tem certeza que deseja excluir a compra NF #<?= $compra->NotaFiscal_Entrada ?>? Esta ação não pode ser desfeita.')">🗑</a>
                  <?php endif; ?>
                  <a href="../ItemCompra/index.php?notaFiscal_Entrada=<?= $compra->NotaFiscal_Entrada ?>" class="btn btn-sm btn-pharma-success me-1">Ver Itens</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="p-5 text-center">
          <p class="text-secondary fs-5 mb-0">Nenhuma compra registrada.</p>
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

  <!-- Toast de Sucesso -->
  <?php if ($sucessoCompra): ?>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div id="toastSucesso" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
      <div class="d-flex">
        <div class="toast-body fw-bold fs-6">
          ✅ <?= htmlspecialchars($sucessoCompra) ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      <?php if ($sucessoCompra): ?>
        var toastEl = document.getElementById('toastSucesso');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
      <?php endif; ?>
    });

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