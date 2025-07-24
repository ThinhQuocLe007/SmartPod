<?php
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST parameters
    $potID = $_POST['POT_ID'] ?? ''; // POT_ID from the form
    $timePeriods = $_POST['time_periods'] ?? []; // Array of time periods
    $startDate = $_POST['start_date'] ?? ''; // Start Date
    $endDate = $_POST['end_date'] ?? ''; // End Date

    // Check if POT_ID, start_date, end_date, and time periods are provided
    if ($potID && $startDate && $endDate && !empty($timePeriods)) {
        foreach ($timePeriods as $period) {
            // Extract time period details
            $startTime = $period['start_time'] ?? '';
            $endTime = $period['end_time'] ?? '';
            $pumpState = strtolower($period['pump_state'] ?? 'off') === 'on' ? 1 : 0; // Map 'ON' to 1 and 'OFF' to 0
            $lightState = strtolower($period['light_state'] ?? 'off') === 'on' ? 1 : 0; // Map 'ON' to 1 and 'OFF' to 0

            // Ensure start and end times are provided
            if ($startTime && $endTime) {
                try {
                    // Prepare the SQL statement to insert into the `schedules` table
                    $stmt = $conn->prepare("INSERT INTO schedules (POT_ID, start_date, end_date, start_time, end_time, pump_state, light_state) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Error preparing SQL statement: " . $conn->error);
                    }

                    // Bind parameters and execute the statement
                    $stmt->bind_param("sssssss", $potID, $startDate, $endDate, $startTime, $endTime, $pumpState, $lightState);
                    if ($stmt->execute()) {
                        echo "Time period added successfully for POT_ID: $potID<br>";
                    } else {
                        throw new Exception("Error executing SQL statement: " . $stmt->error);
                    }

                    // Close the statement
                    $stmt->close();
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "Start and End Time are required for each time period.<br>";
            }
        }
    } else {
        echo "POT_ID, Start Date, End Date, and at least one time period are required!<br>";
    }
}

// Close the database connection
$conn->close();
