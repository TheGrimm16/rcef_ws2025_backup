import polars as pl
import sys
import json
from urllib.parse import quote

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

season = sys.argv[1] if len(sys.argv) > 1 else "ds2025_"
station = sys.argv[2] if len(sys.argv) > 2 else "%"

if __name__ == "__main__":
    sed_verified = pl.read_database_uri(f"SELECT *, LEFT(rcef_id, 2) as regCode FROM {season}rcep_paymaya.sed_verified", uri)
    tbl_claim = pl.read_database_uri(f"SELECT * FROM {season}rcep_paymaya.tbl_claim", uri)

    if station != "%" and station != "11005":
        stations = pl.read_database_uri(f"SELECT DISTINCT(region) as region FROM {season}sdms_db_dev.lib_station WHERE stationID = '{station}'", uri)
        region_list = list(stations.select(pl.col("region")).to_series())
        region_codes = pl.read_database_uri(f"SELECT regionName, regCode FROM {season}rcep_delivery_inspection.lib_prv WHERE regionName IN ({','.join(f"'{region}'" for region in region_list)}) GROUP BY regCode", uri)
        sed_verified = sed_verified.filter(pl.col("regCode").is_in(region_codes.select(pl.col("regCode")).to_series()))
        tbl_claim = tbl_claim.filter(pl.col("region").is_in(region_codes.select(pl.col("regionName"))))
    

    count_fca = sed_verified.group_by(["province_name"]).agg(pl.col("municipality_name").n_unique().alias("count_fca")).select("count_fca").sum().item()
    count_fca_reg = sed_verified.select(pl.col("regCode")).n_unique()
    count_fca_prv = sed_verified.select(pl.col("province_name")).n_unique()
    count_fca_mun = count_fca
    
    total_fca_members = sed_verified.select('sed_id').count().item()
    total_male_fca = sed_verified.filter(pl.col('ver_sex').str.starts_with('M')).select('sed_id').count().item()
    total_female_fca = sed_verified.filter(pl.col('ver_sex').str.starts_with('F')).select('sed_id').count().item()

    perc_male = total_male_fca / total_fca_members * 100
    perc_female = total_female_fca / total_fca_members * 100
    
    total_distributed_bags = tbl_claim.select('claimId').count().item()

    obj = {
        "total_fca_org": count_fca,
        "count_fca_region": count_fca_reg,
        "count_fca_province": count_fca_prv,
        "count_fca_municipality": count_fca_mun,
        "total_fca_members": total_fca_members,
        "perc_male": perc_male,
        "perc_female": perc_female,
        "total_distributed_bags": total_distributed_bags
    }
    json_string = json.dumps(obj)
    print(json_string)