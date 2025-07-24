<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'config.php';

// Get today's date
$today = date('Y-m-d');

// Prepare a statement to fetch today's schedules
$stmt = $conn->prepare("SELECT light_state, pump_state, start_time, end_time FROM schedules WHERE start_date = ?");
if (!$stmt) {
    error_log("Statement preparation failed: " . $conn->error);
    echo json_encode(["error" => "Statement preparation failed."]);
    exit;
}

// Bind and execute the statement
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode($schedules);

$stmt->close();
$conn->close();
?>
