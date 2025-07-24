# %%
import time
import uuid
import psutil
import random
from threading import Thread
from rich.traceback import install
from paho.mqtt import client as mqtt_client
install(show_locals = False)

broker = "broker.emqx.io"
port = 1883
treeTopic = "treeStat"
actuatorTopic = "actuatorStat"
deviceID = "Niggers"


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
        for interface, addrs in psutil.net_if_addrs().items():
            for addr in addrs:
                if addr.family == psutil.AF_LINK and "enp" in interface: break
        
        msg = f"DEV_ID: {addr.address}&TABLE: sensor_data|HUMID: {random.uniform(30, 90):.2f}|TEMP: {random.uniform(30, 50):.2f}?TABLE: pot_trees|PUMP: 0| AUTO: 0"
        client.publish(topic, msg)

def subscribe(client, topic, cursor = None, conn = None):
    def on_message(client, userdata, msg):
        message = msg.payload.decode()
        parts = message.split("|")
    
        devID, info = message.split("&")
        devID = {devID.split(": ")[0]: devID.split(": ")[1]}
        
        infoTable = {}
        splitInfo = info.split("?")
        for i in range(len(splitInfo)):
            smallPart = splitInfo[i].split("|")
            for field in smallPart:
                temp = field.split(": ")
                print(temp)
        

    client.subscribe(topic)
    client.on_message = on_message


if __name__ == "__main__":
    
    client = connect_mqtt(clientID = deviceID, topic = treeTopic)
    publish(client, treeTopic)
    client.loop_forever()

# %%
