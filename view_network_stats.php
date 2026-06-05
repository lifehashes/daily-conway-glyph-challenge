<?php
include_once __DIR__ . '/../../priv/db_conf_laniakea.php';
$timeframe = $_GET['timeframe'] ?? 'today';

// 2. CONSTRUCT TIME-BASED QUERY
// We determine grouping and labels based on the timeframe
switch ($timeframe) {
    case 'week':
        $dateCondition = "YEARWEEK(submitted_at, 1) = YEARWEEK(UTC_DATE(), 1)";
        $groupBy = "DATE(submitted_at)";
        $labelFormat = "DATE_FORMAT(submitted_at, '%a')"; // Mon, Tue...
        break;
    case 'month':
        $dateCondition = "MONTH(submitted_at) = MONTH(UTC_DATE()) AND YEAR(submitted_at) = YEAR(UTC_DATE())";
        $groupBy = "DATE(submitted_at)";
        $labelFormat = "DATE_FORMAT(submitted_at, '%d')"; // 01, 02...
        break;
    case 'year':
        $dateCondition = "YEAR(submitted_at) = YEAR(UTC_DATE())";
        $groupBy = "MONTH(submitted_at)";
        $labelFormat = "DATE_FORMAT(submitted_at, '%b')"; // Jan, Feb...
        break;
    case 'all':
        $dateCondition = "1=1";
        $groupBy = "DATE_FORMAT(submitted_at, '%Y-%m')";
        $labelFormat = "DATE_FORMAT(submitted_at, '%Y-%m')";
        break;
    case 'today':
    default:
        $dateCondition = "submitted_at >= UTC_DATE()";
        $groupBy = "HOUR(submitted_at)";
        $labelFormat = "CONCAT(HOUR(submitted_at), ':00')";
        break;
}

$query = "SELECT $labelFormat as time_label, SUM(attempts) as total_hashes 
          FROM DAILYGLYPH 
          WHERE $dateCondition 
          GROUP BY $groupBy 
          ORDER BY submitted_at ASC";

$res = $pdo->query($query);
$labels = [];
$values = [];
while($row = $res->fetch()) {
    $labels[] = $row['time_label'];
    $values[] = (int)$row['total_hashes'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Network Hash Stats: <?php echo strtoupper($timeframe); ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #0d1112; color: #fff; font-family: monospace; padding: 20px; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; border: 1px solid #3a474a; padding: 30px; background: rgba(0,0,0,0.6); }
        .nav-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--accent); margin-bottom: 30px; padding-bottom: 10px; }
        .btn-group .tf-btn { background: none; border: 1px solid #444; color: #888; padding: 5px 15px; cursor: pointer; font-family: monospace; margin-left: 5px; }
        .btn-group .tf-btn.active { border-color: var(--accent); color: var(--accent); }
        .chart-wrap { height: 400px; position: relative; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="nav-header">
        <div>
            <div style="font-size: 0.6rem; color: var(--dim-text);">NETWORK PERFORMANCE MONITOR</div>
            <div style="font-size: 1.4rem; color: var(--accent); font-weight: bold;">TOTAL HASH FLOW</div>
        </div>
        <div class="btn-group">
            <?php 
            $tfs = ['today'=>'D','week'=>'W','month'=>'M','year'=>'Y','all'=>'Z'];
            foreach($tfs as $key => $label) {
                $active = ($timeframe == $key) ? 'active' : '';
                echo "<button class='tf-btn $active' onclick=\"window.location.href='?timeframe=$key'\">$label</button>";
            }
            ?>
        </div>
    </div>

    <div class="chart-wrap">
        <canvas id="networkChart"></canvas>
    </div>

    <div style="margin-top: 20px; font-size: 0.7rem; color: var(--dim-text); text-align: center;">
        AGGREGATED NETWORK HASHES FOR THE SELECTED PERIOD (UTC TIME)
    </div>
</div>

<script>
    new Chart(document.getElementById('networkChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Hashes',
                data: <?php echo json_encode($values); ?>,
                backgroundColor: 'rgba(76, 175, 80, 0.4)',
                borderColor: '#4CAF50',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#1a1a1a' }, ticks: { color: '#666' } },
                x: { grid: { display: false }, ticks: { color: '#666' } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.raw.toLocaleString()} Hashes`
                    }
                }
            }
        }
    });
</script>
</body>
</html>