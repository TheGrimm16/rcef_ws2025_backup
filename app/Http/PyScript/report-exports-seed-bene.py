import sqlalchemy as sa
from urllib.parse import quote
import pandas as pd
import polars as pl
import tkinter as tk
from tkinter import filedialog
import argparse
import os
import datetime

def main(ssn, prv, mun, cat, province):
    # root = tk.Tk()
    # root.title("Data Report Generator")
    report_headers = ['db_ref', 'rcef_id_x', 'rsbsa_control_no', 'firstName', 'midName', 'lastName', 'extName', 'sex_y', 'birthdate_y', 'tel_no', 'province_x', 'municipality_x', 'mother_name', 'final_area_y', 'claimed_area', 'bags_claimed', 'seed_variety', 'remarks', 'crop_establishment_cs_x', 'seedling_age', 'ecosystem_cs_x', 'planting_week_x', 'kp_kit_count', 'other_benefits_received', 'date_released', 'released_by', 'server_date_received', 'category', 'app_version', 'yield_last_season_details']
    now = datetime.datetime.now()
    date_time_str = now.strftime("%Y%m%d_%H%M%S")

    def extract_yield(df):
        polar_df = pl.from_pandas(merged_df)
        iters = len(polar_df.columns)
        polar_df = polar_df.with_columns(pl.when(pl.col('yield_last_season_details').str.len_chars() < 8).then(None).otherwise(pl.col('yield_last_season_details')).name.keep())
        polar_df = polar_df.with_columns(pl.col("yield_last_season_details").str.replace_all(r"\\", "").alias("yield_last_season_details"))
        polar_df = polar_df.with_columns(pl.col("yield_last_season_details").fill_null('[{"variety": "","area": "0","bags": "0","weight": "0","type": "","class": "","yield": "0","low_yield_cause": "","season": "","year": "0","other_variety_name": ""}]'))
        polar_df = polar_df.with_columns(pl.col("yield_last_season_details").str.json_decode().alias("decoded"))
        if len(polar_df.select("decoded")) > 0:
            polar_df = polar_df.with_columns(pl.col("decoded").list.to_struct("max_width")).unnest("decoded")
            iters = len(polar_df.columns) - iters
            for i in range(iters):
                polar_df = polar_df.with_columns(pl.col(f"field_{i}").struct.rename_fields([f"variety_{i}", f"area_{i}", f"bags_{i}", f"weight_{i}", f"type_{i}", f"class_{i}", f"yield_{i}", f"low_yield_cause_{i}", f"season_{i}", f"year_{i}", f"other_variety_name_{i}"]).alias(f"field_{i}")).unnest(f"field_{i}")
        polar_df = polar_df.rename({"db_ref": "DBREF", "rcef_id_x": "RCEF ID", "rsbsa_control_no": "RSBSA Control No", "firstName": "First Name", "midName": "Middle Name", "lastName": "Last Name", "extName": "Extension Name", "sex_y": "Sex", "birthdate_y": "Birthdate", "tel_no": "Contact Number", "province_x": "Province", "municipality_x": "Municipality", "mother_name": "Mother Name", "final_area_y": "Final Area", "claimed_area": "Claimed Area", "bags_claimed": "Bags Claimed", "seed_variety": "Seed Variety", "remarks": "Remarks", "crop_establishment_cs_x": "Crop Establishment", "seedling_age": "Seedling Age", "ecosystem_cs_x": "Ecosystem", "planting_week_x": "Planting Week", "kp_kit_count": "KP Kit Count", "other_benefits_received": "Other Benefits Received", "date_released": "Date Released", "released_by": "Released By", "server_date_received": "Sync Date", "category": "Category", "app_version": "App Version"})
        polar_df = polar_df.drop(["yield_last_season_details"])
        merged_dfs = polar_df.to_pandas()
        return merged_dfs

    try:
        # Set up connection to mysql mariaDB database
        engine = sa.create_engine('mysql+mysqlconnector://json:%s@192.168.10.44:3306/information_schema' % quote('Zeijan@13'))
        base_query = f"SELECT * FROM {ssn}prv_{prv}.new_released "
        if mun and cat:
            base_query += f"WHERE municipality LIKE '{mun}' AND category LIKE '{cat}'"
            #print(base_query)
        elif mun:
            base_query += f"WHERE municipality LIKE '{mun}'"
        elif cat:
            base_query += f"WHERE category LIKE '{cat}'"
        
        released = pd.read_sql_query(base_query, engine)
        #print(f"Number of rows in new_released: {len(released)}")
        profiles = pd.read_sql_query(f"SELECT * FROM {ssn}prv_{prv}.farmer_information_final", engine)
        #print(f"Number of rows in farmer_information_final: {len(released)}")

        # Merge the two tables based on the 'farmer_id' column
        merged_df = pd.merge(released, profiles, on='db_ref', how='left')
        merged_df = merged_df[report_headers]
        #print(merged_df.head(0))
        merged_df = extract_yield(merged_df)
        
        filepath = f"report/home/{province}_{mun}_{date_time_str}.csv"#"report/home/sample.csv"
        if filepath:
            if filepath.endswith(".csv"):
                merged_df.to_csv(filepath, index=False)
            elif filepath.endswith(".xlsx"):
                merged_df.to_excel(filepath, index=False)
            else:
                print("Invalid file format. Please select a CSV or Excel file.")
            #print(f"Data exported to {filepath}")
            print(f"{filepath}")
        else:
            print("Export cancelled.")
        
    except sa.exc.SQLAlchemyError as e:
        print("Failed to connect to the database: ", e)
        exit(1)
    finally:
        engine.dispose()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Generate data report.')
    parser.add_argument('ssn', type=str, help='Season')
    parser.add_argument('prv', type=str, help='Province')
    parser.add_argument('mun', type=str, nargs='?', default='', help='Municipality')
    parser.add_argument('cat', type=str, nargs='?', default='INBRED', help='Category')
    parser.add_argument('province', type=str, help='Prov')

    args = parser.parse_args()
    main(args.ssn, args.prv, args.mun, args.cat, args.province)





# import os

# def get_current_user():
#     try:
#         # Get the username of the current user
#         username = os.getlogin()
#         return username
#     except Exception as e:
#         print(f"Error getting current user: {e}")
#         return None

# if __name__ == "__main__":
#     user = get_current_user()
#     if user:
#         print(f"Current Active User: {user}")
#     else:
#         print("Failed to retrieve the current user.")



#working code
# import sqlalchemy as sa
# from urllib.parse import quote
# import pandas as pd
# import tkinter as tk
# from tkinter import filedialog
# import argparse
# import os

# def main(ssn, prv, mun, cat):
#     # root = tk.Tk()
#     # root.title("Data Report Generator")
#     report_headers = ['db_ref', 'rcef_id_x', 'rsbsa_control_no', 'firstName', 'midName', 'lastName', 'extName', 'sex_y', 'birthdate_y', 'tel_no', 'province_x', 'municipality_x', ]

#     try:
#         # Set up connection to mysql mariaDB database
#         engine = sa.create_engine('mysql+mysqlconnector://json:%s@192.168.10.44:3306/information_schema' % quote('Zeijan@13'))
#         base_query = f"SELECT * FROM {ssn}prv_{prv}.new_released "
#         if mun and cat:
#             base_query += f"WHERE municipality LIKE '{mun}' AND category LIKE '{cat}'"
#         elif mun:
#             base_query += f"WHERE municipality LIKE '{mun}'"
#         elif cat:
#             base_query += f"WHERE category LIKE '{cat}'"
        
#         released = pd.read_sql_query(base_query, engine)
#         #print(f"Number of rows in new_released: {len(released)}")
#         profiles = pd.read_sql_query(f"SELECT * FROM {ssn}prv_{prv}.farmer_information_final", engine)
#         #print(f"Number of rows in farmer_information_final: {len(released)}")

#         # Merge the two tables based on the 'farmer_id' column
#         merged_df = pd.merge(released, profiles, on='db_ref', how='left')
#         #print(merged_df.head(0))

#         filepath = "report/home/sample.csv"
#         if filepath:
#             if filepath.endswith(".csv"):
#                 merged_df.to_csv(filepath, index=False)
#             elif filepath.endswith(".xlsx"):
#                 merged_df.to_excel(filepath, index=False)
#             else:
#                 print("Invalid file format. Please select a CSV or Excel file.")
#             #print(f"Data exported to {filepath}")
#             print(f"{filepath}")
#         else:
#             print("Export cancelled.")
        
#     except sa.exc.SQLAlchemyError as e:
#         print("Failed to connect to the database: ", e)
#         exit(1)
#     finally:
#         engine.dispose()

# if __name__ == "__main__":
#     parser = argparse.ArgumentParser(description='Generate data report.')
#     parser.add_argument('ssn', type=str, help='Season')
#     parser.add_argument('prv', type=str, help='Province')
#     parser.add_argument('mun', type=str, nargs='?', default='', help='Municipality')
#     parser.add_argument('cat', type=str, nargs='?', default='INBRED', help='Category')

#     args = parser.parse_args()
#     main(args.ssn, args.prv, args.mun, args.cat)





