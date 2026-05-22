<?php
if (session_status() !== PHP_SESSION_ACTIVE)
  session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}
include_once "../Objetos/vendaController.php";
include_once "../Objetos/medicamentoController.php";
include_once "../Objetos/drogariaController.php";
include_once "../Objetos/itemVendaController.php";

if (!isset($_GET['nota_fiscal_saida'])) {
  header("Location: index.php");
  exit();
}
$nota_fiscal = (int) $_GET['nota_fiscal_saida'];

$vendaController = new VendaController();
$venda = $vendaController->localizarVenda($nota_fiscal);

if (!$venda) {
  header("Location: index.php");
  exit();
}

if ($venda->Finalizada == 1) {
  header("Location: ../ItemVenda/index.php?notaFiscal_Saida=" . $nota_fiscal);
  exit();
}

$itemController = new ItemVendaController();
$medController = new MedicamentoController();
$drogController = new DrogariaController();

// ===== AÇÕES POST =====
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['adicionar'])) {
    $codMed = (int) $_POST['cod_med'];
    $qtd = (int) $_POST['qtd'];
    $med = $medController->localizarMedicamento($codMed);

    if ($med && $qtd > 0 && $qtd <= $med->Qtd_Med) {
      $itemController->cadastrarItemVenda([
        'DataVal_ItemVenda' => $med->DataVal_Med,
        'Qtd_ItemVenda' => $qtd,
        'Valor_ItemVenda' => $med->Valor_Med,
        'Cod_Med' => $med->Cod_Med,
        'NotaFiscal_Saida' => $nota_fiscal
      ]);
    } else {
      $_SESSION['erro_venda'] = 'Quantidade inválida ou acima do estoque disponível.';
    }
    header("Location: novaVenda.php?nota_fiscal_saida=" . $nota_fiscal);
    exit();
  }

  if (isset($_POST['remover'])) {
    $itemController->excluirItem((int) $_POST['cod_item_venda']);
    header("Location: novaVenda.php?nota_fiscal_saida=" . $nota_fiscal);
    exit();
  }

  if (isset($_POST['atualizar_qtd'])) {
    $codItem = (int) $_POST['cod_item_venda'];
    $novaQtd = (int) $_POST['nova_qtd'];
    if ($novaQtd > 0) {
        $itemController->atualizarQtd($codItem, $novaQtd);
    }
    header("Location: novaVenda.php?nota_fiscal_saida=" . $nota_fiscal);
    exit();
  }

  if (isset($_POST['salvar'])) {
    $cnpj_drog = $_POST['cnpj_drog'] ?? null;
    $totalVenda = $itemController->calcularTotal($nota_fiscal);
    $vendaController->salvarRascunhoVenda($nota_fiscal, $totalVenda, $cnpj_drog);
    header("Location: index.php");
    exit();
  }

  if (isset($_POST['finalizar'])) {
    $cnpj_drog = $_POST['cnpj_drog'];
    $totalVenda = $itemController->calcularTotal($nota_fiscal);
    if ($vendaController->finalizarVenda($nota_fiscal, $totalVenda, $cnpj_drog)) {
      $_SESSION['venda_sucesso'] = "Venda finalizada com sucesso!";
      header("Location: index.php");
      exit();
    } else {
      header("Location: novaVenda.php?nota_fiscal_saida=" . $nota_fiscal);
      exit();
    }
  }

  if (isset($_POST['cancelar'])) {
    $vendaController->excluirVenda($nota_fiscal);
    header("Location: index.php");
    exit();
  }
}

// ===== DADOS PARA A VIEW =====
if (isset($_POST['pesquisa_med']) && !empty($_POST['termo_med'])) {
  $medicamentosBrutos = $medController->pesquisarPorTermo($_POST['termo_med']);
} else {
  $medicamentosBrutos = $medController->index();
}

// Filtrar medicamentos com estoque > 0
$medicamentos = array_filter($medicamentosBrutos, function($m) {
    return $m->Qtd_Med > 0;
});

$drogarias = $drogController->index();
$itensAtuais = $itemController->localizarItemVenda($nota_fiscal);
$totalVenda = $itemController->calcularTotal($nota_fiscal);

$erroVenda = $_SESSION['erro_venda'] ?? null;
unset($_SESSION['erro_venda']);

$erroVendaModal = $_SESSION['erro_venda_modal'] ?? null;
unset($_SESSION['erro_venda_modal']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $venda->CNPJ_Drog ? 'Editar' : 'Nova' ?> Venda – PharmaPulse</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../css/style.css">
</head>

<body class="d-flex flex-nowrap" style="font-family: 'Manrope', sans-serif;">

  <!-- Sidebar Bootstrap Customizada (Agora com Offcanvas Responsivo) -->
  <aside class="b5-sidebar offcanvas-lg offcanvas-start p-3" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralLabel">
    <div class="offcanvas-header d-lg-none border-bottom border-opacity-25 border-light mb-3">
      <h5 class="offcanvas-title fw-bold text-white text-uppercase" id="menuLateralLabel"><img src="../cfa_logo.png" alt="Distribuidora CFA" class="img-fluid rounded" style="max-height: 70px;"></h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Fechar"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
      <a href="../index.php"
        class="d-none d-lg-flex align-items-center mb-4 text-white text-decoration-none border-bottom pb-3 border-opacity-25"
        style="border-color: #fff;">
        <img src="../cfa_logo.png" alt="Distribuidora CFA" class="img-fluid w-100 rounded" style="object-fit: cover;">
      </a>

      <?php include_once __DIR__ . '/../includes/sidebar_user.php'; ?>
      <ul class="nav nav-pills flex-column mb-auto gap-2">
        <li class="nav-item">
          <a href="../index.php" class="nav-link">
            <span class="fs-5">🏠</span> Menu Principal
          </a>
        </li>
        <li class="nav-item">
          <a href="../Medicamento/index.php" class="nav-link">
            <span class="fs-5">💊</span> Medicamentos
          </a>
        </li>
        <li class="nav-item">
          <a href="../index.php" class="nav-link">
            <span class="fs-5">👥</span> Funcionários
          </a>
        </li>
        <li class="nav-item">
          <a href="../Laboratorio/index.php" class="nav-link">
            <span class="fs-5">🔬</span> Laboratórios
          </a>
        </li>
        <li class="nav-item">
          <a href="../drogaria/index.php" class="nav-link">
            <span class="fs-5">🏪</span> Drogarias
          </a>
        </li>
        <li class="nav-item">
          <a href="../Compra/index.php" class="nav-link">
            <span class="fs-5">🛒</span> Compras
          </a>
        </li>
        <li class="nav-item">
          <a href="index.php" class="nav-link active" aria-current="page">
            <span class="fs-5">📈</span> Vendas
          </a>
        </li>
      </ul>

      <hr class="border-secondary mt-auto">
      <div class="ph-sidebar-footer">
        <a href="../logout.php" class="ph-btn-exit w-100 mt-2 text-decoration-none">
          <span class="fs-5">⏻</span> Sair do Sistema
        </a>
      </div>
    </div>
  </aside>

  <main class="b5-main p-4 p-md-5">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4 gap-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Botão Hamburger (Aparece apenas em telas < lg) -->
        <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">
          <span class="fs-4">☰</span>
        </button>
        <div>
          <h1 class="display-6 fw-bold text-dark m-0" style="color: #1a1c4b !important;">
            <?= $venda->CNPJ_Drog ? 'Continuar Editando NF' : 'Ponto de Venda (PDV) NF' ?> #<?= $nota_fiscal ?>
          </h1>
          <p class="text-secondary mb-0">Selecione medicamentos e finalize a saída.</p>
        </div>
      </div>
      <div>
        <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold shadow-sm m-0">
          ↩ Voltar
        </a>
      </div>
    </div>

    <!-- Alertas -->
    <?php if ($erroVenda): ?>
      <div class="alert alert-danger alert-dismissible fade show fw-bold" role="alert">
        <?= htmlspecialchars($erroVenda) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Grid do Bootstrap: Duas Colunas -->
    <div class="row g-4">

      <!-- COLUNA DA ESQUERDA: MEDICAMENTOS DISPONÍVEIS -->
      <div class="col-12 col-xl-6">
        <div class="card card-pharma h-100">
          <div class="card-body p-4">
            <h4 class="fw-bold mb-4" style="color: #1a1c4b;">📦 Medicamentos em Estoque</h4>
            <p class="text-secondary mb-2" style="font-size:.82rem;">💡 Clique em qualquer linha para adicionar rapidamente via modal.</p>

            <form method="POST" class="d-flex gap-2 mb-4 align-items-center">
              <input type="text" name="termo_med" class="form-control" placeholder="Nome do medicamento ou cód..."
                value="<?= htmlspecialchars($_POST['termo_med'] ?? '') ?>">
              <button type="submit" name="pesquisa_med" class="btn btn-pharma-success px-4 fw-bold shadow-sm">Buscar</button>
              <?php if (isset($_POST['pesquisa_med'])): ?>
                <a href="novaVenda.php?nota_fiscal_saida=<?= $nota_fiscal ?>" class="btn btn-outline-secondary px-3 fw-bold shadow-sm">✕</a>
              <?php endif; ?>
            </form>

            <div class="table-responsive" style="max-height: 500px;">
              <table class="table table-hover align-middle">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>Cód</th>
                    <th>Nome</th>
                    <th>Qtd</th>
                    <th>R$ Unidade</th>
                    <th>Ação</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($medicamentos): ?>
                    <?php foreach ($medicamentos as $med): ?>
                      <tr class="med-row" style="cursor:pointer;"
                          data-cod="<?= $med->Cod_Med ?>"
                          data-nome="<?= htmlspecialchars($med->Nome_Med, ENT_QUOTES) ?>"
                          data-max="<?= $med->Qtd_Med ?>"
                          data-valor="R$ <?= number_format($med->Valor_Med, 2, ',', '.') ?>">
                        <td class="fw-bold"><?= htmlspecialchars($med->Cod_Med) ?></td>
                        <td><?= htmlspecialchars($med->Nome_Med) ?></td>
                        <td>
                          <span class="badge bg-secondary"><?= htmlspecialchars($med->Qtd_Med) ?> unid.</span>
                        </td>
                        <td class="text-success fw-bold">R$ <?= number_format($med->Valor_Med, 2, ',', '.') ?></td>
                        <td class="text-center align-middle" style="height: 1px;">
                          <form method="POST" class="m-0" style="display: inline-block; vertical-align: middle;" onclick="event.stopPropagation()">
                            <div class="d-flex align-items-center gap-2">
                              <input type="hidden" name="cod_med" value="<?= $med->Cod_Med ?>">
                              <input type="number" name="qtd" min="1" max="<?= $med->Qtd_Med ?>" value="1"
                                class="form-control form-control-sm text-center m-0" style="width: 60px;">
                              <button type="submit" name="adicionar" class="btn btn-sm btn-pharma-success fw-bold p-0 shadow-sm"
                                style="width: 32px; height: 32px; line-height: 30px; border-radius: 6px;">+</button>
                            </div>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center py-4">Nenhum medicamento encontrado no estoque.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

      <!-- COLUNA DA DIREITA: CARRINHO E FINALIZAÇÃO -->
      <div class="col-12 col-xl-6">
        <div class="card card-pharma h-100">
          <div class="card-body p-4 d-flex flex-column">
            <h4 class="fw-bold mb-4" style="color: #1a1c4b;">🛒 Carrinho / Itens da NF</h4>

            <div class="table-responsive flex-grow-1" style="max-height: 350px;">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Item</th>
                    <th>Med.</th>
                    <th>Qtd</th>
                    <th>V. Unit</th>
                    <th>V. Total</th>
                    <th>Remover</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($itensAtuais)): ?>
                    <?php foreach ($itensAtuais as $item): ?>
                      <tr>
                        <td>#<?= htmlspecialchars($item->Cod_ItemVenda) ?></td>
                        <td><?= htmlspecialchars($item->Cod_Med) ?></td>
                        <td>
                          <form method="POST" class="m-0 d-flex gap-1" style="vertical-align: middle;">
                            <input type="hidden" name="cod_item_venda" value="<?= $item->Cod_ItemVenda ?>">
                            <input type="hidden" name="atualizar_qtd" value="1">
                            <input type="number" name="nova_qtd" class="form-control form-control-sm text-center d-inline-block qtd-input" 
                                   style="width: 70px;" value="<?= htmlspecialchars($item->Qtd_ItemVenda) ?>" min="1" 
                                   data-valor="<?= $item->Valor_ItemVenda ?>" 
                                   oninput="atualizarTotais()"
                                   onchange="this.form.submit()">
                          </form>
                        </td>
                        <td>R$ <?= number_format($item->Valor_ItemVenda, 2, ',', '.') ?></td>
                        <td class="fw-bold item-total">R$ <?= number_format($item->Qtd_ItemVenda * $item->Valor_ItemVenda, 2, ',', '.') ?></td>
                        <td class="text-center align-middle" style="height: 1px;">
                        <form method="POST" id="formRemoverVenda_<?= $item->Cod_ItemVenda ?>" class="m-0" style="display: inline-block; vertical-align: middle;">
                            <input type="hidden" name="cod_item_venda" value="<?= $item->Cod_ItemVenda ?>">
                            <button type="button" class="btn btn-sm btn-danger p-0 shadow-sm"
                              style="width: 32px; height: 32px; line-height: 30px; border-radius: 6px;"
                              onclick="abrirModalForm(event, 'formRemoverVenda_<?= $item->Cod_ItemVenda ?>', 'Remover do Carrinho', 'Deseja remover este item do carrinho?', 'remover')">🗑</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center py-5 text-secondary">Ainda não há itens na venda.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Totalizador -->
            <div class="bg-light p-3 rounded mt-3 mb-4 d-flex justify-content-between align-items-center border">
              <h5 class="mb-0 text-secondary fw-bold">TOTAL A PAGAR:</h5>
              <h3 class="mb-0 text-success fw-bold" id="card_total_venda">R$ <?= number_format($totalVenda ?? 0, 2, ',', '.') ?></h3>
            </div>

            <!-- Form de Fechamento -->
            <form method="POST" id="formFinalizarVenda" class="mt-auto">
              <div class="mb-4">
                <label for="cnpj_drog" class="form-label fw-bold">Selecione a Drogaria Compradora:</label>
                <select name="cnpj_drog" id="cnpj_drog" class="form-select form-select-lg" required
                  onchange="verificarFinalizar()">
                  <option value="">Clique para selecionar...</option>
                  <?php if ($drogarias): ?>
                    <?php foreach ($drogarias as $drog): ?>
                      <option value="<?= htmlspecialchars($drog->CNPJ_Drog) ?>" <?= (isset($venda->CNPJ_Drog) && $venda->CNPJ_Drog == $drog->CNPJ_Drog) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($drog->Nome_Drog) ?> (CNPJ: <?= htmlspecialchars($drog->CNPJ_Drog) ?>)
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <div class="form-text">Faturamento vinculado ao CNPJ destino.</div>
              </div>

              <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center mt-4 pt-3 border-top">
                <button type="button" class="btn btn-danger px-4 fw-bold shadow-sm" formnovalidate
                  onclick="abrirModalForm(event, 'formFinalizarVenda', 'Cancelar Venda', 'Isso excluirá a venda e esvaziará o carrinho. Confirmar?', 'cancelar')">✖ Cancelar</button>
                <button type="submit" name="salvar" class="btn btn-warning text-white px-4 fw-bold shadow-sm" formnovalidate>Salvar Rascunho</button>
                <button type="button" id="btn_finalizar"
                  class="btn btn-pharma-success px-4 fw-bold shadow-sm" disabled onclick="abrirModalFinalizar()">✔ Finalizar Venda</button>
              </div>
            </form>

          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- Modal: Adicionar Medicamento via Linha da Tabela -->
  <div class="modal fade" id="modalAdicionarVenda" tabindex="-1" aria-labelledby="modalAdicionarVendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#1a1c4b;">
          <h5 class="modal-title text-white fw-bold" id="modalAdicionarVendaLabel">🛒 Adicionar ao Carrinho</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-1 text-secondary" style="font-size:.8rem;">Medicamento selecionado:</p>
          <h6 class="fw-bold mb-1" id="modal_venda_nome" style="color:#1a1c4b;"></h6>
          <p class="text-secondary mb-3" style="font-size:.85rem;">Valor unit.: <span id="modal_venda_valor"></span> &nbsp;|&nbsp; Em estoque: <strong id="modal_venda_max"></strong> unid.</p>
          <form method="POST" id="formModalVenda">
            <input type="hidden" name="cod_med" id="modal_venda_cod">
            <div class="mb-3">
              <label class="form-label fw-bold">Quantidade</label>
              <input type="number" name="qtd" id="modal_venda_qtd" min="1" value="1" class="form-control form-control-lg text-center fw-bold">
            </div>
            <div class="d-grid">
              <button type="submit" name="adicionar" class="btn btn-pharma-success btn-lg fw-bold">✔ Confirmar Adição</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação -->
  <div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#c0392b;">
          <h5 class="modal-title text-white fw-bold" id="modalExclusaoLabel">⚠️ Confirmar</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-0 fw-bold" id="modalExclusaoMensagem" style="color:#333;"></p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="modalExclusaoBtnConfirmar" class="btn btn-danger px-4 fw-bold">🗑 Confirmar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Resumo do Pedido -->
  <div class="modal fade" id="modalResumoPedido" tabindex="-1" aria-labelledby="modalResumoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#1a1c4b;">
          <h5 class="modal-title text-white fw-bold" id="modalResumoLabel">🧾 Resumo do Pedido</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="fw-bold text-secondary">Itens no Carrinho:</span>
              <span id="resumo_qtd_itens" class="badge bg-primary rounded-pill" style="font-size:1rem;"><?= $itensAtuais ? count($itensAtuais) : 0 ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="fw-bold text-secondary">Drogaria:</span>
              <span id="resumo_drogaria" class="fw-bold text-dark text-end"></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom-0">
              <span class="fw-bold text-secondary fs-5">Total a Pagar:</span>
              <span id="resumo_total" class="fw-bold text-success fs-4"></span>
            </li>
          </ul>
          <p class="text-muted text-center mb-0" style="font-size:0.85rem;">Ao confirmar, o estoque será atualizado e a venda finalizada.</p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-pharma-success px-4 fw-bold" onclick="submeterFinalizacao()">✔ Confirmar Compra</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Erro de Estoque -->
  <?php if ($erroVendaModal): ?>
  <div class="modal fade" id="modalErroEstoque" tabindex="-1" aria-labelledby="modalErroEstoqueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#c0392b;">
          <h5 class="modal-title text-white fw-bold" id="modalErroEstoqueLabel">❌ Falha na Finalização</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <?= $erroVendaModal ?>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary px-4 w-100" data-bs-dismiss="modal">Entendi, vou corrigir</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    <?php if ($erroVendaModal): ?>
      window.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Modal(document.getElementById('modalErroEstoque')).show();
      });
    <?php endif; ?>

    function atualizarTotais() {
      let totalGeral = 0;
      document.querySelectorAll('.qtd-input').forEach(input => {
        let qtd = parseInt(input.value) || 0;
        let valorUnit = parseFloat(input.dataset.valor);
        let totalItem = qtd * valorUnit;
        
        let tr = input.closest('tr');
        let tdTotal = tr.querySelector('.item-total');
        if (tdTotal) {
          tdTotal.textContent = 'R$ ' + totalItem.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        
        totalGeral += totalItem;
      });
      
      let elTotal = document.getElementById('card_total_venda');
      if (elTotal) {
        elTotal.textContent = 'R$ ' + totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      }
    }

    function abrirModalFinalizar() {
      var select = document.getElementById('cnpj_drog');
      var drogariaNome = select.options[select.selectedIndex].text;
      document.getElementById('resumo_drogaria').textContent = drogariaNome;
      document.getElementById('resumo_total').textContent = document.getElementById('card_total_venda').textContent;
      new bootstrap.Modal(document.getElementById('modalResumoPedido')).show();
    }

    function submeterFinalizacao() {
      var form = document.getElementById('formFinalizarVenda');
      var hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'finalizar';
      hidden.value = '1';
      form.appendChild(hidden);
      form.submit();
    }
    function verificarFinalizar() {
      var select = document.getElementById('cnpj_drog');
      var btn = document.getElementById('btn_finalizar');
      var temItens = <?= !empty($itensAtuais) ? 'true' : 'false' ?>;
      btn.disabled = (!temItens || select.value === "");
    }
    window.onload = verificarFinalizar;

    function abrirModalForm(e, formId, titulo, mensagem, nomeBtn) {
      e.preventDefault();
      document.getElementById('modalExclusaoLabel').textContent = '⚠️ ' + titulo;
      document.getElementById('modalExclusaoMensagem').textContent = mensagem;
      var modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
      document.getElementById('modalExclusaoBtnConfirmar').onclick = function() {
        var form = document.getElementById(formId);
        if (nomeBtn) {
          var hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = nomeBtn;
          hidden.value = '1';
          form.appendChild(hidden);
        }
        form.submit();
      };
      modal.show();
    }

    document.querySelectorAll('.med-row').forEach(function(row) {
      row.addEventListener('click', function() {
        var cod   = this.dataset.cod;
        var nome  = this.dataset.nome;
        var max   = parseInt(this.dataset.max);
        var valor = this.dataset.valor;

        document.getElementById('modal_venda_cod').value        = cod;
        document.getElementById('modal_venda_nome').textContent  = nome;
        document.getElementById('modal_venda_valor').textContent = valor;
        document.getElementById('modal_venda_max').textContent   = max;

        var qtdInput = document.getElementById('modal_venda_qtd');
        qtdInput.max   = max;
        qtdInput.value = 1;

        var modal = new bootstrap.Modal(document.getElementById('modalAdicionarVenda'));
        modal.show();
      });
    });
  </script>
</body>

</html>
