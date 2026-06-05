<?php
// 1. DATABASE CONNECTION
include_once __DIR__ . '/../../priv/db_conf_laniakea.php';
$targetUser = isset($_GET['user']) ? $_GET['user'] : '';

if ($targetUser === '') { die("No user specified."); }

// 2. DATA RETRIEVAL (Last 24 Hours / Current Day)
// We calculate the second of the day (0-86399) to plot on the X-axis
$hashData = array_fill(0, 86400, 0);
$glyphData = [];

// Query for Hash Activity
$hQuery = "SELECT TIME_TO_SEC(TIME(submitted_at)) as sec, SUM(attempts) as total_hashes 
           FROM DAILYGLYPH 
           WHERE owner = ? AND submitted_at >= UTC_DATE()
           GROUP BY sec";
$hStmt = $pdo->prepare($hQuery);
$hStmt->execute([$targetUser]);

while($row = $hStmt->fetch()) { // Added missing parentheses ()
    $hashData[(int)$row['sec']] = (int)$row['total_hashes'];
}

// Query for Glyph Discoveries
$gQuery = "SELECT TIME_TO_SEC(TIME(submitted_at)) as sec, iterations, suite 
           FROM DAILYGLYPH 
           WHERE owner = ? AND submitted_at >= UTC_DATE()";
$gStmt = $pdo->prepare($gQuery);
$gStmt->execute([$targetUser]);

while($row = $gStmt->fetch()) { // Added missing parentheses ()
    $glyphData[] = [
        'x' => (int)$row['sec'], 
        'y' => (int)$row['iterations'], 
        'suite' => (int)$row['suite']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile: <?php echo htmlspecialchars($targetUser); ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #0d1112; color: #fff; font-family: monospace; padding: 20px; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; border: 1px solid #3a474a; padding: 20px; background: rgba(0,0,0,0.5); }
        .header { border-bottom: 1px solid var(--accent); padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .stat-value { color: var(--accent); font-size: 1.5rem; font-weight: bold; }
        .chart-container { position: relative; height: 300px; margin-bottom: 40px; border: 1px solid #222; padding: 10px; }
        h2 { font-size: 0.8rem; color: var(--gold); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">
        <div style="font-size: 0.7rem; color: var(--dim-text);">NETWORK NODE ANALYTICS</div>
        <div class="stat-value"><?php echo htmlspecialchars($targetUser); ?></div>
        <div style="font-size: 0.6rem; color: var(--dim-text); margin-top: 5px;">ACTIVE SESSION: <?php echo date('Y-m-d'); ?> (UTC)</div>
    </div>

    <h2>01 // Hashing Intensity (Hashes per Second)</h2>
    <div class="chart-container">
        <canvas id="hashChart"></canvas>
    </div>

    <h2>02 // Glyph Discoveries (Generations over Time)</h2>
    <div class="chart-container">
        <canvas id="glyphChart"></canvas>
    </div>
</div>

<script>
    // Data passed from PHP
    const hashRaw = <?php echo json_encode($hashData); ?>;
    const glyphPoints = <?php echo json_encode($glyphData); ?>;

    const secondsInDay = Array.from({length: 86400}, (_, i) => i);

    // Common Chart Configuration
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { 
                type: 'linear', 
                min: 0, max: 86400,
                grid: { color: '#1a1a1a' },
                ticks: {
                    callback: value => {
                        let h = Math.floor(value / 3600);
                        return h + ":00";
                    },
                    color: '#444'
                }
            },
            y: { grid: { color: '#1a1a1a' }, ticks: { color: '#666' } }
        },
        plugins: { legend: { display: false } }
    };

    // 1. Hash Intensity Chart
    new Chart(document.getElementById('hashChart'), {
        type: 'line',
        data: {
            labels: secondsInDay,
            datasets: [{
                data: hashRaw,
                borderColor: '#3498db',
                borderWidth: 1,
                fill: true,
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                pointRadius: 0
            }]
        },
        options: commonOptions
    });

    // 2. Glyph Discoveries Chart
    new Chart(document.getElementById('glyphChart'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Discoveries',
                data: glyphPoints,
                backgroundColor: context => {
                    const suite = context.raw?.suite;
                    return suite === 8 ? '#f1c40f' : (suite === 6 ? '#9b59b6' : '#2ecc71');
                },
                pointRadius: 5
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `Suite ${ctx.raw.suite}: ${ctx.raw.y} Generations`
                    }
                }
            }
        }
    });
</script>

</body>
</html>