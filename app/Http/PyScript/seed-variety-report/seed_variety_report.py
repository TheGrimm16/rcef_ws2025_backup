import os
import sys
import polars as pl
from urllib.parse import quote
import time
import json

uri = "mysql://root:%s@192.168.10.44:3306/" % quote('Zeijan@13')
ssn = sys.argv[1] if len(sys.argv) > 1 else None
# start_time = time.time()

# Query to get distinct prefixes
lib_dropoff_point_df = pl.read_database_uri(query=f"select distinct left(prv_dropoff_id,4) as prv_dropoff_id from {ssn}rcep_delivery_inspection.lib_dropoff_point;", uri=uri)
regions_data_df = pl.read_database_uri(query=f"select distinct region FROM {ssn}rcep_delivery_inspection.lib_dropoff_point;", uri=uri)

# Extract distinct prefixes
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

# Loop through each prefix and fetch the corresponding table
for prefix in available_tables:
    table_name = f"{ssn}prv_{prefix}.new_released"
    query = f"SELECT sum(bags_claimed) AS total_seed_data, seed_variety, province, municipality, claimed_area, content_rsbsa, category FROM {table_name} WHERE category='INBRED' group by seed_variety, municipality;"
    
    try:
        df = pl.read_database_uri(query=query, uri=uri)
        dataframes[table_name] = df
    except Exception as e:
        print(f"Failed to fetch data from {table_name}: {e}")

# Optionally, combine all fetched DataFrames into a single DataFrame
if dataframes:
    combined_df = pl.concat(list(dataframes.values()))
else:
    print("No matching tables found.")

# Process the combined data
overall_seed_data = combined_df.group_by(["seed_variety"]).agg([
    pl.col("total_seed_data").sum().fill_nan(0).alias("total_seed_bags")
])
total_seed_variety = combined_df.select(
    pl.col("seed_variety").n_unique().alias("total_seed_variety")
)
total_seed_data = combined_df.select(
    pl.col("total_seed_data").sum().alias("total_seed_data")
)

# Convert the results to dictionaries
overall_seed_data = overall_seed_data.to_dict(as_series=False)
total_seed_variety = total_seed_variety.to_dict(as_series=False)
total_seed_data = total_seed_data.to_dict(as_series=False)
regions_data = regions_data_df.to_dict(as_series=False)
# Combine the results into a single JSON object
combined_results = {
    "regions": regions_data,
    "overall_seed_data": overall_seed_data,
    "total_seed_variety": total_seed_variety,
    "total_seed_data": total_seed_data,
}

# Convert the combined results to JSON format
combined_results_json = json.dumps(combined_results, indent=4)

# Print the JSON result
print(combined_results_json)

# end_time = time.time()
# elapsed_time = end_time - start_time

# print(f"Benchmark Time: {elapsed_time:.2f} seconds")
