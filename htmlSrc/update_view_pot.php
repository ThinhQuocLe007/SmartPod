<?php
// Include database configuration file
include 'config.php';

// Query to retrieve the latest data for each pot tree
$query = "SELECT pot_trees.POT_ID, pot_trees.name, pot_trees.humd_thres, pot_trees.temp_thres, pot_trees.pump, pot_trees.light, 
                 latest_data.HUMIDITY AS humidity, latest_data.TEMPERATURE AS temperature
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
        $response[] = $row;
    }
}

// Output the data as JSON
echo json_encode($response);
mysqli_close($conn);
