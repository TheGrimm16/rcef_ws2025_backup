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

coopName = coopDetails['coopName']
accreditation_no = coopDetails['accreditation_no']

getPending_query = f"SELECT * FROM {season}rcep_delivery_inspection.tbl_delivery_transaction WHERE isBuffer!=9 AND accreditation_no LIKE '{accreditation_no}' AND status = 0"
cursor.execute(getPending_query)
getPending = cursor.fetchall()
getPending_df = pd.DataFrame(getPending,columns = [col[0] for col in cursor.description])

getConfirmed_query = f"SELECT * FROM {season}rcep_delivery_inspection.tbl_delivery WHERE isBuffer!=9 AND coopAccreditation LIKE '{accreditation_no}' AND is_cancelled = 0 AND batchTicketNumber NOT IN(SELECT batchTicketNumber FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE isBuffer!=9 AND  moa_number LIKE '{moa}' GROUP BY batchTicketNumber)"
cursor.execute(getConfirmed_query)
getConfirmed = cursor.fetchall()
getConfirmed_df = pd.DataFrame(getConfirmed,columns = [col[0] for col in cursor.description])

getInspected_query = f"SELECT * FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE isBuffer!=9 AND batchTicketNumber IN(SELECT batchTicketNumber FROM {season}rcep_delivery_inspection.tbl_delivery WHERE isBuffer!=9 AND coopAccreditation LIKE '{accreditation_no}' AND is_cancelled = 0 GROUP BY batchTicketNumber)"
cursor.execute(getInspected_query)
getInspected = cursor.fetchall()
getInspected_df = pd.DataFrame(getInspected,columns = [col[0] for col in cursor.description])

cursor.close()
connection.close()

def safe_convert(val):
    if isinstance(val, Decimal):
        return float(val)   # or str(val) if you want precision
    if isinstance(val, (pd.Timestamp, datetime)):
        return val.isoformat()
    if pd.isna(val) or val is None:
        return None
    return val

def df_to_records(df):
    return [
        {col: safe_convert(val) for col, val in row.items()}
        for row in df.to_dict(orient="records")
    ]

result = {
    "coop_name": coopName,
    "pending": df_to_records(getPending_df),
    "confirmed": df_to_records(getConfirmed_df),
    "inspected": df_to_records(getInspected_df)
}

print(json.dumps(result))