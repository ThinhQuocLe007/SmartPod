<?php
header('Content-Type: application/json');
include 'config.php';

$potID = isset($_GET['POT_ID']) ? trim($_GET['POT_ID']) : '';

if (!empty($potID)) {
    try {
        // Fetch start_date and end_date for the current POT_ID
        $stmt = $conn->prepare("SELECT start_date, end_date FROM schedules WHERE pot_id = ?");
        $stmt->bind_param("s", $potID);
        $stmt->execute();
        $result = $stmt->get_result();

        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $startDate = new DateTime($row['start_date']);
            $endDate = new DateTime($row['end_date']);

            // Add all dates between start_date and end_date to the list
            while ($startDate <= $endDate) {
                $dates[] = $startDate->format('Y-m-d');
                $startDate->modify('+1 day');
            }
        }

        echo json_encode($dates);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid POT_ID']);
}
?>
