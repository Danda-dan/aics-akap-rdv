import pandas as pd
import os
import sys
import json
import mysql.connector
import random
import string

from dateutil import parser
from datetime import datetime, timedelta
from rapidfuzz import process, fuzz


now = datetime.now().strftime("%m/%d/%Y %H:%M:%S")
# print("\nTime started: ", now)

# Retrieve arguments
request_file = sys.argv[1]
file_name = sys.argv[2]
program = sys.argv[3]
usertype = sys.argv[4]
poo = sys.argv[5]
# start_date = sys.argv[4]
# end_date = sys.argv[5]

clean_list = []
served_db= []

clean_list_columns = []
served_db_columns = []

conn = mysql.connector.connect(
    host="127.0.0.1",
    user="root",
    password="",
    database="dedup_laravel"
)

# conn2 = mysql.connector.connect(
#     host="127.0.0.1",
#     user="root",
#     password="",
#     database="dedup_project"
# )

# if conn2.is_connected():
#     # print("Database connection established for Dedup...\n")

#     cursor2 = conn2.cursor()

if conn.is_connected():
    # print("Database connection established for Dedup...\n")

    cursor = conn.cursor()

    if program == 'ECT':
        query = """
        SELECT * FROM ect_clean_list;
        """

        cursor.execute(query)
        clean_list = cursor.fetchall()
        clean_list_columns = [desc[0] for desc in cursor.description]

        cursor.execute("SELECT * FROM ect_served_database;")
        served_db = cursor.fetchall()
        served_db_columns = [desc[0] for desc in cursor.description]

    elif program == 'AICS':
        query = """
        SELECT * FROM aics_clean_list;
        """

        cursor.execute(query)
        clean_list = cursor.fetchall()
        clean_list_columns = [desc[0] for desc in cursor.description]

        cursor.execute("SELECT * FROM aics_served_database;")
        served_db = cursor.fetchall()
        served_db_columns = [desc[0] for desc in cursor.description]


# path to folder 
folder_path = 'data/request'

name_threshold = 94
bday_threshold = 90

file_name = "../storage/app/private/" + file_name

clean_list_df = pd.DataFrame(clean_list, columns=clean_list_columns)
clean_list_df = clean_list_df.astype(str)

# Mapping dictionary for replacements
replacements = {
    'ma.': 'maria',
    'sto.': 'santo',
    'sta.': 'santa',
    '-': ' ',
    'ñ': 'n'
}

# Function to perform multiple replacements
def replace_multiple(text, replacements):
    for old, new in replacements.items():
        text = text.replace(old, new)
    return text

def clean_middle_value(value):
    if len(value) > 0:
        return value[0]  
    return ''

clean_list_df['Full Name'] = clean_list_df['first_name'] + ' ' + clean_list_df['middle_name'] + ' ' + clean_list_df['last_name']
clean_list_df['Full Name 2'] = clean_list_df['first_name'] + ' ' + clean_list_df['last_name']
clean_list_df['Full Name 3'] = clean_list_df['first_name'] + ' ' + clean_list_df['middle_name'].apply(clean_middle_value) + ' ' + clean_list_df['last_name']

clean_list_df[['Full Name', 'Full Name 2', 'Full Name 3']] = clean_list_df[['Full Name', 'Full Name 2', 'Full Name 3']].apply(lambda col: col.str.lower())

clean_list_df['Full Name'] = clean_list_df['Full Name'].apply(lambda x: replace_multiple(x, replacements))
clean_list_df['Full Name 2'] = clean_list_df['Full Name 2'].apply(lambda x: replace_multiple(x, replacements))
clean_list_df['Full Name 3'] = clean_list_df['Full Name 3'].apply(lambda x: replace_multiple(x, replacements))

clean_list_df['Birthday'] = pd.to_datetime(clean_list_df[['birth_day', 'birth_month', 'birth_year']].rename(
        columns={'birth_year': 'year', 'birth_month': 'month', 'birth_day': 'day'}
    ), errors='coerce')

clean_list_df['Birthday'] = clean_list_df['Birthday'].dt.strftime('%d %m %Y')

# clean_list_df['Birthday'] = clean_list_df['birth_day'] + ' ' + clean_list_df['birth_month'] + ' ' + clean_list_df['birth_year']

db_df = pd.DataFrame(served_db, columns=served_db_columns)
db_df = db_df.astype(str)

db_df['Full Name'] = db_df['first_name'] + ' ' + db_df['middle_name'] + ' ' + db_df['last_name']
db_df['Full Name 2'] = db_df['first_name'] + ' ' + db_df['last_name']
db_df['Full Name 3'] = db_df['first_name'] + ' ' + db_df['middle_name'].apply(clean_middle_value) + ' ' + db_df['last_name']

db_df[['Full Name', 'Full Name 2', 'Full Name 3']] = db_df[['Full Name', 'Full Name 2', 'Full Name 3']].apply(lambda col: col.str.lower())

db_df['Full Name'] = db_df['Full Name'].apply(lambda x: replace_multiple(x, replacements))
db_df['Full Name 2'] = db_df['Full Name 2'].apply(lambda x: replace_multiple(x, replacements))
db_df['Full Name 3'] = db_df['Full Name 3'].apply(lambda x: replace_multiple(x, replacements))

# db_df['Birthday'] = pd.to_datetime(db_df[['birth_day', 'birth_month', 'birth_year']].rename(
#         columns={'birth_year': 'year', 'birth_month': 'month', 'birth_day': 'day'}
#     ), errors='coerce')

# db_df['Birthday'] = db_df['Birthday'].dt.strftime('%d %m %Y')

db_df['Birthday'] = db_df['birth_day'] + ' ' + db_df['birth_month'] + ' ' + db_df['birth_year']

# columns_to_retain = ["FIRST NAME", "MIDDLE NAME", "LAST NAME", "EXTENSION NAME", "BIRTHDAY", "CITY/MUNICIPALITY", "BARANGAY"]

master_list = pd.DataFrame()
valid_recs = pd.DataFrame()
valid_recs_no_mid = pd.DataFrame()
invalid_recs = pd.DataFrame()

column_mapping = {
    'CONTROL NUMBER': 'control_number',
    'FIRST NAME': 'first_name',
    'MIDDLE NAME': 'middle_name',
    'LAST NAME': 'last_name',
    'EXTENSION NAME': 'extension_name',
    'BIRTH DAY': 'birth_day',
    'BIRTH MONTH': 'birth_month',
    'BIRTH YEAR': 'birth_year',
    'SEX': 'sex',
    'CIVIL STATUS': 'civil_status',
    'OCCUPATION': 'occupation',
    'MONTHLY SALARY': 'monthly_salary',
    'CATEGORY': 'category',
    'SUB-CATEGORY': 'subcategory',
    'TYPE OF ASSISTANCE': 'type_of_assistance',
    'PROVINCE': 'province',
    'CITY/MUNICIPALITY': 'city_municipality',
    'BARANGAY': 'barangay',
    'PUROK': 'purok',
    'CONTACT NUMBER': 'contact_number',
    'AMOUNT': 'amount',
    'CHARGING': 'charging',
    'File Source': 'file_source',
    'DATE PROCESSED': 'date_processed'
    # Add more mappings as needed
}

def save_clean_list(row):
    if program == 'ECT':
        now = datetime.now().strftime("%Y-%m-%d")

        query = """
            INSERT INTO ect_clean_list (first_name, middle_name, last_name, extension_name, date_processed) 
                VALUES (%s, %s, %s, %s);
            """
    
        cursor.execute(query, (row['FIRST NAME'], row['MIDDLE NAME'], row['LAST NAME'], now,))
        conn.commit()
    elif program == 'AICS':
        now = datetime.now().strftime("%m/%d/%Y")

        row['DATE PROCESSED'] = now
        # row['CONTROL NUMBER'] = control_number
        values = [row[spreadsheet_column] for spreadsheet_column, database_column in column_mapping.items()]
        # if pd.isnull(values):
        #     print('null: ', row)
        # Construct the INSERT query dynamically
        query = "INSERT INTO aics_clean_list ({}) VALUES ({})".format(
            ', '.join(column_mapping.values()),
            ', '.join(['%s'] * len(column_mapping))
        )
            
        cursor.execute(query, values)
        conn.commit()

# Remove special characters in the file, except "-"
def remove_special_chars(text):
    if isinstance(text, str):  # Check if the value is a string
        return ''.join(e for e in text if e.isalnum() or e == "-" or e == "." or e == " ")
    else:
        return text  # Return the original value if it's not a string
    
# Function to check if the date is valid
def is_valid_date(date_series):
    try:
        pd.to_datetime(date_series, format='%d/%m/%Y', dayfirst=True, errors='raise')
        return True
    except (ValueError, TypeError):
        return False
    
# Function to extract initials from a full name
def get_initials(province):
    parts = province.split()
    if province == 'DAVAO DEL SUR':
        return 'DDS'
    elif province == 'DAVAO DEL NORTE':
        return 'DDN'
    elif province == 'DAVAO DE ORO':
        return 'DDO'
    elif province == 'DAVAO ORIENTAL':
        return 'DOR'
    elif province == 'DAVAO OCCIDENTAL':
        return 'DOC'
    elif len(parts) == 3:
        return parts[0][0].upper() + parts[1][0].upper() + parts[2][0].upper()  # First letter of provinces
    elif len(parts) == 2:
        return parts[0][0].upper() + parts[1][0].upper()  # First letter of provinces
    elif len(parts) == 1:
        return parts[0][0].upper()  # Only one province part
    return 'RDV'  # Empty province

# Function to generate a random code with a random sequence of letters and numbers
def generate_random_code(existing_codes, province):
    while True:
        # Generate the components
        letters = random.choices(string.ascii_uppercase, k=3)
        numbers = random.choices(string.digits, k=2)
        # Combine and shuffle them
        code_components = letters + numbers
        random.shuffle(code_components)
        base_code = ''.join(code_components)
        # if city_muni == 'Davao City' or city_muni == 'DAVAO CITY' or city_muni == 'CITY OF DAVAO' or city_muni == 'City of Davao':
        #     control_number = f"DVO-{base_code}"
        # else:
        
        if province == '' or province is None:
            control_number = f"RDV-{base_code}"
        else:
            control_number = f"{province}-{base_code}"
        
        # Ensure uniqueness
        if control_number not in existing_codes and control_number not in clean_list_df['control_number']:
            existing_codes.add(control_number)
            if usertype != 'grievance_officer':
                return control_number
            # if con_number != '' or con_number is not None:
            #     return con_number


# Load the Excel file into a DataFrame and store it in the dictionary
df = pd.read_excel(file_name)
# df = pd.read_csv(file_name, encoding='latin1')

if usertype != 'grievance_officer':
    # Check if the required column exists
    response = {}

    if 'AMOUNT' not in df.columns:
        response = {
            "status": "error",
            "message": f"Required column AMOUNT is missing in the uploaded file."
        }

        print(json.dumps(response))

        raise ValueError(f"Required column AMOUNT is missing in the uploaded file.")
        
    # Check for null values in the required column
    if df['AMOUNT'].isnull().any():
        response = {
            "status": "error",
            "message": "Column AMOUNT contains empty values. Please ensure all rows have values."
        }

        print(json.dumps(response))
        
        raise ValueError(f"Column AMOUNT contains empty values. Please ensure all rows have values.")

df['File Source'] = request_file

master_list = pd.concat([master_list, df], ignore_index=True)

# df = df[columns_to_retain]
    
other_cols = df.columns.difference(['PROVINCE', 'CITY/MUNICIPALITY', 'BARANGAY', 'PUROK'])
df[other_cols] = df[other_cols].map(remove_special_chars)

df['File Source'] = request_file
df = df.fillna('')
df = df.astype(str)

df['Birthday'] = pd.to_datetime(df[['BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR']].rename(
        columns={'BIRTH YEAR': 'year', 'BIRTH MONTH': 'month', 'BIRTH DAY': 'day'}
    ), errors='coerce')

str_max_length = 50

# Check if any string value exceeds the maximum length while ignoring non-string values
exceeds_limit = df.drop(columns='File Source').map(lambda x: len(x) > str_max_length if isinstance(x, str) else False)

df_invalid = df[(df['LAST NAME'] == '') | (df['FIRST NAME'] == '') | (df['Birthday'] == '') | df['Birthday'].isnull() | ~(df['Birthday'].apply(is_valid_date)) | exceeds_limit.any(axis=1)]
df_valid = df[(df['LAST NAME'] != '') & (df['MIDDLE NAME'] != '') & (df['FIRST NAME'] != '') & (df['FIRST NAME'] != '') & (df['Birthday'] != '') & df['Birthday'].notnull() & df['Birthday'].apply(is_valid_date) & ~exceeds_limit.any(axis=1)]
df_valid_no_mid = df[(df['LAST NAME'] != '') & (df['MIDDLE NAME'] == '') & (df['FIRST NAME'] != '') & (df['FIRST NAME'] != '') & (df['Birthday'] != '') & df['Birthday'].notnull() & df['Birthday'].apply(is_valid_date) & ~exceeds_limit.any(axis=1)]
 
valid_recs = pd.concat([valid_recs, df_valid], ignore_index=True)
valid_recs_no_mid = pd.concat([valid_recs_no_mid, df_valid_no_mid], ignore_index=True) 
invalid_recs = pd.concat([invalid_recs, df_invalid], ignore_index=True)

valid_recs = pd.concat([valid_recs, valid_recs_no_mid], ignore_index=True)
# valid_recs['EXTENSION NAME'] = valid_recs['EXTENSION NAME'].astype(str)

valid_recs['Birthday'] = valid_recs['Birthday'].dt.strftime('%d %m %Y')

valid_recs['Full Name'] = valid_recs['FIRST NAME'] + ' ' + valid_recs['MIDDLE NAME'] + ' ' + valid_recs['LAST NAME']
valid_recs['Full Name 2'] = valid_recs['FIRST NAME'] + ' ' + valid_recs['LAST NAME']
valid_recs['Full Name 3'] = valid_recs['FIRST NAME'] + ' ' + valid_recs['MIDDLE NAME'].apply(clean_middle_value) + ' ' + valid_recs['LAST NAME']

valid_recs[['Full Name', 'Full Name 2', 'Full Name 3']] = valid_recs[['Full Name', 'Full Name 2', 'Full Name 3']].apply(lambda col: col.str.lower())

# Apply replacements only to the full_name column
valid_recs['Full Name'] = valid_recs['Full Name'].apply(lambda x: replace_multiple(x, replacements))
valid_recs['Full Name 2'] = valid_recs['Full Name 2'].apply(lambda x: replace_multiple(x, replacements))
valid_recs['Full Name 3'] = valid_recs['Full Name 3'].apply(lambda x: replace_multiple(x, replacements))

# List to store rows where similarity is above threshold
similar_rows = []
similar_index = []
temp_list = []
fullname_list = []

for i, row1 in enumerate(valid_recs[['FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 'EXTENSION NAME', 'Birthday', 'Full Name', 'Full Name 2', 'Full Name 3', 'File Source']].to_numpy()):
    for j, row2 in enumerate(valid_recs[['FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 'EXTENSION NAME', 'Birthday', 'Full Name', 'Full Name 2', 'Full Name 3', 'File Source']].to_numpy()[i+1:]):
        if i != '':
            # print(row1)

            remarks = 'Possible Match'

            if (row1[1] != '' and row1[1] is not None and row2[1] != '' and row2[1] is not None) and (len(row1[1]) != 1 and len(row2[1]) != 1):
                fullname1_ratio = fuzz.token_sort_ratio(row1[5], row2[5])
                fullname2_ratio = fuzz.ratio(row1[5], row2[5])
                birthday_ratio = fuzz.token_sort_ratio(row1[4], row2[4])

                if (fullname1_ratio == 100 or fullname2_ratio == 100) and birthday_ratio == 100:
                    remarks = 'Exact Match'

                if (fullname1_ratio>=name_threshold or fullname2_ratio>=name_threshold) and birthday_ratio>=bday_threshold:
                    fullname_list.append({
                        'Name on request': row1[0] + ' ' + row1[1] + ' ' + row1[2] + ' ' + row1[3],
                        'File source 1': row1[8],
                        'Row number': i + 1,
                        'On matching': row2[0] + ' ' + row2[1] + ' ' + row2[2] + ' ' + row2[3],
                        'File source 2': row2[8],
                        'Remarks': remarks
                    })

                    similar_index.append(i)
                    # similar_index.append(j)
            elif (row1[1] == '' or row1[1] is None or row2[1] == '' or row2[1] is None):
                fullname1_ratio = fuzz.token_sort_ratio(row1[6], row2[6])
                fullname2_ratio = fuzz.ratio(row1[6], row2[6])
                birthday_ratio = fuzz.token_sort_ratio(row1[4], row2[4])

                if (fullname1_ratio == 100 or fullname2_ratio == 100) and birthday_ratio == 100:
                    remarks = 'Exact Match'

                if (fullname1_ratio>=name_threshold or fullname2_ratio>=name_threshold) and birthday_ratio>=bday_threshold:
                    fullname_list.append({
                        'Name on request': row1[0] + ' ' + row1[2] + ' ' + row1[3],
                        'File source 1': row1[8],
                        'Row number': i + 1,
                        'On matching': row2[0] + ' ' + row2[2] + ' ' + row2[3],
                        'File source 2': row2[8],
                        'Remarks': remarks
                    })

                    similar_index.append(i)
            elif (len(row1[1]) == 1 or len(row2[1]) == 1):
                fullname1_ratio = fuzz.token_sort_ratio(row1[7], row2[7])
                fullname2_ratio = fuzz.ratio(row1[7], row2[7])
                birthday_ratio = fuzz.token_sort_ratio(row1[4], row2[4])

                if (fullname1_ratio == 100 or fullname2_ratio == 100) and birthday_ratio == 100:
                    remarks = 'Exact Match'

                if (fullname1_ratio>=name_threshold or fullname2_ratio>=name_threshold) and birthday_ratio>=bday_threshold:
                    fullname_list.append({
                        'Name on request': row1[0] + ' ' + row1[1] + ' ' + row1[2] + ' ' + row1[3],
                        'File source 1': row1[8],
                        'Row number': i + 1,
                        'On matching': row2[0] + ' ' + row2[1] + ' ' + row2[2] + ' ' + row2[3],
                        'File source 2': row2[8],
                        'Remarks': remarks
                    })

                    similar_index.append(i)

valid_recs.drop(index=similar_index, inplace=True)
similar_index = []

fullname_list = pd.DataFrame(fullname_list)

valid_recs = valid_recs.reset_index(drop=True)

# print('Duplicates:')
# print(fullname_list)

matches = []
fullname_matches = []
clean_list_matches = []
token_matching = []

def clean_match(index, name):
    remarks = 'Possible Match'
    if name[1] != '' and name[1] is not None and len(name[1]) > 1:
        ratio = process.extractOne(name[4], clean_list_df['Full Name'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[4], clean_list_df['Full Name'].to_numpy(), scorer=fuzz.ratio)
        
        if ratio[1] == 100 or ratio2[1] == 100:
            remarks = 'Exact Match'

        if ratio[1] >= name_threshold or ratio2[1] >= name_threshold:
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                birthday = clean_list_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                birthday = clean_list_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]

            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)

            if birthday_ratio >= bday_threshold:
                clean_list_matches.append({
                    'Name on request': name[0] + ' ' + name[1] + ' ' + name[2] + ' ' + name[3],
                    'File source 1': name[7],
                    'On matching': clean_list_df.iloc[df_index]['first_name'] + ' ' + clean_list_df.iloc[df_index]['middle_name'] + ' ' + clean_list_df.iloc[df_index]['last_name'] + ' ' + clean_list_df.iloc[df_index]['extension_name'],
                    'File source 2': 'Clean List' + ' - ' + clean_list_df.iloc[df_index]['file_source'],
                    'Remarks': remarks
                })
                similar_index.append(index)

    elif (name[1] == '' or name[1] is None) and len(name[1]) == 0:
        ratio = process.extractOne(name[5], clean_list_df['Full Name 2'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[5], clean_list_df['Full Name 2'].to_numpy(), scorer=fuzz.ratio)

        if (ratio[1] == 100 or ratio2[1] == 100):
            remarks = 'Exact Match'
            
        if (ratio[1] >= name_threshold or ratio2[1] >= name_threshold):
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                birthday = clean_list_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                birthday = clean_list_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]

            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)

            if birthday_ratio >= bday_threshold:
                clean_list_matches.append({
                    'Name on request': name[0] + ' ' + name[2] + ' ' + name[3],
                    'File source 1': name[7],
                    'On matching': clean_list_df.iloc[df_index]['first_name'] + ' ' + clean_list_df.iloc[df_index]['middle_name'] + ' ' + clean_list_df.iloc[df_index]['last_name'] + ' ' + clean_list_df.iloc[df_index]['extension_name'],
                    'File source 2': 'Clean List' + ' - ' + clean_list_df.iloc[df_index]['file_source'],
                    'Remarks': remarks
                })
                similar_index.append(index)

    elif len(name[1]) == 1:
        ratio = process.extractOne(name[6], clean_list_df['Full Name 3'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[6], clean_list_df['Full Name 3'].to_numpy(), scorer=fuzz.ratio)

        if (ratio[1] == 100 or ratio2[1] == 100):
            remarks = 'Exact Match'
                
        if (ratio[1] >= name_threshold or ratio2[1] >= name_threshold):
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                birthday = clean_list_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                birthday = clean_list_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]
                
            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)

            if birthday_ratio >= bday_threshold:
                clean_list_matches.append({
                    'Name on request': name[0] + ' ' + name[2] + ' ' + name[3],
                    'File source 1': name[7],
                    'On matching': clean_list_df.iloc[df_index]['first_name'] + ' ' + clean_list_df.iloc[df_index]['middle_name'] + ' ' + clean_list_df.iloc[df_index]['last_name'] + ' ' + clean_list_df.iloc[df_index]['extension_name'],
                    'File source 2': 'Clean List' + ' - ' + clean_list_df.iloc[df_index]['file_source'],
                    'Remarks': remarks
                })
                similar_index.append(index)
        
def token_sort_match(name):
    remarks = 'Possible Match'
    if name[1] != '' and name[1] is not None and len(name[1]) > 1:
        ratio = process.extractOne(name[4], db_df['Full Name'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[4], db_df['Full Name'].to_numpy(), scorer=fuzz.ratio)
        # print(fullname, ratio)
        if (ratio[1] == 100 or ratio2[1] == 100):
            remarks = 'Exact Match'

        if (ratio[1] >= name_threshold or ratio2[1] >= name_threshold):
            date_served = None
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                date_served = db_df.iloc[ratio[2]]['date_last_served']
                birthday = db_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                date_served = db_df.iloc[ratio2[2]]['date_last_served']
                birthday = db_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]

            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)
            
            try:
                # Convert the selected date to a datetime object
                selected_date = parser.parse(date_served).date()
            except ValueError:
                selected_date = None

            # Calculate the date 91 days before today
            today = datetime.today().date()
            date_91_days_ago = today - timedelta(days=91)

            # Check if the selected date is within the range
            if selected_date is not None:
                if selected_date >= date_91_days_ago and birthday_ratio >= bday_threshold:
                    token_matching.append({
                        'Name on request': name[0] + ' ' + name[1] + ' ' + name[2] + ' ' + name[3],
                        'File source 1': name[7],
                        'On matching': db_df.iloc[df_index]['first_name'] + ' ' + db_df.iloc[df_index]['middle_name'] + ' ' + db_df.iloc[df_index]['last_name'] + ' ' + db_df.iloc[df_index]['extension_name'],
                        'Date last served': selected_date,
                        'Last served location': db_df.iloc[df_index]['last_served_location'],
                        'File source 2': 'Served Database',
                        'Remarks': remarks
                    })
                    similar_index.append(index)
                    return True
        return False
                
    elif (name[1] == '' or name[1] is None) and len(name[1]) == 0:
        ratio = process.extractOne(name[5], db_df['Full Name 2'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[5], db_df['Full Name 2'].to_numpy(), scorer=fuzz.ratio)
        if ratio[1] == 100 or ratio2[1] == 100:
            remarks = 'Exact Match'

        # print(fullname, ratio)
        if ratio[1] >= name_threshold or ratio2[1] >= name_threshold:
            date_served = None
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                date_served = db_df.iloc[ratio[2]]['date_last_served']
                birthday = db_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                date_served = db_df.iloc[ratio2[2]]['date_last_served']
                birthday = db_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]

            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)

            try:
                # Convert the selected date to a datetime object
                selected_date = parser.parse(date_served).date()
            except ValueError:
                selected_date = None

            # Calculate the date 91 days before today
            today = datetime.today().date()
            date_91_days_ago = today - timedelta(days=91)

            # Check if the selected date is within the range
            if selected_date is not None:
                if selected_date >= date_91_days_ago and birthday_ratio >= bday_threshold:
                    token_matching.append({
                        'Name on request': name[0] + ' ' + name[2] + ' ' + name[3],
                        'File source 1': name[7],
                        'On matching': db_df.iloc[df_index]['first_name'] + ' ' + db_df.iloc[df_index]['middle_name'] + ' ' + db_df.iloc[df_index]['last_name'] + ' ' + db_df.iloc[df_index]['extension_name'],
                        'Date last served': selected_date,
                        'Last served location': db_df.iloc[df_index]['last_served_location'],
                        'File source 2': 'Served Database',
                        'Remarks': remarks
                    })
                    similar_index.append(index)
                    return True
        return False

    elif len(name[1]) == 1:
        ratio = process.extractOne(name[6], db_df['Full Name 3'].to_numpy(), scorer=fuzz.token_sort_ratio)
        ratio2 = process.extractOne(name[6], db_df['Full Name 3'].to_numpy(), scorer=fuzz.ratio)
        if ratio[1] == 100 or ratio2[1] == 100:
            remarks = 'Exact Match'

        # print(fullname, ratio)
        if ratio[1] >= name_threshold or ratio2[1] >= name_threshold:
            date_served = None
            birthday = None
            df_index = None

            if ratio[1] >= ratio2[1]:
                date_served = db_df.iloc[ratio[2]]['date_last_served']
                birthday = db_df.iloc[ratio[2]]['Birthday']
                df_index = ratio[2]
            elif ratio2[1] > ratio[1]:
                date_served = db_df.iloc[ratio2[2]]['date_last_served']
                birthday = db_df.iloc[ratio2[2]]['Birthday']
                df_index = ratio2[2]

            birthday_ratio = fuzz.token_sort_ratio(name[8], birthday)

            try:
                # Convert the selected date to a datetime object
                selected_date = parser.parse(date_served).date()
            except ValueError:
                selected_date = None

            # Calculate the date 91 days before today
            today = datetime.today().date()
            date_91_days_ago = today - timedelta(days=91)

            # Check if the selected date is within the range
            if selected_date is not None:
                if selected_date >= date_91_days_ago and birthday_ratio >= bday_threshold:
                    token_matching.append({
                        'Name on request': name[0] + ' ' + name[1] + ' ' + name[2] + ' ' + name[3],
                        'File source 1': name[7],
                        'On matching': db_df.iloc[df_index]['first_name'] + ' ' + db_df.iloc[df_index]['middle_name'] + ' ' + db_df.iloc[df_index]['last_name'] + ' ' + db_df.iloc[df_index]['extension_name'],
                        'Date last served': selected_date,
                        'Last served location': db_df.iloc[df_index]['last_served_location'],
                        'File source 2': 'Served Database',
                        'Remarks': remarks
                    })
                    similar_index.append(index)
                    return True
        return False

index = -1

for row, (index2, row2) in zip(valid_recs[['FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 'EXTENSION NAME', 'Full Name', 'Full Name 2', 'Full Name 3', 'File Source', 'Birthday', 'BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR']].to_numpy(), valid_recs.iterrows()):
    index += 1
    # print(index, row)
    # if not clean_list_df.empty:
    #         clean_match(index, row)
            
    if usertype != 'grievance_officer':
        token_sort_match(row)

        if not clean_list_df.empty:
            clean_match(index, row)
        
    # else:
    #     match = token_sort_match(row)

    #     if not match:
    #         query = """
    #         DELETE FROM aics_clean_list WHERE control_number = %s;
    #         """

    #         cursor.execute(query, (row[9],))
    #         conn.commit()

    #         save_clean_list(row2.to_dict())

valid_recs.drop(index=similar_index, inplace=True)

matches = pd.DataFrame(matches)
fullname_list = pd.DataFrame(fullname_list)
clean_list_matches = pd.DataFrame(clean_list_matches)
fullname_matches = pd.DataFrame(fullname_matches)
token_matching = pd.DataFrame(token_matching)

fullname_list = pd.concat([fullname_list, clean_list_matches], ignore_index=True)

# print('Possible served:')
# print(token_matching)

clean_df = valid_recs.reset_index(drop=True)
clean_df.astype(str)

# Generate unique codes for each name
existing_codes = set()
clean_df['CONTROL NUMBER'] = [generate_random_code(existing_codes, poo) for _, row in clean_df.iterrows()]

now = datetime.now().strftime("%m/%d/%Y")
# print("\nTime finished: ", now)

clean_df['Date Processed'] = now
clean_df['Birthday'] = clean_df['Birthday'].str.replace(' ', '/')
clean_list_df['Birthday'] = clean_list_df['Birthday'].str.replace(' ', '/')

# Set index to start at 1
clean_df.index = pd.RangeIndex(start=1, stop=len(clean_df) + 1)

# clean_list_df = pd.concat([clean_list_df, clean_df], ignore_index=True)
# clean_list_df.astype(str)

file_sources = set(master_list['File Source'].to_numpy())

now = datetime.now().strftime("%m-%d-%Y %H%M%S")

columns_to_modify = ['BIRTH DAY', 'BIRTH MONTH', 'BIRTH YEAR']
for col in columns_to_modify:
    clean_df[col] = clean_df[col].str.replace('.0', '', regex=False)
    invalid_recs[col] = invalid_recs[col].str.replace('.0', '', regex=False)

if usertype != 'grievance_officer':
    clean_df.apply(save_clean_list, axis=1)

# Create the directory if it does not exist
if not os.path.exists("spreadsheets"):
    os.makedirs("spreadsheets")

if not os.path.exists("spreadsheets/result"):
    os.makedirs("spreadsheets/result")
    
if not os.path.exists("spreadsheets/clean"):
    os.makedirs("spreadsheets/clean")

# Get the Documents directory
documents_path = os.path.join(os.path.expanduser('~'), 'Documents')

# Ensure the directory exists
if not os.path.exists(documents_path):
    os.makedirs(documents_path)

clean_df['MONTHLY SALARY'] = clean_df['MONTHLY SALARY'].replace(['', None], 0)
clean_df.drop(columns=['Full Name', 'Full Name 2', 'Full Name 3'], inplace=True)

for item in file_sources:
    master_df_file = master_list[master_list['File Source'] == item] if not master_list.empty else master_list
    clean_df_file = clean_df[clean_df['File Source'] == item] if not clean_df.empty else clean_df
    invalid_df_file = invalid_recs[invalid_recs['File Source'] == item] if not invalid_recs.empty else invalid_recs
    dup_df_file = fullname_list[fullname_list['File source 1'] == item] if not fullname_list.empty else fullname_list
    served_df_file = token_matching[token_matching['File source 1'] == item] if not token_matching.empty else token_matching

    result_file_path = os.path.join(documents_path, f'Record verification for {item} {now}.xlsx')

    with pd.ExcelWriter(result_file_path) as writer:  
        master_df_file.to_excel(writer, sheet_name='Raw List', index=False)

        # clean_df_file.drop(columns=['Full Name', 'Full Name 2', 'Full Name 3'], inplace=True)
        clean_df_file.to_excel(writer, sheet_name='Clean', index=False)

        invalid_df_file.to_excel(writer, sheet_name='Invalid', index=False)
        dup_df_file.to_excel(writer, sheet_name='Duplicates', index=False)
        served_df_file.to_excel(writer, sheet_name='Served', index=False)
    
    # clean_file_path = os.path.join(documents_path, f'Clean List for {item} {now}.xlsx')
    # clean_df_file.to_excel(clean_file_path, index=True)

    no_match_file_path = os.path.join(documents_path, f'No Match for {item} {now}.csv')
    # clean_df = clean_df[['CONTROL NUMBER', 'LAST NAME', 'FIRST NAME', 'MIDDLE NAME', 'EXTENSION NAME',  'Birthday', 'PROVINCE', 'CITY/MUNICIPALITY', 'BARANGAY', 'PUROK', 'AMOUNT', 'CHARGING', 'TYPE OF ASSISTANCE']]
    
    clean_df.to_csv(no_match_file_path, index=False)

# final_clean_file_path = os.path.join(documents_path, 'clean.xlsx')
# clean_list_df.to_excel(final_clean_file_path, index=False)

# print("\nSuccessfully generated the deduplication report.")

result = {
    "status": "success",
    "master_list": master_df_file.shape[0],
    "clean_list": clean_df.shape[0],
    "invalid_list": invalid_df_file.shape[0],
    "duplicate_list": dup_df_file.shape[0],
    "served_list": served_df_file.shape[0]
}

print(json.dumps(result))
os.startfile(documents_path)