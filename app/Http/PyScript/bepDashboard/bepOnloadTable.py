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


season = sys.argv[1]

bepData = []

# Connect to MySQL database
cursor = connection.cursor()

getProvinces_query = f"SELECT COUNT(DISTINCT(paymaya_code)) as beneficiaries FROM {season}rcep_paymaya.tbl_beneficiaries"
cursor.execute(getProvinces_query)
getProvinces = cursor.fetchall()




bepData.append({'targetBeneficiaries': 'blank','targetBags': 'blank','targetArea': 'blank','actualBeneficiaries': 'blank','actualBags': 'blank','actualArea': 'blank'})
finalBepData = json.dumps(bepData)
print(finalBepData)

