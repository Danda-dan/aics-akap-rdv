from rapidfuzz import process, fuzz
import os
import pandas as pd
import json
from dateutil import parser
import mysql.connector

# database_folder_path = 'data/served'
# database_file = 'conso_served.xlsx'

# db_file_path = os.path.join(database_folder_path, database_file)

# db_df = pd.read_excel(db_file_path)
# db_df = db_df.fillna('')
# db_df = db_df.astype(str)
# db_df['Full Name'] = db_df['First Name'] + ' ' + db_df['Middle Name'] + ' ' + db_df['Last Name']

ratio = fuzz.ratio('KIRBY INGAN ENG XXXXXXXXXXXXXXXXXXXXXXXX', 'KIRBY ENGAN.ENG XXXXXXXXXXXXXXXXXXXXXXXX')
# print('Ratio: ', ratio)
# mn_ratio = fuzz.ratio('PALANG', 'MASANGCAY')
# ln_ratio = fuzz.ratio('BELONTA', 'BELACHO')
# avg_ratio = (fn_ratio + mn_ratio + ln_ratio) / 3

fullname_ratio =  fuzz.ratio('DACILAASAS DIVINE', 'DAKILAASAS DIVINE') # 17 LENGTH
fullname_ratio2 = fuzz.ratio('DACILAASAS DIVINE', 'DAKILAASAA DIVINE') # 17 LENGTH
fullname_ratio3 = fuzz.ratio('DACILA ASA DIVINE', 'DAKILA ASA DIVINE') # 17 LENGTH
fullname_ratio4 = fuzz.ratio('DACILA ASS DIVINE', 'DAKILA ASA DIVINE') # 17 LENGTH
fullname_ratio5 = fuzz.ratio('DORIS PEREZ ABENDAN', 'DORIS PEREZ ABINDAN') # 19 LENGTH
fullname_ratio6 = fuzz.ratio('DORIS PEREZ ABENDAN', 'DORIS PEREZ ABINDAD') # 19 LENGTH
fullname_ratio7 = fuzz.ratio('Chris Sto. Niño', 'Chris Sto. Nino') # 19 LENGTH
fullname_token_sort_ratio = fuzz.token_sort_ratio('KERBY SANTO INGAN ENG', 'KIRBY SANTO INGANENG')
# extract = process.extractOne('DORIS PEREZ ABENDAN', db_df['Full Name'].to_numpy(), scorer=fuzz.ratio)
bday_ratio = fuzz.token_sort_ratio('23 09 1996', '23 09 1997')

fullname_ratios =  fuzz.token_sort_ratio('DACILASASA DIVINAGRACIA XXXX XXXX XXXX X', 'DAKILASASA DIVINAGRACIA XXXX XXXX XXXX X') # 23 LENGTH
fullname_ratios2 = fuzz.token_sort_ratio('DACELASASA DIVINAGRACIA XXXX XXXX XXXX X', 'DAKILISASA DIVINAGRACIA XXXX XXXX XXXX X') # 23 LENGTH
fullname_ratios3 = fuzz.token_sort_ratio('DACILA ASA DIVINAGRACIA XXXX XXXX XXXX X', 'DAKILA ASA DIVINAGRACIA XXXX XXXX XXXX X') # 23 LENGTH
fullname_ratios4 = fuzz.token_sort_ratio('DACILA ASS DIVINAGRACIA XXXX XXXX XXXX X', 'DAKILA ASA DIVINAGRACIA XXXX XXXX XXXX X') # 23 LENGTH

# print('BDay Ratio: ', bday_ratio)
# print('Full name Ratio:   ', fullname_ratios)
# print('Full name Ratio 2: ', fullname_ratios2)
# print('Full name Ratio 3: ', fullname_ratios3)
# print('Full name Ratio 4: ', fullname_ratios4)
# print('Full name Ratio 5: ', fullname_ratio5)
# print('Full name Ratio 6: ', fullname_ratio6)
# print('Full name Ratio 7: ', fullname_ratio7)

# name = 'KIRBY INGAN ENG XXXXXXXXXXXXXXXXXXXXXX'
# print('name length: ', len(name))
# print('Token sort Ratio: ', fullname_token_sort_ratio)
# print('Total Ratio: ', ln_ratio + mn_ratio + fn_ratio)
# print('Avg Ratio: ', avg_ratio)
# print('Extract one: ', extract)

# result = {
#     "bday_ratio": bday_ratio,
#     "fullname_ratio": fullname_ratio,
#     "fullname_token_sort_ratio": fullname_token_sort_ratio
# }

# print(json.dumps(result))

# query = 'JOSIE ESGANA DIZON'
# choices = ['JOSIE DIZON ESGANA', 'JOSIE E DIZON', 'JESIE ESGANA DIZON', 'JOSIE ESGANA DISON', 'JUAN ESGANA DIZON']  

# print('generating dedup verification successful')
# fn_ratio = fuzz.ratio('geeks for geeks', 'geek for geek')
# mn_ratio = fuzz.ratio('geeks for geeks', 'geek geek')
# ln_ratio = fuzz.ratio('geeks for geeks', 'g. for geeks')

# Get a list of matches ordered by score, default limit to 5 
# extract = process.extract(query, choices, scorer=fuzz.token_sort_ratio) 
# print(extract)
# # [('geeks geeks', 95), ('g. for geeks', 95), ('geek for geek', 93)] 
   
# # If we want only the top one 
# extractOne = process.extractOne(query, choices, scorer=fuzz.token_sort_ratio) 
# print(extractOne)
# ('geeks geeks', 95) 

# print(fn_ratio)
# print(mn_ratio)
# print(ln_ratio)

# Sample DataFrame
data = {
    'Name': ['Alice', '', 'Charlotte', 'David'],
    'City': ['New York', 'LA', 'San Francisco', 'Chicago'],
    'Country': ['USA', 'USA', 'United States', 'USA']
}

df = pd.DataFrame(data)

df = df.fillna('')
cols = ['Name', 'City']
df[cols] = df[cols].replace('', pd.NA)

missing_cols = [col for col in cols if df[col].isnull().any()]

print("Missing columns:", missing_cols)

print(df)
# columns = ['Name', 'City']
# missing_cols = [col for col in columns if df[col].isnull().any()]
# print("Missing columns:", missing_cols)

# # Set the maximum allowed length
# max_length = 10

# # Check if any value exceeds the maximum length
# exceeds_limit = df.map(lambda x: len(x) > max_length)

# # Print rows where any column exceeds the limit
# rows_exceeding = df[exceeds_limit.any(axis=1)]

# # print("Rows with values exceeding max length:")
# # print(rows_exceeding)

# leng = len('Record verification for Initial RDV Test File.xlsx')
# # print('length: ', leng)

# province = 'DAVAO OCCIDENTAL/111000032729'
# # parts = province.split()
# # print(parts)
# # print(len(parts))

# # if len(parts) == 3:
# #     print(parts[0])
# #     print(parts[1])
# #     print(parts[2])
# #     print(parts[0][0].upper() + parts[1][0].upper() + parts[2][0].upper())   # First letter of provinces
# # elif len(parts) == 2:
# #     print(parts[0][0].upper() + parts[1][0].upper())   # First letter of provinces
# # elif len(parts) == 1:
# #     print(parts[0][0].upper())   # Only one province part

# date_served = '05/26/2025'
# selected_date = parser.parse(date_served).date()
# selected_date.strftime('%d %m %Y')

# # print('date:', selected_date)

# df = pd.DataFrame(
#         {
#             'birth_day': [
#                 '12a.3', '4b.5', '26 years old'
#             ],
#             'birth_month': [
#                 '7e.8', '3', 'asadd'
#             ],
#             'birth_year': [
#                 '1997', '199s8.16', '2000'
#             ]
#         }
#     )

# columns = ['birth_day', 'birth_month', 'birth_year']
# df[columns] = df[columns].astype(str).replace(r'[^0-9.]', '', regex=True)
# df[columns] = df[columns].apply(
#     lambda col: pd.to_numeric(col, errors='coerce')
# )
# # df = df.dropna()
# df[columns] = df[columns].fillna(0)
# df[columns] = df[columns].astype(int)
# df[columns] = df[columns].astype(str).map(lambda x: x.zfill(2))
# df['Birthday'] = df['birth_day'] + '/' + df['birth_month'] + '/' + df['birth_year']
# # df['Birthday'] = df['Birthday'].apply(parser.parse)
# # print('df: ', df)

# df = pd.DataFrame({'text': ['Hello', 'This is a test', 'Exactly forty characters here123456']})

# df['padded'] = df['text'].astype(str).str.pad(width=40, side='right', fillchar='X')

# # print(df)

# conn = mysql.connector.connect(
#     host="127.0.0.1",
#     user="root",
#     password="",
#     database="dedup_laravel"
# )

# if conn.is_connected():
#     # print("Database connection established for Dedup...\n")

#     cursor = conn.cursor()

#     query = """
#         SELECT * FROM aics_clean_list;
#         """

#     cursor.execute(query)
#     clean_list = cursor.fetchall()
#     clean_list_columns = [desc[0] for desc in cursor.description]

#     cursor.execute("SELECT * FROM aics_served_database;")
#     served_db = cursor.fetchall()
#     served_db_columns = [desc[0] for desc in cursor.description]

# # Mapping dictionary for replacements
# replacements = {
#     'ma.': 'maria',
#     'sto.': 'santo',
#     'sta.': 'santa',
#     '-': ' ',
#     'ñ': 'n'
# }

# # Function to perform multiple replacements
# def replace_multiple(text, replacements):
#     for old, new in replacements.items():
#         text = text.replace(old, new)
#     return text

# def clean_middle_value(value):
#     if len(value) > 0:
#         return value[0]  
#     return ''

# db_df = pd.DataFrame(served_db, columns=served_db_columns)
# db_df = db_df.astype(str)

# db_df['Full Name'] = db_df['first_name'] + ' ' + db_df['middle_name'] + ' ' + db_df['last_name']
# db_df['Full Name 2'] = db_df['first_name'] + ' ' + db_df['last_name']
# db_df['Full Name 3'] = db_df['first_name'] + ' ' + db_df['middle_name'].apply(clean_middle_value) + ' ' + db_df['last_name']

# name_cols = ['Full Name', 'Full Name 2', 'Full Name 3']
# db_df[name_cols] = db_df[name_cols].apply(lambda col: col.str.lower())

# db_df['Full Name'] = db_df['Full Name'].apply(lambda x: replace_multiple(x, replacements))
# db_df['Full Name 2'] = db_df['Full Name 2'].apply(lambda x: replace_multiple(x, replacements))
# db_df['Full Name 3'] = db_df['Full Name 3'].apply(lambda x: replace_multiple(x, replacements))

# # db_df[['Full Name', 'Full Name 2', 'Full Name 3']] = db_df[['Full Name', 'Full Name 2', 'Full Name 3']].map(lambda x: x.ljust(40, 'X'))

# name = 'kirby ingan eng'
# ratio = process.extractOne(name, db_df['Full Name 2'].to_numpy(), scorer=fuzz.token_sort_ratio)
# ratio2 = process.extractOne(name, db_df['Full Name 2'].to_numpy(), scorer=fuzz.ratio)

# print('Name: ', name)
# print('Ratio: ', ratio)
# print('Ratio2: ', ratio2)