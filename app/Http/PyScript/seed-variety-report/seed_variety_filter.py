import os
import sys
import polars as pl
from urllib.parse import quote
import time
import json

uri = "mysql://root:%s@192.168.10.44:3306/" % quote('Zeijan@13')
ssn = sys.argv[1] if len(sys.argv) > 1 else None
prv = sys.argv[2] if len(sys.argv) > 1 else None
prov = sys.argv[3] if len(sys.argv) > 1 else None
muni = sys.argv[4] if len(sys.argv) > 1 else None
# start_time = time.time()
lib_dropoff_point_df = pl.read_database_uri(query=f"select distinct left(prv_dropoff_id,4) as prv_dropoff_id from {ssn}rcep_delivery_inspection.lib_dropoff_point where prv_dropoff_id like '{prv}%'  ;", uri=uri)

available_tables = (
    lib_dropoff_point_df
    .with_columns([
        pl.col("prv_dropoff_id").alias("prefix")
    ])
    .select("prefix")
    .unique()
    .to_series()
    .to_list()
)

dataframes = {}

for prefix in available_tables:
    table_name = f"{ssn}prv_{prefix}.new_released"
    if len(prv) == 4 and prov!='0' and muni == '0':
        query = f"SELECT sum(bags_claimed) AS total_seed_data, seed_variety, province, municipality, claimed_area, content_rsbsa, category FROM {table_name} WHERE category='INBRED' and province='{prov}' group by seed_variety, municipality;"
    if len(prv) == 4 and prov != '0' and muni != '0':
        query = f"SELECT sum(bags_claimed) AS total_seed_data, seed_variety, province, municipality, claimed_area, content_rsbsa, category FROM {table_name} WHERE category='INBRED' and province='{prov}' and municipality='{muni}' group by seed_variety, municipality;"
    if len(prv) == 2:
        query = f"SELECT sum(bags_claimed) AS total_seed_data, seed_variety, province, municipality, claimed_area, content_rsbsa, category FROM {table_name} WHERE category='INBRED' group by seed_variety, municipality;"
    try:
        df = pl.read_database_uri(query=query, uri=uri)
        dataframes[table_name] = df
    except Exception as e:
        print(f"Failed to fetch data from {table_name}: {e}")

if dataframes:
    combined_df = pl.concat(list(dataframes.values()))
else:
    print("No matching tables found.")

overall_seed_data = combined_df.group_by(["seed_variety"]).agg([
    pl.col("total_seed_data").sum().fill_nan(0).alias("total_seed_bags")
])

overall_seed_data = overall_seed_data.to_dict(as_series=False)

combined_results = {
    "overall_seed_data": overall_seed_data
}

combined_results_json = json.dumps(combined_results, indent=4)

print(combined_results_json)
# end_time = time.time()
# elapsed_time = end_time - start_time

# print(f"Benchmark Time: {elapsed_time:.2f} seconds")
