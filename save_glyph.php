<?php
/**
 * DAILY GLYPH REGISTRY - SECURE UPLOAD
 * Prevents duplicate binary pattern spamming and validates payload integrity.
 */

// Database connection
include_once __DIR__ . '/../../priv/db_conf_laniakea.php';

// Get the JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // 1. DATA VALIDATION: Ensure binary pattern is present
    if (empty($data['bin'])) {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "error" => "MISSING_PATTERN", "message" => "No binary pattern detected."]);
        exit;
    }

    // 2a. ANTI-SPAM: Check if this binary pattern already exists in the registry
    $checkStmt = $pdo->prepare("SELECT id FROM DAILYGLYPH WHERE bin = ? LIMIT 1");
    $checkStmt->execute([$data['bin']]);
    
    if ($checkStmt->fetch()) {
        // Pattern is a duplicate; reject the upload
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false, 
            "error" => "DUPLICATE_PATTERN", 
            "message" => "This exact layout has already been computed and logged today."
        ]);
        exit;
    }

    // 2b. SANITY CHECK: Minimum generation validation
    if (intval($data['iterations']) < 10) {
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false, 
            "error" => "LOW_GENERATION_COUNT", 
            "message" => "Glyph rejected: Generations must be >= 10."
        ]);
        exit;
    }

    // 3. REGISTRY INSERTION
    $query = "INSERT INTO DAILYGLYPH 
        (owner, nist_pulse_id, salt_value, origin_hash, suite, iterations, peak, hash_index, attempts, bin, terminus_hash) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([
            $data['owner'], 
            $data['nist_pulse_id'], 
            $data['salt_value'], 
            $data['origin_hash'], 
            $data['suite'], 
            $data['iterations'], 
            $data['peak'], 
            $data['hash_index'],
            $data['attempts'], 
            $data['bin'], 
            $data['terminus_hash']
        ]);

        header('Content-Type: application/json');
        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        
    } catch (\PDOException $e) {
        header('Content-Type: application/json');
        // Check for race-condition duplicate error (MySQL Native Error 1062)
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
            echo json_encode([
                "success" => false, 
                "error" => "DUPLICATE_GLYPH", 
                "message" => "Another node just registered this exact glyph payload."
            ]);
        } else {
            // General fallback database error
            echo json_encode([
                "success" => false, 
                "error" => "DATABASE_ERROR", 
                "message" => "System execution failure."
            ]);
        }
    }
}
?>