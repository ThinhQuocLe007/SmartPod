<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'config.php';

// Fetch all pot trees along with the latest sensor data
$query = "SELECT pot_trees.POT_ID, pot_trees.name, pot_trees.humd_thres, pot_trees.temp_thres, pot_trees.pump, pot_trees.light, pot_trees.image_path, pot_trees.created_at, 
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

// Check if there are any pot trees in the database
$potTrees = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $potTrees[] = $row;
    }
} else {
    echo "Error fetching data: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Pot Trees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="view_pot_tree.css">
</head>
<body>

<div class="top-panel">
    <div class="top-panel-left">
        <h1 class="top-panel-iot-title">IoT Smart Pot</h1>
    </div>
    <div class="top-panel-right">
        <a href="view_pot_tree.php">Gallery</a>
        <a href="introduce.html">About this work</a>
        <a href="About.html">Creator</a>
    </div>
</div>

<div class="container">
    <div class="action-bar">
        <button class="add-pot-button" onclick="openModal()">Add New Pot Tree</button>
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search...">
            <button class="search-btn"><i class="fas fa-search"></i></button>
        </div>
        <button id="toggle-view" class="toggle-view-btn">
            <i class="fas fa-th-large"></i>
        </button>
    </div>

    <div class="pot-tree-grid">
        <?php if (!empty($potTrees)): ?>
            <?php foreach ($potTrees as $pot): ?>
                <a href="dashboard.php?POT_ID=<?php echo urlencode($pot['POT_ID']); ?>" class="pot-tree">
                    <form action="remove_pot_tree.php" method="POST" style="display:inline;">
                        <input type="hidden" name="POT_ID" value="<?php echo htmlspecialchars($pot['POT_ID'] ?? ''); ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this pot tree?');">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </form>
                    <img src="<?php echo htmlspecialchars($pot['image_path'] ?? 'uploads/default-image.png'); ?>" alt="Pot Tree Image">
                    <div class="pot-tree-content">
                        <h3><?php echo htmlspecialchars($pot['name'] ?? 'Unnamed Pot'); ?></h3>
                        <div class="timestamp">
                            Added on <?php echo date("d M Y", strtotime($pot['created_at'])); ?>
                        </div>
                        <div class="icon-row">
                            <!-- Check humidity state -->
                            <i class="fas fa-tint icon" style="color: <?php echo ($pot['humidity'] < $pot['humd_thres']) ? 'red' : 'green'; ?>" 
                               title="Humidity: <?php echo $pot['humidity'] ?? 'N/A'; ?>%"></i>

                            <!-- Check temperature state -->
                            <i class="fas fa-thermometer-half icon" style="color: <?php echo ($pot['temperature'] < $pot['temp_thres']) ? 'orange' : 'blue'; ?>" 
                               title="Temperature: <?php echo $pot['temperature'] ?? 'N/A'; ?>°C"></i>

                            <!-- Check pump state -->
                            <i class="fas fa-shower icon" style="color: <?php echo ($pot['pump']) ? 'green' : 'grey'; ?>" 
                               title="Pump: <?php echo ($pot['pump']) ? 'On' : 'Off'; ?>"></i>

                            <!-- Check light state -->
                            <i class="fas fa-lightbulb icon" style="color: <?php echo ($pot['light']) ? 'orange' : 'grey'; ?>" 
                            title="Light: <?php echo ($pot['light']) ? 'On' : 'Off'; ?>">
                            </i>

                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No pot trees found in the database.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Structure for Add New Pot Tree -->
<div id="addPotModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Add New Pot Tree</h2>
        <form id="potTreeForm" action="addnewpot.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="POT_ID">POT ID</label>
                <input type="text" id="POT_ID" name="POT_ID" readonly> <!-- Automatically filled -->
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
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="apply-btn">Apply</button>
        </form>
    </div>
</div>

<script>
    function openModal(potId = '') {
        document.getElementById("POT_ID").value = potId; // Pre-fill POT_ID
        document.getElementById("addPotModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("addPotModal").style.display = "none";
    }

    // Check for new POT_ID from new_pot_id.txt
    function checkForNewPotId() {
        fetch("new_pot_id.txt")
            .then(response => response.text())
            .then(data => {
                const potId = data.trim(); // Remove any extra whitespace
                if (potId) {
                    openModal(potId); // Open the modal if a new POT_ID is found
                    clearNewPotId(); // Clear the file after processing
                }
            })
            .catch(error => console.error("Error checking for new POT_ID:", error));
    }

    function clearNewPotId() {
        fetch("clear_new_pot_id.php", { method: "POST" })
            .then(() => console.log("Cleared new_pot_id.txt"))
            .catch(error => console.error("Error clearing new_pot_id.txt:", error));
    }

    // Poll every 5 seconds for new POT_IDs
    setInterval(checkForNewPotId, 1000);
</script>

</body>
</html>
