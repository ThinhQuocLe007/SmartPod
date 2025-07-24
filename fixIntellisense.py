import json
import os

config_path = os.path.join(".vscode", "c_cpp_properties.json")

# Check if the file exists and is not empty
if not os.path.exists(config_path) or os.path.getsize(config_path) == 0:
    raise FileNotFoundError(f"{config_path} is missing or empty.")

# Read the file and remove lines that start with "//"
with open(config_path, "r") as f:
    lines = f.readlines()

# Filter out comment lines
json_str = "".join(line for line in lines if not line.strip().startswith("//"))

# Parse the JSON configuration
try:
    config = json.loads(json_str)
except json.JSONDecodeError as e:
    raise ValueError(f"Error decoding JSON from {config_path}: {e}")

# Override the includePath
config["configurations"][0]["includePath"] = [
    "**",
    "/home/alterraonix/.platformio/packages/**"
]

# Save the modified configuration
with open(config_path, "w") as f:
    json.dump(config, f, indent=4)
