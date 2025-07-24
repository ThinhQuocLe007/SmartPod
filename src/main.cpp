#include "modules.hpp"
#include <string>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <OneWire.h>
#include <DallasTemperature.h>


#define PUMP            23
#define LIGHT           22
#define HUMID           32
#define TEMP            14
#define MANUALPUMPON    18
#define MANUALPUMPOFF   19

OneWire oneWire(TEMP);
DallasTemperature heatSensor(&oneWire);
WiFiClient espclient;
PubSubClient client(espclient);

const char* ssid = "OPPO Reno8 5G";
const char* password = "hoang38661";
const char* broker = "broker.emqx.io";
String macAddressString = WiFi.macAddress();
const char* macAddress = macAddressString.c_str();

const char* clientID = "treeDevice";
const char* treeTopic = "treeStat";
const char* actuatorTopic = "actuatorStat";

const int MAX_PAIRS = 6;
const int devID = 1;
const uint16_t port = 1883;

uint32_t startTimer;
String receivedMessage;
bool completeConstruction, pumpState, lightState;
int numPairs = 0;

KeyValuePair actualPairs[MAX_PAIRS];


void setup() {

    Serial.begin(9600);
    connectWifi(ssid, password);

    
    client.setServer(broker, port);
    client.setCallback(callback);
    reconnect(client, clientID);
    client.subscribe(treeTopic);
    client.subscribe(actuatorTopic);
    
    pinMode(HUMID, INPUT);
    pinMode(PUMP, OUTPUT);
    pinMode(LIGHT, OUTPUT);
    pinMode(MANUALPUMPON, INPUT_PULLDOWN);
    pinMode(MANUALPUMPOFF, INPUT_PULLUP);
    heatSensor.begin();

    startTimer = millis();
    heatSensor.setWaitForConversion(false);
}


void loop() {
    // Send sensors information to server
    if (millis() - startTimer >= 1500) {
        if (!client.connected()) reconnect(client, clientID);

        auto humid = humidRead(HUMID);
        heatSensor.requestTemperatures();
        auto temp = heatSensor.getTempCByIndex(0);

        char message[105];
        snprintf(message, sizeof(message), "DEV_ID: %s&TABLE: sensor_data|HUMID: %.2f|TEMP: %.2f?TABLE: pot_trees|PUMP: %i|LIGHT: %i", 
                                            macAddress, humid, temp, pumpState, lightState);

        client.publish(treeTopic, message); // Using treeTopic to send and actuatorTopic to receive information
        startTimer = millis();
    }


    client.loop(); // Check if Server is sending messages

    // Construct message and extract information for this device
    bool correctDevice = false;
    if ((completeConstruction) & (receivedMessage.length() > 0)){

        KeyValuePair tempPairs[MAX_PAIRS];
        int tempNumPairs = parseKeyValuePairs(receivedMessage, tempPairs, MAX_PAIRS);

        for (int i = 0; i < tempNumPairs; i++) {
            if (!tempPairs[i].isInt & tempPairs[i].strValue == macAddressString){
                correctDevice = true;
            }
            if (tempPairs[i].isInt & correctDevice){
                if (tempPairs[i].key == "PUMP") pumpState = tempPairs[i].intValue;
                if (tempPairs[i].key == "LIGHT") lightState = tempPairs[i].intValue;
            }
        }
        completeConstruction = false;
    }

    
    digitalWrite(PUMP, !pumpState);
    digitalWrite(LIGHT, !lightState);
}
