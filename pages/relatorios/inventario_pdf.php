<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['id'])) {
    exit('Acesso negado.');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Produto.php';
require_once __DIR__ . '/pdf_helper.php';

$db = new Database();
$conn = $db->conectar();
$produto = new Produto($conn);
$produtos = $produto->listar();

$totalProdutos = count($produtos);
$totalStock = array_sum(array_map(static fn($item) => (int)$item['quantidade'], $produtos));
$valorTotal = array_sum(array_map(static fn($item) => ((float)$item['preco'] * (int)$item['quantidade']), $produtos));
$baixoStock = count(array_filter($produtos, static fn($item) => (int)$item['quantidade'] < 10));
$valorMedio = $totalProdutos > 0 ? $valorTotal / $totalProdutos : 0;
$valorTotalFormatado = number_format($valorTotal, 2, ',', '.');
$valorMedioFormatado = number_format($valorMedio, 2, ',', '.');

$critico = count(array_filter($produtos, static fn($item) => (int)$item['quantidade'] < 10));
$baixo = count(array_filter($produtos, static fn($item) => (int)$item['quantidade'] >= 10 && (int)$item['quantidade'] < 30));
$ok = count(array_filter($produtos, static fn($item) => (int)$item['quantidade'] >= 30));
$chartMax = max(1, $critico, $baixo, $ok);

$produtosCriticos = array_filter($produtos, static fn($item) => (int)$item['quantidade'] < 10);
usort($produtosCriticos, static fn($a, $b) => (int)$a['quantidade'] - (int)$b['quantidade']);

function chartBar(int $value, int $max, string $cssClass): string {
    $height = max(6, ($value / $max) * 100);
    return '<div class="c-bar-group">
        <div class="c-bar-track">
            <div class="c-bar-fill ' . $cssClass . '" style="height:' . $height . '%"></div>
        </div>
        <span class="c-bar-count">' . $value . '</span>
        <span class="c-bar-label">' . match($cssClass) {
            'critical' => 'Crítico',
            'low' => 'Baixo',
            default => 'OK'
        } . '</span>
    </div>';
}

$rows = '';
$totalValorCalc = 0;
foreach ($produtos as $item) {
    $qty = (int)$item['quantidade'];
    $valor = (float)$item['preco'] * $qty;
    $totalValorCalc += $valor;
    $stockClass = $qty < 10 ? 'critical' : ($qty < 30 ? 'low' : 'ok');
    $rows .= '<tr>';
    $rows .= '<td class="td-code">' . htmlspecialchars($item['codigo']) . '</td>';
    $rows .= '<td class="td-name">' . htmlspecialchars($item['nome']) . '</td>';
    $rows .= '<td class="td-cat">' . htmlspecialchars($item['categoria'] ?? '—') . '</td>';
    $rows .= '<td class="td-num r">' . number_format((float)$item['preco'], 2, ',', '.') . '</td>';
    $rows .= '<td class="td-num c"><span class="stock-pill ' . $stockClass . '">' . $qty . '</span></td>';
    $rows .= '<td class="td-num r">' . number_format($valor, 2, ',', '.') . '</td>';
    $rows .= '</tr>';
}

$critRows = '';
foreach ($produtosCriticos as $item) {
    $qty = (int)$item['quantidade'];
    $valor = (float)$item['preco'] * $qty;
    $critRows .= '<tr>';
    $critRows .= '<td class="td-code">' . htmlspecialchars($item['codigo']) . '</td>';
    $critRows .= '<td class="td-name">' . htmlspecialchars($item['nome']) . '</td>';
    $critRows .= '<td class="td-cat">' . htmlspecialchars($item['categoria'] ?? '—') . '</td>';
    $critRows .= '<td class="td-num r">' . number_format((float)$item['preco'], 2, ',', '.') . '</td>';
    $critRows .= '<td class="td-num c crit-qty">' . $qty . '</td>';
    $critRows .= '<td class="td-num r">' . number_format($valor, 2, ',', '.') . '</td>';
    $critRows .= '</tr>';
}

$dataEmissao = date('d/m/Y');
$usuarioNome = htmlspecialchars($_SESSION['nome'] ?? '—');

$chartBarCritical = chartBar($critico, $chartMax, 'critical');
$chartBarLow = chartBar($baixo, $chartMax, 'low');
$chartBarOk = chartBar($ok, $chartMax, 'ok');

$alertSection = '';
if ($baixoStock > 0) {
    $alertSection = <<<ALERT
    <div class="alert-section">
      <div class="alert-stripes"></div>
      <div class="alert-body">
        <div class="alert-header">
          <span class="alert-badge"><span>⚠ ALERTA</span></span>
          <h2>Produtos com stock crítico</h2>
          <div class="alert-count">{$baixoStock}<small>itens em risco</small></div>
        </div>
        <table class="alert-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nome</th>
              <th>Categoria</th>
              <th class="r">Preço (Kz)</th>
              <th class="r">Stock</th>
              <th class="r">Valor (Kz)</th>
            </tr>
          </thead>
          <tbody>{$critRows}</tbody>
        </table>
      </div>
    </div>
ALERT;
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', 'Liberation Sans', 'DejaVu Sans', Arial, sans-serif;
      color: #334155;
      background: #f4f2ed;
      font-size: 9.5px;
      line-height: 1.55;
    }

    .safety-strip {
      background: #d4870e;
      height: 4px;
      width: 100%;
    }

    .page {
      max-width: 190mm;
      margin: 0 auto;
      padding: 22px 30px 30px;
    }

    .letterhead {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      padding-bottom: 14px;
      border-bottom: 2px solid #1a1d23;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .brand-tag {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 9px;
      letter-spacing: 0.12em;
      background: #d4870e;
      color: #1a1d23;
      padding: 2px 6px;
      line-height: 1.4;
    }

    .brand-name {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 15px;
      letter-spacing: 0.1em;
      color: #1a1d23;
    }

    .meta {
      text-align: right;
    }

    .meta h1 {
      font-weight: 600;
      font-size: 15px;
      color: #1a1d23;
      margin: 0 0 3px;
    }

    .meta p {
      font-size: 9px;
      color: #94a3b8;
      margin: 0;
    }

    .kpi-row {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
    }

    .kpi-card {
      flex: 1;
      background: #ffffff;
      border-radius: 6px;
      padding: 11px 13px;
      border-left: 3px solid #1a1d23;
    }

    .kpi-card.accent { border-left-color: #d4870e; }
    .kpi-card.caution { border-left-color: #b91c1c; }

    .kpi-label {
      font-size: 8px;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #94a3b8;
      margin-bottom: 3px;
    }

    .kpi-value {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 17px;
      color: #1a1d23;
    }

    .kpi-value.accent { color: #d4870e; }
    .kpi-value.caution { color: #b91c1c; }

    .kpi-sub {
      font-size: 8px;
      color: #94a3b8;
      margin-top: 1px;
    }

    .section {
      background: #ffffff;
      border-radius: 6px;
      padding: 14px 16px;
      margin-bottom: 14px;
    }

    .section-head {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
    }

    .section-head::before {
      content: '';
      width: 3px;
      height: 13px;
      background: #d4870e;
      border-radius: 2px;
      flex-shrink: 0;
    }

    .section-head h2 {
      font-size: 11px;
      font-weight: 600;
      color: #1a1d23;
      margin: 0;
    }

    .alert-section {
      background: #1a1d23;
      border-radius: 6px;
      padding: 0;
      margin-bottom: 14px;
      overflow: hidden;
    }

    .alert-stripes {
      background: repeating-linear-gradient(
        -45deg,
        #b91c1c 0px, #b91c1c 8px,
        #1a1d23 8px, #1a1d23 16px
      );
      height: 6px;
    }

    .alert-body {
      padding: 14px 16px 12px;
    }

    .alert-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .alert-badge {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 8px;
      letter-spacing: 0.12em;
      background: #b91c1c;
      color: #ffffff;
      padding: 3px 8px;
      line-height: 1.4;
      transform: skewX(-8deg);
      display: inline-block;
    }

    .alert-badge span {
      display: inline-block;
      transform: skewX(8deg);
    }

    .alert-header h2 {
      font-size: 11px;
      font-weight: 600;
      color: #f8f4f0;
      margin: 0;
    }

    .alert-count {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 22px;
      color: #b91c1c;
      margin-left: auto;
      line-height: 1;
    }

    .alert-count small {
      font-family: 'Inter', 'Liberation Sans', Arial, sans-serif;
      font-weight: 400;
      font-size: 9px;
      color: #94a3b8;
      display: block;
      text-align: right;
    }

    .alert-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5px;
    }

    .alert-table thead th {
      font-size: 7px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #94a3b8;
      padding: 4px 6px;
      border-bottom: 1px solid #334155;
      text-align: left;
    }

    .alert-table thead th.r,
    .alert-table tbody td.r { text-align: right; }

    .alert-table tbody td {
      padding: 4px 6px;
      border-bottom: 1px solid #252830;
      color: #e2e8f0;
    }

    .alert-table tbody tr:last-child td { border-bottom: none; }

    .crit-qty {
      font-weight: 700;
      color: #f87171 !important;
    }

    .chart-row {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      gap: 28px;
      height: 130px;
      padding: 6px 0 0;
    }

    .c-bar-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      min-width: 56px;
    }

    .c-bar-track {
      width: 36px;
      height: 90px;
      background: #f1f0ec;
      border-radius: 4px;
      display: flex;
      align-items: flex-end;
      overflow: hidden;
    }

    .c-bar-fill {
      width: 100%;
      border-radius: 4px;
      min-height: 3px;
    }

    .c-bar-fill.critical { background: #b91c1c; }
    .c-bar-fill.low { background: #d4870e; }
    .c-bar-fill.ok { background: #0f766e; }

    .c-bar-count {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 12px;
      color: #1a1d23;
    }

    .c-bar-label {
      font-size: 8.5px;
      color: #94a3b8;
    }

    .table-wrap {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5px;
    }

    thead th {
      font-family: 'Inter', 'Liberation Sans', Arial, sans-serif;
      font-size: 7px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      background: #1a1d23;
      color: #d4870e;
      padding: 6px 8px;
      text-align: left;
    }

    thead th:last-child { text-align: right; }
    thead th:nth-child(4) { text-align: right; }
    thead th:nth-child(5) { text-align: center; }

    tbody tr:nth-child(even) td {
      background: #faf9f7;
    }

    tbody td {
      padding: 5px 8px;
      border-bottom: 1px solid #e8e6e1;
      vertical-align: middle;
    }

    tbody td:last-child { text-align: right; }
    tbody td:nth-child(4) { text-align: right; }
    tbody td:nth-child(5) { text-align: center; }

    .td-code {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      color: #94a3b8;
      font-size: 7.5px;
    }

    .td-name {
      font-weight: 500;
      color: #1a1d23;
    }

    .td-cat {
      color: #64748b;
      font-size: 8px;
    }

    .td-num {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 600;
      font-size: 8px;
    }

    .td-num.r { text-align: right; }
    .td-num.c { text-align: center; }

    .stock-pill {
      display: inline-block;
      padding: 1px 8px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 7.5px;
      min-width: 26px;
    }

    .stock-pill.critical { background: #fef2f2; color: #b91c1c; }
    .stock-pill.low { background: #fffbeb; color: #d4870e; }
    .stock-pill.ok { background: #ecfdf5; color: #0f766e; }

    tfoot td {
      padding: 6px 8px;
      border-top: 2px solid #1a1d23;
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 700;
      font-size: 9px;
      color: #1a1d23;
      background: #f4f2ed;
    }

    tfoot td:last-child { text-align: right; }
    tfoot td.r { text-align: right; }

    .footer {
      margin-top: 20px;
      padding-top: 10px;
      border-top: 1px solid #e2e0db;
      display: flex;
      justify-content: space-between;
      font-size: 7.5px;
      color: #94a3b8;
    }
  </style>
</head>
<body>
  <div class="safety-strip"></div>
  <div class="page">
    <div class="letterhead">
      <div class="brand">
        <span class="brand-tag">SYS</span>
        <span class="brand-name">ESTOQUE</span>
      </div>
      <div class="meta">
        <h1>Relatório de Inventário</h1>
        <p>Emissão: {$dataEmissao} · {$totalProdutos} produtos registados</p>
      </div>
    </div>

    <div class="kpi-row">
      <div class="kpi-card">
        <div class="kpi-label">Total Produtos</div>
        <div class="kpi-value">{$totalProdutos}</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Unidades em Stock</div>
        <div class="kpi-value">{$totalStock}</div>
      </div>
      <div class="kpi-card accent">
        <div class="kpi-label">Valor Total</div>
        <div class="kpi-value accent">Kz {$valorTotalFormatado}</div>
        <div class="kpi-sub">Médio: Kz {$valorMedioFormatado}/un</div>
      </div>
      <div class="kpi-card caution">
        <div class="kpi-label">Stock Baixo</div>
        <div class="kpi-value caution">{$baixoStock}</div>
        <div class="kpi-sub">Produtos com &lt; 10 unidades</div>
      </div>
    </div>

    {$alertSection}

    <div class="section">
      <div class="section-head">
        <h2>Distribuição por estado de stock</h2>
      </div>
      <div class="chart-row">
        {$chartBarCritical}
        {$chartBarLow}
        {$chartBarOk}
      </div>
    </div>

    <div class="section">
      <div class="section-head">
        <h2>Detalhes dos produtos</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Código</th>
              <th>Nome</th>
              <th>Categoria</th>
              <th>Preço (Kz)</th>
              <th>Stock</th>
              <th>Valor (Kz)</th>
            </tr>
          </thead>
          <tbody>{$rows}</tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="font-family:'Inter',sans-serif;font-weight:400;font-size:8px;color:#94a3b8;text-transform:none;letter-spacing:0;">{$totalProdutos} produtos · {$totalStock} unidades</td>
              <td class="r">—</td>
              <td class="r">—</td>
              <td class="r">Kz {$valorTotalFormatado}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="footer">
      <span>Gerado por: {$usuarioNome}</span>
      <span>SYS ESTOQUE · Relatório de Inventário</span>
    </div>
  </div>
</body>
</html>
HTML;

$pdfPath = PdfReportHelper::buildHtmlToPdf($html, 'inventario.pdf');

if (PHP_SAPI === 'cli') {
    readfile($pdfPath);
} else {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="inventario.pdf"');
    readfile($pdfPath);
}

@unlink($pdfPath);
