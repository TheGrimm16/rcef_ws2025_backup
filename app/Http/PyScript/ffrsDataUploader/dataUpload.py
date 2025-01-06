import polars as pl
import pandas as pd
import mysql.connector
import sys
import random
from datetime import datetime
import json
from urllib.parse import quote

connection = mysql.connector.connect(
    host="192.168.10.44",
    user="json",
    password="Zeijan@13",
    database="ffrs_august_2024",
)

# Connect to MySQL database
cursor = connection.cursor()

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

# Load JSON data from the file (path passed as an argument)
file_path = "C:\\xampp\\htdocs\\rcef_ds2025\\storage\\data.json"

# Uncomment for live
# file_path = "C:\\Apache24\\htdocs\\rcef_ds2025\\storage\\data.json"

file_path = sys.argv[1]
season = sys.argv[2]
# with open(file_path, 'r') as f:
#     data = json.load(f)

formatted_date = datetime.now()
formatted_date = formatted_date.strftime('%Y-%m-%d')

data = pd.read_json(file_path)
    
for index,record in data.iterrows():
    newArea = record['crop_area']
    prv_code = str(record['parcel_reg_code']).zfill(2)+str(record['parcel_prv_code']).zfill(3)+str(record['parcel_mun_code']).zfill(2)
    if len(prv_code) < 5:
                  prv_code = str(record['farmer_reg_code']).zfill(2)+str(record['farmer_prv_code']).zfill(3)+str(record['farmer_mun_code']).zfill(2)
                  if len(prv_code) < 5:
                        tmp_prv = record['rsbsa_no']
                        prv_arr = tmp_prv.split('-')
                        prv_code = prv_arr[0] + prv_arr[1].zfill(3) + prv_arr[2]
    getTable_query = f"SELECT geo_code1 from {season}rcep_delivery_inspection.geo_map WHERE geo_code LIKE '{prv_code}%' LIMIT 1"
    cursor.execute(getTable_query)
    getTable = cursor.fetchall()
    prv_tbl = getTable[0][0][:4]
    claiming_prv = getTable[0][0][:2]+'-'+getTable[0][0][2:4]+'-'+getTable[0][0][4:6]

    checkIfExists_query = f"SELECT * from {season}prv_{prv_tbl}.farmer_information_final WHERE rsbsa_control_no LIKE '{record['rsbsa_no']}' AND claiming_prv LIKE '{claiming_prv}' AND firstName LIKE '{record['first_name']}' AND lastName LIKE '{record['last_name']}' AND midName LIKE '{record['middle_name']}'"
    cursor.execute(checkIfExists_query)
    checkIfExists = cursor.fetchall()

    if checkIfExists:
        checkIfExists_df = pd.DataFrame(checkIfExists,columns = [col[0] for col in cursor.description])
        for index,data in checkIfExists_df.iterrows():
            db_ref = data['db_ref']
            getAreaHistory_query = f"SELECT * from {season}prv_{prv_tbl}.area_history WHERE db_ref LIKE {db_ref} ORDER BY version DESC LIMIT 1"
            cursor.execute(getAreaHistory_query)
            getAreaHistory = cursor.fetchall()  
            getAreaHistory_df = pd.DataFrame(getAreaHistory,columns = [col[0] for col in cursor.description])
            for index,data in getAreaHistory_df.iterrows():
                if(data['area'] != newArea):
                      areaVersion = data['version'] + 1
                      insertUpdate_query = f"INSERT INTO {season}prv_{prv_tbl}.area_history (id, db_ref, area, date, version) VALUES (NULL, {db_ref}, {newArea}, '{formatted_date}', {areaVersion})"
                      cursor.execute(insertUpdate_query)
                      connection.commit()
    else:
        checkIfExists_query = f"SELECT * from {season}prv_{prv_tbl}.farmer_information_final WHERE rsbsa_control_no LIKE '{record['rsbsa_no']}' AND claiming_prv LIKE '{claiming_prv}'"
        cursor.execute(checkIfExists_query)
        checkIfExists = cursor.fetchall()
        if checkIfExists:
            checkIfExists_df = pd.DataFrame(checkIfExists,columns = [col[0] for col in cursor.description])
            for index,data in checkIfExists_df.iterrows():
                db_ref = data['db_ref']
                getAreaHistory_query = f"SELECT * from {season}prv_{prv_tbl}.area_history WHERE db_ref LIKE {db_ref} ORDER BY version DESC LIMIT 1"
                cursor.execute(getAreaHistory_query)
                getAreaHistory = cursor.fetchall()  
                getAreaHistory_df = pd.DataFrame(getAreaHistory,columns = [col[0] for col in cursor.description])
                for index,data in getAreaHistory_df.iterrows():
                    if(data['area'] != newArea):
                        areaVersion = data['version'] + 1
                        insertUpdate_query = f"INSERT INTO {season}prv_{prv_tbl}.area_history (id, db_ref, area, date, version) VALUES (NULL, {db_ref}, {newArea}, '{formatted_date}', {areaVersion})"
                        cursor.execute(insertUpdate_query)
                        connection.commit()
        else:
            checkIfExists_query = f"SELECT * from {season}prv_{prv_tbl}.farmer_information_final WHERE claiming_prv LIKE '{claiming_prv}' AND firstName LIKE '{record['first_name']}' AND lastName LIKE '{record['last_name']}' AND midName LIKE '{record['middle_name']}'"
            cursor.execute(checkIfExists_query)
            checkIfExists = cursor.fetchall()
            if checkIfExists:
                checkIfExists_df = pd.DataFrame(checkIfExists,columns = [col[0] for col in cursor.description])
                for index,data in checkIfExists_df.iterrows():
                    db_ref = data['db_ref']
                    getAreaHistory_query = f"SELECT * from {season}prv_{prv_tbl}.area_history WHERE db_ref LIKE {db_ref} ORDER BY version DESC LIMIT 1"
                    cursor.execute(getAreaHistory_query)
                    getAreaHistory = cursor.fetchall()  
                    getAreaHistory_df = pd.DataFrame(getAreaHistory,columns = [col[0] for col in cursor.description])
                    for index,data in getAreaHistory_df.iterrows():
                        if(data['area'] != newArea):
                            areaVersion = data['version'] + 1
                            insertUpdate_query = f"INSERT INTO {season}prv_{prv_tbl}.area_history (id, db_ref, area, date, version) VALUES (NULL, {db_ref}, {newArea}, '{formatted_date}', {areaVersion})"
                            cursor.execute(insertUpdate_query)
                            connection.commit()
            else:
                prv_code = str(record['parcel_reg_code']).zfill(2)+str(record['parcel_prv_code']).zfill(3)+str(record['parcel_mun_code']).zfill(2)
                if len(prv_code) < 5:
                    prv_code = record['farmer_reg_code']+record['farmer_prv_code']+record['farmer_mun_code']
                    if len(prv_code) < 5:
                        tmp_prv = record['rsbsa_no']
                        prv_arr = tmp_prv.split('-')
                        prv_code = prv_arr[0] + prv_arr[1].zfill(3) + prv_arr[2]
                getTable_query = f"SELECT geo_code1 from {season}rcep_delivery_inspection.geo_map WHERE geo_code LIKE '{prv_code}%' LIMIT 1"
                cursor.execute(getTable_query)
                getTable = cursor.fetchall()

                prv_tbl = getTable[0][0][:4]
                claiming_prv = '-'.join([getTable[0][0][i:i+2] for i in range(0, 6, 2)])
                fullName = f"{record['last_name']}, {record['first_name']} {record['middle_name']} {record['ext_name']}"
                
                parcel_address_json = record["parcel_address_json"]
                parcel_address_json = parcel_address_json.replace("{ '", '{ \"')
                parcel_address_json = parcel_address_json.replace("' : '", '\" : \"')
                parcel_address_json = parcel_address_json.replace("', '", '\", \"')
                parcel_address_json = parcel_address_json.replace("'}", '\"}')
                parcel_address_json = '[' + parcel_address_json + ']'
                
                parcel_address_json = json.loads(parcel_address_json)
                no_of_parcels = len(parcel_address_json)

                date_created = datetime.now()
                formatted_datetime = date_created.strftime('%Y-%m-%d %H:%M:%S')

                getMaxDbRef_query = f"SELECT MAX(db_ref) FROM {season}prv_{prv_tbl}.farmer_information_final"
                cursor.execute(getMaxDbRef_query)
                getMaxDbRef = cursor.fetchall()

                maxDbRef = (getMaxDbRef[0][0])
                db_ref = maxDbRef + 1

                getRcef_id_query = f"SELECT DISTINCT(rcef_id) AS rcef_id FROM {season}prv_{prv_tbl}.farmer_information_final"
                cursor.execute(getRcef_id_query)
                getRcef_id = [item[0] for item in cursor.fetchall()]

                while True:
                    rcef_id = f"{prv_tbl}{str(random.randint(1, 999999)).zfill(6)}"
                    if rcef_id not in getRcef_id:
                        break

                
                total_croparea_per_bgy = {}

                for parcel in parcel_address_json:
                    bgy = parcel['parcel_bgy']
                    croparea = float(parcel['croparea'])
                    if bgy in total_croparea_per_bgy:
                        total_croparea_per_bgy[bgy] += croparea
                    else:
                        total_croparea_per_bgy[bgy] = croparea

                output_list = []

                for bgy, total_croparea in total_croparea_per_bgy.items():
                    output_list.append({'parcel_bgy': bgy, 'total_croparea': total_croparea})
                
                sorted_output_list = sorted(output_list, key=lambda x: x['total_croparea'], reverse=True)

                brgy = sorted_output_list[0]['parcel_bgy']

                getClaimingBrgy_query = f"SELECT geo_code1 from {season}rcep_delivery_inspection.geo_map WHERE geo_code LIKE '{prv_code}%' AND bgy_name LIKE '{brgy}' LIMIT 1"
                cursor.execute(getClaimingBrgy_query)
                getClaimingBrgy = cursor.fetchall()

                claiming_brgy = (getClaimingBrgy[0][0])

                crop_area = record["crop_area"]

                parcel_address_json = json.dumps(parcel_address_json)
            
                # insertData_query = f"""INSERT INTO {season}prv_{prv_tbl}.farmer_information_final (id, is_new, is_dq, claiming_prv, claiming_brgy, no_of_parcels, parcel_brgy_info, rsbsa_control_no, db_ref, rcef_id, new_rcef_id, assigned_rsbsa, farmer_id, distributionID, da_intervention_card, lastName, firstName, midName, extName, fullName, sex, birthdate, region, province, municipality, brgy_name, mother_name, spouse, tel_no, geo_code, civil_status, fca_name, is_pwd, is_arb, is_ip, tribe_name, ben_4ps, data_source, sync_date, crop_establishment_cs, ecosystem_cs, ecosystem_source_cs, planting_week, final_area, final_claimable, is_claimed, total_claimed, total_claimed_area, is_replacement, replacement_area, replacement_bags, replacement_bags_claimed, replacement_area_claimed, replacement_reason, prev_claimable, prev_final_area, prev_claimed, prev_claimed_area, dq_reason, is_ebinhi, print_count, to_prv_code) VALUES (0, 0, 0, "{claiming_prv}", "{claiming_brgy}", {no_of_parcels}, "{parcel_address_json}", "{record["rsbsa_no"]}", {db_ref}, "{rcef_id}", 0, "{record["rsbsa_no"]}", {db_ref}, {db_ref}, "-", "{record["last_name"]}", "{record["first_name"]}", "{record["middle_name"]}", "{record["ext_name"]}", "{fullName}", "{record["gender"]}", "{record["birthday"]}", "{record["farmer_address_reg"]}", "{record["farmer_address_prv"]}", "{record["farmer_address_mun"]}", "{record["farmer_address_bgy"]}", "{record["mother_maiden_name"]}", "{record["spouse"]}", "{record["contact_num"]}", "{record["geo_loc"]}", "-", "{record["fca_name"]}", "{record["pwd"]}", "{record["arb"]}", "{record["ancestral_domain"]}", "-", "{record["beneficiary_4ps"]}", "FFRS Monthly Update", "{formatted_datetime}", "-", "{record["farm_type"]}", "-", "-", {record["crop_area"]}, CEIL({record["crop_area"]}*2), 0, 0, 0, 0, 0, 0, 0, 0, "-", 0, 0, 0, 0, "-", 0, 0, 0);"""
                # cursor.execute(insertData_query)
                # connection.commit()

                # insertUpdate_query = f"INSERT INTO {season}prv_{prv_tbl}.area_history (id, db_ref, area, date, version) VALUES (NULL, {db_ref}, {crop_area}, '{formatted_date}', 1.00)"
                # cursor.execute(insertUpdate_query)
                # connection.commit()
    # break
    
cursor.close()
connection.close()
# Return the processed data as JSON
print(data)