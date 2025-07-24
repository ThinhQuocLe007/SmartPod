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

                <!-- Humidity card -->
                <div class="humidity-card">
                    <h3>Humidity</h3>
                    <div class="humid-content">
                        <span id="HumidityInfo" class="info">Details</span>
                        <span id="currentHumidityValue" class="value">-</span>
                        <span id="previousHumidityValue" class="previous-value">Previous: -</span>
                    </div>
                    <button id="humidityPopupButton">View History</button>
                </div>
                <!-- Temperature card -->
                <div class="temperature-card">
                    <h3>Temperature</h3>
                    <div class="temperature-content">
                        <span id="TemperatureInfo" class="info">Details</span>
                        <span id="TemperatureValue" class="value">-</span>
                        <span id="previousTemperatureValue" class="previous-value">Previous: -</span>
                    </div>
                    <button id="temperaturePopupButton">View History</button>
                </div>

                <!-- Humidity Modal -->
                <div id="humidityModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" id="closeHumidityModal">&times;</span>
                        <h2>Humidity History</h2>
                        <div id="humidityList" class="humidity-list">
                            <!-- Humidity history content will be dynamically populated here -->
                        </div>
                    </div>
                </div>

                <!-- Temperature Modal -->
                <div id="temperatureModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" id="closeTemperatureModal">&times;</span>
                        <h2>Temperature History</h2>
                        <div id="temperatureList" class="temperature-list">
                            <!-- Temperature history content will be dynamically populated here -->
                        </div>
                    </div>
                </div>


                <!-- Control Panel -->
                <div class="control-calendar-container">
                    <!-- Control Card -->
                    <div class="Control-Card">
                        <div class="controlMode-row">
                            <div id="manualMode" class="control-group manual">
                                <i class="fas fa-hand-paper"></i>
                            <label>Manual</label>
                        </div>
                        <div id="autoMode" class="control-group auto checked">
                            <i class="fas fa-spinner"></i>
                            <label>Auto</label>
                        </div>
                    </div>
                        <div class="LightPump-row">
                            <div id="lightControl" class="control-group light disabled">
                                <i class="fas fa-lightbulb"></i>
                                <label>Light</label>
                        </div>
                        <div id="pumpControl" class="control-group pump disabled">
                            <i class="fas fa-shower"></i>
                            <label>Pump</label>
                        </div>
                </div>
                        <h3>Control</h3>
                    </div>

                    <!-- Calendar Card -->
                </div>



            </div>

            <div class="chart-card">
                <h3>Humidity and Temperature Levels</h3>
                <button id="toggleIntervalButton">Switch to Per Minute</button>
                <canvas id="combinedChart"></canvas>
            </div>

        </div>

        <!-- Right Side-->
        <div class="dashboard-right">
            <div id="calendar-container" class="calendar-summary-card">
                <div class="schedule-controls">
                    <button id="openModal" class="schedule-button">
                        <i class="fas fa-calendar-alt"></i> Set Schedule
                    </button>
                    <button id="openDeleteModal" class="delete-button">
                        <i class="fas fa-trash-alt"></i> Delete Schedule
                    </button>
                </div>
                <div id="calendar"></div>
            </div>



            <div id="scheduleListContainer" class="card schedule-list-card">
                <h3>Today's Schedule Overview</h3>
                    <ul class="color-legend">
                        <li><span class="color-box green"></span> Light and Pump ON</li>
                        <li><span class="color-box yellow"></span> Light ON, Pump OFF</li>
                        <li><span class="color-box blue"></span> Light OFF, Pump ON</li>
                        <li><span class="color-box gray"></span> Light and Pump OFF</li>
                    </ul>
                <ul id="scheduleList">
                    <!-- Schedule list items will be dynamically populated -->
                </ul>
                <div id="scheduleColorDescription" class="color-description">
                    
                </div>

            </div>


            <!-- Calendar Section -->
            
        </div>
    </div>
</div>

<!-- Add schedule popups -->
<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h2>Set SmartPot Schedule</h2>
        <form id="scheduleForm">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            <div id="timePeriods">
                <div class="time-period">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="time_periods[0][start_time]" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="time_periods[0][end_time]" required>
                    </div>
                    <div class="form-group">
                        <label for="pump_state">Pump State</label>
                        <select name="time_periods[0][pump_state]">
                            <option value="ON">ON</option>
                            <option value="OFF">OFF</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="light_state">Light State</label>
                        <select name="time_periods[0][light_state]">
                            <option value="ON">ON</option>
                            <option value="OFF">OFF</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="button" id="addTimePeriod">+ Add Another Time Period</button>
            <button type="submit">Add Schedule</button>
        </form>
    </div>
</div>
        
<!-- Delete Schedule Modal -->
<div id="deleteScheduleModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeDeleteModal">&times;</span>
        <h2>Delete Scheduled Dates</h2>
        <div id="scheduledDatesList">
            <!-- Scheduled dates will be dynamically populated here -->
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const manualModeButton = document.getElementById("manualMode");
    const autoModeButton = document.getElementById("autoMode");
    const lightControlButton = document.getElementById("lightControl");
    const pumpControlButton = document.getElementById("pumpControl");

    // Initialize default mode to Auto
    let isAutoMode = true;

    function setMode(auto) {
        isAutoMode = auto;
        if (auto) {
            // Auto mode enabled
            autoModeButton.classList.add("checked");
            manualModeButton.classList.remove("checked");
            lightControlButton.classList.add("disabled");
            pumpControlButton.classList.add("disabled");

            // Update the mode to Auto
            sendControlState("auto", 1);
        } else {
            // Manual mode enabled
            autoModeButton.classList.remove("checked");
            manualModeButton.classList.add("checked");
            lightControlButton.classList.remove("disabled");
            pumpControlButton.classList.remove("disabled");

            // Update the mode to Manual
            sendControlState("auto", 0);

            // Set pump and light to OFF when switching to Manual
            sendControlState("pump", 0);
            sendControlState("light", 0);
        }
    }

    // Event Listeners for Mode Buttons
    manualModeButton.addEventListener("click", () => {
        if (isAutoMode) setMode(false);
    });

    autoModeButton.addEventListener("click", () => {
        if (!isAutoMode) setMode(true);
    });

    // Event Listeners for Light and Pump Controls
    function toggleControl(controlType, button) {
        if (!isAutoMode) {
            const isActive = button.classList.toggle("checked");
            const state = isActive ? 1 : 0;
            sendControlState(controlType, state);
        }
    }

    lightControlButton.addEventListener("click", () => toggleControl("light", lightControlButton));
    pumpControlButton.addEventListener("click", () => toggleControl("pump", pumpControlButton));

    // Send Control State to Server
    function sendControlState(type, state) {
        fetch(`update_control.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `POT_ID=${encodeURIComponent(potID)}&type=${encodeURIComponent(type)}&state=${state}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`${type} updated successfully.`);
            } else {
                console.error(`Error updating ${type}:`, data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // Initialize Default State
    setMode(true);
});


    const potID = <?php echo json_encode($potID); ?>;
    const updateInterval = 3000;
    const modal = document.getElementById("scheduleModal");
    const openModalBtn = document.getElementById("openModal");
    const closeModalBtn = document.getElementById("closeModal");
    const form = document.getElementById("scheduleForm");
    const timePeriods = document.getElementById("timePeriods");
    let timePeriodCount = 1;
    let currentInterval = "perSecond"; // Default interval
    // Open modal
    openModalBtn.onclick = () => {
        modal.style.display = "flex";
    };

    // Close modal
    closeModalBtn.onclick = () => {
        modal.style.display = "none";
    };

    // Close modal when clicking outside of it
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // Add new time period
    document.getElementById("addTimePeriod").onclick = () => {
        const newPeriod = document.createElement("div");
        newPeriod.classList.add("time-period");
        newPeriod.innerHTML = `
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" name="time_periods[${timePeriodCount}][start_time]" required>
            </div>
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" name="time_periods[${timePeriodCount}][end_time]" required>
            </div>
            <div class="form-group">
                <label for="pump_state">Pump State</label>
                <select name="time_periods[${timePeriodCount}][pump_state]">
                    <option value="ON">ON</option>
                    <option value="OFF">OFF</option>
                </select>
            </div>
            <div class="form-group">
                <label for="light_state">Light State</label>
                <select name="time_periods[${timePeriodCount}][light_state]">
                    <option value="ON">ON</option>
                    <option value="OFF">OFF</option>
                </select>
            </div>
        `;
        timePeriods.appendChild(newPeriod);
        timePeriodCount++;
    };

    // Submit schedule form
    form.onsubmit = async (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        formData.append("POT_ID", <?php echo json_encode($potID); ?>);

        const response = await fetch("add_schedule.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.text();
        alert(result);
        modal.style.display = "none";
    };

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
    
    function navigateToSchedule() {
        window.location.href = `scheduleform.php?POT_ID=${encodeURIComponent(potID)}`;
    }

    let previousHumidity = null; // Global to retain state
    let previousTemperature = null; // Global to retain state

    async function fetchData() {
        try {
            const response = await fetch(`get_latest_data.php?POT_ID=${potID}`);
            const data = await response.json();

            if (JSON.stringify(data) !== JSON.stringify(previousData)) {
                previousData = data;

                if (data.length > 0) {
                    // Get the latest and second-to-last data points
                    const latestData = data[data.length - 1];
                    const previousDataPoint = data.length > 1 ? data[data.length - 2] : null;

                    // Update Humidity Card
                    const humidityCard = document.getElementById("currentHumidityValue");
                    const previousHumidityCard = document.getElementById("previousHumidityValue");
                    const humidityInfo = document.getElementById("HumidityInfo");
                    const currentHumidity = parseFloat(latestData.HUMIDITY.toFixed(2));
                    humidityCard.textContent = `${currentHumidity}%`;

                    if (previousDataPoint) {
                        const previousHumidity = parseFloat(previousDataPoint.HUMIDITY.toFixed(2));
                        previousHumidityCard.textContent = `Previous: ${previousHumidity}%`;

                        // Calculate and display the change
                        const humidityChange = (currentHumidity - previousHumidity).toFixed(2);
                        const changeElement = document.createElement("span");
                        changeElement.textContent = ` (${humidityChange > 0 ? '+' : ''}${humidityChange}%)`;
                        changeElement.style.color = humidityChange > 0 ? "green" : "red";
                        humidityCard.appendChild(changeElement);
                    } else {
                        previousHumidityCard.textContent = "Previous: -";
                    }

                    // Update Humidity Info with timestamp
                    const humidityTime = new Date(latestData.TIMESTAMP).toLocaleString();
                    humidityInfo.textContent = `${humidityTime}`;

                    // Update Temperature Card
                    const temperatureCard = document.getElementById("TemperatureValue");
                    const previousTemperatureCard = document.getElementById("previousTemperatureValue");
                    const temperatureInfo = document.getElementById("TemperatureInfo");
                    const currentTemperature = parseFloat(latestData.TEMPERATURE.toFixed(2));
                    temperatureCard.textContent = `${currentTemperature}°C`;

                    if (previousDataPoint) {
                        const previousTemperature = parseFloat(previousDataPoint.TEMPERATURE.toFixed(2));
                        previousTemperatureCard.textContent = `Previous: ${previousTemperature}°C`;

                        // Calculate and display the change
                        const temperatureChange = (currentTemperature - previousTemperature).toFixed(2);
                        const changeElement = document.createElement("span");
                        changeElement.textContent = ` (${temperatureChange > 0 ? '+' : ''}${temperatureChange}°C)`;
                        changeElement.style.color = temperatureChange > 0 ? "green" : "red";
                        temperatureCard.appendChild(changeElement);
                    } else {
                        previousTemperatureCard.textContent = "Previous: -";
                    }

                    // Update Temperature Info with timestamp
                    const temperatureTime = new Date(latestData.TIMESTAMP).toLocaleString();
                    temperatureInfo.textContent = `${temperatureTime}`;
                }

                // Update Humidity Modal List
                const humidityList = document.getElementById("humidityList");
                humidityList.innerHTML = ""; // Clear previous list
                let lastHumidity = null;

                data.forEach((point) => {
                    const time = new Date(point.TIMESTAMP).toLocaleTimeString();
                    const currentHumidity = parseFloat(point.HUMIDITY.toFixed(2));
                    const listItem = document.createElement("div");
                    listItem.className = "humidity-list-item";

                    const timeElement = document.createElement("span");
                    timeElement.textContent = time;

                    const valueElement = document.createElement("span");
                    valueElement.textContent = `${currentHumidity}%`;

                    const changeElement = document.createElement("span");
                    if (lastHumidity !== null) {
                        const change = (currentHumidity - lastHumidity).toFixed(2);
                        changeElement.textContent = change > 0 ? `+${change}` : change;
                        changeElement.style.color = change > 0 ? "green" : "red";
                    } else {
                        changeElement.textContent = "-";
                    }

                    listItem.appendChild(timeElement);
                    listItem.appendChild(valueElement);
                    listItem.appendChild(changeElement);

                    humidityList.appendChild(listItem);
                    lastHumidity = currentHumidity; // Update the last value for next calculation
                });

                // Update Temperature Modal List
                const temperatureList = document.getElementById("temperatureList");
                temperatureList.innerHTML = ""; // Clear previous list
                let lastTemperature = null;

                data.forEach((point) => {
                    const time = new Date(point.TIMESTAMP).toLocaleTimeString();
                    const currentTemperature = parseFloat(point.TEMPERATURE.toFixed(2));
                    const listItem = document.createElement("div");
                    listItem.className = "temperature-list-item";

                    const timeElement = document.createElement("span");
                    timeElement.textContent = time;

                    const valueElement = document.createElement("span");
                    valueElement.textContent = `${currentTemperature}°C`;

                    const changeElement = document.createElement("span");
                    if (lastTemperature !== null) {
                        const change = (currentTemperature - lastTemperature).toFixed(2);
                        changeElement.textContent = change > 0 ? `+${change}` : change;
                        changeElement.style.color = change > 0 ? "green" : "red";
                    } else {
                        changeElement.textContent = "-";
                    }

                    listItem.appendChild(timeElement);
                    listItem.appendChild(valueElement);
                    listItem.appendChild(changeElement);

                    temperatureList.appendChild(listItem);
                    lastTemperature = currentTemperature; // Update the last value for next calculation
                });


                // Update Combined Chart
                combinedChart.data.labels = data.map((point) =>
                    new Date(point.TIMESTAMP).toLocaleTimeString()
                );
                combinedChart.data.datasets[0].data = data.map((point) => point.HUMIDITY);
                combinedChart.data.datasets[1].data = data.map((point) => point.TEMPERATURE);
                combinedChart.update();
            }
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }


    document.addEventListener("DOMContentLoaded", () => {
        // Get buttons and modals
        const humidityPopupButton = document.getElementById("humidityPopupButton");
        const temperaturePopupButton = document.getElementById("temperaturePopupButton");
        const humidityModal = document.getElementById("humidityModal");
        const temperatureModal = document.getElementById("temperatureModal");
        const closeHumidityModal = document.getElementById("closeHumidityModal");
        const closeTemperatureModal = document.getElementById("closeTemperatureModal");

        // Open Humidity Modal
        humidityPopupButton.addEventListener("click", () => {
            humidityModal.style.display = "flex";
        });

        // Open Temperature Modal
        temperaturePopupButton.addEventListener("click", () => {
            temperatureModal.style.display = "flex";
        });

        // Close Humidity Modal
        closeHumidityModal.addEventListener("click", () => {
            humidityModal.style.display = "none";
        });

        // Close Temperature Modal
        closeTemperatureModal.addEventListener("click", () => {
            temperatureModal.style.display = "none";
        });

        // Close modals when clicking outside
        window.addEventListener("click", (event) => {
            if (event.target === humidityModal) {
                humidityModal.style.display = "none";
            }
            if (event.target === temperatureModal) {
                temperatureModal.style.display = "none";
            }
        });
    });
    

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

    document.addEventListener("DOMContentLoaded", async () => {
        const calendarContainer = document.getElementById("calendar");
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // Fetch scheduled dates for the current POT_ID
        async function fetchScheduledDates() {
            const response = await fetch(`get_scheduled_dates.php?POT_ID=${encodeURIComponent(potID)}`);
            return response.json();
        }

        // Render calendar with navigation and highlighted dates
        function renderCalendar(scheduledDates) {
            const calendarContainer = document.getElementById("calendar");
            calendarContainer.innerHTML = "";

            const navHeader = document.createElement("div");
            navHeader.classList.add("calendar-nav");
            navHeader.innerHTML = `
                <button id="prevMonth">&lt;</button>
                <span id="currentMonth">${new Date(currentYear, currentMonth).toLocaleString("default", {
                    month: "long",
                    year: "numeric",
                })}</span>
                <button id="nextMonth">&gt;</button>
            `;
            calendarContainer.appendChild(navHeader);

            const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();

            const calendarGrid = document.createElement("div");
            calendarGrid.classList.add("calendar-grid");

            daysOfWeek.forEach((day) => {
                const dayElement = document.createElement("div");
                dayElement.classList.add("calendar-header");
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            });

            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement("div");
                emptyCell.classList.add("calendar-cell", "empty");
                calendarGrid.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement("div");
                dayCell.classList.add("calendar-cell");
                dayCell.textContent = day;

                const currentDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
                if (scheduledDates.includes(currentDate)) {
                    dayCell.classList.add("highlighted");
                }

                calendarGrid.appendChild(dayCell);
            }

            calendarContainer.appendChild(calendarGrid);

            document.getElementById("prevMonth").addEventListener("click", () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                fetchScheduledDates().then(renderCalendar);
            });

            document.getElementById("nextMonth").addEventListener("click", () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                fetchScheduledDates().then(renderCalendar);
            });

            // Highlight the current date based on the schedule
            highlightCurrentDate(scheduledDates);
        }



        // Fetch and render scheduled dates
        const scheduledDates = await fetchScheduledDates();
        renderCalendar(scheduledDates);
    });
    
    document.addEventListener("DOMContentLoaded", () => {
        const today = new Date();
        const currentDateString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        // Find the calendar grid cells
        const calendarGrid = document.querySelector('.calendar-grid');
        if (calendarGrid) {
            const calendarCells = calendarGrid.querySelectorAll(".calendar-cell");

            calendarCells.forEach(cell => {
                // Match today's date with the cell's text
                const cellDate = Number(cell.textContent.trim());
                if (!cell.classList.contains("empty") && cellDate === today.getDate()) {
                    cell.classList.add("current-date");
                }
            });
        } else {
            console.error("Calendar grid not found.");
        }
    });

    function highlightCurrentDate(scheduledDates) {
        const today = new Date();
        const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const calendarGrid = document.querySelector('.calendar-grid');

        if (calendarGrid) {
            const calendarCells = calendarGrid.querySelectorAll(".calendar-cell");
            calendarCells.forEach(cell => {
                const cellDate = Number(cell.textContent.trim());
                if (!cell.classList.contains("empty") && cellDate === today.getDate()) {
                    if (scheduledDates.includes(todayString)) {
                        // Highlight as green if the date exists in the schedule
                        cell.classList.add("highlighted");
                        cell.classList.remove("current-date");
                    } else {
                        // Blue border for the current date without a schedule
                        cell.classList.add("current-date");
                    }
                }
            });
        } else {
            console.error("Calendar grid not found.");
        }
    }





    // Function to fetch chart data
    async function fetchChartData() {
        try {
            const endpoint =
                currentInterval === "perSecond"
                    ? `get_latest_data.php?POT_ID=${potID}`
                    : `get_latest_data_perMin.php?POT_ID=${potID}`;

            const response = await fetch(endpoint);
            const data = await response.json();

            if (data && data.length > 0) {
                // Update chart with the fetched data
                combinedChart.data.labels = data.map((point) =>
                    new Date(point.TIMESTAMP || point.minute).toLocaleTimeString()
                );
                combinedChart.data.datasets[0].data = data.map((point) => parseFloat(point.HUMIDITY));
                combinedChart.data.datasets[1].data = data.map((point) => parseFloat(point.TEMPERATURE));
                combinedChart.update();
            }
        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
    }

    // Toggle button functionality
    document.getElementById("toggleIntervalButton").addEventListener("click", () => {
        currentInterval = currentInterval === "perSecond" ? "perMinute" : "perSecond";
        const button = document.getElementById("toggleIntervalButton");
        button.textContent =
            currentInterval === "perSecond" ? "Switch to Per Minute" : "Switch to Per Second";
        fetchChartData();
    });


    async function populateScheduleList() {
        const scheduleList = document.getElementById("scheduleList");

        // Fetch today's schedule
        async function fetchSchedules() {
            try {
                const response = await fetch("get_schedules_today.php");
                return await response.json();
            } catch (error) {
                console.error("Error fetching schedules:", error);
                return []; // Return an empty array on error
            }
        }

        // Populate the schedule list
        async function renderList() {
            const schedules = await fetchSchedules();

            // Clear the list
            scheduleList.innerHTML = "";

            if (schedules.length === 0) {
                const listItem = document.createElement("li");
                listItem.textContent = "No schedule for today.";
                listItem.classList.add("off");
                scheduleList.appendChild(listItem);
            } else {
                schedules.forEach((schedule) => {
                    const { start_time, end_time, light_state, pump_state } = schedule;

                    // Create list item
                    const listItem = document.createElement("li");
                    listItem.textContent = `${start_time} - ${end_time}`;

                    // Determine the class based on states
                    if (light_state == 1 && pump_state == 1) {
                        listItem.classList.add("both-on");
                    } else if (light_state == 1) {
                        listItem.classList.add("light-on");
                    } else if (pump_state == 1) {
                        listItem.classList.add("pump-on");
                    } else {
                        listItem.classList.add("off");
                    }


                    scheduleList.appendChild(listItem);
                });
            }
        }

        renderList();
    }
    const deleteModal = document.getElementById("deleteScheduleModal");
    const openDeleteModalBtn = document.getElementById("openDeleteModal");
    const closeDeleteModalBtn = document.getElementById("closeDeleteModal");
    const scheduledDatesList = document.getElementById("scheduledDatesList");

    // Open delete modal
    openDeleteModalBtn.onclick = () => {
        deleteModal.style.display = "flex";
        fetchScheduledDatesForDeletion(); // Fetch scheduled dates and populate the modal
    };

    // Close delete modal
    closeDeleteModalBtn.onclick = () => {
        deleteModal.style.display = "none";
    };

    // Close delete modal when clicking outside of it
    window.onclick = (event) => {
        if (event.target === deleteModal) {
            deleteModal.style.display = "none";
        }
    };

    // Fetch scheduled dates for deletion
    async function fetchScheduledDatesForDeletion() {
        try {
            const response = await fetch(`fetch_schedules.php?POT_ID=${encodeURIComponent(potID)}`);
            const data = await response.json();

            const scheduledDatesList = document.getElementById("scheduledDatesList");
            scheduledDatesList.innerHTML = ""; // Clear previous content

            if (data.success && data.schedules && data.schedules.length > 0) {
                data.schedules.forEach((schedule) => {
                    const { date, start_time, end_time } = schedule;

                    const dateItem = document.createElement("div");
                    dateItem.classList.add("scheduled-date-item");

                    const dateText = document.createElement("span");
                    dateText.textContent = `${date} (${start_time} - ${end_time})`;

                    const deleteBtn = document.createElement("button");
                    deleteBtn.textContent = "Delete";
                    deleteBtn.onclick = () => deleteScheduledDate(date, start_time, end_time);

                    dateItem.appendChild(dateText);
                    dateItem.appendChild(deleteBtn);
                    scheduledDatesList.appendChild(dateItem);
                });
            } else {
                const noDataText = document.createElement("p");
                noDataText.textContent = "No scheduled dates found.";
                scheduledDatesList.appendChild(noDataText);
            }
        } catch (error) {
            console.error("Error fetching scheduled dates:", error);
        }
    }



    // Delete a scheduled date
    async function deleteScheduledDate(date, start_time, end_time) {
        if (!confirm(`Are you sure you want to delete the schedule for ${date} (${start_time} - ${end_time})?`)) {
            return;
        }

        try {
            const response = await fetch("delete_schedule.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    POT_ID: potID,
                    start_date: date,
                    start_time,
                    end_time,
                }),
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                fetchScheduledDatesForDeletion(); // Refresh the modal list
            } else {
                alert(result.error || "Failed to delete the schedule.");
            }
        } catch (error) {
            console.error("Error deleting schedule:", error);
        }
    }




    // Trigger the schedule list population on page load
    document.addEventListener("DOMContentLoaded", populateScheduleList);


    fetchData();
    setInterval(fetchData, updateInterval);
</script>

</body>
</html>
