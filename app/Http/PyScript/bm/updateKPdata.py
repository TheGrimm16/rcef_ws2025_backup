import mysql.connector
import pandas as pd
from datetime import datetime
import requests
import json
import math
import random
import json
import sys
from datetime import datetime, timezone, timedelta
import pytz


philippine_tz = pytz.timezone('Asia/Manila')
now = datetime.now(philippine_tz)
formatted_time = now.strftime('%a %b %d %Y %H:%M:%S')
utc_offset = now.strftime('%z')
utc_offset_formatted = f"GMT{utc_offset[:3]}{utc_offset[3:]}"
timezone_name = "Philippine Standard Time"
current_date = f"{formatted_time} {utc_offset_formatted} ({timezone_name})"

currentSeason = sys.argv[1]
# seasons = ['ds2025_']


connection = mysql.connector.connect(
    host="192.168.10.44",
    user="json",
    password="Zeijan@13",
    database="mongodb_data",
)
cursor = connection.cursor()
getPrv_query = f"SELECT TABLE_SCHEMA FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '{currentSeason}prv_%' AND length(TABLE_SCHEMA)=15 AND TABLE_ROWS > 0 AND TABLE_NAME LIKE 'new_released' GROUP BY TABLE_SCHEMA"
cursor.execute(getPrv_query)
getPrv = cursor.fetchall()
getPrv_df = pd.DataFrame(getPrv,columns = [col[0] for col in cursor.description])
for index,data in getPrv_df.iterrows():
    table = data['TABLE_SCHEMA']
    getReleased_query = f'SELECT db_ref, content_rsbsa, sex, birthdate, prv_dropoff_id, sum(kp_kit_count) as kp_kits FROM {table}.new_released WHERE kp_kit_count > 0 GROUP BY db_ref'
    cursor.execute(getReleased_query)
    getReleased = cursor.fetchall()
    getReleased_df = pd.DataFrame(getReleased,columns = [col[0] for col in cursor.description])
    for index,data in getReleased_df.iterrows():
        prv = data['prv_dropoff_id'][:-2]
        season = currentSeason[:-1].upper()
        kpKits = data['kp_kits']
        encodedBy = 'RSMS'
        time_stamp = current_date
        getInfo_query = f'SELECT * FROM {table}.farmer_information_final WHERE db_ref LIKE "{data["db_ref"]}" GROUP BY db_ref LIMIT 1'
        cursor.execute(getInfo_query)
        getInfo = cursor.fetchall()
        getInfo_df = pd.DataFrame(getInfo,columns = [col[0] for col in cursor.description])
        for index,data in getInfo_df.iterrows():
            fullName = data['lastName'] + ', ' + data['firstName'] + ' ' + data['midName']
            rsbsa = data['rsbsa_control_no']
            sex = data['sex']
            birthdate = data['birthdate']

        getPrvInfo_query = f'SELECT * FROM {currentSeason}rcep_delivery_inspection.lib_prv WHERE prv LIKE "{prv}" LIMIT 1'
        cursor.execute(getPrvInfo_query)
        getPrvInfo = cursor.fetchall()
        getPrvInfo_df = pd.DataFrame(getPrvInfo,columns = [col[0] for col in cursor.description])
        for index,data in getPrvInfo_df.iterrows():
            location = data['municipality'] + ', ' + data['province'] + ', ' + data['regionName']

        checkIfExists_query = f"SELECT * FROM kp_distribution.kp_distribution_app WHERE fullName LIKE '{fullName}' AND rsbsa_control_no LIKE '{rsbsa}' AND birthDate LIKE '{birthdate}' AND location LIKE '{location}' AND season LIKE '{season}'"
        cursor.execute(checkIfExists_query)
        checkIfExists = cursor.fetchall()

        if(checkIfExists):
            print(f"{fullName} - {rsbsa} - {season} already exists.")
        else:
            insertData_query = f"""INSERT INTO kp_distribution.kp_distribution_app (id, fullName, rsbsa_control_no, sex, birthdate, location, season, kpKits, calendars, testimonials, services, apps, yunpalayun, encodedBy, time_stamp) VALUES (0, '{fullName}','{rsbsa}','{sex}','{birthdate}','{location}','{season}',{kpKits},0,0,0,0,0,'{encodedBy}','{time_stamp}');"""
            # print(f"{fullName} - {rsbsa} - {season}")
            cursor.execute(insertData_query)
            connection.commit()
cursor.close()
connection.close()

print("KP Data updated successfully.")
 