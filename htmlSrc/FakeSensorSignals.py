import paho.mqtt.client as mqtt  # type: ignore
import random
import time

# MQTT broker configuration
broker = "broker.emqx.io"
port = 1883
publish_topic = "treeStat"
client_id = "thinh_publisher"

# Device ID (fixed as in your example)
device_id = "10:06:1C:A6:CA:AC"

# Connect to MQTT broker
def connect_mqtt():
    client = mqtt.Client(client_id)
    client.connect(broker, port)
    return client

# Publish fake humidity and temperature data
def publish_fake_data(client):
    while True:
        # Generate fake humidity and temperature data
        humidity = round(random.uniform(20.0, 80.0), 2)  # Humidity between 20% and 80%
        temperature = round(random.uniform(15.0, 35.0), 2)  # Temperature between 15°C and 35°C

        # Format message as expected
        message = f"DEV_ID: {device_id}&TABLE: sensor_data|HUMID: {humidity}|TEMP: {temperature}?TABLE: pot_trees|PUMP: 1|AUTO: 1"

        # Publish message to the topic
        result = client.publish(publish_topic, message)

        # Check if publish was successful
        status = result[0]
        if status == 0:
            print(f"Sent `{message}` to topic `{publish_topic}`")
        else:
            print(f"Failed to send message to topic {publish_topic}")

        time.sleep(3)  # Send a new message every 3 seconds

if __name__ == "__main__":
    # Connect to MQTT broker and start publishing fake data
    client = connect_mqtt()
    client.loop_start()  # Start the network loop

    try:
        publish_fake_data(client)
    except KeyboardInterrupt:
        print("Stopped publishing data.")
    finally:
        client.loop_stop()
        client.disconnect()
