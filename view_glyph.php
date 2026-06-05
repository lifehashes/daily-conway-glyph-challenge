<?php
// view_glyph.php
include_once __DIR__ . '/../../priv/db_conf_laniakea.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM DAILYGLYPH WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]); 
$glyph = $stmt->fetch();

if (!$glyph) { die("Glyph not found."); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Glyph Inspector - #<?php echo $id; ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #0d1112; color: #fff; font-family: monospace; padding: 40px; height: auto; min-height: 100%; overflow-y: auto; }
        /* Main Container: max-width increased to accommodate two columns */
        .container { 
            border: 1px solid #3a474a; 
            padding: 20px; 
            max-width: 1000px; 
            margin: 40px auto; 
            height: auto; 
            overflow: visible; 
        }
        
        /* The Split Wrapper */
        .inspector-split { display: flex; gap: 20px; align-items: flex-start; }
        
        /* Column Control */
        .col-left { flex: 1; min-width: 0; } 
        .col-right { 
            
            /* flex: 1; border-left: 1px solid #161e20; padding-left: 20px; min-height: 400px; */

            flex: 1; 
            border-left: 1px solid #161e20; 
            padding-left: 20px; 
            min-height: auto;            
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        
        }
        
        .gold { color: #d4af37; }
        .accent { color: #4CAF50; }
        .bin-box { word-break: break-all; background: #000; padding: 15px; border: 1px solid #222; font-size: 0.8rem; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #161e20; }
    </style>
    <SCRIPT SRC="classes.js"></SCRIPT>
    <SCRIPT SRC="controls.js"></SCRIPT>
    <SCRIPT SRC="auxiliary.js"></SCRIPT>
    <SCRIPT SRC="sha256.js"></SCRIPT>
</head>
<body>
    <div class="container">
        <h2 class="gold">GLYPH INSPECTOR // ID: <?php echo $glyph['id']; ?></h2>
        
        <div class="inspector-split">
            <!-- Left Half: Data and Metadata -->
            <div class="col-left">
                <table>
                    <tr><td>OWNER</td><td class="accent"><?php echo htmlspecialchars($glyph['owner']); ?></td></tr>
                    <tr><td>SUITE</td><td><?php echo $glyph['suite']; ?></td></tr>
                    <tr><td>ITERATIONS</td><td><?php echo $glyph['iterations']; ?></td></tr>
                    <tr><td>PEAK</td><td><?php echo $glyph['peak']; ?></td></tr>
                    <tr><td>INDEX</td><td><?php echo $glyph['hash_index']; ?></td></tr>
                    <tr><td>SUBMITTED</td><td><?php echo $glyph['submitted_at']; ?></td></tr>
                </table>
                
                <h3 class="gold">ORIGIN HASH</h3>
                <div class="bin-box"><?php echo $glyph['origin_hash']; ?></div>
                
                <h3 class="gold">BINARY DATA</h3>
                <div class="bin-box" style="color: #4CAF50;"><?php echo $glyph['bin']; ?></div>
                
                <h3 class="gold">TERMINUS STATE</h3>
                <div class="bin-box"><?php echo htmlspecialchars($glyph['terminus_state']); ?></div>

                <div class="stats-grid">
                    <div class="stat-box">GENERATIONS <span class="stat-val" id="disp-iter">0</span></div>
                    <div class="stat-box">PEAK <span class="stat-val" id="disp-peak">0</span></div>
                    <div class="stat-box">MIN <span class="stat-val" id="disp-min">0</span></div>
                    <div class="stat-box">MAX <span class="stat-val" id="disp-max">0</span></div>
                    <div class="stat-box">HASH <span class="stat-val" id="disp-hash">----</span></div>
                    <div class="stat-box">INDEX <span class="stat-val" id="disp-index">--</span></div>
                </div>

            </div>

            <!-- Right Half: Reserved for Rendering/Visuals -->
            <div class="col-right">
                <div class="diamond-placeholder" style="border-color: #c0c0c0;">
                    <canvas width="256" height="256" id="explCanvas" style="background-color:#0a0a0a; border: 4px solid #c0c0c0;"></canvas>
                </div>

                <div id="originHash">---- ORIGIN HASH ----</div>
                <div id="currentHash">---- CURRENT HASH ----</div>

                <div class="hash-stats-explorer">
                    [ITERATIONS:<span id="outpIt">0</span>][GENERATIONS:<span id="outpStep">0</span>][MIN:<span id="outpMin">0</span>][MAX:<span id="outpMax">0</span>][PEAK:<span id="outpPeak">0</span>][TERMINUS:<span id="terminus">----</span>]
                </div>

                <div id="SGPI">-- S.G.P.I. --</div>

                <div class="button-row">
                    <INPUT TYPE="TEXT" ID="startBin" MAXLENGTH="256" style="width: 80px; margin-bottom: 0;">
                    <button id="btnLoad" onClick="Load();" disabled>LOAD</button>
                </div>

                <div class="button-row">
                    <button id="ctrlRewind" onClick="Restart();" disabled>|&lt;&lt;</button>
                    <button id="ctrlPause" onClick="Pause();" disabled>||</button>
                    <button id="ctrlForw" onClick="Iterate(1);" disabled>&gt;</button>
                    <button id="ctrlPlay" onClick="Iterate(0);" disabled>&gt;&gt;</button>
                </div>

                <!-- Generational Histogram -->
                <div style="margin-top: 20px; border-top: 1px solid #333; padding-top: 15px; width: 256px;">
                    <canvas id="distGraph" width="256" height="192" style="background: #050505;"></canvas>                    
                    <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                        <span style="font-size: 0.6rem; color: var(--gold);">RARITY DISTRIBUTION (PDF)</span>
                        <span id="rarityRatio" style="font-size: 0.6rem; color: #4CAF50; font-weight: bold;">1 in 1</span>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <script>

    var autoHashActive = false;
    var suite4 = true;
    var suite6 = true;
    var suite8 = true;

    const sourceBin = "<?php echo $glyph['bin']; ?>";
    var publicSalt = "<?php echo $glyph['salt_value']; ?>";
    console.log("[view_glyph.php] publicSalt retrieved from database: " + publicSalt);

    var explTorus = new Torus("explCanvas", 0, "circle", "regular", 16, true, sourceBin);
    let originHash = MatrixToString(explTorus.O,"hash");
    updateRarityGraph(<?php echo $glyph['iterations']; ?>);

    document.getElementById("originHash").innerHTML = originHash;
    document.getElementById("ctrlRewind").disabled = true;
    document.getElementById("ctrlPause").disabled = true;
    document.getElementById("ctrlForw").disabled = false;
    document.getElementById("ctrlPlay").disabled = false;

    function updateRarityGraph(currentGen) {
        const canvas = document.getElementById('distGraph');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const ratioEl = document.getElementById('rarityRatio');
        
        const a = 1260.42, b = 0.266, c = 0.015;
        const maxX = 1500; 
        const maxY = 2500; 
        const peakVal = 1885; // Baseline commonality
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // 1. Draw static background curve
        ctx.beginPath();
        // Visual Styling for Visibility
        ctx.strokeStyle = '#2d452f'; 
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.shadowBlur = 5;
        ctx.shadowColor = '#1b2b1c';

        for (let x = 0; x < canvas.width; x++) {
            let valX = (x / canvas.width) * maxX;
            let valY = a * Math.pow(valX, b) * Math.exp(-c * valX);
            let plotX = x;
            let plotY = canvas.height - (valY / maxY) * canvas.height;
            if (x === 0) ctx.moveTo(plotX, plotY);
            else ctx.lineTo(plotX, plotY);
        }
        ctx.stroke();
        ctx.shadowBlur = 0; // resetting blur so it does not bleed into other elements

        // 2. Calculate "1 in X" Rarity[cite: 7]
        const valAtGen = a * Math.pow(currentGen, b) * Math.exp(-c * currentGen);
        
        if (ratioEl) {
            // Calculate ratio: Peak density divided by current density
            // We use Math.max to ensure we don't divide by zero at extreme tails
            let ratio = peakVal / Math.max(valAtGen, 0.0001);
            
            let formattedRatio = Math.ceil(ratio).toLocaleString();
            ratioEl.innerText = "1 in " + formattedRatio;
        }

        // 3. Draw Tracker and Dot[cite: 7]
        let dotX = (currentGen / maxX) * canvas.width;
        let dotY = canvas.height - (valAtGen / maxY) * canvas.height;

        ctx.setLineDash([5, 5]);
        ctx.strokeStyle = 'rgba(212, 175, 55, 0.3)';
        ctx.beginPath();
        ctx.moveTo(dotX, canvas.height);
        ctx.lineTo(dotX, dotY);
        ctx.stroke();
        ctx.setLineDash([]); 

        ctx.fillStyle = '#d4af37';
        ctx.beginPath();
        ctx.arc(dotX, dotY, 4, 0, Math.PI * 2);
        ctx.fill();
    }

    </script>
</body>
</html>