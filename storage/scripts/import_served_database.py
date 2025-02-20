import sys
import mysql.connector
import pandas as pd
import json


file_name = sys.argv[1]
request_file = sys.argv[2]
program = sys.argv[3]

conn = mysql.connector.connect(
    host="127.0.0.1",
    user="root",
    password="",
    database="dedup_laravel"
)

if conn.is_connected():
    cur = conn.cursor()

folder_path = "spreadsheets"

# Define the path to the password-protected Excel file and the password
password = 'davor2024'

# file_name = input("Enter the name of the file: ")
# sheet_name = input("Enter the name of the sheet: ")

# file_path = os.path.join(folder_path, file_name)

# print("\nimporting excel file...")
request_file = "../storage/app/private/" + request_file

df = pd.read_excel(request_file)

df = df.fillna('')

row_count = len(df)

# print(f'The DataFrame has {row_count} rows.')

column_mapping = {
    'CONTROL NUMBER': 'control_number',
    'FIRST NAME': 'first_name',
    'MIDDLE NAME': 'middle_name',
    'LAST NAME': 'last_name',
    'EXTENSION NAME': 'extension_name',
    'BIRTH DAY': 'birth_day',
    'BIRTH MONTH': 'birth_month',
    'BIRTH YEAR': 'birth_year',
    'PROVINCE': 'province',
    'CITY/MUNICIPALITY': 'city_municipality',
    'DATE LAST SERVED': 'date_last_served',
    'LAST SERVED LOCATION': 'last_served_location',
    'PROGRAM': 'program',
    'EVENT TYPE': 'event_type',
    'PARTNERS': 'partners',
    'CHARGING': 'charging',
    'SDO INCHARGE': 'sdo_incharge',
    'OTHER REMARKS': 'other_remarks'
    # Add more mappings as needed
}

# print("\nimporting to database...")

for index, row in df.iterrows():
    # Extract values from the DataFrame according to the mapping
    values = [row[spreadsheet_column] for spreadsheet_column, database_column in column_mapping.items()]
    # if pd.isnull(values):
    #     print('null: ', row)
    # Construct the INSERT query dynamically

    query = "INSERT INTO aics_served_database ({}) VALUES ({})".format(
            ', '.join(column_mapping.values()),
            ', '.join(['%s'] * len(column_mapping))
        )
    cur.execute(query, values)
    conn.commit()

cur.close()
conn.close()

result = {
    "message": True
}

print(json.dumps(result))