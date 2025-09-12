import mysql.connector
import re

# --- CONFIG ---
USE_SPECIFIC_TABLES = True  # True → include specific tables
SCHEMA_PATTERN = "ws2025_prv_%"  # additional schemas/databases to include
TABLE = "new_released"
COLUMN = "yield_last_season_details"

# Specific tables you want to estimate
SPECIFIC_TABLES = [
    # ("ws2025_rcep_delivery_inspection", "tbl_delivery"),
    # ("ws2025_rcep_delivery_inspection", "tbl_actual_delivery"),
    # ("ws2025_rcep_reports", "lib_national_reports"),
    # ("ws2025_rcep_paymaya", "tbl_beneficiaries"),
    # ("ws2025_rcep_paymaya", "tbl_claim"),
    # ("ws2025_sdms_db_dev", "users_coop"),
    # ("ws2025_rcep_seed_cooperatives", "tbl_cooperatives"),
]

# --- MYSQL CONNECTION ---
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password=""
)
cursor = conn.cursor(dictionary=True)

# --- FUNCTIONS ---
def get_schemas_by_pattern(cursor, pattern):
    cursor.execute(f"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE '{pattern}'")
    return [row["SCHEMA_NAME"] for row in cursor.fetchall()]

def get_tables_in_schema(cursor, schema):
    cursor.execute(f"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{schema}'")
    return [(schema, row["TABLE_NAME"]) for row in cursor.fetchall()]

def column_memory_estimate(column_type, str_len=None):
    column_type = column_type.lower()
    if re.match(r"int|tinyint|smallint|mediumint|bigint", column_type):
        return 4
    elif re.match(r"decimal|numeric|float|double|real", column_type):
        return 8
    elif re.match(r"varchar\((\d+)\)", column_type) or re.match(r"char\((\d+)\)", column_type) \
         or column_type in ("text", "mediumtext", "longtext"):
        return (str_len + 8) if str_len is not None else 16
    elif column_type in ("timestamp", "datetime"):
        return 8
    else:
        return 16

# --- BUILD TABLE LIST ---
pattern_tables = []
specific_tables_to_add = []

# Step 1: Add tables from schema pattern
if SCHEMA_PATTERN:
    pattern_schemas = get_schemas_by_pattern(cursor, SCHEMA_PATTERN)
    for schema in pattern_schemas:
        pattern_tables.extend(get_tables_in_schema(cursor, schema))

# Step 2: Add specific tables only if not in pattern_tables
if USE_SPECIFIC_TABLES:
    pattern_set = set(pattern_tables)
    for t in SPECIFIC_TABLES:
        if t not in pattern_set:
            specific_tables_to_add.append(t)

# Step 3: Final ordered list: pattern first, then remaining specific tables
tables_to_process = pattern_tables + specific_tables_to_add

# --- MEMORY ESTIMATION ---
total_memory_avg = 0
total_memory_actual_max = 0
total_memory_schema_max = 0

for schema, table in tables_to_process:
    try:
        cursor.execute(f"SELECT COUNT(*) AS cnt FROM {schema}.{table}")
        row_count = cursor.fetchone()["cnt"]

        cursor.execute(f"""
            SELECT COLUMN_NAME, COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{schema}' AND TABLE_NAME = '{table}'
        """)
        columns = cursor.fetchall()

        row_memory_avg = 0
        row_memory_actual_max = 0
        row_memory_schema_max = 0

        for col in columns:
            col_name = col["COLUMN_NAME"]
            col_type = col["COLUMN_TYPE"]

            declared_max = None
            m = re.match(r"varchar\((\d+)\)", col_type.lower()) or re.match(r"char\((\d+)\)", col_type.lower())
            if m:
                declared_max = int(m.group(1))

            avg_len = actual_max_len = None
            if col_type.lower().startswith(("varchar", "char", "text", "mediumtext", "longtext")):
                try:
                    cursor.execute(f"""
                        SELECT AVG(CHAR_LENGTH(`{col_name}`)) AS avg_len,
                               MAX(CHAR_LENGTH(`{col_name}`)) AS max_len
                        FROM {schema}.{table}
                    """)
                    res = cursor.fetchone()
                    avg_len = int(res["avg_len"]) if res["avg_len"] else 0
                    actual_max_len = int(res["max_len"]) if res["max_len"] else 0
                except:
                    avg_len = actual_max_len = 0

            row_memory_avg += column_memory_estimate(col_type, avg_len)
            row_memory_actual_max += column_memory_estimate(col_type, actual_max_len)
            row_memory_schema_max += column_memory_estimate(col_type, declared_max)

        table_memory_avg = row_count * row_memory_avg
        table_memory_actual_max = row_count * row_memory_actual_max
        table_memory_schema_max = row_count * row_memory_schema_max

        total_memory_avg += table_memory_avg
        total_memory_actual_max += table_memory_actual_max
        total_memory_schema_max += table_memory_schema_max

        print(f"{schema}.{table}: {row_count} rows | "
              f"~{table_memory_avg / (1024**2):.2f} MB (actual avg) | "
              f"~{table_memory_actual_max / (1024**2):.2f} MB (actual max) | "
              f"~{table_memory_schema_max / (1024**2):.2f} MB (schema max)")

    except mysql.connector.Error as e:
        print(f"{schema}.{table} skipped: {e}")

print(f"\nTotal estimated memory: "
      f"~{total_memory_avg / (1024**2):.2f} MB (actual avg) | "
      f"~{total_memory_actual_max / (1024**2):.2f} MB (actual max) | "
      f"~{total_memory_schema_max / (1024**2):.2f} MB (schema max)")

cursor.close()
conn.close()
