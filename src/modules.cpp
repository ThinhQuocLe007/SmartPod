#include "modules.hpp"

float humidRead(int pin){
    int sensorVal = analogRead(pin);
    return 100 - ((sensorVal / 4095.0) * 100);
};

void connectWifi(const char* ssid, const char* password){
    Serial.println("Connecting to Wifi");
    WiFi.begin(ssid, password);
    
    while (WiFi.status() != WL_CONNECTED){
        delay(500);
        Serial.print(".");
    }

    Serial.println();
    Serial.print("Connected to WiFi. IP address: "); Serial.println(WiFi.localIP());
};

void reconnect(PubSubClient& client, const char* clientID) {
    while (!client.connected()) {
        Serial.print("Attempting MQTT connection for ");
        Serial.print(clientID);
        Serial.print("...");
        if (client.connect(clientID)) {
            Serial.println("Connected to MQTT Broker");
        } else {
            Serial.print("Failed, rc=");
            Serial.print(client.state());
            Serial.println(" retrying in 5 seconds...");
            delay(5000);
        }
    }
}


int parseKeyValuePairs(String input, KeyValuePair pairs[], int maxPairs) {
    int start = 0;
    int pairCount = 0;

    while (start < input.length() && pairCount < maxPairs) {
        int colonIndex = input.indexOf(':', start);
        int pipeIndex = input.indexOf('|', start);

        if (colonIndex == -1) {
            break;
        }
        
        if (pipeIndex == -1) {
            pipeIndex = input.length();  // Last key-value pair, so set pipeIndex to end of input
        }

        String key = input.substring(start, colonIndex);
        key.trim();  // Trim spaces from key

        String valueStr = input.substring(colonIndex + 1, pipeIndex);
        valueStr.trim();  // Trim spaces from value

        
        KeyValuePair pair;
        pair.key = key;

        if (key == "DEV_ID") {
            pair.strValue = valueStr;
            pair.isInt = false;
        } else {
            pair.intValue = valueStr.toInt();
            pair.isInt = true;
        }

        pairs[pairCount] = pair;  // Store the key-value pair in the array
        pairCount++;

        start = pipeIndex + 1;
    }

    return pairCount;  // Return the number of pairs parsed
}

int getValueByKey(const String &key, KeyValuePair pairs[], int numPairs) {
    for (int i = 0; i < numPairs; i++) {
        if (pairs[i].key == key) {
            if (pairs[i].isInt) return pairs[i].intValue;
        }
    }
}


void callback(char* topic, byte* payload, unsigned int length) {
    receivedMessage = "";
    completeConstruction = false;
    if (strcmp(topic, "actuatorStat") == 0){
        for (int i = 0; i < length; i++) {
            receivedMessage += (char)payload[i];
        }
    }
    completeConstruction = true;
}