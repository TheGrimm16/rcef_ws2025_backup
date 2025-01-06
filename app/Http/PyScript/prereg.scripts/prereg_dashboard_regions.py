import polars as pl
import sys
import json
from urllib.parse import quote

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

season = sys.argv[1] if len(sys.argv) > 1 else "ds2025_"
station = sys.argv[2] if len(sys.argv) > 2 else "%"


if __name__ == "__main__":
    regions = pl.read_database_uri(f"SELECT regionName, regCode FROM {season}rcep_delivery_inspection.lib_prv WHERE regCode <> '99' GROUP BY regCode", uri)

    if station != "%" and station != "11005":
        stations = pl.read_database_uri(f"SELECT region FROM {season}sdms_db_dev.lib_station WHERE stationID = '{station}' GROUP BY region", uri)
        regions = regions.filter(pl.col("regionName").is_in(stations.select(pl.col("region"))))
    
    sed_verified = pl.read_database_uri(f"SELECT LEFT(rcef_id, 2) as code FROM {season}rcep_paymaya.sed_verified", uri)

    region_arr = []
    values_arr = []
    for region in regions.iter_rows(named=True):
        r_code = region["regCode"]
        r_name = region["regionName"]

        count = sed_verified.filter(pl.col("code").eq(r_code)).count().item()

        region_arr.append(r_name)
        values_arr.append(count)

    json_string = json.dumps({"regions": region_arr, "values": values_arr})
    print(json_string)
