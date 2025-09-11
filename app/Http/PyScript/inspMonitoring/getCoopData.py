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
    database="mongodb_data",
)

# Connect to MySQL database
cursor = connection.cursor()

season = sys.argv[1]
prv_dropoff_id = sys.argv[2]

# print(season,prv_dropoff_id)

getCoopInDelivery_query = f"SELECT DISTINCT(coopAccreditation) FROM {season}rcep_delivery_inspection.tbl_delivery WHERE batchTicketNumber IN (SELECT DISTINCT(batchTicketNumber) FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE prv_dropoff_id LIKE '{prv_dropoff_id}' AND isRejected = 0) AND is_cancelled = 0"
cursor.execute(getCoopInDelivery_query)
getCoopInDelivery = cursor.fetchall()

if(getCoopInDelivery):
    getCoopInDelivery_df = pd.DataFrame(getCoopInDelivery,columns = [col[0] for col in cursor.description])

    finalData = []

    prv = prv_dropoff_id[0:6]

    getRegionName_query = f"SELECT regionName FROM {season}rcep_delivery_inspection.lib_prv WHERE prv LIKE '{prv}'"
    cursor.execute(getRegionName_query)
    getRegionName = cursor.fetchall()
    regionName = getRegionName[0][0]

    for index,data in getCoopInDelivery_df.iterrows():
        coopAccred = data['coopAccreditation']
        getCoopName_query = f"SELECT coopName FROM {season}rcep_seed_cooperatives.tbl_cooperatives WHERE accreditation_no LIKE '{coopAccred}'"
        cursor.execute(getCoopName_query)
        getCoopName = cursor.fetchall()
        coopName = getCoopName[0][0]
        

        getTotalCommitments_query = f"SELECT SUM(volume) as volume FROM {season}rcep_seed_cooperatives.tbl_commitment_regional WHERE accreditation_no LIKE '{coopAccred}' AND region_name LIKE '{regionName}'"
        cursor.execute(getTotalCommitments_query)
        getTotalCommitments = cursor.fetchall()
        totalCommitments = getTotalCommitments[0][0]

        getInspected_query = f"SELECT SUM(totalBagCount) as totalBagCount FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE batchTicketNumber IN (SELECT DISTINCT(batchTicketNumber)  FROM {season}rcep_delivery_inspection.tbl_delivery WHERE coopAccreditation LIKE '{coopAccred}' AND region LIKE '{regionName}' AND is_cancelled = 0 AND prv_dropoff_id LIKE '{prv_dropoff_id}') AND isRejected = 0"
        cursor.execute(getInspected_query)
        getInspected = cursor.fetchall()
        totalInspected = getInspected[0][0]

        finalData.append({
            "coopAccred": coopAccred,
            "coopName": coopName,
            "regionName": regionName,
            "totalCommitments": int(totalCommitments) if totalCommitments else 0,
            "totalInspected": int(totalInspected) if totalInspected else 0
        })

else:
    getCoopInDelivery_query = f"SELECT DISTINCT(coopAccreditation) FROM {season}rcep_delivery_inspection.tbl_delivery WHERE prv_dropoff_id LIKE '{prv_dropoff_id}' AND is_cancelled = 0"
    cursor.execute(getCoopInDelivery_query)
    getCoopInDelivery = cursor.fetchall()
    getCoopInDelivery_df = pd.DataFrame(getCoopInDelivery,columns = [col[0] for col in cursor.description])

    finalData = []

    prv = prv_dropoff_id[0:6]

    getRegionName_query = f"SELECT regionName FROM {season}rcep_delivery_inspection.lib_prv WHERE prv LIKE '{prv}'"
    cursor.execute(getRegionName_query)
    getRegionName = cursor.fetchall()
    regionName = getRegionName[0][0]

    for index,data in getCoopInDelivery_df.iterrows():
        coopAccred = data['coopAccreditation']
        getCoopName_query = f"SELECT coopName FROM {season}rcep_seed_cooperatives.tbl_cooperatives WHERE accreditation_no LIKE '{coopAccred}'"
        cursor.execute(getCoopName_query)
        getCoopName = cursor.fetchall()
        coopName = getCoopName[0][0]
        

        getTotalCommitments_query = f"SELECT SUM(volume) as volume FROM {season}rcep_seed_cooperatives.tbl_commitment_regional WHERE accreditation_no LIKE '{coopAccred}' AND region_name LIKE '{regionName}'"
        cursor.execute(getTotalCommitments_query)
        getTotalCommitments = cursor.fetchall()
        totalCommitments = getTotalCommitments[0][0]

        totalInspected = 0

        finalData.append({
            "coopAccred": coopAccred,
            "coopName": coopName,
            "regionName": regionName,
            "totalCommitments": int(totalCommitments) if totalCommitments else 0,
            "totalInspected": int(totalInspected)}) if totalInspected else 0

cursor.close()
connection.close()
print(json.dumps(finalData))
