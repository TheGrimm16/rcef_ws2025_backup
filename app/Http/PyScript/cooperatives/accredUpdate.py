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
season = sys.argv[2]
newAccred = sys.argv[3]

getAllCoop_query = f"SELECT * FROM {season}rcep_seed_cooperatives.tbl_cooperatives WHERE coopId = {coopId}"
cursor.execute(getAllCoop_query)
getAllCoop = cursor.fetchall()
getAllCoop_df = pd.DataFrame(getAllCoop,columns = [col[0] for col in cursor.description])
for index,data in getAllCoop_df.iterrows():
    coopId = data['coopId']
    accred = data['accreditation_no']
    
    updateCommitment_query = f'UPDATE {season}rcep_seed_cooperatives.tbl_commitment_regional SET accreditation_no = "{newAccred}" WHERE coopID = "{coopId}"'
    cursor.execute(updateCommitment_query)
    
    updateTblClaim_query = f'UPDATE {season}rcep_paymaya.tbl_claim SET coopAccreditation = "{newAccred}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateTblClaim_query)
    
    updateTblBeneficiaries_query = f'UPDATE {season}rcep_paymaya.tbl_beneficiaries SET coop_accreditation = "{newAccred}" WHERE coop_accreditation = "{accred}"'
    cursor.execute(updateTblBeneficiaries_query)

    updateTblCoopPaymentDetails_query = f'UPDATE {season}rcep_paymaya.tbl_coop_payment_details SET coop_ref = "{newAccred}" WHERE coop_ref = "{accred}"'
    cursor.execute(updateTblCoopPaymentDetails_query)
    
    updateUserCoop_query = f'UPDATE {season}sdms_db_dev.users_coop SET coopAccreditation = "{newAccred}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateUserCoop_query)
    
    updateReqUserCoop_query = f'UPDATE {season}sdms_db_dev.request_users_coop SET coopAccreditation = "{newAccred}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateReqUserCoop_query)

    updateDelivery_query = f'UPDATE {season}rcep_delivery_inspection.tbl_delivery SET coopAccreditation = "{newAccred}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateDelivery_query)

    updateRla_query = f'UPDATE {season}rcep_delivery_inspection.tbl_rla_details SET coopAccreditation = "{newAccred}" WHERE coopAccreditation = "{accred}"'
    cursor.execute(updateRla_query)

    updateRlaRequest_query = f'UPDATE {season}rcep_delivery_inspection.rla_requests SET coop_accreditation = "{newAccred}" WHERE coop_accreditation = "{accred}"'
    cursor.execute(updateRlaRequest_query)
    
    updateDeliveryTransaction_query = f'UPDATE {season}rcep_delivery_inspection.tbl_delivery_transaction SET accreditation_no = "{newAccred}" WHERE accreditation_no = "{accred}"'
    cursor.execute(updateDeliveryTransaction_query)
    
    updateSeedGrower_query = f'UPDATE {season}rcep_delivery_inspection.tbl_seed_grower SET coop_accred = "{newAccred}" WHERE coop_accred = "{accred}"'
    cursor.execute(updateSeedGrower_query)

    updateCooperative_query = f'UPDATE {season}rcep_seed_cooperatives.tbl_cooperatives SET accreditation_no = "{newAccred}" WHERE coopID = "{coopId}"'
    cursor.execute(updateCooperative_query)

    connection.commit()

          
cursor.close()
connection.close()

print(coopId)
 