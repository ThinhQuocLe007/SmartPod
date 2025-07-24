<?php
include 'config.php';

$query = "SELECT pot_trees.POT_ID, latest_data.HUMIDITY AS humidity, latest_data.TEMPERATURE AS temperature, pot_trees.humd_thres, pot_trees.temp_thres
          FROM pot_trees
          LEFT JOIN (
              SELECT POT_ID, HUMIDITY, TEMPERATURE
              FROM sensor_data AS sd
              WHERE TIMESTAMP = (
                  SELECT MAX(TIMESTAMP)
                  FROM sensor_data
                  WHERE POT_ID = sd.POT_ID
              )
          ) AS latest_data ON pot_trees.POT_ID = latest_data.POT_ID";
$result = mysqli_query($conn, $query);

$response = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate the percentage relative to thresholds
        $row['humidityPercent'] = isset($row['humidity']) && $row['humd_thres'] > 0 ? min(100, ($row['humidity'] / $row['humd_thres']) * 100) : 0;
        $row['tempPercent'] = isset($row['temperature']) && $row['temp_thres'] > 0 ? min(100, ($row['temperature'] / $row['temp_thres']) * 100) : 0;
        $response[] = $row;
    }
}

echo json_encode($response);
mysqli_close($conn);
?>
