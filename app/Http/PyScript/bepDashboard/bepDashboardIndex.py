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



getTargetBene_query = f"SELECT COUNT(DISTINCT(paymaya_code)) as beneficiaries FROM {season}rcep_paymaya.tbl_beneficiaries"
cursor.execute(getTargetBene_query)
getTargetBene = cursor.fetchall()
targetBene = getTargetBene[0][0]

getTargetBags_query = f"SELECT SUM(bags) as bags FROM {season}rcep_paymaya.tbl_beneficiaries"
cursor.execute(getTargetBags_query)
getTargetBags = cursor.fetchall()
targetBags = float(getTargetBags[0][0])

getTargetArea_query = f"SELECT SUM(area) as area FROM {season}rcep_paymaya.tbl_beneficiaries"
cursor.execute(getTargetArea_query)
getTargetArea = cursor.fetchall()
targetArea = float(getTargetArea[0][0])

getActualBene_query = f"SELECT SUM(total_beneficiaries) as beneficiaries FROM {season}rcep_paymaya.paymaya_total_beneficiaries"
cursor.execute(getActualBene_query)
getActualBene = cursor.fetchall()
actualBene = int(getActualBene[0][0])

getActualBags_query = f"SELECT SUM(total_bags) as bags FROM {season}rcep_paymaya.paymaya_total_bags"
cursor.execute(getActualBags_query)
getActualBags = cursor.fetchall()
actualBags = float(getActualBags[0][0])

getActualArea_query = f"SELECT SUM(sum_area) as area FROM {season}rcep_paymaya.paymaya_claim_area"
cursor.execute(getActualArea_query)
getActualArea = cursor.fetchall()
actualArea = float(getActualArea[0][0])



bepData.append({'targetBeneficiaries': targetBene,'targetBags': targetBags,'targetArea': targetArea,'actualBeneficiaries': actualBene,'actualBags': actualBags,'actualArea': actualArea})
finalBepData = json.dumps(bepData)
print(finalBepData)

# getTotal_df = pd.DataFrame(getTotal,columns = [col[0] for col in cursor.description])
# final = getTotal_df.to_json(orient="records")
# final = json.dumps(final)


# cursor.close()
# connection.close()
# print(finalBepData)
