import pandas as pd
import numpy as np
import joblib
import json
import sys

# กำหนด path ของไฟล์โมเดล
# model_path = r'C:\xampp\htdocs\myProjact\knn_model.pkl'

# โหลดโมเดล
try:
    # loaded_data = joblib.load(model_path)
    loaded_data = joblib.load('./knn_smote_k3_weights_distance.pkl')
    scaler, model = loaded_data  # แยกตัวแปรออกจาก Tuple
except Exception as e:
    print(json.dumps({"error": f"Failed to load model: {str(e)}"}))
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
    
    # ปรับสเกลข้อมูลก่อนทำนาย
try:
    X_new_scaled = scaler.transform(new_data)
except Exception as e:
    print(json.dumps({"error": f"Scaling failed: {str(e)}"}))
    sys.exit(1)

# ทำการพยากรณ์
try:
    predicted_output = model.predict(X_new_scaled)
    # print(f"Predicted output: {predicted_output}")  # ตรวจสอบค่า output
    predicted_value = float(predicted_output[0])  # ดึงค่าแรกจาก NumPy array
    # print(f"Predicted value: {predicted_value}")
except Exception as e:
    print(json.dumps({"error": f"Model prediction failed: {str(e)}"}))
    sys.exit(1)


# ส่งค่าผลลัพธ์กลับให้ PHP
sys.stdout.write(json.dumps({"predicted_output": predicted_value}))
sys.stdout.flush()