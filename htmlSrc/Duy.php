<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="./dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons"rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Plants System</title>
</head>
<body>

    <div class="container">

        <!-- side bar container  -->
        <aside>
            <div class="top">
                <div class="logo">
                    <img src="logoute1.png">
                    <h1>HCM<span class="danger">UTE</span></h1>
                </div>
            </div>
            <div class="sidebar">
                <a href="#" class="home">
                    <span class="material-symbols-outlined">home</span>
                    <h3>Home</h3>
                </a>                
                <a href="#" id="dashboard-button" class="active" aria-current="true">
                    <span class="material-symbols-outlined">grid_view</span>
                    <h3>Dashboard</h3>
                </a>                
                <a href="#" id="schedule-button" class="schedule">
                    <span class="material-symbols-outlined">schedule</span>
                    <h3>Schedule</h3>
                </a>
                
            </div>
        </aside>
        <!--end of sidebar-->

        <!--start of main-->
        <main >
            <!--Dashboard content-->
            <div id="dashboard-content" aria-hidden="false">
                <h1>Smart Plant System</h1>

                <!--start class insights contain 4 grid for tracking and control-->
                <div class="insights">
                    <!--Current plant pot-->
                    <div class="plant">
                        <h2 id="plant-title">Plant Details</h2>
                        <div class="plant-content" id="plant-details">
                            <p>Select a plant to view details.</p>
                        </div>
                    </div>
                    
                    <!-- Value container for temperature and humidity -->
                    <div class="value">
                        <div class="value-item">
                            <div class="value-content">
                                <p>Humidity</p>
                                <div class="icon-value">
                                    <span class="icon material-symbols-outlined">water_drop</span>
                                    <h3 id="humidity-value">65%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="value-item">
                            <div class="value-content">
                                <p>Temperature</p>
                                <div class="icon-value">
                                    <span class="icon material-symbols-outlined">thermostat</span>
                                    <h3 id="temperature-value">25°C</h3>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <!--Threshold with Tabs and Sliders-->
                    <div class="popup-container">
                        <div class="tabs">
                            <button class="tab-button active" onclick="openTab('humidity')">Humidity</button>
                            <button class="tab-button" onclick="openTab('temperature')">Temperature</button>
                        </div>

                        <!-- Humidity Threshold Tab -->
                        <div id="humidity" class="tab-content active">
                            <div class="threshold-inputs">
                                <label for="humidity-min">Min</label>
                                <input type="range" id="humidity-min" min="0" max="100" value="30" step="1" oninput="updateHumidityMin(this.value)">
                                <span id="humidity-min-display">30%</span>

                                <label for="humidity-max">Max</label>
                                <input type="range" id="humidity-max" min="0" max="100" value="70" step="1" oninput="updateHumidityMax(this.value)">
                                <span id="humidity-max-display">70%</span>
                            </div>
                        </div>

                        <!-- Temperature Threshold Tab -->
                        <div id="temperature" class="tab-content">
                            <div class="threshold-inputs">
                                <label for="temperature-min">Min</label>
                                <input type="range" id="temperature-min" min="0" max="50" value="15" step="1" oninput="updateTemperatureMin(this.value)">
                                <span id="temperature-min-display">15°C</span>

                                <label for="temperature-max">Max</label>
                                <input type="range" id="temperature-max" min="0" max="50" value="30" step="1" oninput="updateTemperatureMax(this.value)">
                                <span id="temperature-max-display">30°C</span>
                            </div>
                        </div>
                        
                        <div class="submit-button-container">
                            <button type="submit" class="submit-button">Submit</button>
                        </div>
                    </div>



                    <!-- Control panel -->
                    <div class="control">
                        <div class="mode-toggler">
                            <span class="mode-option">Manual</span>
                            <span class="mode-option active">Auto</span> <!-- Auto is initially active -->
                        </div>
                        <div class="control-buttons">
                            <button class="control-btn pump">
                                <span class="material-symbols-outlined">water_drop</span> Pump
                            </button>
                            <button class="control-btn led">
                                <span class="material-symbols-outlined">lightbulb</span> LED
                            </button>
                        </div>
                    </div>


                </div>
                <!-- end of class insights-->

                <!-- for dual chart-->
                <canvas id="dualAxisChart"></canvas>
            </div>


            <!-- Schedule Content -->
            <div id="schedule-content" aria-hidden="true" style="display: none;">
                <div id="schedule-container">
                    <header>
                        <button id="newEventButton" class="new-event-button">+ New Event</button>
                    </header>
                    <div id="formBackground" class="form-background hidden"></div>
                    <div id="eventList" class="event-list"></div>
                    <div id="formModal" class="modal hidden">
                        <div id="scheduleFormContainer" class="form-container">
                            <h1>Schedule a New Event</h1>
                            <form id="scheduleForm">
                                <div class="form-group">
                                    <label for="imageFile">Upload Image:</label>
                                    <input type="file" id="imageFile" name="imageFile" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label for="plantName">Plant Name:</label>
                                    <input type="text" id="plantName" name="plantName" placeholder="Enter plant name" required>
                                    
                                </div>
                                <div class="form-group">
                                    <label for="location">Location:</label>
                                    <input type="text" id="location" name="location" placeholder="Enter location" required>
                                    
                                </div>
                                <div class="form-group">
                                    <label for="host">Host:</label>
                                    <input type="text" id="host" name="host" placeholder="Enter host name" required>
                                </div>
                                <div class="form-group">
                                    <label for="date">Date:</label>
                                    <input type="date" id="date" name="date" required>
                                </div>
                                <div class="form-group">
                                    <label for="time">Time:</label>
                                    <input type="time" id="time" name="time" required>
                                </div>
                                <div class="form-group">
                                    <label>Actions:</label>
                                    <div class="checkbox-group">
                                        <label><input type="checkbox" id="pump" name="action" value="pump"> Pump</label>
                                        <label><input type="checkbox" id="led" name="action" value="led"> LED</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="repeat">Repeat:</label>
                                    <select id="repeat" name="repeat">
                                        <option value="none">None</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="custom">Custom Days</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="custom-days-group" style="display: none;">
                                    <label>Select Days:</label>
                                    <div class="checkbox-group">
                                        <label><input type="checkbox" value="Monday"> Monday</label>
                                        <label><input type="checkbox" value="Tuesday"> Tuesday</label>
                                        <label><input type="checkbox" value="Wednesday"> Wednesday</label>
                                        <label><input type="checkbox" value="Thursday"> Thursday</label>
                                        <label><input type="checkbox" value="Friday"> Friday</label>
                                        <label><input type="checkbox" value="Saturday"> Saturday</label>
                                        <label><input type="checkbox" value="Sunday"> Sunday</label>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit">Set</button>
                                    <button type="button" id="cancelButton">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>    



        </main>
        <!--end of main-->

         <!-- Calendar UI in the right class -->
         <div class="right">
            <div class="content-container">

                <!-- time current  -->
                <div class="top">
                    <div id="currentDate"></div>
                    <div id="currentTime"></div>
                </div>

                <!-- calendar  -->
                <div class="calendar">
                    <div class="header">
                        <button id="prevBtn">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="monthYear" id="monthYear">November 2024</div>
                        <button id="nextBtn">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="days">
                        <div class="day">Mon</div>
                        <div class="day">Tue</div>
                        <div class="day">Wed</div>
                        <div class="day">Thu</div>
                        <div class="day">Fri</div>
                        <div class="day">Sat</div>
                        <div class="day">Sun</div>
                    </div>
                    <div class="dates" id="dates"></div>
                </div>
                
                <!-- add plant  -->
                <div class="The-state-of-various-plants">
                    <h2>Garden</h2>
                    
                    <div id="plant-list">
                        <!-- Plant items will be dynamically added here -->
                    </div>
                    <a href="#" class="add-plant" id="add-plant-btn">
                        <span class="material-symbols-outlined">add_circle</span>
                        <h3>Add Plant</h3>
                    </a>
                
                    <!-- Popup for Adding a New Plant -->
                    <div id="add-plant-popup" class="popup hidden">
                        <h3>Add New Plant</h3>
                        <input type="text" id="plant-name" placeholder="Enter plant name" required>
                        <input type="text" id="plant-location" placeholder="Enter location" required>
                        <textarea id="plant-host" placeholder="Enter host" rows="3" required></textarea>
                        <button id="submit-plant">Add Plant</button>
                        <button id="cancel-plant">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Updated JavaScript code -->
    <script>
        // Control panel 
        document.addEventListener('DOMContentLoaded', function () {
            const modeOptions = document.querySelectorAll('.mode-option');
            const pumpButton = document.querySelector('.control-btn.pump');
            const ledButton = document.querySelector('.control-btn.led');

            // Set initial state: Auto mode active, Pump and LED disabled
            pumpButton.disabled = true;
            ledButton.disabled = true;

            modeOptions.forEach(option => {
                option.addEventListener('click', () => {
                    // Toggle active mode
                    modeOptions.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');

                    // Check if Manual mode is selected
                    const isManualMode = option.textContent === 'Manual';

                    // Enable or disable Pump and LED based on mode
                    pumpButton.disabled = !isManualMode;
                    ledButton.disabled = !isManualMode;

                    // Remove active state from Pump and LED when switching to Auto
                    if (!isManualMode) {
                        pumpButton.classList.remove('active');
                        ledButton.classList.remove('active');
                    }
                });
            });

            // Toggle active state for Pump and LED buttons when clicked (only in Manual mode)
            pumpButton.addEventListener('click', () => {
                if (!pumpButton.disabled) pumpButton.classList.toggle('active');
            });

            ledButton.addEventListener('click', () => {
                if (!ledButton.disabled) ledButton.classList.toggle('active');
            });
        });

        // Update time on left top
        function updateDateTime() {
            const now = new Date();
    
            const optionsDate = { weekday: 'long', month: 'long', day: 'numeric' };
            const formattedDate = now.toLocaleDateString('en-US', optionsDate);
    
            const optionsTime = { hour: 'numeric', minute: 'numeric', hour12: true };
            const formattedTime = now.toLocaleTimeString('en-US', optionsTime);
    
            document.getElementById('currentDate').textContent = formattedDate;
            document.getElementById('currentTime').textContent = formattedTime;
        }
    
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Add plant button
        document.addEventListener('DOMContentLoaded', function () {
            const addPlantButton = document.getElementById('add-plant-btn');
            const addPlantPopup = document.getElementById('add-plant-popup');
            const plantNameInput = document.getElementById('plant-name');
            const plantLocationInput = document.getElementById('plant-location');
            const plantHostInput = document.getElementById('plant-host');
            const submitPlantButton = document.getElementById('submit-plant');
            const cancelPlantButton = document.getElementById('cancel-plant');
            const plantList = document.getElementById('plant-list');
            const plantDetails = document.getElementById('plant-details'); // Element to display selected plant details
            const plantTitle = document.getElementById('plant-title'); // Element to display plant name as title

            // Store plants in an array
            const plants = [];

            // Show popup when clicking Add Plant button
            addPlantButton.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent default link behavior
                addPlantPopup.classList.remove('hidden'); // Show popup
                plantNameInput.focus(); // Focus on the first input field
            });

            // Add plant to list and hide popup when clicking Submit button
            submitPlantButton.addEventListener('click', () => {
                const plantName = plantNameInput.value.trim();
                const plantLocation = plantLocationInput.value.trim();
                const plantHost = plantHostInput.value.trim();

                if (plantName) {
                    const plant = {
                        name: plantName,
                        location: plantLocation,
                        host: plantHost
                    };
                    plants.push(plant);

                    const plantItem = document.createElement('div');
                    plantItem.classList.add('item', 'online');
                    plantItem.innerHTML = `
                        <div class="icon">
                            <span class="material-symbols-outlined">forest</span>
                        </div>
                        <h3>${plantName}</h3>
                    `;
                    plantList.appendChild(plantItem);

                    // Add click event to show details in the insights section
                    plantItem.addEventListener('click', () => {
                        displayPlantDetails(plant);
                    });

                    // Clear input fields and hide popup
                    plantNameInput.value = '';
                    plantLocationInput.value = '';
                    plantHostInput.value = '';
                    addPlantPopup.classList.add('hidden');
                }
            });

            // Hide popup and clear fields when clicking Cancel button
            cancelPlantButton.addEventListener('click', () => {
                addPlantPopup.classList.add('hidden'); // Hide popup
                plantNameInput.value = '';
                plantLocationInput.value = '';
                plantHostInput.value = '';
            });

            // Function to display plant details in the insights section
            function displayPlantDetails(plant) {
                plantTitle.textContent = plant.name; // Update plant title
                plantDetails.innerHTML = `
                    <div class="plant-image">
                        <img src="plantpot.jpeg" alt="Plant Image">
                    </div>
                    <div class="plant-details">
                        <p><strong>Location:</strong> ${plant.location}</p>
                        <p><strong>Host:</strong> ${plant.host}</p>
                    </div>
                `;
            }
        });

        // For schedule button is clicked
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth();

        document.addEventListener('DOMContentLoaded', () => {
            const dashboardContent = document.getElementById('dashboard-content');
            const scheduleContent = document.getElementById('schedule-content');
            const dashboardButton = document.getElementById('dashboard-button');
            const scheduleButton = document.getElementById('schedule-button');
            const body = document.body;

            const newEventButton = document.getElementById('newEventButton');
            const formModal = document.getElementById('formModal');
            const formBackground = document.getElementById('formBackground');
            const repeatSelect = document.getElementById('repeat');
            const customDaysGroup = document.getElementById('custom-days-group');
            const scheduleForm = document.getElementById('scheduleForm');
            const cancelButton = document.getElementById('cancelButton');
            const eventList = document.getElementById('eventList');
            const datesContainer = document.getElementById('dates');

            // Store events for repeating logic
            const events = [];

            // Toggle between Dashboard and Schedule views
            dashboardButton.addEventListener('click', () => {
                dashboardContent.style.display = 'block';
                scheduleContent.style.display = 'none';
                body.classList.remove('schedule-mode');
                dashboardButton.classList.add('active');
                scheduleButton.classList.remove('active');
            });

            scheduleButton.addEventListener('click', () => {
                dashboardContent.style.display = 'none';
                scheduleContent.style.display = 'block';
                body.classList.add('schedule-mode');
                scheduleButton.classList.add('active');
                dashboardButton.classList.remove('active');
            });

            // Open the modal
            newEventButton.addEventListener('click', () => {
                formModal.classList.remove('hidden');
                formBackground.classList.remove('hidden');
            });

            // Show/hide custom days based on repeat selection
            repeatSelect.addEventListener('change', () => {
                if (repeatSelect.value === 'custom') {
                    customDaysGroup.style.display = 'block';
                } else {
                    customDaysGroup.style.display = 'none';
                }
            });

            // Close the modal
            const hideModal = () => {
                formModal.classList.add('hidden');
                formBackground.classList.add('hidden');
            };

            formBackground.addEventListener('click', hideModal);
            cancelButton.addEventListener('click', hideModal);

            // Handle form submission
            scheduleForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const plantName = document.getElementById('plantName').value.trim();
                const location = document.getElementById('location').value.trim();
                const host = document.getElementById('host').value.trim();
                const date = document.getElementById('date').value;
                const time = document.getElementById('time').value;
                const pumpChecked = document.getElementById('pump').checked;
                const ledChecked = document.getElementById('led').checked;
                const repeat = document.getElementById('repeat').value;

                if (!plantName || !location || !host || !date || !time) {
                    alert("Please fill in all required fields!");
                    return;
                }

                const actions = [];
                if (pumpChecked) actions.push("Pump");
                if (ledChecked) actions.push("LED");

                const customDays = Array.from(customDaysGroup.querySelectorAll('input[type="checkbox"]:checked'))
                    .map(checkbox => checkbox.value);

                if (repeat === 'custom' && customDays.length === 0) {
                    alert("Please select at least one day for custom repeat.");
                    return;
                }

                const imageFile = document.getElementById('imageFile').files[0];
                const imageUrl = imageFile ? URL.createObjectURL(imageFile) : 'default-plant.png';

                const eventDetails = {
                    plantName,
                    location,
                    host,
                    date,
                    time,
                    repeat,
                    actions,
                    imageUrl,
                    customDays: repeat === 'custom' ? customDays : null
                };

                events.push(eventDetails); // Add event to events array
                displayEventsForDate(new Date(date)); // Refresh events for the selected date

                scheduleForm.reset();
                hideModal();
            });

            // Update month and year display
            function updateMonthYear() {
                const monthNames = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                document.getElementById('monthYear').textContent = `${monthNames[currentMonth]} ${currentYear}`;
            }

            // Render calendar
            function renderCalendar() {
                const datesContainer = document.getElementById('dates');
                const year = currentYear;
                const month = currentMonth;

                // Clear existing dates
                datesContainer.innerHTML = '';

                // Get first day and total days in the current month
                const firstDayIndex = new Date(year, month, 1).getDay();
                const lastDayIndex = new Date(year, month + 1, 0).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const prevMonthDays = new Date(year, month, 0).getDate();

                const nextDays = 7 - lastDayIndex - 1; // Days to show from next month

                // Adjust Sunday index
                const firstDay = firstDayIndex === 0 ? 7 : firstDayIndex;

                // Previous month's dates
                for (let x = firstDay - 1; x > 0; x--) {
                    const dateElement = document.createElement('div');
                    dateElement.classList.add('date', 'inactive');
                    dateElement.textContent = prevMonthDays - x + 1;

                    datesContainer.appendChild(dateElement);
                }

                // Current month's dates
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateElement = document.createElement('div');
                    dateElement.classList.add('date');
                    dateElement.textContent = day;

                    datesContainer.appendChild(dateElement);
                }

                // Next month's dates
                for (let y = 1; y <= nextDays; y++) {
                    const dateElement = document.createElement('div');
                    dateElement.classList.add('date', 'inactive');
                    dateElement.textContent = y;

                    datesContainer.appendChild(dateElement);
                }

                // Update the month and year display
                updateMonthYear();

                // Add event listener to the dates container
                datesContainer.addEventListener('click', onDateClick);

                // Highlight today's date and display events
                highlightTodayAndDisplayEvents();
            }

            // Handle clicks on date elements
            function onDateClick(event) {
                const dateElement = event.target.closest('.date');
                if (!dateElement) return;

                document.querySelectorAll('.date').forEach(el => el.classList.remove('active'));
                dateElement.classList.add('active');

                let day = parseInt(dateElement.textContent);
                let month = currentMonth;
                let year = currentYear;

                if (dateElement.classList.contains('inactive')) {
                    if (day > 15) {
                        // Date from previous month
                        month--;
                        if (month < 0) {
                            month = 11;
                            year--;
                        }
                    } else {
                        // Date from next month
                        month++;
                        if (month > 11) {
                            month = 0;
                            year++;
                        }
                    }
                    currentMonth = month;
                    currentYear = year;
                    renderCalendar();
                    return;
                }

                const selectedDate = new Date(year, month, day);
                displayEventsForDate(selectedDate);
            }

            // Handle next and previous buttons
            document.getElementById('nextBtn').addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                renderCalendar();
            });

            document.getElementById('prevBtn').addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderCalendar();
            });

            // Display events for a specific date
            function displayEventsForDate(selectedDate) {
                // Normalize the selected date using UTC
                const selectedDateOnly = new Date(Date.UTC(selectedDate.getUTCFullYear(), selectedDate.getUTCMonth(), selectedDate.getUTCDate()));
                const selectedDateTime = selectedDateOnly.getTime();

                eventList.innerHTML = ''; // Clear existing events

                const todaysEvents = events.filter(event => {
                    // Normalize the event date
                    const eventDate = new Date(event.date);
                    const eventDateOnly = new Date(Date.UTC(eventDate.getUTCFullYear(), eventDate.getUTCMonth(), eventDate.getUTCDate()));
                    const eventDateTime = eventDateOnly.getTime();

                    if (eventDateTime > selectedDateTime) return false; // Exclude future dates

                    if (event.repeat === 'none') {
                        return eventDateTime === selectedDateTime;
                    } else if (event.repeat === 'daily') {
                        return selectedDateTime >= eventDateTime;
                    } else if (event.repeat === 'weekly') {
                        return selectedDateTime >= eventDateTime && selectedDateOnly.getUTCDay() === eventDateOnly.getUTCDay();
                    } else if (event.repeat === 'monthly') {
                        return selectedDateTime >= eventDateTime && selectedDateOnly.getUTCDate() === eventDateOnly.getUTCDate();
                    } else if (event.repeat === 'custom') {
                        const dayName = selectedDateOnly.toLocaleDateString('en-US', { weekday: 'long' });
                        return selectedDateTime >= eventDateTime && event.customDays && event.customDays.includes(dayName);
                    }
                    return false;
                });

                // Display event blocks
                if (todaysEvents.length === 0) {
                    eventList.innerHTML = `<p>No events scheduled for this day.</p>`;
                } else {
                    todaysEvents.forEach(event => addEventBlock(event));
                }
            }

            // Add an event block to the UI
            function addEventBlock(details) {
                const eventDateTime = new Date(`${details.date}T${details.time}`);
                const formattedDate = eventDateTime.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                const formattedTime = eventDateTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });

                const eventBlock = document.createElement('div');
                eventBlock.classList.add('event-block');
                eventBlock.innerHTML = `
                    <h4>${formattedDate} ${formattedTime}</h4>
                    <h3>${details.plantName}</h3>
                    <div class="content-section">
                        <div class="image-section">
                            <img src="${details.imageUrl}" alt="Plant">
                        </div>
                        <div class="details-section">
                            <p><strong>Location:</strong> ${details.location}</p>
                            <p><strong>Host:</strong> ${details.host}</p>
                            <p><strong>Actions:</strong> ${details.actions.join(', ')}</p>
                            <p><strong>Repeat:</strong> ${details.repeat}</p>
                            ${
                                details.customDays
                                    ? `<p><strong>Custom Days:</strong> ${details.customDays.join(', ')}</p>`
                                    : ''
                            }
                        </div>
                    </div>
                `;
                eventList.appendChild(eventBlock);
            }

            // Highlight today's date and show today's events
            function highlightTodayAndDisplayEvents() {
                const now = new Date();

                // Only highlight today's date if we are on the current month and year
                if (currentYear === now.getFullYear() && currentMonth === now.getMonth()) {
                    const today = now.getDate();
                    const dates = document.querySelectorAll('.date');

                    dates.forEach(dateElement => {
                        const dateValue = parseInt(dateElement.textContent);

                        if (dateValue === today && !dateElement.classList.contains('inactive')) {
                            dateElement.classList.add('active');
                        } else {
                            dateElement.classList.remove('active');
                        }
                    });

                    // Show events for today
                    displayEventsForDate(now);
                } else {
                    // If not on current month, remove any active class and display events for the first day
                    document.querySelectorAll('.date').forEach(el => el.classList.remove('active'));
                    const firstDateElement = document.querySelector('.date:not(.inactive)');
                    if (firstDateElement) {
                        firstDateElement.classList.add('active');
                        const day = parseInt(firstDateElement.textContent);
                        const selectedDate = new Date(currentYear, currentMonth, day);
                        displayEventsForDate(selectedDate);
                    }
                }
            }

            // Initialize calendar and events
            renderCalendar();
        });

    </script>

</body>
</html>
