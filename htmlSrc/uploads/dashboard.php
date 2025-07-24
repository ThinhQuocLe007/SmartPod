<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'config.php';

// Get `POT_ID` from the query parameter and validate it as an integer
$potID = isset($_GET['POT_ID']) ? trim($_GET['POT_ID']) : '';
error_log("Received request for POT_ID: " . $potID); // Log the POT_ID received

if (!empty($potID)) {
    // Prepare a statement to fetch the pot details
    $stmt = $conn->prepare("SELECT name, image_path, pump, light FROM pot_trees WHERE POT_ID = ?");
    if (!$stmt) {
        error_log("Statement preparation failed: " . $conn->error);
        echo json_encode(["error" => "Statement preparation failed."]);
        exit;
    }

    // Bind and execute the statement
    $stmt->bind_param("s", $potID); // Bind $potID as a string
    $stmt->execute();
    $result = $stmt->get_result();
    $potData = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($potData) {
        error_log("Pot data fetched: " . json_encode($potData));
    } else {
        error_log("No data found for POT_ID: $potID");
        echo json_encode(["error" => "No data found for the given POT_ID."]);
        exit;
    }
} else {
    echo "Invalid POT_ID.";
    error_log("Invalid POT_ID received: $potID");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pot Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard.css">

</head>
<body>

<div class="top-panel">
    <div class="top-panel-left">
        <h1 class="top-panel-iot-title">IoT Smart Pot</h1>
    </div>
    <div class="top-panel-right">
        <a href="view_pot_tree.php">Gallery</a>
        <a href="introduce.html">About this work</a>
        <a href="#">Creator</a>
    </div>
</div>


<div class="container">
    <div class="dashboard">
        <!-- Left Side -->
        <div class="dashboard-left">
            <!-- Pot Overview Section -->
            <div class="potImg-Control">
                <div class="plant-card">
                    <img id="potImage" src="<?php echo htmlspecialchars($potData['image_path'] ?? 'uploads/default-image.png'); ?>" alt="Pot Tree" class="pot-image">
                    <h3 id="plantName"><?php echo htmlspecialchars($potData['name'] ?? 'Unnamed Pot'); ?></h3>
                </div>

                <!-- Control Panel -->
                <div class="Control-Card">
                    <!-- Manual and Auto Controls -->
                    <div class="controlMode-row">
                        <div class="control-group manual <?php echo isset($potData['auto']) && !$potData['auto'] ? 'checked' : ''; ?>" onclick="toggleControl('manual')">
                            <i class="fas fa-hand-paper"></i>
                            <label>Manual</label>
                        </div>
                        <div class="control-group auto <?php echo isset($potData['auto']) && $potData['auto'] ? 'checked' : ''; ?>" onclick="toggleControl('auto')">
                            <i class="fas fa-spinner"></i>
                            <label>Auto</label>
                        </div>
                    </div>
                    <!-- New row for Light and Pump -->
                    <div class="LightPump-row">
                        <!-- Light Control -->
                        <div class="control-group light <?php echo isset($potData['light']) && $potData['light'] ? 'checked' : ''; ?>" onclick="toggleControl('light')">
                            <i class="fas fa-lightbulb"></i>
                            <label>Light</label>
                        </div>
                        <!-- Pump Control -->
                        <div class="control-group pump <?php echo isset($potData['pump']) && $potData['pump'] ? 'checked' : ''; ?>" onclick="toggleControl('pump')">
                            <i class="fas fa-shower"></i>
                            <label>Pump</label>
                        </div>
                    </div>
                    <h3>Control</h3>
                </div>



            </div>

            <div class="chart-card">
                <h3>Humidity and Temperature Levels</h3>
                <canvas id="combinedChart"></canvas>
            </div>

        </div>

        <!-- Right Side-->
        <div class="dashboard-right">
            
            <!-- Calendar Card -->
            <div class="calendar">
                <div class="calendar-header">
                    <span>October 2023</span>
                </div>
                <div class="calendar-days">
                    <div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div><div>S</div>
                    <!-- Generate calendar days dynamically or statically -->
                    <div class="day"></div><div class="day"></div><div class="day"></div><div class="day"></div><div class="day"></div><div class="day">1</div><div class="day">2</div>
                    <div class="day highlighted">3</div><div class="day highlighted">4</div><div class="day">5</div><div class="day highlighted">6</div><div class="day">7</div><div class="day">8</div><div class="day">9</div>
                    <!-- Add more days as needed -->
                </div>
                <div class="legend">
                    <span class="dot highlighted"></span> Harvest
                </div>
            </div>

            <!-- Current Humidity Card -->
            <div class="card humidity-summary-card">
                <h3>Current Humidity</h3>
                <div id="humidityList" class="humidity-list">
                    <!-- List items will be dynamically added here -->
                </div>
            </div>

            <!-- Current Temperature Card -->
            <div class="card temperature-summary-card">
                <h3>Current Temperature</h3>
                <div id="temperatureList" class="temperature-list">
                    <!-- List items will be dynamically added here -->
                </div>
            </div>


        </div>
    </div>
</div>

        



<script>
    const potID = <?php echo json_encode($potID); ?>;
    const updateInterval = 3000;
    let previousData = null;

    // Initialize the combined chart context
    const combinedCtx = document.getElementById('combinedChart').getContext('2d');

    const combinedChart = new Chart(combinedCtx, {
        type: 'line',
        data: {
            labels: [], // Time labels
            datasets: [
                {
                    label: 'Humidity (%)',
                    data: [], // Humidity data
                    borderColor: 'blue',
                    yAxisID: 'y1',
                    fill: false
                },
                {
                    label: 'Temperature (°C)',
                    data: [], // Temperature data
                    borderColor: 'red',
                    yAxisID: 'y2',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Humidity (%)'
                    },
                    min: 0,
                    max: 100
                },
                y2: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    },
                    min: 20,
                    max: 40,
                    grid: {
                        drawOnChartArea: false // Avoid overlapping gridlines
                    }
                }
            }
        }
    });

    async function fetchData() {
        try {
            const response = await fetch(`get_latest_data.php?POT_ID=${potID}`);
            const data = await response.json();

            if (JSON.stringify(data) !== JSON.stringify(previousData)) {
                previousData = data;

                // Update Current Humidity List
                const humidityList = document.getElementById("humidityList");
                humidityList.innerHTML = "";
                let previousHumidity = null;

                data.forEach((point, index) => {
                    const time = new Date(point.TIMESTAMP).toLocaleTimeString();
                    const currentHumidity = parseFloat(point.HUMIDITY.toFixed(2));
                    const listItem = document.createElement("div");
                    listItem.className = "humidity-list-item";

                    const timeElement = document.createElement("span");
                    timeElement.textContent = time;

                    const valueElement = document.createElement("span");
                    valueElement.textContent = `${currentHumidity}%`;

                    const changeElement = document.createElement("span");
                    if (index === 0) {
                        changeElement.textContent = "-";
                    } else {
                        const change = (currentHumidity - previousHumidity).toFixed(2);
                        changeElement.textContent = change > 0 ? `+${change}` : change;
                        changeElement.style.color = change > 0 ? "green" : "red";
                    }

                    previousHumidity = currentHumidity;
                    listItem.appendChild(timeElement);
                    listItem.appendChild(valueElement);
                    listItem.appendChild(changeElement);
                    humidityList.appendChild(listItem);
                });

                // Update Current Temperature List
                const temperatureList = document.getElementById("temperatureList");
                temperatureList.innerHTML = "";
                let previousTemperature = null;

                data.forEach((point, index) => {
                    const time = new Date(point.TIMESTAMP).toLocaleTimeString();
                    const currentTemperature = parseFloat(point.TEMPERATURE.toFixed(2));
                    const listItem = document.createElement("div");
                    listItem.className = "temperature-list-item";

                    const timeElement = document.createElement("span");
                    timeElement.textContent = time;

                    const valueElement = document.createElement("span");
                    valueElement.textContent = `${currentTemperature}°C`;

                    const changeElement = document.createElement("span");
                    if (index === 0) {
                        changeElement.textContent = "-";
                    } else {
                        const change = (currentTemperature - previousTemperature).toFixed(2);
                        changeElement.textContent = change > 0 ? `+${change}` : change;
                        changeElement.style.color = change > 0 ? "green" : "red";
                    }

                    previousTemperature = currentTemperature;
                    listItem.appendChild(timeElement);
                    listItem.appendChild(valueElement);
                    listItem.appendChild(changeElement);
                    temperatureList.appendChild(listItem);
                });

                // Update Combined Chart
                combinedChart.data.labels = [];
                combinedChart.data.datasets[0].data = []; // Humidity dataset
                combinedChart.data.datasets[1].data = []; // Temperature dataset

                data.forEach(point => {
                    const time = new Date(point.TIMESTAMP).toLocaleTimeString();
                    combinedChart.data.labels.push(time);
                    combinedChart.data.datasets[0].data.push(point.HUMIDITY);
                    combinedChart.data.datasets[1].data.push(point.TEMPERATURE);
                });

                combinedChart.update();
            }
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    function toggleControl(controlType) {
    const controlElement = document.querySelector(`.control-group.${controlType}`);
    const isChecked = controlElement.classList.toggle('checked');
    const state = isChecked ? 1 : 0;

    console.log(`Sending request: POT_ID=${potID}, type=${controlType}, state=${state}`);

    fetch(`update_control.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `POT_ID=${encodeURIComponent(potID)}&type=${encodeURIComponent(controlType)}&state=${state}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);
        if (data.success) {
            console.log(data.success);
        } else {
            console.error(data.error);
        }
    })
    .catch(error => console.error("Error updating control:", error));
}


    function sendControlState(controlType, state) {
        fetch(`update_control.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `POT_ID=${potID}&type=${controlType}&state=${state}`
        }).then(() => console.log(`${controlType} updated successfully.`))
        .catch(error => console.error("Error updating control:", error));
    }



    fetchData();
    setInterval(fetchData, updateInterval);
</script>

</body>
</html>
