<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set SmartPot Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .pot-id {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
        }

        input, select, button {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        .time-periods {
            margin-bottom: 15px;
        }

        .time-period {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f5f5f5;
        }

        .time-period h3 {
            margin-top: 0;
        }

        .remove-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .add-period-btn {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Set SmartPot Schedule</h1>

        <!-- Display POT_ID -->
        <div class="pot-id">
            POT ID: <?php echo htmlspecialchars($_GET['POT_ID'] ?? 'Unknown'); ?>
        </div>

        <form action="add_schedule.php" method="POST" id="scheduleForm">
            <input type="hidden" name="POT_ID" value="<?php echo htmlspecialchars($_GET['POT_ID'] ?? ''); ?>">

            <!-- Date Range -->
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" required>

            <!-- Time Periods -->
            <div class="time-periods" id="timePeriods">
                <div class="time-period">
                    <h3>Time Period 1</h3>
                    <label for="start_time_1">Start Time:</label>
                    <input type="time" name="time_periods[0][start_time]" id="start_time_1" required>

                    <label for="end_time_1">End Time:</label>
                    <input type="time" name="time_periods[0][end_time]" id="end_time_1" required>

                    <label for="pump_state_1">Pump State:</label>
                    <select name="time_periods[0][pump_state]" id="pump_state_1" required>
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                    </select>

                    <label for="light_state_1">Light State:</label>
                    <select name="time_periods[0][light_state]" id="light_state_1" required>
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                    </select>
                </div>
            </div>

            <button type="button" class="add-period-btn" id="addTimePeriod">+ Add Another Time Period</button>

            <!-- Submit -->
            <button type="submit">Add Schedule</button>
        </form>
    </div>

    <script>
        let timePeriodCount = 1;

        document.getElementById('addTimePeriod').addEventListener('click', () => {
            const timePeriodsDiv = document.getElementById('timePeriods');

            const newTimePeriod = document.createElement('div');
            newTimePeriod.className = 'time-period';
            newTimePeriod.innerHTML = `
                <h3>Time Period ${timePeriodCount + 1}</h3>
                <label for="start_time_${timePeriodCount}">Start Time:</label>
                <input type="time" name="time_periods[${timePeriodCount}][start_time]" id="start_time_${timePeriodCount}" required>

                <label for="end_time_${timePeriodCount}">End Time:</label>
                <input type="time" name="time_periods[${timePeriodCount}][end_time]" id="end_time_${timePeriodCount}" required>

                <label for="pump_state_${timePeriodCount}">Pump State:</label>
                <select name="time_periods[${timePeriodCount}][pump_state]" id="pump_state_${timePeriodCount}" required>
                    <option value="ON">ON</option>
                    <option value="OFF">OFF</option>
                </select>

                <label for="light_state_${timePeriodCount}">Light State:</label>
                <select name="time_periods[${timePeriodCount}][light_state]" id="light_state_${timePeriodCount}" required>
                    <option value="ON">ON</option>
                    <option value="OFF">OFF</option>
                </select>

                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            `;
            timePeriodsDiv.appendChild(newTimePeriod);
            timePeriodCount++;
        });
    </script>
</body>
</html>
