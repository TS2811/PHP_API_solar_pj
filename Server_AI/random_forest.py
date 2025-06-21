import pandas as pd
import numpy as np
import joblib
import json
import sys

# กำหนด path ของไฟล์โมเดล
model_path = r'C:\xampp\htdocs\myProjact\solar_cleaning_model_smote.pkl'

# โหลดโมเดล
try:
    gbr = joblib.load(model_path)
    # gbr = joblib.load('./naive_bayes_model_n.pkl')
except Exception as e:
    sys.stdout.write(json.dumps({"error": f"Failed to load model: {str(e)}"}))
    sys.exit(1)

# กำหนด path ของไฟล์ที่ต้องการอ่าน
input_file_path = r'C:\xampp\htdocs\myProjact\pythonInput.json'
# input_file_path = r'C:\Users\Admin\Desktop\AI\pythonInput.json'

# อ่านข้อมูลจากไฟล์ .json
try:
    with open(input_file_path, 'r', encoding='utf-8') as file:
        file_content = file.read().strip()  # อ่านไฟล์และลบช่องว่าง
        data = json.loads(file_content)  # แปลงจาก JSON string เป็น dict
except FileNotFoundError:
    sys.stdout.write(json.dumps({"error": "Input file not found"}))
    sys.exit(1)
except json.JSONDecodeError:
    sys.stdout.write(json.dumps({"error": "Invalid JSON format in input file"}))
    sys.exit(1)

# แปลงชื่อคีย์ให้ตรงกัน
key_mapping = {
    "Temp": "Temperature",
    "Humi": "Humidity",
    "lux1": "Light1",
    "lux2": "Light2",
    "lux3": "Light3"
}

data = {key_mapping.get(k, k): v for k, v in data.items()}

# ลบคีย์ที่ไม่จำเป็น
for key in ["DateTime", "eqpID"]:
    data.pop(key, None)

# ตรวจสอบว่าข้อมูลมีค่าครบถ้วน
required_keys = ['current', 'voltage', 'Light1', 'Light2', 'Light3', 'Temperature', 'Humidity']
missing_keys = [key for key in required_keys if key not in data]
if missing_keys:
    sys.stdout.write(json.dumps({"error": f"Missing keys: {', '.join(missing_keys)}"}))
    sys.exit(1)

# แปลงข้อมูลเป็น DataFrame
try:
    new_data = pd.DataFrame([{k: float(v) for k, v in data.items()}])
except Exception as e:
    sys.stdout.write(json.dumps({"error": f"Data conversion failed: {str(e)}"}))
    sys.exit(1)

# ทำการพยากรณ์
try:
    predicted_output = gbr.predict(new_data)
    predicted_value = int(predicted_output[0])  # แปลงค่าให้อยู่ในรูป float
except Exception as e:
    sys.stdout.write(json.dumps({"error": f"Prediction failed: {str(e)}"}))
    sys.exit(1)

# ส่งค่าผลลัพธ์กลับไปให้ PHP
sys.stdout.write(json.dumps({"predicted_output": predicted_value}))
sys.stdout.flush()