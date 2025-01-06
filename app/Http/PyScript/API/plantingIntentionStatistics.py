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


prv = sys.argv[1]
season = sys.argv[2]

stat = []

getTable_query = f"SELECT province, municipality, LEFT(prv_dropoff_id,6) as prv_code, seed_variety from {season}prv_{prv}.new_released GROUP BY prv_code, seed_variety"
cursor.execute(getTable_query)
getTable = cursor.fetchall()
getTable_df = pd.DataFrame(getTable,columns = [col[0] for col in cursor.description])
for index,data in getTable_df.iterrows():
    province = data['province']
    municipality = data['municipality']
    prv_code = data['prv_code']
    psa_code = 'PH' + prv_code + '000'
    seed_variety = data['seed_variety']
    
    getRegion_query = f"SELECT regionName from {season}rcep_delivery_inspection.lib_prv WHERE prv = '{prv_code}'"
    cursor.execute(getRegion_query)
    getRegion = cursor.fetchall()

    region = getRegion[0][0]

    getTotalFarmers_query = f"SELECT COUNT(DISTINCT(db_ref)) as totalFarmers from {season}prv_{prv}.new_released WHERE province LIKE '{province}' AND municipality LIKE '{municipality}' AND prv_dropoff_id LIKE '{prv_code}%' AND seed_variety LIKE '{seed_variety}'"
    cursor.execute(getTotalFarmers_query)
    getTotalFarmers = cursor.fetchall()

    totalFarmers = getTotalFarmers[0][0]

    getTotalBagsArea_query = f"SELECT SUM(bags_claimed) as totalBags, SUM(claimed_area) as totalArea from {season}prv_{prv}.new_released WHERE province LIKE '{province}' AND municipality LIKE '{municipality}' AND prv_dropoff_id LIKE '{prv_code}%' AND seed_variety LIKE '{seed_variety}'"
    cursor.execute(getTotalBagsArea_query)
    getTotalBagsArea = cursor.fetchall()

    totalBags = int(getTotalBagsArea[0][0])
    totalArea = float(getTotalBagsArea[0][1])


    getEarly_query = f"SELECT DISTINCT(planting_week) as early from {season}prv_{prv}.new_released WHERE province LIKE '{province}' AND municipality LIKE '{municipality}' AND prv_dropoff_id LIKE '{prv_code}%' AND seed_variety LIKE '{seed_variety}' ORDER BY planting_week limit 1"
    cursor.execute(getEarly_query)
    getEarly = cursor.fetchall()

    earlyPlanting = getEarly[0][0]

    getMax_query = f"SELECT DISTINCT(planting_week) as max from {season}prv_{prv}.new_released WHERE province LIKE '{province}' AND municipality LIKE '{municipality}' AND prv_dropoff_id LIKE '{prv_code}%' AND seed_variety LIKE '{seed_variety}' ORDER BY planting_week DESC limit 1"
    cursor.execute(getMax_query)
    getMax = cursor.fetchall()

    maxPlanting = getMax[0][0]


    getPeak_query = f"SELECT DISTINCT(planting_week) as peak, COUNT(*) as totalCount from {season}prv_{prv}.new_released WHERE province LIKE '{province}' AND municipality LIKE '{municipality}' AND prv_dropoff_id LIKE '{prv_code}%' AND seed_variety LIKE '{seed_variety}' ORDER BY totalCount DESC limit 1"
    cursor.execute(getPeak_query)
    getPeak = cursor.fetchall()

    peakPlanting = getPeak[0][0]

    stat.append({'region': region,'province': province, 'municipality': municipality, 'psa_code': psa_code, 'seed_variety': seed_variety, 'totalFarmers': totalFarmers, 'totalBags': totalBags, 'totalArea': totalArea, 'earlyPlanting': earlyPlanting, 'peakPlanting': peakPlanting, 'maxPlanting': maxPlanting})

cursor.close()
connection.close()
print(stat)

# New Released Table
# GROUP BY
# Region
# Province
# Municipality
# PSA Code
# Seed Variety Name

# COUNT
# Total Farmer Beneficiaries
# Total Bags Distributed
# Estimated Area Planted

# PLANTING WEEK
# Planting Intention (early) - earliest planting week
# Planting Intention (peak) - largest number of count of planting week
# Planting Intention (max) - latest planting week