<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'config.php';

// Get `POT_ID` from the query parameter and validate it as an integer
$potID = isset($_GET['POT_ID']) ? (int)$_GET['POT_ID'] : 0;
error_log("Received request for POT_ID (per minute): " . $potID); // Log the POT_ID received

if ($potID > 0) {
    // Prepare a statement to fetch average data per minute for the last 30 minutes
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(TIMESTAMP, '%Y-%m-%d %H:%i:00') AS minute,
            AVG(HUMIDITY) AS HUMIDITY,
            AVG(TEMPERATURE) AS TEMPERATURE
        FROM sensor_data
        WHERE POT_ID = ?
        GROUP BY minute
        ORDER BY minute DESC
        LIMIT 30
    ");
    if (!$stmt) {
        error_log("Statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Statement preparation failed."]);
        exit;
    }

    // Bind and execute the statement
    $stmt->bind_param("i", $potID);
    $stmt->execute();
    $result = $stmt->get_result();

    $dataPoints = [];
    while ($row = $result->fetch_assoc()) {
        // Collect each row as a data point
        $dataPoints[] = $row;
    }

    // Reverse the data points to show oldest to newest
    $dataPoints = array_reverse($dataPoints);

    // Output the data as JSON
    echo json_encode($dataPoints);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid POT_ID."]);
}
?>
