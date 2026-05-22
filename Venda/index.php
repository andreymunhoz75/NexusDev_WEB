<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION["login"])) {
    header("Location: " . (file_exists("login.php") ? "" : "../") . "login.php");
    exit();
}
include_once "../Objetos/vendaController.php";
include_once "../Objetos/drogariaController.php";

$controller = new VendaController();
$drogController = new drogariaController();

$vendas = $controller->index();
$drogarias = $drogController->index();

$logado = isset($_SESSION['cpf']) && !empty($_SESSION['cpf']);

if (isset($_GET['auto_iniciar']) && $_GET['auto_iniciar'] == 1 && $logado) {
    $id = $controller->iniciarVenda($_SESSION['cpf']);
    header("Location: novaVenda.php?nota_fiscal_saida=" . $id);
    exit();
}

$a = null;
$status_selecionado = "";
$cnpj_selecionado = "";
$data_inicio = "";
$data_fim    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["iniciar_venda"])) {
        if(!isset($_SESSION['cpf']) || empty($_SESSION['cpf'])){
            header("Location: ../login.php");
            exit();
        }
        $id = $controller->iniciarVenda($_SESSION['cpf']);
        header("Location: novaVenda.php?nota_fiscal_saida=" . $id);
        exit();
    }
    
    if (isset($_POST["pesquisa_nf"]) && !empty($_POST["pesquisa_nf"])) {
        $a = $controller->pesquisaVenda($_POST["pesquisa_nf"]);
    }

    $status_selecionado = $_POST["filtro_status"] ?? "";
    $cnpj_selecionado   = $_POST["filtro_drogaria"] ?? "";
    $data_inicio        = $_POST["data_inicio"] ?? "";
    $data_fim           = $_POST["data_fim"] ?? "";
    $vendas = $controller->filtrarVendas(
        $_POST["pesquisa_nf"] ?? null,
        $status_selecionado,
        $cnpj_selecionado,
        $data_inicio ?: null,
        $data_fim ?: null
    );
} else {
    $status_selecionado = "";
    $cnpj_selecionado   = "";
    $data_inicio        = "";
    $data_fim           = "";
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["excluir"])) {
        $controller->excluirVenda($_GET["excluir"]);
        header("Location: index.php");
        exit();
    }
    if (isset($_GET["cancelar"]) && ($_SESSION['login']->Funcao ?? '') === 'Administrador') {
        $controller->cancelarVenda($_GET["cancelar"]);
        header("Location: index.php");
        exit();
    }
}

$totalVendas = $vendas ? count($vendas) : 0;
$valorTotalVendas = 0;
if ($vendas) {
    foreach ($vendas as $v) {
        $valorTotalVendas += $v->Valor_Venda ?? 0;
    }
}

$sucessoVenda = $_SESSION['venda_sucesso'] ?? null;
unset($_SESSION['venda_sucesso']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendas – PharmaPulse (Bootstrap 5)</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- CSS Customizado (Atualizado com regras B5) -->
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
      <?php if (($_SESSION['login']->Funcao ?? '') === 'Administrador'): ?>
      <li class="nav-item">
        <a href="../funcionario/index.php" class="nav-link">
          <span class="fs-5">👥</span> Funcionários
        </a>
      </li>
      <?php endif; ?>
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

  <!-- Conteúdo Principal -->
  <main class="b5-main p-4 p-md-5">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4 gap-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Botão Hamburger (Aparece apenas em telas < lg) -->
        <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">
          <span class="fs-4">☰</span>
        </button>
        <div>
          <h1 class="display-6 fw-bold text-dark m-0" style="color: #1a1c4b !important;">Painel de Vendas</h1>
          <p class="text-secondary mb-0">Central de faturamento e notas fiscais.</p>
        </div>
      </div>
      <div>
        <form method="POST" class="m-0">
          <?php if($logado): ?>
            <button type="submit" name="iniciar_venda" class="btn btn-pharma-success btn-lg px-4 shadow-sm fw-bold">
              + Nova Venda
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-pharma-success btn-lg px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#loginModal">
              + Nova Venda
            </button>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- Seção de Busca -->
    <div class="card card-pharma mb-4">
      <div class="card-body p-4">
        <form method="POST" class="row g-3 align-items-end">
          <div class="col-md-2">
            <label for="pesquisa_nf" class="form-label fw-bold">Pesquisar NF</label>
            <input type="number" class="form-control" id="pesquisa_nf" name="pesquisa_nf" placeholder="Ex: 105" value="<?= isset($_POST['pesquisa_nf']) ? htmlspecialchars($_POST['pesquisa_nf']) : '' ?>">
          </div>
          <div class="col-md-2">
            <label for="filtro_drogaria" class="form-label fw-bold">Filtrar por Drogaria</label>
            <select name="filtro_drogaria" id="filtro_drogaria" class="form-select">
              <option value="">Todas as Drogarias</option>
              <?php if($drogarias): ?>
                <?php foreach($drogarias as $d): ?>
                  <option value="<?= $d->CNPJ_Drog ?>" <?= $cnpj_selecionado == $d->CNPJ_Drog ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d->Nome_Drog) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filtro_status" class="form-label fw-bold">Situação</label>
            <select name="filtro_status" id="filtro_status" class="form-select">
              <option value="">Todas as Situações</option>
              <option value="1" <?= $status_selecionado === "1" ? "selected" : "" ?>>Finalizada</option>
              <option value="0" <?= $status_selecionado === "0" ? "selected" : "" ?>>Em Aberto / Rascunho</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="data_inicio_venda" class="form-label fw-bold">Data Início</label>
            <input type="date" id="data_inicio_venda" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
          </div>
          <div class="col-md-2">
            <label for="data_fim_venda" class="form-label fw-bold">Data Fim</label>
            <input type="date" id="data_fim_venda" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-pharma-success w-100 fw-bold">Aplicar Filtros</button>
          </div>
        </form>

        <!-- Bloco individual removido. O filtro $vendas já cuidará disso na tabela principal. -->
      </div>
    </div>

    <!-- Indicadores -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-sm" style="border-left: 4px solid #1a1c4b !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Total de Vendas no Sistema</h6>
            <h2 class="fw-bold mb-0" style="color: #1a1c4b;"><?= $totalVendas ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 bg-white shadow-sm" style="border-left: 4px solid #27ae60 !important;">
          <div class="card-body">
            <h6 class="text-secondary mb-1">Valor Total de Vendas</h6>
            <h2 class="fw-bold mb-0" style="color: #27ae60;">R$ <?= number_format($valorTotalVendas, 2, ',', '.') ?></h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Vendas -->
    <h3 class="fw-bold mb-3" style="color: #1a1c4b;">Vendas Registradas</h3>
    
    <div class="card card-pharma">
      <div class="card-body p-0">
        <?php if ($vendas): ?>
        <div class="table-responsive">
          <table class="table table-pharma mb-0 align-middle">
            <thead>
              <tr>
                <th scope="col" class="ps-4">Nota Fiscal</th>
                <th scope="col">Data da Venda</th>
                <th scope="col">Valor Total</th>
                <th scope="col">Drogaria</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-end pe-4">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vendas as $venda): ?>
              <tr>
                <td class="ps-4 fw-bold fs-6">#<?= htmlspecialchars($venda->NotaFiscal_Saida) ?></td>
                <td><?= date("d/m/Y", strtotime($venda->Data_Venda)) ?></td>
                <td class="fw-bold text-success">R$ <?= number_format($venda->Valor_Venda ?? 0, 2, ',', '.') ?></td>
                <td>
                  <span class="text-secondary small fw-bold text-uppercase">
                    <?= htmlspecialchars($venda->Nome_Drog ?? 'Venda Direta') ?>
                  </span>
                </td>
                <td>
                  <?php if($venda->Finalizada): ?>
                    <span class="badge bg-success px-3 py-2">Finalizada</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-2">Em Aberto/Rascunho</span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-4">
                  <?php if($venda->Finalizada): ?>
                    <?php if (($_SESSION['login']->Funcao ?? '') === 'Administrador'): ?>
                    <a href="#" class="btn btn-sm btn-outline-danger"
                       onclick="abrirModalExcluir(event, 'index.php?cancelar=<?= $venda->NotaFiscal_Saida ?>', 'Cancelar Venda', 'Deseja cancelar a venda NF #<?= $venda->NotaFiscal_Saida ?>? O estoque será estornado automaticamente.')">Cancelar Venda</a>
                    <?php endif; ?>
                  <?php else: ?>
                    <a href="novaVenda.php?nota_fiscal_saida=<?= $venda->NotaFiscal_Saida ?>" class="btn btn-sm btn-outline-secondary me-1">Editar</a>
                    <a href="#" class="btn btn-sm btn-outline-danger"
                       onclick="abrirModalExcluir(event, 'index.php?excluir=<?= $venda->NotaFiscal_Saida ?>', 'Excluir Venda', 'Tem certeza que deseja excluir a venda NF #<?= $venda->NotaFiscal_Saida ?>? Esta ação não pode ser desfeita.')">🗑</a>
                  <?php endif; ?>
                  
                  <a href="../ItemVenda/index.php?notaFiscal_Saida=<?= $venda->NotaFiscal_Saida ?>" class="btn btn-sm btn-pharma-success me-1">Ver Itens</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="p-5 text-center">
          <p class="text-secondary fs-5 mb-0">Nenhuma venda encontrada.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </main>

  <!-- Modal de Login -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
        <div class="modal-header border-0 p-4" style="background-color: #1a1c4b;">
          <h5 class="modal-title fw-bold text-white" id="loginModalLabel">🔍’ Área Restrita</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-secondary mb-4">Apenas funcionários autenticados podem iniciar uma nova venda. Por favor, identifique-se.</p>
          <form method="POST" action="../login.php">
            <input type="hidden" name="redirect" value="Venda/index.php?auto_iniciar=1">
            <div class="mb-3">
              <label for="login" class="form-label fw-bold">E-mail ou Login</label>
              <input type="text" class="form-control" id="login" name="login" placeholder="seu@email.com" required style="padding: 12px; border-radius: 8px;">
            </div>
            <div class="mb-4">
              <label for="senha" class="form-label fw-bold">Senha</label>
              <input type="password" class="form-control" id="senha" name="senha" placeholder="••••••••" required style="padding: 12px; border-radius: 8px;">
            </div>
            <button type="submit" class="btn btn-pharma-success w-100 fw-bold py-3 shadow-sm" style="border-radius: 8px;">Entrar e Continuar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

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
  <?php if ($sucessoVenda): ?>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div id="toastSucesso" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
      <div class="d-flex">
        <div class="toast-body fw-bold fs-6">
          ✅ <?= htmlspecialchars($sucessoVenda) ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      <?php if ($sucessoVenda): ?>
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

