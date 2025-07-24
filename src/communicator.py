# %%
import time
import logging
from threading import Thread
from rich.traceback import install
import mysql.connector as connector
from paho.mqtt import client as mqtt_client
install(show_locals = False)

broker = "broker.emqx.io"
port = 1883
treeTopic = "treeStat"
actuatorTopic = "actuatorStat"
deviceID = "Server"


def connect_mqtt(topic: str, clientID: str):
    def on_connect(client, userdata, flags, rc):
        if rc == 0:
            print("Connected to MQTT Broker!")
        else:
            print("Failed to connect, return code %d\n", rc)
    client = mqtt_client.Client(clientID)
    client.subscribe(topic)

    client.on_connect = on_connect
    client.connect(broker, port)
    return client


FIRST_RECONNECT_DELAY = 1
RECONNECT_RATE = 2
MAX_RECONNECT_COUNT = 12
MAX_RECONNECT_DELAY = 60


def publish(client, topic: str):
    msg_count = 1
    while True:
        time.sleep(.5)
        msg = f"Message number {msg_count}"
        result = client.publish(topic, msg)
        msg_count += 1

def subscribe(client, topic, cursor = None, conn = None):
    def on_message(client, userdata, msg):
        message = msg.payload.decode()
        

    client.subscribe(topic)
    client.on_message = on_message


if __name__ == "__main__":
    
    client = connect_mqtt(clientID = deviceID, topic = treeTopic)
    subscribe(client, treeTopic)

    publishThread = Thread(target = publish, args = (client, actuatorTopic))
    publishThread.start()
    client.loop_forever()
