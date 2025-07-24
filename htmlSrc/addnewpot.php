<?php
// Enable error reporting to catch any issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'config.php';

// Initialize a message variable for feedback
$message = "";

// Get POT_ID from URL if available and sanitize it
$POT_ID = isset($_GET['POT_ID']) ? htmlspecialchars($_GET['POT_ID']) : '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $POT_ID = $_POST['POT_ID'];
    $name = $_POST['name'];
    $humd_thres = $_POST['humd_thres'];
    $temp_thres = $_POST['temp_thres'];
    $image = $_FILES['image'];

    // Check if the POT_ID already exists in the database
    $checkQuery = "SELECT * FROM pot_trees WHERE POT_ID = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "s", $POT_ID);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        $message = "Error: POT_ID already exists. Please use a different POT_ID.";
    } else {
        // Proceed if POT_ID is unique
        if ($image['error'] == 0) {
            // Define the upload directory and create a unique file name
            $uploadDir = 'uploads/';
            $imagePath = $uploadDir . basename($image['name']);

            // Create the uploads directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file to the upload directory
            if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                // Prepare the SQL insert statement for pot_trees
                $sql = "INSERT INTO pot_trees (POT_ID, name, humd_thres, temp_thres, pump, light, image_path) VALUES (?, ?, ?, ?, 0, 0, ?)";
                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {
                    // Bind the parameters for pot_trees
                    mysqli_stmt_bind_param($stmt, "ssdds", $POT_ID, $name, $humd_thres, $temp_thres, $imagePath);

                    // Execute the statement for pot_trees
                    if (mysqli_stmt_execute($stmt)) {
                        // Insert a new entry into sensor_data with initial values (e.g., NULL or 0)
                        $sensorDataSql = "INSERT INTO sensor_data (POT_ID, HUMIDITY, TEMPERATURE, TIMESTAMP) VALUES (?, NULL, NULL, NOW())";
                        $sensorDataStmt = mysqli_prepare($conn, $sensorDataSql);

                        if ($sensorDataStmt) {
                            // Bind the POT_ID parameter for sensor_data
                            mysqli_stmt_bind_param($sensorDataStmt, "s", $POT_ID);

                            // Execute the statement for sensor_data
                            if (mysqli_stmt_execute($sensorDataStmt)) {
                                // Redirect to view_pot_tree.php after successful addition
                                header("Location: view_pot_tree.php");
                                exit; // Prevent further script execution
                            } else {
                                $message = "Error: Could not execute sensor_data query: " . mysqli_stmt_error($sensorDataStmt);
                            }

                            // Close the sensor_data statement
                            mysqli_stmt_close($sensorDataStmt);
                        } else {
                            $message = "Error: Could not prepare sensor_data query: " . mysqli_error($conn);
                        }
                    } else {
                        $message = "Error: Could not execute pot_trees query: " . mysqli_stmt_error($stmt);
                    }

                    // Close the pot_trees statement
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "Error: Could not prepare pot_trees query: " . mysqli_error($conn);
                }
            } else {
                $message = "Error: Could not upload the image.";
            }
        } else {
            $message = "Error: There was an issue with the image upload.";
        }
    }

    // Close the check statement and database connection
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pot Tree</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background-color: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="file"] { width: 100%; padding: 8px; box-sizing: border-box; }
        .apply-btn { display: block; width: 100%; padding: 10px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .apply-btn:hover { background-color: #218838; }
        .message { margin-bottom: 15px; font-weight: bold; color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Pot Tree</h2>

        <!-- Display the message -->
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, 'Error') === 0 ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <form id="potTreeForm" action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="POT_ID">POT ID</label>
                <input type="text" id="POT_ID" name="POT_ID" value="<?php echo $POT_ID; ?>" readonly required>
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="humd_thres">Humidity Threshold (%)</label>
                <input type="number" id="humd_thres" name="humd_thres" required>
            </div>
            <div class="form-group">
                <label for="temp_thres">Temperature Threshold (°C)</label>
                <input type="number" id="temp_thres" name="temp_thres" required>
            </div>
            <div class="form-group">
                <label for="image">Image of Pot Tree</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="apply-btn">Apply</button>
        </form>
    </div>
</body>
</html>
