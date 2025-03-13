import sys
import mysql.connector
import pandas as pd
import json


file_name = sys.argv[1]
request_file = sys.argv[2]

conn = mysql.connector.connect(
    host="127.0.0.1",
    user="root",
    password="",
    database="dedup_laravel"
)

if conn.is_connected():
    cur = conn.cursor()

folder_path = "spreadsheets"

# print("\nimporting excel file...")
request_file = "../storage/app/private/" + request_file

df = pd.read_csv(request_file, encoding='latin1')

df = df.fillna('')

# row_count = len(df)

# print("\nimporting to database...")

for index, row in df.iterrows():
    query = "DELETE FROM aics_clean_list WHERE control_number = %s"
    cur.execute(query, (row['CONTROL NUMBER'],))
    conn.commit()

cur.close()
conn.close()

result = {
    "message": True
}

print(json.dumps(result))