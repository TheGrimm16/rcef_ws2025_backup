import pandas as pd
import mysql.connector

connection = mysql.connector.connect(
    host="192.168.10.44",
    user="json",
    password="Zeijan@13",
    database="ffrs_august_2024",
)

# Connect to MySQL database
cursor = connection.cursor()

getTables_query = f"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'ffrs_january_2025' AND TABLE_NAME LIKE 'region%'"
cursor.execute(getTables_query)
getTables = cursor.fetchall()
getTables_df = pd.DataFrame(getTables,columns = [col[0] for col in cursor.description])

for table in getTables:
    print (table["TABLE_NAME"])
    break