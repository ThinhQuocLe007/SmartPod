import time
import mysql.connector as connector
from paho.mqtt import client as mqtt_client

is_pot_id_in_database_call_count = 0

# MQTT and MySQL configuration
broker = "broker.emqx.io"
port = 1883
subscribe_topic = "treeStat"
publish_topic = "actuatorStat"
client_id = "thinh"
db_config = {
    'host': 'localhost',
    'user': 'quocthinh',
    'password': 'quocthinh',
    'database': 'smartpot'
}


def connect_db():
    """Establish a connection to the database."""
    try:
        connection = connector.connect(**db_config)
        print("Connected to database.")
        return connection
    except connector.Error as e:
        print(f"Error connecting to database: {e}")
        raise


def is_pot_id_in_database(cursor, pot_id):
    """Check if a given POT_ID exists in the pot_trees table."""
    global is_pot_id_in_database_call_count
    is_pot_id_in_database_call_count += 1  # Increment the call count
    try:
        cursor.execute("COMMIT")
        cursor.execute("SELECT COUNT(*) FROM pot_trees WHERE POT_ID = %s", (pot_id,))
        count = cursor.fetchone()[0]
        return count > 0
    except connector.Error as e:
        print(f"Error checking POT_ID in database: {e}")
        return False


def save_new_pot_id_to_file(pot_id):
    """Save the new POT_ID to new_pot_id.txt."""
    try:
        with open("new_pot_id.txt", "w") as file:
            file.write(pot_id)
        print(f"New POT_ID {pot_id} saved to new_pot_id.txt.")
    except Exception as e:
        print(f"Error saving POT_ID to file: {e}")

def connect_mqtt(db_connection):
    """Connect to the MQTT broker."""
    client = mqtt_client.Client(client_id)

    def on_connect(client, userdata, flags, rc):
        if rc == 0:
            print("Connected to MQTT Broker!")
            client.subscribe(subscribe_topic)
            print(f"Subscribed to topic: {subscribe_topic}")
        else:
            print(f"Failed to connect, return code {rc}")

    def on_message(client, userdata, msg):
        """Handle incoming MQTT messages."""
        cursor = db_connection.cursor()
        message = msg.payload.decode()
        print(f"Received message: {message} on topic {msg.topic}")

        # Parse the message
        pot_id, sensor_data = parse_message(message)

        # Check if the POT_ID exists in the database
        if is_pot_id_in_database(cursor, pot_id):
            print(f"POT_ID {pot_id} exists in database. Saving sensor data...")
            save_sensor_data(cursor, pot_id, sensor_data)
            db_connection.commit()
        else:
            print(f"POT_ID {pot_id} does not exist in the database. Ignoring message.")
            save_new_pot_id_to_file(pot_id)

        # Print the number of times is_pot_id_in_database was called
        print(f"is_pot_id_in_database called {is_pot_id_in_database_call_count} times.")

    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(broker, port)
    return client


def save_sensor_data(cursor, pot_id, sensor_data):
    """Save extracted sensor data to the sensor_data table."""
    try:
        humidity = sensor_data.get('HUMID', None)  # Default to None if not present
        temperature = sensor_data.get('TEMP', None)

        # Insert the sensor data
        cursor.execute(
            """
            INSERT INTO sensor_data (POT_ID, HUMIDITY, TEMPERATURE, TIMESTAMP)
            VALUES (%s, %s, %s, NOW())
            """,
            (pot_id, humidity, temperature),
        )
        print(f"Sensor data saved for POT_ID {pot_id}: HUMIDITY={humidity}, TEMPERATURE={temperature}.")
    except connector.Error as e:
        print(f"Error saving sensor data: {e}")


def parse_message(message):
    """Parse the incoming MQTT message."""
    try:
        devID, infor = message.split('&')
        dev_id = devID.split(': ')[1]  # Extract DEV_ID
        parts = infor.split('?')

        sensor_data = {}

        for part in parts:
            if "TABLE: sensor_data" in part:
                sensor_values = part.split('|')
                for item in sensor_values[1:]:
                    key, value = item.split(': ')
                    sensor_data[key] = float(value)

        return dev_id, sensor_data
    except Exception as e:
        print(f"Error parsing message: {e}")
        return None, {}


def fetch_pot_trees_data(db_connection):
    """Fetch data from the pot_trees table."""
    try:
        cursor = db_connection.cursor() 
        cursor.execute("COMMIT")
        cursor = db_connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM pot_trees")
        return cursor.fetchall()
    except connector.Error as e:
        print(f"Error fetching data from pot_trees: {e}")
        return []


def publish(client, topic, db_connection):
    """Publish messages to the MQTT topic using data from the pot_trees and schedules tables."""
    while True:
        time.sleep(1)  # Adjust the publishing interval
        pot_trees_data = fetch_pot_trees_data(db_connection)
        if not pot_trees_data:
            print("No data to publish. Retrying...")
            continue
        
        for pot in pot_trees_data:
            try:
                pot_id = pot.get('POT_ID', 'Unknown')
                pump_status = pot.get('pump', 0)  # Default to 0 if not present
                light_status = pot.get('light', 0)  # Default to 0 if not present
                auto_mode = pot.get('auto', 0)  # Default to 0 (manual mode) if not present

                if auto_mode == 0:  # Manual Mode
                    # Send data directly from pot_trees
                    message = (
                        f"DEV_ID: {pot_id}|"
                        f"PUMP: {pump_status}|"
                        f"LIGHT: {light_status}"
                    )
                else:  # Auto Mode
                    # Fetch data from schedules table
                    cursor = db_connection.cursor(dictionary=True)
                    current_time = time.strftime('%H:%M:%S')  # Current time in HH:MM:SS
                    cursor.execute(
                        """
                        SELECT pump_state, light_state
                        FROM schedules
                        WHERE POT_ID = %s AND start_time <= %s AND end_time >= %s
                        """,
                        (pot_id, current_time, current_time)
                    )
                    schedule_data = cursor.fetchone()

                    if schedule_data:  # Schedule exists and is active
                        pump_status = schedule_data.get('pump_state', 0)
                        light_status = schedule_data.get('light_state', 0)
                    else:  # No active schedule
                        pump_status = 0
                        light_status = 0

                    message = (
                        f"DEV_ID: {pot_id}|"
                        f"PUMP: {pump_status}|"
                        f"LIGHT: {light_status}"
                    )

                # Publish the constructed message
                result = client.publish(topic, message)
                if result[0] == 0:
                    print(f"Sent '{message}' to topic '{topic}'")
                else:
                    print(f"Failed to send message to topic {topic}")
            except KeyError as e:
                print(f"Key error: {e}. Check the database schema.")
            except Exception as e:
                print(f"Error constructing or sending message: {e}")


if __name__ == "__main__":
    # Initialize database connection
    db_connection = connect_db()

    # Initialize MQTT connection
    client = connect_mqtt(db_connection)

    # Start the MQTT loop in a thread for publishing
    from threading import Thread
    publish_thread = Thread(target=publish, args=(client, publish_topic, db_connection))
    publish_thread.start()

    # Start the MQTT loop for subscribing
    try:
        print("Starting MQTT loop to listen for messages...")
        client.loop_forever()
    except KeyboardInterrupt:
        print("Exiting and closing connections...")
    finally:
        db_connection.close()
