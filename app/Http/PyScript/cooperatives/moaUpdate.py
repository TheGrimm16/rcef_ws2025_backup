import mysql.connector
import pandas as pd
import sys

# Database connection details

connection = mysql.connector.connect(
        host="192.168.10.44",
        user="json",
        password="Zeijan@13",
        database="mongodb_data",
    )

# Connect to MySQL database
cursor = connection.cursor()

coopId = sys.argv[1]

getAllCoop_query = f"SELECT * FROM ds2025_rcep_seed_cooperatives.tbl_cooperatives WHERE coopId = {coopId}"
cursor.execute(getAllCoop_query)
getAllCoop = cursor.fetchall()
getAllCoop_df = pd.DataFrame(getAllCoop,columns = [col[0] for col in cursor.description])
for index,data in getAllCoop_df.iterrows():
    coopId = data['coopId']
    accred = data['accreditation_no']
    moa = data['current_moa']
    
    updateCommitment_query = f'UPDATE ds2025_rcep_seed_cooperatives.tbl_commitment SET moa_number = "{moa}" WHERE coopID = "{coopId}"'
    cursor.execute(updateCommitment_query)
    
    updateTotalCommitment_query = f'UPDATE ds2025_rcep_seed_cooperatives.tbl_total_commitment SET moa_number = "{moa}" WHERE coopID = "{coopId}"'
    cursor.execute(updateTotalCommitment_query)

    updateRla_query = f'UPDATE ds2025_rcep_delivery_inspection.tbl_rla_details SET moaNumber = "{moa}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateRla_query)

    updateRlaRequest_query = f'UPDATE ds2025_rcep_delivery_inspection.rla_requests SET coop_moa = "{moa}" WHERE coop_accreditation = "{accred}"'
    cursor.execute(updateRlaRequest_query)
    
    updateDeliveryTransaction_query = f'UPDATE ds2025_rcep_delivery_inspection.tbl_delivery_transaction SET moa_number = "{moa}" WHERE accreditation_no = "{accred}"'
    cursor.execute(updateDeliveryTransaction_query)

    updateDelivery_query = f'UPDATE ds2025_rcep_delivery_inspection.tbl_delivery SET moa_number = "{moa}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateDelivery_query)

    updateInspection_query = f'UPDATE ds2025_rcep_delivery_inspection.tbl_inspection SET moa_number = "{moa}" WHERE batchTicketNumber IN (SELECT batchTicketNumber FROM ds2025_rcep_delivery_inspection.tbl_delivery WHERE coopAccreditation LIKE "{accred}")'
    cursor.execute(updateInspection_query)
    
    updateActualDelivery_query = f'UPDATE ds2025_rcep_delivery_inspection.tbl_actual_delivery SET moa_number = "{moa}" WHERE batchTicketNumber IN (SELECT batchTicketNumber FROM ds2025_rcep_delivery_inspection.tbl_delivery WHERE coopAccreditation LIKE "{accred}")'
    cursor.execute(updateActualDelivery_query)
    connection.commit()

    print(accred)

          
cursor.close()
connection.close()

print(coopId)
 