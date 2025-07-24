#pragma once
#include <WiFi.h>
#include <PubSubClient.h>
#include <Arduino.h>
#include <HardwareSerial.h>
#include <WiFiClient.h>

extern String receivedMessage;
extern bool completeConstruction;
struct KeyValuePair {
    String key;
    String strValue;  // For string values like DEV_ID
    int intValue;     // For integer values like PUMP and LIGHT
    bool isInt;       // Flag to indicate whether the value is an integer
};

float humidRead(int pin);

void connectWifi(const char* ssid, const char* password);

void reconnect(PubSubClient& client, const char* clientId);

void callback(char* topic, byte* payload, unsigned int length);

int parseKeyValuePairs(String input, KeyValuePair pairs[], int maxPairs);

int getValueByKey(const String &key, KeyValuePair pairs[], int numPairs);