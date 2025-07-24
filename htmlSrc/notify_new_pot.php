<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include your database connection file
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Check if new_pot_id was provided
    if (isset($data['new_pot_id'])) {
        $newPotId = htmlspecialchars($data['new_pot_id']);

        // Store the new POT_ID for the front end to detect and show a pop-up
        file_put_contents("new_pot_id.txt", $newPotId);

        echo json_encode(["status" => "success", "message" => "New POT_ID received"]);
    } else {
        echo json_encode(["status" => "error", "message" => "No POT_ID provided"]);
    }
}
?>
