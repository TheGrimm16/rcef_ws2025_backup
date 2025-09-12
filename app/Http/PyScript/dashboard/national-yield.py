import os
import time
import orjson
import concurrent.futures

# Performance Configuration
USE_MYSQLCLIENT = True  # True = mysqlclient, False = pymysql
MAX_WORKERS = max(1, int(os.cpu_count() * 0.5)) #control the max CPU threads allocation min is 1
BATCH_SIZE = 200000 #control memory allocation this value is in rows
USE_TRANSACTIONS = True #create a read only view for specific instance/time. For data integrity
USE_PROCESS_POOL = True #Toggle for multithreading
DEBUG = True  # Toggle debug output

if USE_MYSQLCLIENT:
    import MySQLdb as mysql_driver
else:
    import pymysql as mysql_driver

#DB Configuration
DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = ""
DB_PORT = 3306
SEASON_PREFIX = "ws2025_prv_"
TABLE_NAME = "new_released"
COLUMN = "yield_last_season_details"

def get_prv_codes():
    conn = mysql_driver.connect(
        host=DB_HOST,
        user=DB_USER,
        passwd=DB_PASS if USE_MYSQLCLIENT else DB_PASS,
        db="ws2025_rcep_delivery_inspection",
    )
    with conn.cursor() as cursor:
        cursor.execute("SELECT DISTINCT prv_code FROM lib_prv")
        prv_codes = [row[0] for row in cursor.fetchall()]
    conn.close()
    return prv_codes

def deduplicate_entries(entries):
    seen = {}
    for entry in entries:
        variety = entry.get("variety")
        area = float(entry.get("area", 0))
        if variety not in seen or area > float(seen[variety].get("area", 0)):
            seen[variety] = entry
    return list(seen.values())

def process_database(prv_code):
    start_time = time.time()
    db_name = f"{SEASON_PREFIX}{prv_code}"
    total_production = 0.0
    total_area = 0.0
    error = None

    try:
        conn = mysql_driver.connect(
            host=DB_HOST,
            user=DB_USER,
            passwd=DB_PASS if USE_MYSQLCLIENT else DB_PASS,
            db=db_name,
        )
        with conn.cursor() as cursor:
            try:
                if USE_TRANSACTIONS:
                    cursor.execute("START TRANSACTION READ ONLY")

                cursor.execute(f"SELECT COUNT(*) FROM {TABLE_NAME}")
                total_rows = cursor.fetchone()[0]

                for offset in range(0, total_rows, BATCH_SIZE):
                    cursor.execute(
                        f"SELECT {COLUMN} FROM {TABLE_NAME} LIMIT {BATCH_SIZE} OFFSET {offset}"
                    )
                    rows = cursor.fetchall()

                    for row in rows:
                        try:
                            data = orjson.loads(row[0])
                            if isinstance(data, list):
                                data = deduplicate_entries(data)
                            else:
                                data = [data]

                            for entry in data:
                                try:
                                    bags = float(entry.get("bags", 0))
                                    weight = float(entry.get("weight", 0))
                                    area = float(entry.get("area", 0))
                                    production = bags * weight
                                    if area > 0 and production > 0:
                                        total_production += production
                                        total_area += area
                                except Exception:
                                    continue
                        except Exception:
                            continue

                if USE_TRANSACTIONS:
                    cursor.execute("COMMIT")
            except Exception as e:
                if USE_TRANSACTIONS:
                    cursor.execute("ROLLBACK")
                error = f"Error processing data: {e}"

        conn.close()

    except (mysql_driver.MySQLError if USE_MYSQLCLIENT else mysql_driver.err.OperationalError) as e:
        error = f"Connection error: {e}"
        elapsed = time.time() - start_time
        if DEBUG:
            print(f"[{prv_code}] ERROR after {elapsed:.2f}s: {error}")
        return (prv_code, 0.0, 0.0, error)

    elapsed = time.time() - start_time
    if DEBUG:
        print(
            f"[{prv_code}] Rows: {total_rows} Production: {total_production:.2f} Area: {total_area:.2f} Completed in {elapsed:.2f}s"
        )
    return (prv_code, total_production, total_area, error)

def aggregate_all():
    overall_start = time.time()
    prv_codes = get_prv_codes()
    executor_cls = (
        concurrent.futures.ProcessPoolExecutor
        if USE_PROCESS_POOL
        else concurrent.futures.ThreadPoolExecutor
    )

    sum_production = 0.0
    sum_area = 0.0

    with executor_cls(max_workers=MAX_WORKERS) as executor:
        futures = {
            executor.submit(process_database, code): code for code in prv_codes
        }
        for future in concurrent.futures.as_completed(futures):
            code, prod_sum, area_sum, err = future.result()
            sum_production += prod_sum
            sum_area += area_sum
            if err:
                print(f"[{code}] ERROR: {err}")

    final_yield = (sum_production / sum_area / 1000) if sum_area > 0 else 0.0
    overall_elapsed = time.time() - overall_start
    if DEBUG:
        print(f"Total Elapsed Time: {overall_elapsed:.2f}s \nTotal Yield: ", end="")
    print(f"{final_yield:.10f}")
    if DEBUG:
        print(f"Debug mode ON")

if __name__ == "__main__":
    aggregate_all()
