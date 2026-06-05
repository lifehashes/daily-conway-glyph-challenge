<?php
include_once __DIR__ . '/../../priv/db_conf_laniakea.php';

$timeframe = $_GET['timeframe'] ?? 'today';
// $timeframe = 'today';

switch ($timeframe) {
    case 'week':
        // Records from the current calendar week (starts Sunday by default)
        $dateCondition = "YEARWEEK(submitted_at, 1) = YEARWEEK(UTC_DATE(), 1)";
        break;
    case 'month':
        // Records from the current calendar month only
        $dateCondition = "MONTH(submitted_at) = MONTH(UTC_DATE()) AND YEAR(submitted_at) = YEAR(UTC_DATE())";
        break;
    case 'quarter':
        // Records from the current calendar quarter (1, 2, 3, or 4)
        $dateCondition = "QUARTER(submitted_at) = QUARTER(UTC_DATE()) AND YEAR(submitted_at) = YEAR(UTC_DATE())";
        break;
    case 'year':
        // Records from the current calendar year only (Jan 1st to Now)
        $dateCondition = "YEAR(submitted_at) = YEAR(UTC_DATE())";
        break;
    case 'all':
        $dateCondition = "1=1";
        break;
    case 'today':
    default:
        $dateCondition = "submitted_at >= UTC_DATE()";
        break;
}

// $dateCondition = "submitted_at >= '2026-04-26 11:00:00'";
// $dateCondition = "submitted_at >= UTC_DATE()";

$verifiedOnly = isset($_GET['verified']) && $_GET['verified'] === '1'; // Wildcard toggle
// Define the Suite 4 specific filter
$suite4Filter = "";
if ($verifiedOnly) {
    // Only show entries with a valid NIST Pulse ID
    $suite4Filter = " AND nist_pulse_id IS NOT NULL AND nist_pulse_id != 0 AND nist_pulse_id != ''";
}

// 1. Top performers for each suite
// Logic: Higher Iterations (G), Higher Peak (P), Lower Index (I)
// Auto-balancing between suites: Fetch enough candidates to potentially fill the 30 slots
// We grab 30 per suite to ensure if two suites are empty, one can take the full 30.
$query = "(SELECT id, owner, suite, iterations, peak, hash_index, 
           TIMESTAMPDIFF(SECOND, submitted_at, UTC_TIMESTAMP()) as age_seconds
           FROM DAILYGLYPH WHERE $dateCondition AND suite = 8 
           ORDER BY iterations DESC, peak DESC, hash_index ASC LIMIT 30)
          UNION ALL
          (SELECT id, owner, suite, iterations, peak, hash_index,
           TIMESTAMPDIFF(SECOND, submitted_at, UTC_TIMESTAMP()) as age_seconds 
           FROM DAILYGLYPH WHERE $dateCondition AND suite = 6
           ORDER BY iterations DESC, peak DESC, hash_index ASC LIMIT 30)
          UNION ALL
          (SELECT id, owner, suite, iterations, peak, hash_index,
           TIMESTAMPDIFF(SECOND, submitted_at, UTC_TIMESTAMP()) as age_seconds 
           FROM DAILYGLYPH WHERE $dateCondition AND suite = 4 $suite4Filter
           ORDER BY iterations DESC, peak DESC, hash_index ASC LIMIT 30)";

$result = $pdo->query($query);

// Pre-initialize to avoid empty index errors
$raw_data = [8 => [], 6 => [], 4 => []]; 

while($row = $result->fetch()) {
    $raw_data[$row['suite']][] = $row;
}

$final_data = [];
$total_limit = 30;
$per_suite_target = 10;
$suites = [8, 6, 4];

// PHASE 1: Mandatory Minimums
// Take up to 10 from each suite.
foreach ($suites as $s) {
    $take = min(count($raw_data[$s]), $per_suite_target);
    $final_data = array_merge($final_data, array_splice($raw_data[$s], 0, $take));
}

// PHASE 2: Fair Overflow Distribution
// If we still have room, distribute remaining slots evenly 
// between suites that still have data left.
while (count($final_data) < $total_limit) {
    $added_this_round = 0;
    
    foreach ($suites as $s) {
        if (count($final_data) >= $total_limit) break;
        
        if (!empty($raw_data[$s])) {
            $final_data[] = array_shift($raw_data[$s]);
            $added_this_round++;
        }
    }
    
    // If no suite had anything left to give, stop the loop
    if ($added_this_round === 0) break;
}

// 3. Sort for frontend display[cite: 1]
usort($final_data, function($a, $b) {
    if ($a['suite'] != $b['suite']) return $b['suite'] <=> $a['suite'];
    if ($a['iterations'] != $b['iterations']) return $b['iterations'] <=> $a['iterations'];
    return $b['peak'] <=> $a['peak'];
});

/* ========== */

// 2. Latest submitted Glyph
$lastQuery = "SELECT suite, iterations, peak, hash_index, submitted_at, owner 
              FROM DAILYGLYPH 
              ORDER BY id DESC LIMIT 1";
$lastResult = $pdo->query($lastQuery);
$lastGlyph = $lastResult->fetch();

// 3. Total Daily Hashing Query
$totalQuery = "SELECT SUM(attempts) as total FROM DAILYGLYPH WHERE $dateCondition";
$totalResult = $pdo->query($totalQuery);
$totalRow = $totalResult->fetch();
$totalHashings = $totalRow['total'] ?? 0;

// 4. Top 5 Contributors (Sum of attempts)
$topContributorsQuery = "SELECT owner, SUM(attempts) as total_attempts 
                         FROM DAILYGLYPH 
                         WHERE $dateCondition
                         GROUP BY owner 
                         ORDER BY total_attempts DESC 
                         LIMIT 5";
$topResult = $pdo->query($topContributorsQuery);
$topContributors = [];
while($row = $topResult->fetch()) { $topContributors[] = $row; }

// 5. NEW: Average Network Hashings per Minute (Last 5 Minutes)
$fiveMinQuery = "SELECT COALESCE(SUM(attempts), 0) as recent_total 
                 FROM DAILYGLYPH 
                 WHERE submitted_at >= DATE_SUB(
                     (SELECT MAX(submitted_at) FROM DAILYGLYPH), 
                     INTERVAL 5 MINUTE
                 )";
$fiveMinResult = $pdo->query($fiveMinQuery);
$fiveMinRow = $fiveMinResult->fetch();

$recentTotal = $fiveMinRow['recent_total'];
$avgPerMinute = $recentTotal / 5;

// 5. User-specific data (highest submission and contribution to hashings)
$userOwner = isset($_GET['owner']) ? $_GET['owner'] : '';

$userData = [
    'user_best' => 'N/A',
    'user_total_hashes' => 0
];

if ($userOwner !== '') {
    // 1. Get user's highest-ranked Glyph (S.G.P.I. order) using a secure prepared statement
    $bestQuery = "SELECT suite, iterations, peak, hash_index FROM DAILYGLYPH 
                  WHERE owner = ? AND $dateCondition 
                  ORDER BY suite DESC, iterations DESC, peak DESC LIMIT 1";
    $bestStmt = $pdo->prepare($bestQuery);
    $bestStmt->execute([$userOwner]);
    if ($row = $bestStmt->fetch()) {
        $userData['user_best'] = "{$row['suite']}.{$row['iterations']}.{$row['peak']}.{$row['hash_index']}";
    }

    // 2. Get user's total hash attempts using a secure prepared statement
    $countQuery = "SELECT SUM(attempts) as total FROM DAILYGLYPH 
                   WHERE owner = ? AND $dateCondition";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute([$userOwner]);
    if ($row = $countStmt->fetch()) {
        $userData['user_total_hashes'] = $row['total'] ?? 0;
    }
}

// COLLATE OUTPUT
header('Content-Type: application/json');
echo json_encode([
    "data" => $final_data,
    "total_hashings" => $totalHashings,
    "last_glyph" => $lastGlyph,
    "top_contributors" => $topContributors,
    "hash_rate_pm" => $avgPerMinute,
    "user_stats" => $userData
]);
?>