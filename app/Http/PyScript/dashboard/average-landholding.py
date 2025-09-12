import asyncio
import aiomysql
import time

DEBUG = True

WORKERS_MIN = 12
WORKERS_MAX = 82

DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = ""
DB_PORT = 3306
DB_NAME = "ws2025_rcep_delivery_inspection"
SEASON_PREFIX = "ws2025_prv_"

async def get_prv_codes(pool):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute("SELECT DISTINCT prv_code FROM lib_prv")
            rows = await cursor.fetchall()
            return [row[0] for row in rows]

async def process_database(pool, prv_code):
    start_time = time.time()
    db_name = f"{SEASON_PREFIX}{prv_code}"
    sum_area = 0.0
    count_rows = 0
    error = None

    try:
        async with pool.acquire() as conn:
            await conn.select_db(db_name)
            async with conn.cursor() as cursor:
                await cursor.execute("START TRANSACTION READ ONLY")
                await cursor.execute(
                    "SELECT SUM(final_area), COUNT(*) FROM farmer_information_final WHERE final_area > 0.1"
                )
                row = await cursor.fetchone()
                sum_area = float(row[0]) if row[0] is not None else 0.0
                count_rows = int(row[1]) if row[1] is not None else 0
                await cursor.execute("COMMIT")
    except Exception as e:
        error = f"Error in {prv_code}: {e}"

    elapsed_ms = (time.time() - start_time) * 1000.0
    if DEBUG:
        print(
            f"[{prv_code}]: SUM = {sum_area:.4f}, COUNT = {count_rows}, Completed in {elapsed_ms:.2f} ms"
        )
    return prv_code, sum_area, count_rows, error

async def main():
    overall_start = time.time()
    pool = await aiomysql.create_pool(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASS,
        db=DB_NAME,
        minsize=WORKERS_MIN,
        maxsize=WORKERS_MAX,
    )

    prv_codes = await get_prv_codes(pool)
    total_prv = len(prv_codes)

    tasks = [process_database(pool, code) for code in prv_codes]
    results = await asyncio.gather(*tasks)

    global_sum = 0.0
    global_count = 0

    for code, sum_area, count_rows, err in results:
        global_sum += sum_area
        global_count += count_rows
        if err:
            print(f"[{code}] ERROR: {err}")

    normalized = (global_sum / global_count) if global_count > 0 else 0.0

    elapsed_ms = (time.time() - overall_start) * 1000.0
    if DEBUG:
        print(f"Total Elapsed: {elapsed_ms:.2f} ms\nNormalized Average: ", end="")
    print(f"{normalized:.10f}")
    if DEBUG:
        print(f"Debug mode ON")
    pool.close()
    await pool.wait_closed()

if __name__ == "__main__":
    asyncio.run(main())
