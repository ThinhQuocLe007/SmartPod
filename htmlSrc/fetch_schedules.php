<?php
header('Content-Type: application/json');
include 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the POT_ID from GET request
$potID = isset($_GET['POT_ID']) ? trim($_GET['POT_ID']) : '';

if (!empty($potID)) {
    try {
        // Fetch start_date, start_time, and end_time for the specified POT_ID
        $stmt = $conn->prepare("SELECT start_date AS date, start_time, end_time FROM schedules WHERE POT_ID = ?");
        $stmt->bind_param("s", $potID);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules = $result->fetch_all(MYSQLI_ASSOC);

        // Return schedules in JSON format
        echo json_encode(["success" => true, "schedules" => $schedules]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid or missing POT_ID."]);
}
?>
