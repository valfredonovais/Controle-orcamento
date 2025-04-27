<?php
session_start();

$arquivo = 'orcamentos.json';

function carregarOrcamentos($arquivo) {
    if (file_exists($arquivo)) {
        $dados = file_get_contents($arquivo);
        return json_decode($dados, true) ?? [];
    }
    return [];
}

function salvarOrcamentos($arquivo, $dados) {
    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT));
}

$orcamentos = carregarOrcamentos($arquivo);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'adicionar') {
        $categoria = $_POST['categoria'];
        $descricao = $_POST['descricao'];
        $valor = floatval($_POST['valor']);
        $orcamentos[] = [
            'categoria' => $categoria,
            'descricao' => $descricao,
            'valor' => $valor
        ];
        salvarOrcamentos($arquivo, $orcamentos);
    }
    if ($_POST['acao'] == 'excluir') {
        $indice = intval($_POST['indice']);
        if (isset($orcamentos[$indice])) {
            unset($orcamentos[$indice]);
            $orcamentos = array_values($orcamentos);
            salvarOrcamentos($arquivo, $orcamentos);
        }
    }
    if ($_POST['acao'] == 'limpar') {
        $orcamentos = [];
        salvarOrcamentos($arquivo, $orcamentos);
    }
}

$totais = [
    'Investimento' => 0,
    'Despesas da Casa' => 0,
    'Férias e Diversão' => 0
];

$totalGeral = 0;

foreach ($orcamentos as $item) {
    if (isset($totais[$item['categoria']])) {
        $totais[$item['categoria']] += $item['valor'];
        $totalGeral += $item['valor'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento Mensal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f6f8; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; }
        form { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        input, select, button { padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #3498db; color: white; cursor: pointer; }
        button:hover { background-color: #2980b9; }
        .item { background: #e9ecef; padding: 10px; margin-bottom: 10px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .excluir-btn { background-color: #e74c3c; }
        .excluir-btn:hover { background-color: #c0392b; }
        .limpar-btn { background-color: #f39c12; margin-top: 10px; }
        .limpar-btn:hover { background-color: #e67e22; }
        .grafico { margin-top: 30px; }
        @media (max-width: 600px) {
            .item { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Controle de Orçamento</h1>

    <h2>Total Geral: R$ <?= number_format($totalGeral, 2, ',', '.') ?></h2>

    <div class="formulario">
        <form method="post">
            <input type="hidden" name="acao" value="adicionar">
            
            <label>Categoria:</label>
            <select name="categoria" required>
                <option value="Investimento">Investimento</option>
                <option value="Despesas da Casa">Despesas da Casa</option>
                <option value="Férias e Diversão">Férias e Diversão</option>
            </select>

            <label>Descrição:</label>
            <input type="text" name="descricao" required>

            <label>Valor:</label>
            <input type="number" step="0.01" name="valor" required>

            <button type="submit">Adicionar</button>
        </form>
    </div>

    <div class="lista">
        <h2>Itens adicionados:</h2>
        <?php foreach ($orcamentos as $indice => $item): ?>
            <div class="item">
                <div>
                    <strong><?= htmlspecialchars($item['categoria']) ?>:</strong> 
                    <?= htmlspecialchars($item['descricao']) ?> - <strong>R$ <?= number_format($item['valor'], 2, ',', '.') ?></strong>
                </div>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="indice" value="<?= $indice ?>">
                    <button type="submit" class="excluir-btn">Excluir</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post">
        <input type="hidden" name="acao" value="limpar">
        <button type="submit" class="limpar-btn">Limpar Tudo</button>
    </form>

    <div class="grafico">
        <h2>Distribuição</h2>
        <canvas id="graficoPizza" width="400" height="400"></canvas>
    </div>
</div>

<script>
const dados = {
    labels: ['Investimento', 'Despesas da Casa', 'Férias e Diversão'],
    datasets: [{
        label: 'Orçamento',
        data: [
            <?= $totais['Investimento'] ?>,
            <?= $totais['Despesas da Casa'] ?>,
            <?= $totais['Férias e Diversão'] ?>
        ],
        backgroundColor: [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)'
        ],
        borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 206, 86, 1)'
        ],
        borderWidth: 1
    }]
};

const config = {
    type: 'pie',
    data: dados,
};

const graficoPizza = new Chart(
    document.getElementById('graficoPizza'),
    config
);
</script>

</body>
</html>
