import mysql.connector
import pandas as pd
import json
import sys

currentSeason = sys.argv[1]
claiming_prv = sys.argv[2]
rsbsaNo = sys.argv[3]
lastName = sys.argv[4]
firstName = sys.argv[5]
middleName = sys.argv[6]
extName = sys.argv[7]

# currentSeason = "ws2025_"
# claiming_prv = "03-71-05"
# rsbsaNo = "03-71-05-012-000114"
# lastName = ""
# firstName = ""
# middleName = ""
# extName = ""

connection = mysql.connector.connect(
    host="192.168.10.44",
    user="json",
    password="Zeijan@13",
    database="mongodb_data",
)
cursor = connection.cursor()


prv_table = claiming_prv.replace("-", "")[:-2]

# Prepare the base query
table_name = f"{currentSeason}rcep_paymaya.sed_verified"
# Start with the required condition
where_clauses = ["claiming_prv LIKE %s"]
params = [claiming_prv]

# Track how many optional filters are added
optional_filters_added = 0

# Add optional conditions
if rsbsaNo:
    where_clauses.append("rsbsa_control_number LIKE %s")
    params.append(rsbsaNo)
    optional_filters_added += 1
if lastName:
    where_clauses.append("lname LIKE %s")
    params.append(lastName)
    optional_filters_added += 1
if firstName:
    where_clauses.append("fname LIKE %s")
    params.append(firstName)
    optional_filters_added += 1
if middleName:
    where_clauses.append("midname LIKE %s")
    params.append(middleName)
    optional_filters_added += 1
if extName:
    where_clauses.append("extename LIKE %s")
    params.append(extName)
    optional_filters_added += 1

# Only proceed if at least one additional parameter is given
if optional_filters_added == 0:
    getSedVerified = []
else:
    where_statement = " AND ".join(where_clauses)
    getSedVerified_query = f"SELECT * FROM {table_name} WHERE {where_statement}"
    cursor.execute(getSedVerified_query, params)
    getSedVerified = cursor.fetchall()

if(getSedVerified and len(getSedVerified) == 1):
    getSedVerified_df = pd.DataFrame(getSedVerified, columns=[col[0] for col in cursor.description])
    data_sed = getSedVerified_df.iloc[0]
    sed_id = data_sed['sed_id']
    rsbsa = data_sed['rsbsa_control_number']
    rcefId = data_sed['rcef_id']
    getTblBene_query = f"SELECT * FROM {currentSeason}rcep_paymaya.tbl_beneficiaries WHERE paymaya_code LIKE '{rcefId}' AND rsbsa_control_no LIKE '{rsbsa}'"
    cursor.execute(getTblBene_query)
    getTblBene = cursor.fetchall()
    if (getTblBene):
        getTblBene_df = pd.DataFrame(getTblBene, columns=[col[0] for col in cursor.description])
        data_bene = getTblBene_df.iloc[0]
        bene_id = data_bene['beneficiary_id']
        deleteBene_query = f"DELETE FROM {currentSeason}rcep_paymaya.tbl_beneficiaries WHERE beneficiary_id = {bene_id}"
        cursor.execute(deleteBene_query)    
    
    getFarmerInfo_query = f"SELECT * FROM {currentSeason}prv_{prv_table}.farmer_information_final WHERE rsbsa_control_no LIKE '{rsbsa}' AND rcef_id LIKE '{rcefId}'"
    cursor.execute(getFarmerInfo_query)
    getFarmerInfo = cursor.fetchall()
    if (getFarmerInfo):
        getFarmerInfo_df = pd.DataFrame(getFarmerInfo, columns=[col[0] for col in cursor.description])
        data_farmer = getFarmerInfo_df.iloc[0]
        farmer_id = data_farmer['id']
        updateFarmer_query = f"UPDATE {currentSeason}prv_{prv_table}.farmer_information_final SET is_ebinhi = 0 WHERE id = {farmer_id}"
        cursor.execute(updateFarmer_query)
        
        deleteSed_query = f"DELETE FROM {table_name} WHERE sed_id = {sed_id}"
        cursor.execute(deleteSed_query)
        connection.commit()
        print(0)
        
    else:
        print("No farmer information found. Please contact CES IT for assistance.")

elif(getSedVerified and len(getSedVerified) > 1):
    print("Multiple records found. Please contact CES IT for assistance.")
else:
    print("No data found. Please verify the information you provided.")
cursor.close()
connection.close()
 