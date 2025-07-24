<?php
include 'config.php';

// Query for new devices in sensor_data that aren't in pot_trees
$query = "SELECT DISTINCT sd.POT_ID
          FROM sensor_data sd
          LEFT JOIN pot_trees pt ON sd.POT_ID = pt.POT_ID
          WHERE pt.POT_ID IS NULL
          LIMIT 1";  // Adjust the limit based on needs

$result = mysqli_query($conn, $query);
$new_device = mysqli_fetch_assoc($result);

if ($new_device) {
    echo json_encode(['new_pot_id' => $new_device['POT_ID']]);
} else {
    echo json_encode(['new_pot_id' => null]);
}

mysqli_close($conn);
?>
