<?php
header('Content-Type: application/json');
include 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $potID = $data['POT_ID'] ?? '';
    $startDate = $data['start_date'] ?? '';
    $startTime = $data['start_time'] ?? '';
    $endTime = $data['end_time'] ?? '';

    // Ensure all necessary parameters are provided
    if (!empty($potID) && !empty($startDate) && !empty($startTime) && !empty($endTime)) {
        try {
            // Prepare the DELETE statement
            $stmt = $conn->prepare("DELETE FROM schedules WHERE POT_ID = ? AND start_date = ? AND start_time = ? AND end_time = ?");
            $stmt->bind_param("ssss", $potID, $startDate, $startTime, $endTime);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["success" => true, "message" => "Schedule deleted successfully."]);
                } else {
                    echo json_encode(["success" => false, "message" => "No matching schedule found to delete."]);
                }
            } else {
                throw new Exception("SQL execution error: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Missing required parameters."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}
?>
