import polars as pl
import concurrent.futures
import sys
import json
from urllib.parse import quote

season = sys.argv[1] if len(sys.argv) > 1 else "ws2025_"

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

def process_table(table_path):
    # Read the table
    # print(f"Processing table: {table_path}")
    farmer_profile = pl.read_database_uri(f"SELECT rcef_id, birthdate as bday, TIMESTAMPDIFF(YEAR, STR_TO_DATE(farmer_information_final.birthdate, '%m/%d/%Y'), CURDATE()) AS age FROM {season}prv_{table_path}.farmer_information_final", uri)
    # Perform your processing here
    # For example, you can filter or transform the table
    returnee = sed_verified.join(farmer_profile, on=["rcef_id"], how="inner")
    # Return the processed table
    return returnee

def process_tables_concurrently(table_paths):
    # Create a ThreadPoolExecutor
    with concurrent.futures.ThreadPoolExecutor(max_workers=32) as executor:
        # Submit tasks for each table
        futures = [executor.submit(process_table, table_path) for table_path in table_paths]

        # Collect the results
        results = []
        for future in concurrent.futures.as_completed(futures):
            try:
                result = future.result()
                results.append(result)
            except Exception as exc:
                print(f'Processing table generated an exception: {exc}')

    return results

if __name__ == "__main__":
    sed_verified = pl.read_database_uri(f"SELECT rcef_id, UCASE(SUBSTRING(sed_verified.ver_sex, 1, 1)) as sex, yield FROM {season}rcep_paymaya.sed_verified", uri)
    to_process = sed_verified.select(pl.col("rcef_id").str.slice(0, 4).unique()).to_series()

    processed = process_tables_concurrently(to_process)

    mother_df = pl.DataFrame()
    for table in processed:
        mother_df = mother_df.vstack(table)
    
    allYield = {
        "age_min": mother_df.filter(pl.col("age").le(30)).select(pl.col("yield")).mean().item(),
        "age_mid": mother_df.filter(pl.col("age").gt(30) & pl.col("age").le(60)).select(pl.col("yield")).mean().item(),
        "age_max": mother_df.filter(pl.col("age").gt(60)).select(pl.col("yield")).mean().item()
    }

    maleYield = {
        "age_min": mother_df.filter(pl.col("age").le(30) & pl.col("sex").eq("M")).select(pl.col("yield")).mean().item(),
        "age_mid": mother_df.filter(pl.col("age").gt(30) & pl.col("age").le(60) & pl.col("sex").eq("M")).select(pl.col("yield")).mean().item(),
        "age_max": mother_df.filter(pl.col("age").gt(60) & pl.col("sex").eq("M")).select(pl.col("yield")).mean().item()
    }

    femaleYield = {
        "age_min": mother_df.filter(pl.col("age").le(30) & pl.col("sex").eq("F")).select(pl.col("yield")).mean().item(),
        "age_mid": mother_df.filter(pl.col("age").gt(30) & pl.col("age").le(60) & pl.col("sex").eq("F")).select(pl.col("yield")).mean().item(),
        "age_max": mother_df.filter(pl.col("age").gt(60) & pl.col("sex").eq("F")).select(pl.col("yield")).mean().item()
    }

    yields = {
        "allYield": allYield, 
        "maleYield": maleYield,
        "femaleYield": femaleYield
    }

    json_string = json.dumps(yields)
    print(json_string)