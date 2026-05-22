<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}

include_once "../Objetos/compraController.php";
include_once "../Objetos/catalogoController.php";
include_once "../Objetos/medicamentoController.php";
include_once "../Objetos/itemCompraController.php";

if (!isset($_GET['nota_fiscal_entrada'])) {
    header("Location: index.php");
    exit();
}
$nota_fiscal = (int)$_GET['nota_fiscal_entrada'];

$compraController = new CompraController();
$compra = $compraController->localizarCompra($nota_fiscal);

if (!$compra) {
    header("Location: index.php");
    exit();
}

if ($compra->Finalizada == 1) {
    header("Location: ../ItemCompra/index.php?notaFiscal_Entrada=" . $nota_fiscal);
    exit();
}

$cnpj_lab = $_GET['cnpj_lab'] ?? $compra->CNPJ_Lab;

$itemController = new ItemCompraController();
$catController  = new CatalogoController();
$medController  = new MedicamentoController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['adicionar'])) {
        $codCatMed = (int)$_POST['cod_catMed'];
        $qtd       = (int)$_POST['qtd'];
        $cat       = $catController->buscarCatalogo($codCatMed);

        if ($cat && $qtd > 0 && $qtd <= $cat->quantidade) {
            $itemController->cadastrarItemCompra([
                'DataVal_Item'       => $cat->dataValItemCat,
                'Qtd_Item'           => $qtd,
                'Valor_Item'         => $cat->Valor_CatMed,
                'Data_Venda'         => date('Y-m-d'),
                'NotaFiscal_Entrada' => $nota_fiscal,
                'Cod_CatMed'         => $cat->Cod_CatMed,
                'Cod_Med'            => null
            ]);
        } else {
            $_SESSION['erro_compra'] = 'Quantidade inválida ou acima do disponível em catálogo.';
        }
        header("Location: itensCompra.php?nota_fiscal_entrada={$nota_fiscal}&cnpj_lab={$cnpj_lab}");
        exit();
    }

    if (isset($_POST['remover'])) {
        $itemController->excluirItem((int)$_POST['cod_item']);
        header("Location: itensCompra.php?nota_fiscal_entrada={$nota_fiscal}&cnpj_lab={$cnpj_lab}");
        exit();
    }

    if (isset($_POST['atualizar_qtd'])) {
        $codItem = (int) $_POST['cod_item'];
        $novaQtd = (int) $_POST['nova_qtd'];
        if ($novaQtd > 0) {
            $itemController->atualizarQtd($codItem, $novaQtd);
        }
        header("Location: itensCompra.php?nota_fiscal_entrada={$nota_fiscal}&cnpj_lab={$cnpj_lab}");
        exit();
    }

    if (isset($_POST['salvar'])) {
        $totalCompra = $itemController->calcularTotal($nota_fiscal);
        $compraController->salvarRascunhoCompra($nota_fiscal, $totalCompra);
        header("Location: index.php");
        exit();
    }

    if (isset($_POST['finalizar'])) {
        $itens = $itemController->lerPorNotaFiscal($nota_fiscal);

        foreach ($itens as $item) {
            $medExistente = $medController->buscarPorEAN($item->EAN_Med);

            if ($medExistente) {
                $medController->adicionarEstoqueProcedimento($medExistente->Cod_Med, $item->Qtd_Item);
                $codMedGerado = $medExistente->Cod_Med;
            } else {
                $codMedGerado = $medController->cadastrarMedicamentoERetornarId([
                    'EAN_Med'     => $item->EAN_Med,
                    'Nome_Med'    => $item->Nome_CatMed,
                    'Desc_Med'    => $item->Desc_CatMed,
                    'DataVal_Med' => $item->dataValItemCat,
                    'Qtd_Med'     => $item->Qtd_Item,
                    'Valor_Med'   => $item->Valor_Item,
                    'Cod_CatMed'  => $item->Cod_CatMed
                ]);
            }

            $itemController->atualizarCodMed($item->Cod_Item, $codMedGerado);
        }

        $totalCompra = $itemController->calcularTotal($nota_fiscal);
        if ($compraController->finalizarCompra($nota_fiscal, $totalCompra)) {
            $_SESSION['compra_sucesso'] = "Compra Finalizada com Sucesso!";
            header("Location: index.php");
            exit();
        }
    }

    if (isset($_POST['cancelar'])) {
        $compraController->excluirCompra($nota_fiscal);
        header("Location: index.php");
        exit();
    }
}

if (isset($_POST['pesquisa_cat']) && !empty($_POST['termo_cat'])) {
    $catalogos = $catController->pesquisarPorTermo($cnpj_lab, $_POST['termo_cat']);
} else {
    $catalogos = $catController->lerPorCnpj($cnpj_lab);
}

$itensAtuais = $itemController->lerPorNotaFiscal($nota_fiscal);
$totalCompra = $itemController->calcularTotal($nota_fiscal);

$erroCompra = $_SESSION['erro_compra'] ?? null;
unset($_SESSION['erro_compra']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $compra->Valor_Total > 0 ? 'Editar' : 'Nova' ?> Compra – NF <?= $nota_fiscal ?></title>
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
        <li class="nav-item"><a href="index.php" class="nav-link active"><span class="fs-5">🛒</span> Compras</a></li>
        <li class="nav-item"><a href="../Venda/index.php" class="nav-link"><span class="fs-5">📈</span> Vendas</a></li>
      </ul>
      <hr class="border-secondary mt-auto">
      <div class="ph-sidebar-footer">
        <a href="../logout.php" class="ph-btn-exit w-100 mt-2 text-decoration-none"><span class="fs-5">⏻</span> Sair do Sistema</a>
      </div>
    </div>
  </aside>

  <main class="b5-main p-4 p-md-5">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"><span class="fs-4">☰</span></button>
        <div>
          <h1 class="display-6 fw-bold m-0" style="color:#1a1c4b;">
            <?= $compra->Valor_Total > 0 ? 'Editar' : 'Nova' ?> Compra
            <span class="badge ms-2 fw-bold" style="background:#f39c12;border-radius:50px;font-size:.65em;">Em Aberto</span>
          </h1>
          <p class="text-secondary mb-0">NF #<?= $nota_fiscal ?> — Lab: <?= htmlspecialchars($cnpj_lab) ?></p>
        </div>
      </div>
      <a href="index.php" class="btn btn-outline-secondary fw-bold px-4">← Voltar</a>
    </div>

    <?php if ($erroCompra): ?>
    <div class="alert alert-danger mb-4"><?= htmlspecialchars($erroCompra) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Catálogo do laboratório -->
      <div class="col-lg-6">
        <div class="card card-pharma h-100">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color:#1a1c4b;">Catálogo do Laboratório</h5>
            <p class="text-secondary mb-2" style="font-size:.82rem;">ðŸ’¡ Clique em qualquer linha para adicionar rapidamente via modal.</p>
            <form method="POST" class="d-flex gap-2 mb-3">
              <input type="text" name="termo_cat" class="form-control" placeholder="Nome ou EAN..." value="<?= htmlspecialchars($_POST['termo_cat'] ?? '') ?>">
              <button type="submit" name="pesquisa_cat" class="btn btn-pharma-success px-4 fw-bold shadow-sm">Buscar</button>
              <?php if(isset($_POST['pesquisa_cat'])): ?>
                <a href="itensCompra.php?nota_fiscal_entrada=<?= $nota_fiscal ?>&cnpj_lab=<?= $cnpj_lab ?>" class="btn btn-outline-secondary px-3 fw-bold shadow-sm">✕</a>
              <?php endif; ?>
            </form>
            <div class="table-responsive">
              <table class="table table-pharma mb-0 align-middle" style="font-size:.88rem;">
                <thead>
                  <tr>
                    <th class="ps-3">Cód.</th><th>EAN</th><th>Nome</th><th>Estoque</th><th>Valor</th><th>Qtd</th><th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if($catalogos): ?>
                    <?php foreach($catalogos as $cat): ?>
                    <tr class="catalogo-row" style="cursor:pointer;"
                        data-cod="<?= $cat->Cod_CatMed ?>"
                        data-nome="<?= htmlspecialchars($cat->Nome_CatMed, ENT_QUOTES) ?>"
                        data-max="<?= $cat->quantidade ?>"
                        data-valor="R$ <?= number_format($cat->Valor_CatMed, 2, ',', '.') ?>">
                      <td class="ps-3"><?= $cat->Cod_CatMed ?></td>
                      <td><?= $cat->EAN_Med ?></td>
                      <td class="fw-bold"><?= htmlspecialchars($cat->Nome_CatMed) ?></td>
                      <td><span class="badge bg-light text-dark border"><?= $cat->quantidade ?></span></td>
                      <td>R$ <?= number_format($cat->Valor_CatMed, 2, ',', '.') ?></td>
                      <td>
                        <form method="POST" class="d-flex gap-1 align-items-center" onclick="event.stopPropagation()">
                          <input type="hidden" name="cod_catMed" value="<?= $cat->Cod_CatMed ?>">
                          <input type="number" name="qtd" min="1" max="<?= $cat->quantidade ?>" value="1" class="form-control form-control-sm" style="width:55px;">
                          <button type="submit" name="adicionar" class="btn btn-sm btn-pharma-success fw-bold p-0 shadow-sm" style="width: 32px; height: 32px; line-height: 30px; border-radius: 6px;">+</button>
                        </form>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center text-secondary p-3">Catálogo vazio para este laboratório.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Itens da compra -->
      <div class="col-lg-6">
        <div class="card card-pharma h-100">
          <div class="card-body p-4 d-flex flex-column">
            <h4 class="fw-bold mb-4" style="color: #1a1c4b;">🛒 Carrinho / Itens da NF</h4>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Item</th>
                    <th>EAN</th>
                    <th>Med.</th>
                    <th>Qtd</th>
                    <th>V. Unit</th>
                    <th>V. Total</th>
                    <th>Remover</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(!empty($itensAtuais)): ?>
                    <?php foreach($itensAtuais as $item): ?>
                    <tr class="item-carrinho" data-cod-item="<?= $item->Cod_Item ?>" data-valor="<?= $item->Valor_Item ?>" data-max="<?= $item->quantidade ?>">
                      <td class="ps-3"><?= $item->Cod_Item ?></td>
                      <td><?= $item->EAN_Med ?></td>
                      <td class="fw-bold"><?= htmlspecialchars($item->Nome_CatMed) ?></td>
                      <td>
                        <form method="POST" class="d-flex align-items-center gap-1 mb-0 m-0" onclick="event.stopPropagation()">
                          <input type="hidden" name="cod_item" value="<?= $item->Cod_Item ?>">
                          <input type="hidden" name="atualizar_qtd" value="1">
                          <input type="number" name="nova_qtd" class="form-control form-control-sm input-qtd-carrinho text-center fw-bold" style="width:70px;" min="1" max="<?= $item->quantidade ?>" value="<?= $item->Qtd_Item ?>" onchange="this.form.submit()">
                        </form>
                      </td>
                      <td>R$ <span class="valor-unitario"><?= number_format($item->Valor_Item, 2, ',', '.') ?></span></td>
                      <td class="fw-bold text-success">R$ <span class="subtotal-item"><?= number_format($item->Qtd_Item * $item->Valor_Item, 2, ',', '.') ?></span></td>
                      <td>
                        <form method="POST" id="formRemover_<?= $item->Cod_Item ?>">
                          <input type="hidden" name="cod_item" value="<?= $item->Cod_Item ?>">
                          <button type="button" class="btn btn-sm btn-danger p-0 shadow-sm" style="width: 32px; height: 32px; line-height: 30px; border-radius: 6px;"
                            onclick="abrirModalForm(event, 'formRemover_<?= $item->Cod_Item ?>', 'Remover Item', 'Deseja remover este item da compra?', 'remover')">🗑</button>
                        </form>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                      <td colspan="5" class="text-end fw-bold pe-2">Total Estimado:</td>
                      <td class="fw-bold text-success fs-5">R$ <span id="totalGeralSpan"><?= number_format($totalCompra, 2, ',', '.') ?></span></td>
                      <td></td>
                    </tr>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center text-secondary p-3">Nenhum item adicionado ainda.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Botões de ação no fundo da janela -->
            <form method="POST" id="formAcoesCompra" class="mt-auto pt-4 border-top d-flex flex-wrap gap-2 justify-content-end align-items-center">
              <input type="hidden" name="_acao" id="formAcoesCompra_acao">
              <button type="button" class="btn btn-danger px-4 fw-bold shadow-sm"
                onclick="abrirModalForm(event, 'formAcoesCompra', 'Cancelar Compra', 'Cancelar e excluir esta compra permanentemente? Esta ação não pode ser desfeita.', 'cancelar')">✖ Cancelar</button>
              <button type="submit" name="salvar" class="btn btn-warning text-white px-4 fw-bold shadow-sm" formnovalidate>Salvar Rascunho</button>
              <button type="button" id="btn_finalizar_compra" class="btn btn-pharma-success px-4 fw-bold shadow-sm"
                <?= empty($itensAtuais) ? 'disabled' : '' ?>
                onclick="abrirModalResumoCompra()">✔ Finalizar Compra</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal: Adicionar Item via Linha do Catálogo -->
  <div class="modal fade" id="modalAdicionarCompra" tabindex="-1" aria-labelledby="modalAdicionarCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#1a1c4b;">
          <h5 class="modal-title text-white fw-bold" id="modalAdicionarCompraLabel">➕ Adicionar ao Carrinho</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-1 text-secondary" style="font-size:.8rem;">Produto selecionado:</p>
          <h6 class="fw-bold mb-1" id="modal_compra_nome" style="color:#1a1c4b;"></h6>
          <p class="text-secondary mb-3" style="font-size:.85rem;">Valor unit.: <span id="modal_compra_valor"></span> &nbsp;|&nbsp; Disponível: <strong id="modal_compra_max"></strong> unid.</p>
          <form method="POST" id="formModalCompra">
            <input type="hidden" name="cod_catMed" id="modal_compra_cod">
            <div class="mb-3">
              <label class="form-label fw-bold">Quantidade</label>
              <input type="number" name="qtd" id="modal_compra_qtd" min="1" value="1" class="form-control form-control-lg text-center fw-bold">
            </div>
            <div class="d-grid">
              <button type="submit" name="adicionar" class="btn btn-pharma-success btn-lg fw-bold">✔ Confirmar Adição</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação de Exclusão / Cancelamento -->
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

  <!-- Modal Resumo da Compra (Confirmação de Finalização) -->
  <div class="modal fade" id="modalResumoCompra" tabindex="-1" aria-labelledby="modalResumoCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px; overflow:hidden;">
        <div class="modal-header" style="background:#1a1c4b;">
          <h5 class="modal-title text-white fw-bold" id="modalResumoCompraLabel">🧾 Resumo da Compra</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-4">
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="fw-bold text-secondary">Nota Fiscal:</span>
              <span class="badge bg-primary rounded-pill" style="font-size:1rem;">#<?= $nota_fiscal ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="fw-bold text-secondary">Itens no Carrinho:</span>
              <span id="resumo_compra_qtd_itens" class="badge bg-secondary rounded-pill" style="font-size:1rem;"><?= count($itensAtuais) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="fw-bold text-secondary">Fornecedor / Lab.:</span>
              <span class="fw-bold text-dark text-end"><?= htmlspecialchars($cnpj_lab) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom-0">
              <span class="fw-bold text-secondary fs-5">Total da NF:</span>
              <span id="resumo_compra_total" class="fw-bold text-success fs-4">R$ <?= number_format($totalCompra, 2, ',', '.') ?></span>
            </li>
          </ul>
          <p class="text-muted text-center mb-0" style="font-size:0.85rem;">Ao confirmar, o estoque será atualizado e a compra finalizada.</p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Voltar</button>
          <button type="button" class="btn btn-pharma-success px-4 fw-bold" onclick="submeterFinalizacaoCompra()">✔ Confirmar Finalização</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ── Modal Resumo da Compra ──
    function abrirModalResumoCompra() {
      // Atualiza o total com o valor dinâmico do span (caso o usuário tenha alterado qtds)
      var totalSpan = document.getElementById('totalGeralSpan');
      if (totalSpan) {
        document.getElementById('resumo_compra_total').textContent = 'R$ ' + totalSpan.textContent;
      }
      new bootstrap.Modal(document.getElementById('modalResumoCompra')).show();
    }

    function submeterFinalizacaoCompra() {
      var form = document.getElementById('formAcoesCompra');
      var hidden = document.createElement('input');
      hidden.type  = 'hidden';
      hidden.name  = 'finalizar';
      hidden.value = '1';
      form.appendChild(hidden);
      form.submit();
    }

    document.querySelectorAll('.catalogo-row').forEach(function(row) {
      row.addEventListener('click', function() {
        var cod   = this.dataset.cod;
        var nome  = this.dataset.nome;
        var max   = parseInt(this.dataset.max);
        var valor = this.dataset.valor;

        document.getElementById('modal_compra_cod').value        = cod;
        document.getElementById('modal_compra_nome').textContent  = nome;
        document.getElementById('modal_compra_valor').textContent = valor;
        document.getElementById('modal_compra_max').textContent   = max;

        var qtdInput = document.getElementById('modal_compra_qtd');
        qtdInput.max   = max;
        qtdInput.value = 1;

        var modal = new bootstrap.Modal(document.getElementById('modalAdicionarCompra'));
        modal.show();
      });
    });

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

    document.querySelectorAll('button[data-remover-form]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var formId = btn.dataset.removerForm;
        abrirModalForm({ preventDefault: function(){} }, formId, 'Remover Item', 'Deseja remover este item da compra?', 'remover');
      });
    });

    document.querySelectorAll('.input-qtd-carrinho').forEach(function(input) {
      input.addEventListener('input', function() {
        atualizarTotais();
      });
    });

    function atualizarTotais() {
      let totalGeral = 0;
      document.querySelectorAll('.item-carrinho').forEach(function(row) {
        let qtd = parseInt(row.querySelector('.input-qtd-carrinho').value) || 0;
        let max = parseInt(row.dataset.max) || 0;
        if (qtd > max) {
          qtd = max;
          row.querySelector('.input-qtd-carrinho').value = max;
        }
        let valor = parseFloat(row.dataset.valor) || 0;
        let subtotal = qtd * valor;
        totalGeral += subtotal;
        row.querySelector('.subtotal-item').textContent = subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      });
      document.getElementById('totalGeralSpan').textContent = totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
  </script>

</body>
</html>

