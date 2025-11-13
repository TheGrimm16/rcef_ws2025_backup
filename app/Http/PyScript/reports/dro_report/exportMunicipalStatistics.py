import time
total_start = time.perf_counter()
import sys
import pymysql
import polars as pl
import asyncio
import asyncmy
import os
from datetime import datetime
import pandas as pd


# ----------------------------
# Config / Globals
# ----------------------------
SSN = sys.argv[1] if len(sys.argv) > 1 else "ws2025_"
DB_HOST = "192.168.10.44"
DB_USER = "json"
DB_PASS = "Zeijan@13"
DB_PORT = 3306
DB_NAME = SSN + "rcep_delivery_inspection"

DEBUG = True

# ----------------------------
# Step 1: Fetch province database list with row counts (sync)
# ----------------------------
def get_database_list_with_counts():
    start_time = time.perf_counter()
    conn = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        port=DB_PORT,
    )
    query = f"""
        SELECT 
            CONCAT('{SSN}prv_', LEFT(prv, 4)) AS database_prv,
            COUNT(*) AS row_count
        FROM lib_dropoff_point
        GROUP BY LEFT(prv, 4)
        ORDER BY row_count DESC;
    """
    with conn.cursor() as cur:
        cur.execute(query)
        db_list = [{"name": row[0], "rows": row[1]} for row in cur.fetchall()]
    conn.close()
    if DEBUG:
        print(f"[DEBUG] Fetched {len(db_list)} databases in {time.perf_counter() - start_time:.2f} seconds")
    return db_list

# ----------------------------
# Async PRV query
# ----------------------------
async def query_prv(pool, db_name):
    start_time = time.perf_counter()
    SQL = f"""
        SELECT 
            LEFT(b.prv_dropoff_id, 6) AS MunCode,
            COALESCE(COUNT(DISTINCT CASE 
                WHEN remarks IS NULL OR remarks = "" OR (remarks NOT LIKE "%claimed in home address%" AND remarks NOT LIKE "%intended%")
                    THEN b.content_rsbsa 
                END), 0) AS 'Farmer Beneficiaries (REGULAR)',
            COALESCE(COUNT(DISTINCT CASE 
                WHEN UPPER(LEFT(b.sex,1)) = 'M' AND (remarks IS NULL OR remarks = "")
                    THEN b.content_rsbsa 
                END), 0) AS 'Male (REGULAR)',
            COALESCE(COUNT(DISTINCT CASE 
                WHEN UPPER(LEFT(b.sex,1)) = 'F' AND (remarks IS NULL OR remarks = "")
                    THEN b.content_rsbsa 
                END), 0) AS 'Female (REGULAR)',
            COALESCE(COUNT(DISTINCT CASE 
                WHEN UPPER(LEFT(b.sex,1)) NOT IN ('M','F') AND (remarks IS NULL OR remarks = "") 
                    THEN b.content_rsbsa 
                END), 0) AS 'Undefined (REGULAR)',
            COALESCE(COUNT(DISTINCT CASE 
                WHEN remarks IS NOT NULL AND remarks != "" AND id = 111111111 AND remarks LIKE "%intended%"
                    THEN b.content_rsbsa 
                END), 0) AS 'Farmer Beneficiaries (Home Claim)',
            COALESCE( SUM( CASE
                WHEN remarks IS NOT NULL AND remarks != '' AND id = 111111111 AND (LOWER(remarks) LIKE '%intended%' AND remarks REGEXP '^[0-9]+'
                    THEN CAST(REGEXP_SUBSTR(remarks, '^[0-9]+') AS UNSIGNED)
                END), 0) AS 'Total Distributed Bags (Home Claim)',
            COALESCE(SUM(CASE
                WHEN remarks IS NOT NULL AND remarks != '' AND id = 111111111 AND (LOWER(remarks) LIKE '%intended%' AND LOCATE('area of', LOWER(remarks)) > 0
                    THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(LOWER(remarks), LOCATE('area of', LOWER(remarks)) + LENGTH('area of ')), ' ', 1)) AS DECIMAL(15,2))
                END), 0) AS 'Claimed area (Home Claim)',
            COALESCE(SUM(b.bags_claimed), 0) AS 'Total Distributed Bags (REGULAR)',
            COALESCE(SUM(b.final_area), 0) AS 'Actual area (REGULAR)',
            COALESCE(SUM(b.claimed_area), 0) AS 'Claimed area (REGULAR)'
        FROM {db_name}.new_released b
        WHERE b.category = 'INBRED'
        GROUP BY MunCode;
    """
    async with pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute(SQL)
            columns = [desc[0] for desc in cur.description]
            rows = await cur.fetchall()

    df = pl.DataFrame(rows, schema=columns, orient="row")
    df = df.with_columns([
        pl.col("MunCode").cast(pl.Utf8),
        pl.col("Farmer Beneficiaries (REGULAR)").cast(pl.Int64),
        pl.col("Male (REGULAR)").cast(pl.Int64),
        pl.col("Female (REGULAR)").cast(pl.Int64),
        pl.col("Undefined (REGULAR)").cast(pl.Int64),
        pl.col("Farmer Beneficiaries (Home Claim)").cast(pl.Int64),
        pl.col("Total Distributed Bags (Home Claim)").cast(pl.Int64),
        pl.col("Claimed area (Home Claim)").cast(pl.Float64),
        pl.col("Total Distributed Bags (REGULAR)").cast(pl.Int64),
        pl.col("Actual area (REGULAR)").cast(pl.Float64),
        pl.col("Claimed area (REGULAR)").cast(pl.Float64),
        pl.lit(db_name).alias("database").cast(pl.Utf8)
    ])
    if DEBUG:
        print(f"[DEBUG] Finished query for {db_name} in {time.perf_counter() - start_time:.2f} seconds")
    return df

# ----------------------------
# Async eBinhi query
# ----------------------------
async def query_ebinhi(pool):
    start_time = time.perf_counter()
    SQL = """
        SELECT
            b.region AS region,
            b.province AS province,
            b.municipality AS municipality,
            COALESCE(COUNT(DISTINCT b.beneficiary_id), 0) AS 'Farmer Beneficiaries (eBinhi)',
            COALESCE(COUNT(DISTINCT CASE WHEN UPPER(LEFT(b.sex,1)) = 'M' THEN b.beneficiary_id END), 0) AS 'Male (eBinhi)',
            COALESCE(COUNT(DISTINCT CASE WHEN UPPER(LEFT(b.sex,1)) = 'F' THEN b.beneficiary_id END), 0) AS 'Female (eBinhi)',
            COALESCE(COUNT(DISTINCT CASE WHEN UPPER(LEFT(b.sex,1)) NOT IN ('F','M') THEN b.beneficiary_id END), 0) AS 'Undefined (eBinhi)',
            COALESCE(SUM(a.bags), 0) AS 'Total Distributed Bags (eBinhi)',
            COALESCE(SUM(b.area), 0) AS 'Claimed area (eBinhi)',
            COALESCE(SUM(b.area), 0) AS 'Actual area (eBinhi)'
        FROM
        (
            SELECT beneficiary_id, COUNT(*) AS bags FROM ws2025_rcep_paymaya.tbl_claim
            GROUP BY beneficiary_id
        ) AS a
        LEFT JOIN ws2025_rcep_paymaya.tbl_beneficiaries b
        ON a.beneficiary_id = b.beneficiary_id
        GROUP BY b.region, b.province, b.municipality
        ORDER BY b.region, b.province, b.municipality
        ;
    """
    async with pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute(SQL)
            columns = [desc[0] for desc in cur.description]
            rows = await cur.fetchall()

    df = pl.DataFrame(rows, schema=columns, orient="row")
    if DEBUG:
        print(f"[DEBUG] Finished query for eBinhi in {time.perf_counter() - start_time:.2f} seconds")
    return df

# ----------------------------
# Async PSA_Codes query
# ----------------------------
async def query_psa_codes(pool):
    start_time = time.perf_counter()
    SQL = """
        SELECT prv AS 'MunCode', 
            psa_code AS 'PSA code', 
            updated_psa_code AS 'Updated PSA Code'
        FROM ws2025_rcep_delivery_inspection.lib_prv
        GROUP BY MunCode;
    """
    async with pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute(SQL)
            columns = [desc[0] for desc in cur.description]
            rows = await cur.fetchall()

    df = pl.DataFrame(rows, schema=columns, orient="row")
    if DEBUG:
        print(f"[DEBUG] Finished query for PSA_Codes in {time.perf_counter() - start_time:.2f} seconds")
    return df

# ----------------------------
# Async Actual Delivery query
# ----------------------------
async def query_actual_delivery(pool):
    start_time = time.perf_counter()
    SQL = """
        SELECT
            ad.region,
            ad.province,
            ad.municipality,
            ad.prv AS MunCode,
            COALESCE(SUM(CASE 
                WHEN ad.is_transferred != 1 AND ad.qrStart <= 0 
                    THEN ad.totalBagCount 
                ELSE 0 
                END), 0) AS `Accepted and Inspected Bags (REGULAR)`,
            COALESCE(SUM(CASE 
                WHEN ad.transferCategory = 'P' 
                    THEN ad.totalBagCount 
                ELSE 0 
                END), 0) AS `Transferred Bags (Previous Season)`,
            COALESCE(SUM(CASE 
                WHEN ad.transferCategory != 'P' AND ad.is_transferred = 1 
                    THEN ad.totalBagCount 
                ELSE 0 
                END), 0) AS `Transferred Bags (Current Season)`,
            COALESCE(SUM(CASE 
                WHEN ad.is_transferred != 1 AND ad.qrStart > 0 
                    THEN ad.totalBagCount 
                ELSE 0 
                END), 0) AS 'eBinhi Seeds'
        FROM ws2025_rcep_delivery_inspection.tbl_actual_delivery ad
        GROUP BY MunCode
        ORDER BY MunCode;
    """
    async with pool.acquire() as conn:
        async with conn.cursor() as cur:
            await cur.execute(SQL)
            columns = [desc[0] for desc in cur.description]
            rows = await cur.fetchall()

    df = pl.DataFrame(rows, schema=columns, orient="row")
    if DEBUG:
        print(f"[DEBUG] Finished query for Actual Delivery  in {time.perf_counter() - start_time:.2f} seconds")
    return df

# ----------------------------
# Main
# ----------------------------
async def main():
    total_start = time.perf_counter()
    DATABASE_PRV = get_database_list_with_counts()

    if DEBUG:
        print("\nLoaded province databases (sorted by size):")
        for db in DATABASE_PRV:
            print(f" - {db['name']} ({db['rows']} rows)")
        print(f"\nTotal databases loaded: {len(DATABASE_PRV)}\n")

    # Create a shared async connection pool
    pool = await asyncmy.create_pool(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        port=DB_PORT,
        minsize=(len(DATABASE_PRV) + 3) // 12,
        maxsize=(len(DATABASE_PRV) + 3) // 5
    )

    # Run eBinhi, Actual Delivery, and PSA_Codes concurrently
    ebinhi_task = asyncio.create_task(query_ebinhi(pool))
    actual_delivery_task = asyncio.create_task(query_actual_delivery(pool))
    psa_codes_task = asyncio.create_task(query_psa_codes(pool))

    # Run all province queries concurrently
    prv_tasks = [asyncio.create_task(query_prv(pool, db["name"])) for db in DATABASE_PRV]

    # Wait for all tasks
    results = await asyncio.gather(ebinhi_task, actual_delivery_task, psa_codes_task, *prv_tasks)

    # Extract individual DataFrames
    df_ebinhi = results[0]
    df_actual_delivery = results[1]
    df_psa_codes = results[2]
    df_prv = pl.concat(results[3:], rechunk=True)

    # Debug outputs
    if DEBUG:
        print("[DEBUG] eBinhi DF:")
        print(df_ebinhi)
        print("[DEBUG] Actual Delivery DF:")
        print(df_actual_delivery)
        print("[DEBUG] PSA CODES DF:")
        print(df_psa_codes)
        print("[DEBUG] Provincial DF:")
        print(df_prv)

    # Cleanup
    pool.close()
    await pool.wait_closed()

    # ----------------------------
    # JOIN ALL DATAFRAMES
    # ----------------------------

    # Step 1: Join df_actual_delivery + df_psa_codes on MunCode
    df_joined = df_actual_delivery.join(df_psa_codes, on="MunCode", how="left")

    # Step 2: Join df_prv on MunCode
    df_joined = df_joined.join(df_prv, on="MunCode", how="left")

    # Step 3: Join df_ebinhi using region, province, municipality
    # Only perform this if those columns exist in df_joined
    join_cols = [c for c in ["region", "province", "municipality"] if c in df_joined.columns]
    if all(col in df_joined.columns for col in join_cols):
        df_joined = df_joined.join(df_ebinhi, on=join_cols, how="left")
    else:
        print("[WARN] Skipping eBinhi join — missing region/province/municipality columns in df_prv")

    # ----------------------------
    # Final Output
    # ----------------------------
    print("\n[DEBUG] Final Joined DataFrame:")
    print(df_joined.head())
    print(f"Final shape: {df_joined.shape}")

    # ----------------------------
    # EXPORT TO EXCEL
    # ----------------------------

    path = os.path.join("C:/xampp/htdocs/rcef_ws2025/public/reports/excel_export_regional/")
    os.makedirs(path, exist_ok=True)

    date_now = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f"rs_{date_now}.xlsx"
    file_path = os.path.join(path, filename)

    with pd.ExcelWriter(file_path, engine="xlsxwriter") as writer:
        df_joined.to_pandas().to_excel(writer, sheet_name="Regional Data", index=False)

    print(f"\n[DEBUG] Excel file saved at: {file_path}")

    if DEBUG:
        print(f"[DEBUG] Total Finished in {time.perf_counter() - total_start:.2f} seconds")

    return df_joined

# ----------------------------
# Entry point
# ----------------------------
if __name__ == "__main__":
    df_final = asyncio.run(main())
