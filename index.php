<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Conway Glyph Challenge (v221)</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Glyph Freshness Grades colouring */
        .fresh-2m  { color: #00FF41 !important; text-shadow: 0 0 8px rgba(0, 255, 65, 0.6); font-weight: bold; }
        .fresh-15m { color: #2ecc71 !important; font-weight: bold; }
        .fresh-30m { color: #27ae60 !important; }
        .fresh-60m { color: #4caf50 !important; }
        .fresh-2h  { color: #1e8449 !important; }
        .fresh-3h  { color: #145a32 !important; }
        /* Transition for smooth aging */
        .rank-sgpi {
            transition: color 2s ease, text-shadow 2s ease;
        }
    </style>
    <SCRIPT SRC="classes.js"></SCRIPT>
    <SCRIPT SRC="controls.js"></SCRIPT>
    <SCRIPT SRC="auxiliary.js"></SCRIPT>
    <SCRIPT SRC="sha256.js"></SCRIPT>
</head>
<body onload="getNistSalt()">

<div class="main-container" id="main-scroller">
    <!-- COLUMN I: STATISTICS -->

    <div class="stats-panel swipe-slide">
        <div class="refresh-timer-container">
            <div id="refresh-bar"></div>
        </div>        
        <div class="leaderboard-header">STATISTICS</div>
        
        <div style="text-align: center; margin-bottom: 20px; border: 1px solid var(--accent); padding: 10px;">
            <div style="text-align: center; margin-bottom: 20px; border: 1px solid var(--accent); padding: 10px;">
            <a id="network-stats-link" href="view_network_stats.php?timeframe=today" target="_blank" style="text-decoration: none; color: inherit;">
                <div style="font-size: 0.6rem; color: var(--dim-text); cursor: pointer;">TOTAL NETWORK HASHINGS</div>
                <div id="network-hash-count" style="font-size: 1.2rem; color: var(--accent); font-weight: bold; cursor: pointer;">0</div>
            </a>
            </div>
            
            <div style="font-size: 0.6rem; margin-top: 15px; opacity: 0.8; color: var(--dim-text); border-top: 1px solid rgba(76, 175, 80, 0.2); padding-top: 8px;">
                Last Glyph submitted
            </div>
            <div id="last-glyph-sgpi" style="color: var(--gold); font-size: 0.8rem; margin-top: 5px;">-</div>
            <div style="font-size: 0.6rem; color: var(--dim-text); margin-top: 5px;">
                by <span id="last-glyph-owner" style="color: #fff;">---</span><br>
                at <span id="last-glyph-time" style="color: #fff;">--:--:--</span>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px; border: 1px solid #3498db; padding: 10px; background: rgba(52, 152, 219, 0.05);">
            <div style="font-size: 0.6rem; color: var(--dim-text); letter-spacing: 1px;">NETWORK ACTIVITY (5MIN AVG)</div>
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div id="hash-rate-value" style="font-size: 1.2rem; color: #3498db; font-weight: bold; line-height: 1;">0</div>
                <div style="font-size: 0.55rem; color: var(--dim-text); margin-top: 4px; letter-spacing: 1px;">HASHES/MIN</div>
            </div>
        </div>

        <div style="border: 1px solid var(--border-color); padding: 10px; background: rgba(0,0,0,0.3);">
            <div style="font-size: 0.65rem; color: var(--gold); margin-bottom: 10px; text-align: center; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">
                TOP 5 HASH CONTRIBUTORS
            </div>
            <div id="top-contributors-list">
            </div>
        </div>

        <div style="border: 1px solid var(--border-color); padding: 10px; background: rgba(0,0,0,0.3); margin-top: 15px;">
            <div style="font-size: 0.6rem; color: var(--gold); text-align: center; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; margin-bottom: 8px;">
                YOUR HIGHEST SUBMISSION
            </div>
            <div id="user-best-sgpi" style="text-align: center; color: var(--accent); font-size: 0.9rem; font-weight: bold;">---</div>
        </div>

        <div style="border: 1px solid var(--border-color); padding: 10px; background: rgba(0,0,0,0.3); margin-top: 15px;">
            <div style="font-size: 0.6rem; color: var(--gold); text-align: center; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; margin-bottom: 8px;">
                YOUR NETWORK CONTRIBUTION
            </div>
            <div id="user-total-contribution" style="text-align: center; color: var(--accent); font-size: 0.9rem; font-weight: bold;">0</div>
            <div style="font-size: 0.5rem; color: var(--dim-text); text-align: center; margin-top: 4px;">TOTAL HASHES</div>
        </div>      
        
        <div class="link-box">
            <a href="https://discord.gg/AmvxWftK" target="_blank">Join our Discord</a>
        </div>

        <div class="link-box">
            <a href="https://lifehashes.net" target="_parent">HOME</a>
        </div>

        <div class="mobile-promo-box">
        UPDATE: CONTRIBUTOR VIEW
        </div>

    </div>

    <!-- COLUMN II: LEADERBOARDS -->

    <div class="leaderboard-panel swipe-slide" id="leaderboard">
        <div class="leaderboard-header">RANKINGS</div>

        <div class="timeframe-selector">
            <button class="tf-btn active" id="tf-today" onclick="setTimeframe('today', this)">D</button>
            <button class="tf-btn" id="tf-week" onclick="setTimeframe('week', this)">W</button>
            <button class="tf-btn" id="tf-month" onclick="setTimeframe('month', this)">M</button>
            <button class="tf-btn" id="tf-quarter" onclick="setTimeframe('quarter', this)">Q</button>
            <button class="tf-btn" id="tf-year" onclick="setTimeframe('year', this)">Y</button>
            <button class="tf-btn" id="tf-all" onclick="setTimeframe('all', this)">Z</button>
        </div>

        <div id="suite-8"></div>
        <div id="suite-6"></div>
        <div id="suite-4"></div>
    </div>

    <!-- COLUMN III: INPUT PANEL -->

    <div class="sidebar swipe-slide">
        <h1>CONWAY GLYPH CHALLENGE</h1>

        <div class="stage active">
            <div class="stage-label">00 // Hashing Salt (NIST Beacon)</div>
            <div class="salt-display" id="current-salt">SALT: [FETCHING NIST BEACON...]</div>
        </div>
        
        <br>
        <div class="stage active" id="stage1">
            <div class="stage-label">01 // IDENTITY</div>
            <input type="text" id="owner_name" placeholder="ENTER OWNER NAME...">
            <button class="action-btn" onclick="nextStage(2); updateLeaderboard();">Confirm Name</button>
        </div>

        <div class="stage" id="stage2">
            <div class="stage-label">02 // SEEKER (MINING)</div>
            <div class="stage-label" style="margin-top: 15px;">Target Suite Detection</div>
            <div class="suite-selector">
                <div id="tgt-4" class="lcd-toggle active" onclick="uiToggleSuite(4)">Suite 4</div>
                <div id="tgt-6" class="lcd-toggle active" onclick="uiToggleSuite(6)">Suite 6</div>
                <div id="tgt-8" class="lcd-toggle active" onclick="uiToggleSuite(8)">Suite 8</div>
            </div>
            <div class="stats-grid">
                <div class="stat-box">HASH <span class="stat-val" id="disp-hash">----</span></div>
                <div class="stat-box">INDEX <span class="stat-val" id="disp-index">--</span></div>
            </div>
            <button class="action-btn" id="btn-seek" disabled onclick="nextStage(3); SeekStart(); Discard('Explorer');">(Re-)Start Seeker</button>
        </div>

        <div class="stage" id="stage3">
            <div class="stage-label">03 // EXPLORER (EVOLUTION)</div>
            <div class="stats-grid">
                <div class="stat-box">GENERATIONS <span class="stat-val" id="disp-iter">0</span></div>
                <div class="stat-box">PEAK <span class="stat-val" id="disp-peak">0</span></div>
                <div class="stat-box">MIN <span class="stat-val" id="disp-min">0</span></div>
                <div class="stat-box">MAX <span class="stat-val" id="disp-max">0</span></div>
            </div>
            <button class="action-btn" id="btn-explore" disabled onclick="Transfer('Seeker', 'Explorer'); nextStage(4); Iterate(0);">Run Explorer</button>
        </div>

        <div class="stage" id="stage4">
            <div class="stage-label">04 // REGISTRY</div>
            <button class="action-btn" id="btn-submit-stage" style="border-color: var(--gold); color: var(--gold);" disabled>Submit</button>
        </div>

        <div class="control-group">
            <label class="switch">
                <input type="checkbox" id="autoHashToggle">
                <span class="slider round"></span>
            </label>
            <span class="label-text">Auto-Hash Mode</span>
        </div>
        <div class="control-group">
            <label class="switch">
                <input type="checkbox" id="verifiedOnlyToggle" checked onchange="updateLeaderboard()">
                <span class="slider round" style="background-color: #3498db;"></span>
            </label>
            <span class="label-text">Suite-4 Wildcards</span>
        </div>

    </div>

    <!-- COLUMN IV: SEEKER & EXPLORER NODES -->

    <div class="viewport swipe-slide">
        <div class="diamond-container">
            
            <div class="canvas-section">
                <div id="seekHash">---- SEEKER HASH ----</div>
                <div class="diamond-placeholder">
                    <canvas width="256" height="256" id="seekCanvas" style="background-color:#0a0a0a;"></canvas>
                </div>
                <div id="seekAttempt">-- SEEK ATTEMPT --</div>
            </div>

            <div class="canvas-section">
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
            </div>
        </div>
    </div>

</div>

<script>
    var seekTorus = null; 
    var profTorus = null; 
    var signTorus = null; 
    var explTorus = null; 

    var seekHash = null;
    var originHash = null;
    var currentHash = null;

    var publicSalt = null; // Store the NIST salt here
    var nistPulseId = null;

    var seekerFlag = false; 
    var seekCounter = 0;

    var suite4 = true;
    var suite6 = true;
    var suite8 = true;

    var autoHashActive = false;

    var currentTF = 'today';

    const scroller = document.getElementById('main-scroller');
    let isJumping = false; // Prevent recursive trigger

    scroller.addEventListener('scroll', () => {
        if (window.innerWidth > 768 || isAdjusting) return;

        const x = scroller.scrollLeft;
        const max = scroller.scrollWidth - scroller.clientWidth;
        
        const threshold = 30; 

        if (x >= max - threshold) {
            jumpTo(threshold + 1);
        } else if (x <= threshold) {
            jumpTo(max - threshold - 1);
        }
    });

    function jumpTo(position) {
        isAdjusting = true;
        
        // 1. Kill the snap and smooth scroll temporarily
        scroller.style.scrollSnapType = 'none';
        scroller.style.scrollBehavior = 'auto';
        
        // 2. Perform the jump
        scroller.scrollLeft = position;
        
        // 3. Restore behavior after a tiny frame delay
        requestAnimationFrame(() => {
            setTimeout(() => {
                scroller.style.scrollSnapType = 'x mandatory';
                scroller.style.scrollBehavior = 'smooth';
                isAdjusting = false;
            }, 50); 
        });
    }    

    document.getElementById('autoHashToggle').addEventListener('change', function() {
        autoHashActive = this.checked;
        if (autoHashActive) {
            console.log("Auto-Hash enabled. Starting loop...");
            startAutoWorkflow();
        }
    });

    function nextStage(n) {
        // document.querySelectorAll('.stage').forEach(s => s.classList.remove('active'));
        const activeStage = document.getElementById('stage' + n);
        if(activeStage) {
            activeStage.classList.add('active');
            const btn = activeStage.querySelector('button');
            if(btn) btn.disabled = false;
        }
    }

    async function getNistSalt() {
        // Get the timestamp for last midnight UTC in milliseconds
        const now = new Date();
        const lastMidnight = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(), 0, 0, 0);
        
        // NIST API endpoint for the pulse closest to a specific timestamp
        const url = `https://beacon.nist.gov/beacon/2.0/pulse/time/${lastMidnight}`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            // The 'outputValue' is the high-entropy random string
            publicSalt = data.pulse.outputValue;
            nistPulseId = data.pulse.uri;
            
            const dateStr = `${now.getUTCDate()}/${now.getUTCMonth()+1}/${now.getUTCFullYear()}`;
            document.getElementById('current-salt').innerText = `[NIST @ MIDNIGHT ${dateStr}]: ${publicSalt.substring(0, 24)}...`;
            if (now.getUTCMonth() === 2 && now.getUTCDate() === 14) {
                document.getElementById('current-salt').innerText = `[PI DAY SPECIAL]: SEARCHING FOR π DIGITS...`;
            }            
            
            console.log("Verified Public Salt Acquired:", publicSalt);
            return publicSalt;
        } catch (e) {
            document.getElementById('current-salt').innerText = "SALT: ERROR FETCHING NIST BEACON";
            console.error("Could not fetch NIST salt", e);
        }
    }

    function GenerateVerification() {
        // Collect all data for independent verification
        const verificationData = {
            owner: document.getElementById('owner_name').value,
            nist_pulse_reference: nistPulseId,
            salt_value: publicSalt,
            origin_hash: document.getElementById('originHash').innerText,
            terminus_hash: document.getElementById('currentHash').innerText,
            evolution_stats: {
                iterations: document.getElementById('outpIt').innerText,
                peak: document.getElementById('outpPeak').innerText,
                terminus_state: document.getElementById('terminus').innerText
            }
        };

        console.log("--- VERIFICATION EXPORT GENERATED ---");
        console.log(JSON.stringify(verificationData, null, 2));
        alert("Verification Data Generated in Console. Ready for Registry Upload.");
    }

    async function SubmitToRegistry() {
        // 1. Extract only the numerical ID from the NIST URL
        // e.g., "https://beacon.nist.gov/.../1672739" -> "1672739"
        const pulseParts = nistPulseId.split('/');
        const cleanPulseId = pulseParts[pulseParts.length - 1];

        // 2. Construct the submission payload
        const payload = {
            owner: document.getElementById('owner_name').value || "Anonymous Seeker",
            nist_pulse_id: cleanPulseId,
            salt_value: publicSalt,
            origin_hash: document.getElementById('originHash').innerText,
            terminus_hash: document.getElementById('currentHash').innerText,
            iterations: parseInt(document.getElementById('outpIt').innerText),
            peak: parseInt(document.getElementById('outpPeak').innerText),
            terminus_state: document.getElementById('terminus').innerText,
            // Generates "2026-02-24T15:14:59.000Z"
            timestamp: new Date().toISOString() 
        };

        try {
            // 3. POST to backend endpoint
            const response = await fetch('/api/submit-glyph', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const result = await response.json();
                console.log("--- REGISTRY UPLOAD SUCCESSFUL ---", result);
                alert("Glyph registered! Check the leaderboard.");
            } else {
                throw new Error("Registry rejected the Glyph.");
            }
        } catch (error) {
            console.error("Submission Error:", error);
            alert("Upload failed. Are you connected to the Seeker network?");
        }
    }

    document.getElementById('btn-submit-stage').addEventListener('click', mySaveLogic());

    function mySaveLogic() {
        if (!explTorus) { return; }

        const originHash = document.getElementById("originHash").textContent;
        const evalString = document.getElementById("SGPI").textContent;

        const parts = evalString.split('.');
        const iterations = parseInt(parts[1]);

        // --- ENHANCED THRESHOLD CHECK ---
        if (iterations < 10) {
            console.log("Submission bypassed: Generation count " + iterations + " is below threshold.");
            
            // Only show alert if the user is manually clicking "Submit"
            if (!autoHashActive) {
                alert("Sub-Standard Glyph: Only Glyphs with 10+ generations are worthy of the Registry.");
            } else {
                // If Auto-Hash is on, immediately jump back to seeking the next one
                startAutoWorkflow(); 
            }
            return; 
        }
        // --------------------------------

        const ownerNameInput = document.getElementById("owner_name").value.trim();
        const ownerName = ownerNameInput !== "" ? ownerNameInput : "Anonymous";
        const attemptText = document.getElementById("seekAttempt").textContent;
        const attemptsCount = parseInt(attemptText.replace(/\D/g, "")) || 0;
        
        const resultData = {
            owner: ownerName,
            pulseId: Date.now(), 
            salt: publicSalt,
            originHash: originHash,
            suite: checkHashForDate(originHash, "originHash")[0], 
            iterations: iterations,
            peak: parseInt(parts[2]),
            index: parseInt(parts[3]),
            attempts: attemptsCount,
            terminusHash: document.getElementById("currentHash").textContent
        };

        saveGlyphResult(resultData);
        
        this.innerText = "SAVING...";
        this.disabled = true;
    }

    function saveGlyphResult(resultData) {

        const binaryString = MatrixToString(explTorus.O,"binary");

        const payload = {
            owner: resultData.owner,
            nist_pulse_id: resultData.pulseId,
            salt_value: resultData.salt,
            origin_hash: resultData.originHash,
            suite: resultData.suite,
            iterations: resultData.iterations,
            peak: resultData.peak,
            hash_index: resultData.index,
            attempts: resultData.attempts,
            bin: binaryString,
            terminus_hash: resultData.terminusHash
        };

        fetch('save_glyph.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!autoHashActive){ alert("Glyph Archived! ID: " + data.id); }
                document.getElementById('btn-submit-stage').innerText = "SUBMITTED";
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('btn-submit-stage').disabled = false;
            document.getElementById('btn-submit-stage').innerText = "RETRY SUBMIT";
        });
    }

    function setTimeframe(tf, btn) {
        currentTF = tf;

        // Update UI button states
        document.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update the link to the network stats page
        const link = document.getElementById('network-stats-link');
        if (link) { link.href = `view_network_stats.php?timeframe=${currentTF}`; }

        updateLeaderboard();
    }

    function updateLeaderboard() {
        const ownerName = document.getElementById('owner_name').value || '';

        // Get the state of the Wildcard toggle
        const verifiedOnly = document.getElementById('verifiedOnlyToggle').checked ? 1 : 0

        fetch(`get_leaderboard.php?timeframe=${currentTF}&owner=${encodeURIComponent(ownerName)}&verified=${verifiedOnly}`)
            .then(response => response.json())
            .then(data => {
                // Update the Total Counter
                const counterEl = document.getElementById('network-hash-count');
                // Using toLocaleString adds commas (e.g., 1,240,500)
                counterEl.innerText = parseInt(data.total_hashings).toLocaleString();

                // Update Header to reflect currently selected timeframe
                const header = document.querySelector('.leaderboard-panel .leaderboard-header');
                const tfLabels = {today: 'DAILY', week: 'WEEKLY', month: 'MONTHLY', quarter: 'QUARTERLY', year: 'ANNUAL', all: 'ALL-TIME'};
                header.innerText = `${tfLabels[currentTF]} RANKINGS`;

                // Clear current lists
                [4, 6, 8].forEach(s => document.getElementById(`suite-${s}`).innerHTML = `<div class="suite-title">SUITE ${s}</div>`);

                // Update the Last Submitted Glyph entry
                if (data.last_glyph) {
                    const lg = data.last_glyph;
                    // Format SGPI string
                    const sgpi = `${lg.suite}.${lg.iterations}.${lg.peak}.${lg.hash_index}`;
                    document.getElementById('last-glyph-sgpi').innerText = sgpi;

                    // Format timestamp (local time HH:MM:SS)
                    const date = new Date(lg.submitted_at);
                    document.getElementById('last-glyph-time').innerText = 
                        date.toLocaleTimeString([], { hour12: false });

                    const owner = `${lg.owner}`;
                    document.getElementById('last-glyph-owner').innerText = owner;
                }

                // Populate rankings
                data.data.forEach(entry => {
                    // console.log("Processing Entry:", entry.suite, entry.owner); // DEBUG
                    const container = document.getElementById(`suite-${entry.suite}`);
                    if (container) {
                        const sgpi = `${entry.suite}.${entry.iterations}.${entry.peak}.${entry.hash_index}`;
                        const age = entry.age_seconds;
                        let freshnessClass = '';

                        if (age <= 120) freshnessClass = 'fresh-2m';
                        else if (age <= 900) freshnessClass = 'fresh-15m';
                        else if (age <= 1800) freshnessClass = 'fresh-30m';
                        else if (age <= 3600) freshnessClass = 'fresh-60m';
                        else if (age <= 7200) freshnessClass = 'fresh-2h';
                        else if (age > 7200) freshnessClass = 'fresh-3h';

                        const html = `
                            <a href="./view_glyph.php?id=${entry.id}" target="_blank" style="text-decoration: none; color: inherit;">
                                <div class="rank-item" style="cursor: pointer;">
                                    <span class="rank-sgpi ${freshnessClass}">${sgpi}</span>
                                    <span class="rank-user">${entry.owner}</span>
                                </div>
                            </a>`;
                        container.innerHTML += html;
                    }
                });

                // top 5 contributors to the network hashings
                const contributorsContainer = document.getElementById('top-contributors-list');
                contributorsContainer.innerHTML = ""; // Clear existing
                if (data.top_contributors) {
                    data.top_contributors.forEach((user, index) => {
                        const html = `
                        <div style="display: flex; justify-content: space-between; font-size: 0.65rem; margin-bottom: 5px;">
                            <span style="color: var(--dim-text);">
                                ${index + 1}. 
                                <a href="view_user.php?user=${encodeURIComponent(user.owner)}" 
                                style="color: inherit; text-decoration: underline; cursor: pointer;" target="_blank">
                                ${user.owner}
                                </a>
                            </span>
                            <span style="color: var(--accent);">${parseInt(user.total_attempts).toLocaleString()}</span>
                        </div>`;
                        contributorsContainer.innerHTML += html;
                    });
                }

                // Update Network Activity Rate (Average Hashes/Minute for the last 5 minutes)
                const rateEl = document.getElementById('hash-rate-value');
                rateEl.innerText = Math.floor(data.hash_rate_pm).toLocaleString();

                // User-specific info (highest submission and network hashing contributions)
                if (data.user_stats) {
                    document.getElementById('user-best-sgpi').innerText = data.user_stats.user_best;
                    const userTotalEl = document.getElementById('user-total-contribution');
                    userTotalEl.innerHTML = `
                        <a href="view_user.php?user=${encodeURIComponent(ownerName)}" 
                        style="color: var(--accent); text-decoration: none;" target="_blank">
                        ${parseInt(data.user_stats.user_total_hashes).toLocaleString()}
                        </a>`;
                }                

            });
    }

    function uiToggleSuite(num) {
        const el = document.getElementById(`tgt-${num}`);
        
        // Toggle logic
        let newState;
        if (num === 4) newState = suite4 = !suite4;
        if (num === 6) newState = suite6 = !suite6;
        if (num === 8) newState = suite8 = !suite8;

        // Safety: Prevent disabling all targets
        if (!suite4 && !suite6 && !suite8) {
            // Revert logic
            if (num === 4) suite4 = true;
            if (num === 6) suite6 = true;
            if (num === 8) suite8 = true;
            return; // Exit without changing UI
        }

        // Update UI class
        if (newState) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    }

    // Initial load and set interval for "near real-time" (30 seconds)
    updateLeaderboard();
    // setInterval(updateLeaderboard, 30000);

    let timeLeft = 30; // Seconds between refreshes
    const refreshTotal = 30;
    const bar = document.getElementById('refresh-bar');

    function startTimer() {
        setInterval(() => {
            timeLeft--;
            
            // Calculate percentage
            const percentage = (timeLeft / refreshTotal) * 100;
            bar.style.width = percentage + "%";

            if (timeLeft <= 0) {
                updateLeaderboard(); // Trigger the actual data fetch
                timeLeft = refreshTotal; // Reset timer
                bar.style.transition = 'none'; // Snap back to full
                bar.style.width = "100%";
                
                // Re-enable transition for the next shrink cycle
                setTimeout(() => {
                    bar.style.transition = 'width 1s linear';
                }, 50);
            }
        }, 1000);
    }

    // Call this once on page load
    startTimer();

    // AUTO-HASHING
    function startAutoWorkflow() {
        if (!autoHashActive) return;

        nextStage(3); 
        Discard('Explorer');
        SeekStart(); 

    }

</script>

</body>
</html>