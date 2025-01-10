import polars as pl
from urllib.parse import quote
import sys
import json
import time

season = sys.argv[1] if len(sys.argv) > 1 else "ws2025_"

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

if __name__ == "__main__":
    tbl_beneficiaries = pl.read_database_uri(f"SELECT * FROM {season}rcep_paymaya.tbl_beneficiaries", uri)
    tbl_claim = pl.read_database_uri(f"SELECT * FROM {season}rcep_paymaya.tbl_claim", uri)
    tbl_actual_delivery = pl.read_database_uri(f"SELECT * FROM {season}rcep_delivery_inspection.tbl_actual_delivery WHERE qrValStart != ''", uri)
    
    paymaya_beneficiaries = len(tbl_beneficiaries)
    paymaya_bags = len(tbl_claim)
    # paymaya_beneficiaries_male = len(glbl['tbl_beneficiaries'].filter(pl.col("sex").str.starts_with('M'), pl.col("paymaya_code").is_in(glbl['tbl_claim'].select(pl.col("paymaya_code")))))
    # paymaya_beneficiaries_female = len(glbl['tbl_beneficiaries'].filter(pl.col("sex").str.starts_with('F'), pl.col("paymaya_code").is_in(glbl['tbl_claim'].select(pl.col("paymaya_code")))))
    paymaya_delivery = tbl_actual_delivery.filter(pl.col("qrValStart").ne("")).select(pl.col("totalBagCount")).sum().item()

    time.sleep(3)
    json_string = json.dumps({
        "paymaya_beneficiaries": paymaya_beneficiaries,
        "paymaya_bags": paymaya_bags,
        "paymaya_beneficiaries_male": 0,
        "paymaya_beneficiaries_female": 0,
        "paymaya_delivery": paymaya_delivery
    })
    print(json_string)