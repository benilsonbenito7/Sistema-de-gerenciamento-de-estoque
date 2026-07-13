<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    exit('Acesso negado.');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Movimentacao.php';
require_once __DIR__ . '/../../model/Produto.php';
require_once __DIR__ . '/pdf_helper.php';

$db = new Database();
$conn = $db->conectar();
$movimentacao = new Movimentacao($conn);
$produtoModel = new Produto($conn);
$movimentacoes = $movimentacao->listarTodos(200);

$entrada = count(array_filter($movimentacoes, static fn($item) => (string)$item['tipo'] === 'entrada'));
$saida = count(array_filter($movimentacoes, static fn($item) => (string)$item['tipo'] === 'saida'));
$valorEntradas = 0;
$valorSaidas = 0;
$movimentacoesComValor = [];

foreach ($movimentacoes as $item) {
    $produto = $produtoModel->buscarPorId((int)($item['produto_id'] ?? 0));
    $precoUnitario = (float)($produto['preco'] ?? 0);
    $valorMovimento = (float)$item['quantidade'] * $precoUnitario;

    if ((string)$item['tipo'] === 'entrada') {
        $valorEntradas += $valorMovimento;
        $impacto = $valorMovimento;
    } else {
        $valorSaidas += $valorMovimento;
        $impacto = -$valorMovimento;
    }

    $movimentacoesComValor[] = [
        ...$item,
        'valor_unitario' => $precoUnitario,
        'impacto_valor' => $impacto,
    ];
}

$movimentacoes = $movimentacoesComValor;
$totalMovimentos = count($movimentacoes);
$saldoFinanceiro = $valorEntradas - $valorSaidas;
$chartMax = max(1, $entrada, $saida);

$rows = '';
foreach ($movimentacoes as $item) {
    $isEntrada = (float)$item['impacto_valor'] >= 0;
    $tipoClass = $isEntrada ? 'entrada' : 'saida';
    $tipoLabel = $isEntrada ? 'Entrada' : 'Saída';
    $rows .= '<tr>';
    $rows .= '<td><span class="tipo-badge ' . $tipoClass . '">' . $tipoLabel . '</span></td>';
    $rows .= '<td class="td-name">' . htmlspecialchars($item['produto_nome'] ?? '—') . '</td>';
    $rows .= '<td class="td-num">' . (int)$item['quantidade'] . '</td>';
    $rows .= '<td>' . htmlspecialchars($item['usuario_nome'] ?? '—') . '</td>';
    $rows .= '<td class="td-date">' . htmlspecialchars(date('d/m/Y H:i', strtotime($item['data_movimento']))) . '</td>';
    $rows .= '<td class="td-num ' . $tipoClass . '">' . ($isEntrada ? '+' : '−') . ' Kz ' . number_format(abs((float)$item['impacto_valor']), 2, ',', '.') . '</td>';
    $rows .= '</tr>';
}

$dataEmissao = date('d/m/Y');
$usuarioNome = htmlspecialchars($_SESSION['nome'] ?? '—');
$valorEntradasFormatado = number_format($valorEntradas, 2, ',', '.');
$valorSaidasFormatado = number_format($valorSaidas, 2, ',', '.');
$saldoFinanceiroFormatado = number_format($saldoFinanceiro, 2, ',', '.');
$alturaEntradas = max(6, round(($entrada / $chartMax) * 100));
$alturaSaidas = max(6, round(($saida / $chartMax) * 100));

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
      font-family: 'Inter', 'Liberation Sans', Arial, sans-serif;
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

    .kpi-card.entry {
      border-left-color: #0f766e;
    }

    .kpi-card.exit {
      border-left-color: #b91c1c;
    }

    .kpi-card.accent {
      border-left-color: #d4870e;
    }

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

    .kpi-value.entry { color: #0f766e; }
    .kpi-value.exit { color: #b91c1c; }
    .kpi-value.accent { color: #d4870e; }

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

    .chart-row {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      gap: 40px;
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
      width: 40px;
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

    .c-bar-fill.entry { background: #0f766e; }
    .c-bar-fill.exit { background: #b91c1c; }

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

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5px;
    }

    thead th {
      font-size: 7.5px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #94a3b8;
      padding: 5px 6px;
      border-bottom: 1.5px solid #e2e0db;
      text-align: left;
      white-space: nowrap;
    }

    thead th:last-child { text-align: right; }

    tbody td {
      padding: 5px 6px;
      border-bottom: 1px solid #f1f0ec;
      vertical-align: middle;
    }

    tbody td:last-child { text-align: right; }

    .td-name {
      font-weight: 500;
      color: #1a1d23;
    }

    .td-num {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-weight: 600;
      font-size: 8px;
    }

    .td-num.entrada { color: #0f766e; }
    .td-num.saida { color: #b91c1c; }

    .td-date {
      font-family: 'JetBrains Mono', 'Liberation Mono', 'Courier New', monospace;
      font-size: 7.5px;
      color: #64748b;
    }

    .tipo-badge {
      display: inline-block;
      font-size: 7.5px;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding: 2px 6px;
      border-radius: 3px;
    }

    .tipo-badge.entrada {
      background: #ecfdf5;
      color: #0f766e;
    }

    .tipo-badge.saida {
      background: #fef2f2;
      color: #b91c1c;
    }

    tbody tr:last-child td { border-bottom: none; }

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
        <h1>Relatório de Movimentações</h1>
        <p>Emissão: {$dataEmissao} · Últimos {$totalMovimentos} movimentos</p>
      </div>
    </div>

    <div class="kpi-row">
      <div class="kpi-card">
        <div class="kpi-label">Total Movimentos</div>
        <div class="kpi-value">{$totalMovimentos}</div>
      </div>
      <div class="kpi-card entry">
        <div class="kpi-label">Entradas</div>
        <div class="kpi-value entry">{$entrada}</div>
        <div class="kpi-sub">Kz {$valorEntradasFormatado}</div>
      </div>
      <div class="kpi-card exit">
        <div class="kpi-label">Saídas</div>
        <div class="kpi-value exit">{$saida}</div>
        <div class="kpi-sub">Kz {$valorSaidasFormatado}</div>
      </div>
      <div class="kpi-card accent">
        <div class="kpi-label">Saldo Financeiro</div>
        <div class="kpi-value accent">Kz {$saldoFinanceiroFormatado}</div>
      </div>
    </div>

    <div class="section">
      <div class="section-head">
        <h2>Comparativo: Entradas vs Saídas</h2>
      </div>
      <div class="chart-row">
        <div class="c-bar-group">
          <div class="c-bar-track">
            <div class="c-bar-fill entry" style="height:{$alturaEntradas}%"></div>
          </div>
          <span class="c-bar-count">{$entrada}</span>
          <span class="c-bar-label">Entradas</span>
        </div>
        <div class="c-bar-group">
          <div class="c-bar-track">
            <div class="c-bar-fill exit" style="height:{$alturaSaidas}%"></div>
          </div>
          <span class="c-bar-count">{$saida}</span>
          <span class="c-bar-label">Saídas</span>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-head">
        <h2>Registo de movimentações</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Produto</th>
            <th>Qtd</th>
            <th>Utilizador</th>
            <th>Data</th>
            <th>Impacto (Kz)</th>
          </tr>
        </thead>
        <tbody>{$rows}</tbody>
      </table>
    </div>

    <div class="footer">
      <span>Gerado por: {$usuarioNome}</span>
      <span>SYS ESTOQUE · Relatório de Movimentações</span>
    </div>
  </div>
</body>
</html>
HTML;

$pdfPath = PdfReportHelper::buildHtmlToPdf($html, 'movimentacoes.pdf');

if (PHP_SAPI === 'cli') {
    readfile($pdfPath);
} else {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="movimentacoes.pdf"');
    readfile($pdfPath);
}

@unlink($pdfPath);
