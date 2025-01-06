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
    release = pl.read_database_uri(f"SELECT seed_variety, sex, FLOOR(DATEDIFF(STR_TO_DATE('09/15/2024', '%m/%d/%Y'), STR_TO_DATE(birthdate, '%m/%d/%Y')) / 365.25) AS age, claimed_area FROM {prv['prv_schema']}.new_released WHERE category = 'INBRED'", uri)
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

    seed_varieties = mother_df.select(pl.col('seed_variety')).unique()
    if type == 'variety_sex':
        seeds_breakdown = []
        for seed in seed_varieties.iter_rows(named=True):
            seed_variety = seed['seed_variety']
            male_var = mother_df.filter((pl.col("sex").str.starts_with("M")) & (pl.col("seed_variety") == seed_variety)).count().select(pl.col('sex')).item()
            female_var = mother_df.filter((pl.col("sex").str.starts_with("F")) & (pl.col("seed_variety") == seed_variety)).count().select(pl.col('sex')).item()
            seeds_breakdown.append({"seed_variety": seed_variety, "total_male": male_var, "total_female": female_var})
        seeds_df = pl.DataFrame(seeds_breakdown).sort(by=[pl.col('total_male') + pl.col('total_female')], descending=True)
        json_string = json.dumps(seeds_df.to_dicts())
        print(json_string)
    else:
        seeds_breakdown = []
        for seed in seed_varieties.iter_rows(named=True):
            seed_variety = seed['seed_variety']
            both_sex_low = mother_df.filter((pl.col("age") >= 18) & (pl.col("age") <= 29) & (pl.col("seed_variety") == seed_variety)).count().select(pl.col('sex')).item()
            both_sex_mid = mother_df.filter((pl.col("age") >= 30) & (pl.col("age") <= 59) & (pl.col("seed_variety") == seed_variety)).count().select(pl.col('sex')).item()
            both_sex_high = mother_df.filter((pl.col("age") >= 60) & (pl.col("seed_variety") == seed_variety)).count().select(pl.col('sex')).item()
            seeds_breakdown.append({"seed_variety": seed_variety, "cat1": both_sex_low, "cat2": both_sex_mid, "cat3": both_sex_high})
        seeds_df = pl.DataFrame(seeds_breakdown).sort(by=[pl.col('cat1') + pl.col('cat2') + pl.col('cat3')], descending=True)
        json_string = json.dumps(seeds_df.to_dicts())
        print(json_string)