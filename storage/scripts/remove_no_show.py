import sys
import mysql.connector
import pandas as pd
import json
import chardet

from mysql.connector import Error

file_name = sys.argv[1]
request_file = sys.argv[2]
name = sys.argv[3]

clean_list = []

conn = mysql.connector.connect(
    host="127.0.0.1",
    user="root",
    password="",
    database="aicsakap_rdv_db"
)

if conn.is_connected():
    cur = conn.cursor()

    query = """
            SELECT * FROM aics_clean_list;
            """

    cur.execute(query)
    clean_list = cur.fetchall()
    clean_list_columns = [desc[0] for desc in cur.description]

clean_list_df = pd.DataFrame(clean_list, columns=clean_list_columns)
clean_list_df = clean_list_df.astype(str)

if clean_list_df.empty:
    result = {
        "message": False,
        "msg": "No data found in the clean list."
    }

    print(json.dumps(result))
    sys.exit(0)

folder_path = "spreadsheets"

# print("\nimporting excel file...")
request_file = "../storage/app/private/" + request_file

encoding = 'utf-8'

with open(request_file, "rb") as f:
    result = chardet.detect(f.read())
    encoding = result["encoding"]

df = pd.read_csv(request_file, encoding=encoding)
df_rows = df.shape[0]

# df = df.fillna('')

required_columns = ['CONTROL NUMBER', 'FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 
                    'EXTENSION NAME', 'BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR']

required_data_columns = ['CONTROL NUMBER']

missing = [col for col in required_columns if col not in df.columns]

if missing:
    result = {
        "message": False,
        "missing_cols": "File format/template is incorrect. Missing columns: " + ", ".join(missing)
    }

    print(json.dumps(result))
    sys.exit()

df[required_data_columns] = df[required_data_columns].replace('', pd.NA)
missing_cols = [col for col in required_data_columns if df[col].isnull().any()]

if missing_cols:
    result = {
        "message": False,
        "missing_cols": "Missing data in required columns: " + ", ".join(missing_cols)
    }

    print(json.dumps(result))
    sys.exit()
    
for index, row in df.iterrows():

    try:
        query = "DELETE FROM aics_clean_list WHERE control_number = %s"
        cur.execute(query, (row['CONTROL NUMBER'],))
        conn.commit()

    except Error as e:
        if conn.is_connected():
            conn.rollback()
            response = {
                "status": "error",
                "message": f"An error occurred while updating data. Please try again. \n{e}",
            }

            print(json.dumps(response))
            sys.exit()

try:
    query = """
            INSERT INTO import_no_show_files (file_name, total_no_show, imported_by) 
                    VALUES (%s, %s, %s);
            """
        
    cur.execute(query, (file_name, df_rows, name,))
    conn.commit()

except Error as e:
    if conn.is_connected():
        conn.rollback()
        response = {
            "status": "error",
            "message": f"An error occurred while saving data. Please try again. {e}",
        }

        print(json.dumps(response))
        sys.exit()


cur.close()
conn.close()

result = {
    "message": True
}

print(json.dumps(result))
sys.exit(0)