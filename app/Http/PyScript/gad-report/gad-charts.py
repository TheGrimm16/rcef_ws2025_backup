import polars as pl
import concurrent.futures
import sys
from urllib.parse import quote
from sqlalchemy import create_engine
import threading, time
import json

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

lib_prv = pl.DataFrame()
lib_regions = pl.DataFrame()

releases = pl.DataFrame()
season = sys.argv[1] if len(sys.argv) > 1 else None
type = sys.argv[2] if len(sys.argv) > 2 else None

def get_lib_prv():
    global lib_prv
    global lib_regions
    lib_prv = pl.read_database_uri(f"SELECT regionName, province, prv_code, CONCAT('{season}prv_', prv_code) as prv_schema FROM {season}rcep_delivery_inspection.lib_prv WHERE prv_code NOT LIKE '9999'", uri)
    lib_regions = lib_prv.group_by('regionName').agg(pl.col('regionName').first().alias('regionNames')).select(pl.col('regionNames').alias('regionName'))
    info_schema = pl.read_database_uri(f"SELECT TABLE_SCHEMA as prv_schema FROM information_schema.TABLES WHERE TABLE_NAME LIKE 'new_released' AND LENGTH(TABLE_SCHEMA) = 15 AND TABLE_ROWS > 0 AND TABLE_SCHEMA NOT LIKE '%temp' AND TABLE_SCHEMA NOT LIKE '%9999' AND TABLE_SCHEMA LIKE '{season}prv_%'", uri)
    lib_prv = lib_prv.join(info_schema, on=["prv_schema"], how="inner").select(pl.col("province"), pl.col('prv_schema')).unique()

def process_prv(prv):
    release = pl.read_database_uri(f"SELECT sex, FLOOR(DATEDIFF(STR_TO_DATE('09/15/2024', '%m/%d/%Y'), STR_TO_DATE(birthdate, '%m/%d/%Y')) / 365.25) AS age, claimed_area FROM {prv['prv_schema']}.new_released WHERE category = 'INBRED'", uri)
    return release

def process_prv_concurrently(prvs):
    with concurrent.futures.ThreadPoolExecutor(max_workers=32) as executor:
        futures = [executor.submit(process_prv, prv) for prv in prvs.iter_rows(named=True)]

        result = []
        for future in concurrent.futures.as_completed(futures):
            try:
                result.append(future.result())
            except Exception as exc:
                print(f"Error processing {future.result()}: {exc}")
        
        return result

if __name__ == "__main__":
    threading.Thread(target=get_lib_prv).start()
    
    while threading.active_count() > 1:
        time.sleep(0.005)
        pass
    
    
    processed = process_prv_concurrently(lib_prv)
    mother_df = pl.concat(processed)

    total_individuals = mother_df.count().select(pl.col('sex')).item()
    total_male_low = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 18) & (pl.col("age") <= 29)).count().select(pl.col('sex')).item()
    total_female_low = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 18) & (pl.col("age") <= 29)).count().select(pl.col('sex')).item()
    total_male_mid = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 30) & (pl.col("age") <= 59)).count().select(pl.col('sex')).item()
    total_female_mid = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 30) & (pl.col("age") <= 59)).count().select(pl.col('sex')).item()
    total_male_high = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 60)).count().select(pl.col('sex')).item()
    total_female_high = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 60)).count().select(pl.col('sex')).item() 
    
    if type == 'stacked':
        obj = {
            "male_1_percent": (total_male_low / total_individuals) * 100,
            "male_2_percent": (total_male_mid / total_individuals) * 100,
            "male_3_percent": (total_male_high / total_individuals) * 100,
            "female_1_percent": (total_female_low / total_individuals) * 100,
            "female_2_percent": (total_female_mid / total_individuals) * 100,
            "female_3_percent": (total_female_high / total_individuals) * 100
        }

        json_string = json.dumps(obj)
        print(json_string)
    else: 
        both_sexes_low = total_male_low + total_female_low
        both_sexes_mid = total_male_mid + total_female_mid
        both_sexes_high = total_male_high + total_female_high

        area_male_low = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 18) & (pl.col("age") <= 29)).select(pl.col('claimed_area')).sum().item()
        area_male_mid = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 30) & (pl.col("age") <= 59)).select(pl.col('claimed_area')).sum().item()
        area_male_high = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("age") >= 60)).select(pl.col('claimed_area')).sum().item()
        
        area_female_low = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 18) & (pl.col("age") <= 29)).select(pl.col('claimed_area')).sum().item()
        area_female_mid = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 30) & (pl.col("age") <= 59)).select(pl.col('claimed_area')).sum().item()
        area_female_high = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("age") >= 60)).select(pl.col('claimed_area')).sum().item()

        both_sexes_low_area = area_male_low + area_female_low
        both_sexes_mid_area = area_male_mid + area_female_mid
        both_sexes_high_area = area_male_high + area_female_high

        obj = {
            "total_farmer": total_individuals,
            "farmer_male_1": total_male_low,
            "farmer_male_2": total_male_mid,
            "farmer_male_3": total_male_high,
            "farmer_female_1": total_female_low,
            "farmer_female_2": total_female_mid,
            "farmer_female_3": total_female_high,
            "farmer_1": both_sexes_low,
            "farmer_2": both_sexes_mid,
            "farmer_3": both_sexes_high,
            "claimed_male_1": area_male_low,
            "claimed_male_2": area_male_mid,
            "claimed_male_3": area_male_high,
            "claimed_female_1": area_female_low,
            "claimed_female_2": area_female_mid,
            "claimed_female_3": area_female_high,
            "claimed_1": both_sexes_low_area,
            "claimed_2": both_sexes_mid_area,
            "claimed_3": both_sexes_high_area
        }
        
        json_string = json.dumps(obj)
        print(json_string)