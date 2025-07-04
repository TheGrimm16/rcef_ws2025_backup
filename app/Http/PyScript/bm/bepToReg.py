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
# claiming_prv = "17-53-15"
# rsbsaNo = "17-53-15-012-000061"
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
table_name = f"{currentSeason}prv_{prv_table}.farmer_information_final"
# Start with the required condition
where_clauses = ["claiming_prv LIKE %s"]
params = [claiming_prv]

# Track how many optional filters are added
optional_filters_added = 0

# Add optional conditions
if rsbsaNo:
    where_clauses.append("rsbsa_control_no LIKE %s")
    params.append(rsbsaNo)
    optional_filters_added += 1
if lastName:
    where_clauses.append("lastName LIKE %s")
    params.append(lastName)
    optional_filters_added += 1
if firstName:
    where_clauses.append("firstName LIKE %s")
    params.append(firstName)
    optional_filters_added += 1
if middleName:
    where_clauses.append("midName LIKE %s")
    params.append(middleName)
    optional_filters_added += 1
if extName:
    where_clauses.append("extName LIKE %s")
    params.append(extName)
    optional_filters_added += 1

# Only proceed if at least one additional parameter is given
if optional_filters_added == 0:
    getFarmerInfo = []
else:
    where_statement = " AND ".join(where_clauses)
    getFarmerInfo_query = f"SELECT * FROM {table_name} WHERE {where_statement} AND is_ebinhi = 1"
    cursor.execute(getFarmerInfo_query, params)
    getFarmerInfo = cursor.fetchall()

if(getFarmerInfo and len(getFarmerInfo) == 1):
    getFarmerInfo_df = pd.DataFrame(getFarmerInfo, columns=[col[0] for col in cursor.description])
    data_reg = getFarmerInfo_df.iloc[0]
    reg_id = data_reg['id']
    reg_rsbsa = data_reg['rsbsa_control_no']
    reg_is_claimed = data_reg['is_claimed']
    reg_total_claimed = data_reg['total_claimed']
    reg_total_claimed_area = data_reg['total_claimed_area']
    reg_replacement_bags = data_reg['replacement_bags']
    reg_replacement_area = data_reg['replacement_area']

    total_bags = 0
    total_area = 0.0

    getBepClaims_query = f"SELECT COUNT(*) as bags FROM {currentSeason}rcep_paymaya.tbl_claim WHERE rsbsa_control_no LIKE '{reg_rsbsa}'"
    cursor.execute(getBepClaims_query)
    getBepClaims = cursor.fetchall()
    if(getBepClaims):
        getBepClaims_df = pd.DataFrame(getBepClaims, columns=[col[0] for col in cursor.description])
        bep_claims = getBepClaims_df.iloc[0]['bags']
        bep_area = bep_claims/2.169
    else:
        bep_claims = 0
        bep_area = 0
    
    total_bags = reg_total_claimed + bep_claims
    total_area = reg_total_claimed_area + bep_area

    updateFarmer_query = f"UPDATE {table_name} SET is_ebinhi = 0, is_claimed = 1, total_claimed = {total_bags}, total_claimed_area = {total_area}, replacement_bags = {total_bags}, replacement_area = {total_area} WHERE id = {reg_id}"
    cursor.execute(updateFarmer_query)
    connection.commit()
    print(0)

elif(getFarmerInfo and len(getFarmerInfo) > 1):
    print("Multiple records found. Please contact CES IT for assistance.")
else:
    print("No data found. Please verify the information you provided.")
cursor.close()
connection.close()
 