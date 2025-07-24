<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database configuration
include 'config.php';

// Fetch POST parameters and sanitize inputs
$potID = isset($_POST['POT_ID']) ? trim($_POST['POT_ID']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';
$state = isset($_POST['state']) ? (int)$_POST['state'] : null;

// Define allowed types to prevent SQL injection
$allowedTypes = ['light', 'pump', 'auto'];

// Validate input
if (empty($potID) || empty($type) || $state === null || !in_array($type, $allowedTypes)) {
    error_log("Invalid request data: POT_ID=$potID, type=$type, state=$state");
    echo json_encode(["error" => "Invalid request data."]);
    exit;
}

try {
    // Prepare the SQL statement for updating the specific column
    $stmt = $conn->prepare("UPDATE pot_trees SET $type = ? WHERE POT_ID = ?");
    if (!$stmt) {
        error_log("Statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Statement preparation failed."]);
        exit;
    }

    // Bind the parameters
    $stmt->bind_param("is", $state, $potID);

    // Execute the statement
    if ($stmt->execute()) {
        // Respond with success message
        echo json_encode(["success" => "$type updated to " . ($state ? 'ON' : 'OFF')]);
    } else {
        // Log and respond with error
        error_log("Failed to execute statement: " . $stmt->error);
        echo json_encode(["error" => "Failed to update $type."]);
    }

    $stmt->close();
} catch (Exception $e) {
    // Catch unexpected errors
    error_log("Unexpected error: " . $e->getMessage());
    echo json_encode(["error" => "An unexpected error occurred."]);
}

// Close the database connection
$conn->close();
?>
