import sys
import mysql.connector
import pandas as pd
import json

from mysql.connector import Error

file_name = sys.argv[1]
request_file = sys.argv[2]
program = sys.argv[3]
name = sys.argv[4]

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

df = pd.read_csv(request_file, encoding='UTF-8')

# row_count = len(df)

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
    'OTHER REMARKS': 'other_remarks',
    'FILE SOURCE': 'file_source',
    # Add more mappings as needed
}

# print("\nimporting to database...")

required_columns = ['CONTROL NUMBER', 'FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 
                    'BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR', 'PROVINCE', 'CITY/MUNICIPALITY', 
                    'DATE LAST SERVED', 'LAST SERVED LOCATION', 'PROGRAM', 'EVENT TYPE', 
                    'PARTNERS', 'CHARGING', 'SDO INCHARGE', 'OTHER REMARKS']

required_data_columns = ['FIRST NAME', 'LAST NAME', 'BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR', 'DATE LAST SERVED', 'LAST SERVED LOCATION']

missing = [col for col in required_columns if col not in df.columns]

if missing:
    result = {
        "message": False,
        "missing_cols": missing
    }

    print(json.dumps(result))
    sys.exit()

df[required_data_columns] = df[required_data_columns].replace('', pd.NA)
missing_cols = [col for col in required_data_columns if df[col].isnull().any()]

if missing_cols:
    result = {
        "message": False,
        "missing_cols": missing_cols
    }

    print(json.dumps(result))
    sys.exit()

def is_valid_date(date_series):
    try:
        pd.to_datetime(date_series, format='%d/%m/%Y', dayfirst=True, errors='raise')
        return True
    except (ValueError, TypeError):
        return False
    
df = df.fillna('')
df = df.astype(str)
df_rows = df.shape[0]

df['FILE SOURCE'] = file_name

df['Birthday'] = df['BIRTH DAY'] + '/' + df['BIRTH MONTH'] + '/' + df['BIRTH YEAR']

df_invalid = df[(df['Birthday'] == '') | df['Birthday'].isnull() | ~(df['Birthday'].apply(is_valid_date))]

invalid_bday = []

if df_invalid.shape[0] > 0:
    for index, row in df_invalid.iterrows():
        invalid_bday.append(row['Birthday'])
    result = {
        "message": False,
        "invalid_date": True,
        "invalid_rows": df_invalid.to_dict(orient='records')
    }

    print(json.dumps(result))
    sys.exit()

# df = df.replace('nan', '')

for index, row in df.iterrows():
    # Extract values from the DataFrame according to the mapping
    values = [row[spreadsheet_column] for spreadsheet_column, database_column in column_mapping.items()]

    try:
        if row['CONTROL NUMBER'] != '' and row['CONTROL NUMBER'] is not None:
            query = "DELETE FROM aics_clean_list WHERE control_number = %s"
            cur.execute(query, (row['CONTROL NUMBER'],))
            conn.commit()

        query = "INSERT INTO aics_served_database ({}) VALUES ({})".format(
            ', '.join(column_mapping.values()),
            ', '.join(['%s'] * len(column_mapping))
        )
        cur.execute(query, values)
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

try:
    query = """
            INSERT INTO import_served_files (file_name, total_served, imported_by) 
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
    "message": True,
    "cols": missing_cols
}

print(json.dumps(result))
sys.exit()