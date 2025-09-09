import mysql.connector
import pandas as pd
from datetime import datetime
import requests
import json
import math
import random
import json
import sys
from decimal import Decimal

# Database connection details

connection = mysql.connector.connect(
        host="192.168.10.44",
        user="json",
        password="Zeijan@13",
        database="mongodb_data",
    )

# Connect to MySQL database
cursor = connection.cursor()

season = sys.argv[1]
coop = sys.argv[2]

coopData = []

def decimal_default(obj):
    if isinstance(obj, Decimal):
        return float(obj)
    raise TypeError

getCoop_query = f'SELECT * FROM {season}rcep_seed_cooperatives.tbl_cooperatives WHERE accreditation_no = "{coop}"'
cursor.execute(getCoop_query)
getCoop = cursor.fetchall()
getCoop_df = pd.DataFrame(getCoop,columns = [col[0] for col in cursor.description])
coopDetails = getCoop_df.iloc[0]
coopId = coopDetails['coopId']
accred = coopDetails['accreditation_no']
moa = coopDetails['current_moa']

getCommitments_query = f'SELECT coop_name, accreditation_no, region_name, SUM(volume) as totalCommitments FROM {season}rcep_seed_cooperatives.tbl_commitment_regional WHERE coop_Id = "{coopId}" GROUP BY region_name'
cursor.execute(getCommitments_query)
getCommitments = cursor.fetchall()
getCommitments_df = pd.DataFrame(getCommitments,columns = [col[0] for col in cursor.description])
for index,commitment in getCommitments_df.iterrows():
    coopName = commitment['coop_name']
    accreditation_no = commitment['accreditation_no']
    region = commitment['region_name']
    totalCommitments = commitment['totalCommitments']

    getPending_query = f"SELECT region, SUM(instructed_delivery_volume) as total FROM {season}rcep_delivery_inspection.tbl_delivery_transaction WHERE isBuffer!=9 AND accreditation_no LIKE '{accreditation_no}' AND region LIKE '{region}' AND status = 0 GROUP BY region"
    cursor.execute(getPending_query)
    getPending = cursor.fetchall()
    getPending_df = pd.DataFrame(getPending,columns = [col[0] for col in cursor.description])
    if len(getPending_df) > 0:
        pendingDeliveries = getPending_df.iloc[0]['total']
    else:
        pendingDeliveries = 0
    
    getConfirmed_query = f"SELECT region, SUM(totalBagCount) as total FROM {season}rcep_delivery_inspection.tbl_delivery WHERE isBuffer!=9 AND coopAccreditation LIKE '{accreditation_no}' AND region LIKE '{region}' AND is_cancelled = 0 AND batchTicketNumber NOT IN(SELECT batchTicketNumber FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE isBuffer!=9 AND  moa_number LIKE '{moa}' AND region LIKE '{region}' GROUP BY batchTicketNumber) GROUP BY region;"
    cursor.execute(getConfirmed_query)
    getConfirmed = cursor.fetchall()
    getConfirmed_df = pd.DataFrame(getConfirmed,columns = [col[0] for col in cursor.description])
    if len(getConfirmed_df) > 0:
        confirmedDeliveries = getConfirmed_df.iloc[0]['total']
    else:
        confirmedDeliveries = 0

    getInspected_query = f"SELECT region,SUM(totalBagCount) as total FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE isBuffer!=9 AND batchTicketNumber IN(SELECT batchTicketNumber FROM {season}rcep_delivery_inspection.tbl_delivery WHERE isBuffer!=9 AND coopAccreditation LIKE '{accreditation_no}' AND region LIKE '{region}' AND is_cancelled = 0 GROUP BY batchTicketNumber) GROUP BY region"
    cursor.execute(getInspected_query)
    getInspected = cursor.fetchall()
    getInspected_df = pd.DataFrame(getInspected,columns = [col[0] for col in cursor.description])
    if len(getInspected_df) > 0:
        inspectedDeliveries = getInspected_df.iloc[0]['total']
    else:
        inspectedDeliveries = 0
    
    remainingBalance = totalCommitments - (pendingDeliveries + confirmedDeliveries + inspectedDeliveries)
    coopData.append({
        'coopName': coopName,
        'accreditation_no': accreditation_no,
        'moa_no': moa,
        'region': region if region and region.strip() else "ANY REGION",
        'totalCommitments': totalCommitments,
        'pendingDeliveries': pendingDeliveries,
        'confirmedDeliveries': confirmedDeliveries,
        'inspectedDeliveries': inspectedDeliveries,
        'remainingBalance': remainingBalance
    })

cursor.close()
connection.close()
print(json.dumps(coopData, default=decimal_default))
