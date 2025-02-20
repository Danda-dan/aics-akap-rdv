from rapidfuzz import process, fuzz
import os
import pandas as pd
import json

# database_folder_path = 'data/served'
# database_file = 'conso_served.xlsx'

# db_file_path = os.path.join(database_folder_path, database_file)

# db_df = pd.read_excel(db_file_path)
# db_df = db_df.fillna('')
# db_df = db_df.astype(str)
# db_df['Full Name'] = db_df['First Name'] + ' ' + db_df['Middle Name'] + ' ' + db_df['Last Name']

# fn_ratio = fuzz.ratio('ANGELES', 'ANGELES')
# mn_ratio = fuzz.ratio('PALANG', 'MASANGCAY')
# ln_ratio = fuzz.ratio('BELONTA', 'BELACHO')
# avg_ratio = (fn_ratio + mn_ratio + ln_ratio) / 3

fullname_ratio = fuzz.ratio('JESIE DUMA-AN', 'JESSIE DUMAAN')
# # fullname_ratio2 = fuzz.ratio('ANGELES PALANG BELONTA', 'ANGELEZ BELONTA')
# fullname_ratio2 = fuzz.ratio('VIRGILIO VILLARIN', 'VIRGILIO MILLAN')
fullname_token_sort_ratio = fuzz.token_sort_ratio('MARI. ISABELA DIZON', 'MARIA ISABELA DIZON')
# extract = process.extractOne('DORIS PEREZ ABENDAN', db_df['Full Name'].to_numpy(), scorer=fuzz.ratio)
bday_ratio = fuzz.ratio('21/04/1988', '21/04/1987')

# print('BDay Ratio: ', bday_ratio)
# fn_ratio*=0.4
# print('FN Ratio: ', fn_ratio)
# print('MN Ratio: ', mn_ratio)
# mn_ratio*=0.1
# print('MN Ratio: ', mn_ratio)
# print('LN Ratio: ', ln_ratio)
# ln_ratio*=0.5
# print('LN Ratio: ', ln_ratio)
# print('Full name Ratio: ', fullname_ratio)
# # print('Full name Ratio 2: ', fullname_ratio2)
# print('Token sort Ratio: ', fullname_token_sort_ratio)
# print('Total Ratio: ', ln_ratio + mn_ratio + fn_ratio)
# print('Avg Ratio: ', avg_ratio)
# print('Extract one: ', extract)

result = {
    "bday_ratio": bday_ratio,
    "fullname_ratio": fullname_ratio,
    "fullname_token_sort_ratio": fullname_token_sort_ratio
}

print(json.dumps(result))

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

