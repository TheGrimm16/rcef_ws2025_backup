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
    release = pl.read_database_uri(f"SELECT '{prv['province']}' as province, LEFT(prv_dropoff_id, 4) as prv_code, content_rsbsa, sex, claimed_area FROM {prv['prv_schema']}.new_released WHERE category = 'INBRED'", uri)
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

    total_farmers = mother_df.count().select(pl.col('content_rsbsa')).item()
    total_female_farmers = mother_df.filter(pl.col("sex").str.starts_with("F")).count().select(pl.col('content_rsbsa')).item()
    total_male_farmers = mother_df.filter(pl.col("sex").str.starts_with("M")).count().select(pl.col('content_rsbsa')).item()
    percent_male = (total_male_farmers / total_farmers) * 100
    percent_female = (total_female_farmers / total_farmers) * 100
    claimed_male = mother_df.filter(pl.col("sex").str.starts_with("M")).select(pl.col("claimed_area")).sum().item()
    claimed_female = mother_df.filter(pl.col("sex").str.starts_with("F")).select(pl.col("claimed_area")).sum().item()

    gad_data = {
        "regions": lib_regions.select(pl.col('regionName')).to_dicts(),
        "total_male": total_male_farmers,
        "total_female": total_female_farmers,
        "percent_male": (percent_male),
        "percent_female": percent_female,
        "claimed_male": claimed_male,
        "claimed_female": claimed_female
    }
    json_string = json.dumps(gad_data)
    print(json_string)