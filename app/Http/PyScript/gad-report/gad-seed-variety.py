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

def get_lib_prv():
    global lib_prv
    global lib_regions
    lib_prv = pl.read_database_uri(f"SELECT regionName, province, prv_code, CONCAT('{season}prv_', prv_code) as prv_schema FROM {season}rcep_delivery_inspection.lib_prv WHERE prv_code NOT LIKE '9999'", uri)
    lib_regions = lib_prv.group_by('regionName').agg(pl.col('regionName').first().alias('regionNames')).select(pl.col('regionNames').alias('regionName'))
    info_schema = pl.read_database_uri(f"SELECT TABLE_SCHEMA as prv_schema FROM information_schema.TABLES WHERE TABLE_NAME LIKE 'new_released' AND LENGTH(TABLE_SCHEMA) = 15 AND TABLE_ROWS > 0 AND TABLE_SCHEMA NOT LIKE '%temp' AND TABLE_SCHEMA NOT LIKE '%9999' AND TABLE_SCHEMA LIKE '{season}prv_%'", uri)
    lib_prv = lib_prv.join(info_schema, on=["prv_schema"], how="inner").select(pl.col("province"), pl.col('prv_schema')).unique()

def process_prv(prv):
    release = pl.read_database_uri(f"SELECT seed_variety, sex, FLOOR(DATEDIFF(STR_TO_DATE('09/15/2024', '%m/%d/%Y'), STR_TO_DATE(birthdate, '%m/%d/%Y')) / 365.25) AS age, claimed_area, bags_claimed FROM {prv['prv_schema']}.new_released WHERE category = 'INBRED'", uri)
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

    total_bags = mother_df.cast({pl.Float64: pl.Int64}).select(pl.col('bags_claimed')).sum().item()
    total_male_bags = mother_df.filter(pl.col("sex").str.starts_with("M")).cast({pl.Float64: pl.Int64}).select(pl.col("bags_claimed")).sum().item()
    total_female_bags = mother_df.filter(pl.col("sex").str.starts_with("F")).cast({pl.Float64: pl.Int64}).select(pl.col("bags_claimed")).sum().item()
    total_bags_18_29 = mother_df.filter((pl.col("age") >= 18) & (pl.col("age") <= 29)).cast({pl.Float64: pl.Int64}).select(pl.col("bags_claimed")).sum().item()
    total_bags_30_59 = mother_df.filter((pl.col("age") >= 30) & (pl.col("age") <= 59)).cast({pl.Float64: pl.Int64}).select(pl.col("bags_claimed")).sum().item()
    total_bags_60_up = mother_df.filter((pl.col("age") >= 60)).cast({pl.Float64: pl.Int64}).select(pl.col("bags_claimed")).sum().item()

    seed_varieties = mother_df.group_by('seed_variety').agg(pl.col('seed_variety').first().alias('seed_varieties'), pl.col('bags_claimed').sum().alias('total_bags')).cast({pl.Float64: pl.Int64}).select(pl.col("seed_varieties").alias('seed_variety'), pl.col("total_bags")).sort(by=['total_bags'], descending=True)
    
    gad_per_variety = []
    for variety in seed_varieties.iter_rows(named=True):
        seed_var_variety = variety['seed_variety']
        total_bags_variety = variety['total_bags']
        male_bags = mother_df.filter(pl.col("seed_variety").eq(seed_var_variety) & pl.col("sex").str.starts_with("M")).select(pl.col("bags_claimed")).sum().item()
        female_bags = mother_df.filter(pl.col("seed_variety").eq(seed_var_variety) & pl.col("sex").str.starts_with("F")).select(pl.col("bags_claimed")).sum().item()
        age_18_29_bags = mother_df.filter((pl.col("seed_variety").eq(seed_var_variety) & (pl.col("age") >= 18) & (pl.col("age") <= 29))).select("bags_claimed").sum().item()
        age_30_59_bags = mother_df.filter((pl.col("seed_variety").eq(seed_var_variety) & (pl.col("age") >= 30) & (pl.col("age") <= 59))).select("bags_claimed").sum().item()
        age_60_up_bags = mother_df.filter((pl.col("seed_variety").eq(seed_var_variety) & (pl.col("age") >= 60))).select("bags_claimed").sum().item()

        gad_per_variety.append({
            "seed_variety": seed_var_variety,
            "total_bags": total_bags_variety,
            "cent_bag": (total_bags_variety / total_bags) * 100,
            "male_bag": male_bags,
            "male_cent": (male_bags / total_male_bags) * 100,
            "female_bag": female_bags,
            "female_cent": (female_bags / total_female_bags) * 100,
            "blank": "",
            "cat1_bag": age_18_29_bags,
            "cat1_cent": (age_18_29_bags / total_bags_18_29) * 100,
            "cat2_bag": age_30_59_bags,
            "cat2_cent": (age_30_59_bags / total_bags_30_59)  * 100,
            "cat3_bag": age_60_up_bags,
            "cat3_cent": (age_60_up_bags / total_bags_60_up) * 100
        })
    
    json_string = json.dumps(gad_per_variety)
    print(json_string)