import sys
import polars as pl
from sqlalchemy import create_engine
import json
import fitz

# Default DB config (for testing)
DEFAULT_DB_HOST = "localhost"
DEFAULT_DB_USER = "root"
DEFAULT_DB_PASS = ""
DEFAULT_DB_PORT = 3306
DEFAULT_SEASON_PREFIX = "ws2025_"
DEFAULT_DB_NAME = "sdms_db_dev"
DEFAULT_TABLE_NAME = "lib_geocodes"
DEFAULT_TABLE_NAME_MUN = 'lib_municipalities'
DEFAULT_TABLE_NAME_PROV = "lib_provinces"
DEFAULT_COOP_ACC = ""


# Get arguments from Laravel or use defaults
db_host = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_DB_HOST
db_user = sys.argv[2] if len(sys.argv) > 2 else DEFAULT_DB_USER
db_pass = sys.argv[3] if len(sys.argv) > 3 else DEFAULT_DB_PASS
db_port = int(sys.argv[4]) if len(sys.argv) > 4 else DEFAULT_DB_PORT
season_prefix = sys.argv[5] if len(sys.argv) > 5 else DEFAULT_SEASON_PREFIX
db_name = sys.argv[6] if len(sys.argv) > 6 else DEFAULT_DB_NAME
table_name_brgy = sys.argv[7] if len(sys.argv) > 7 else DEFAULT_TABLE_NAME
table_name_mun = sys.argv[8] if len(sys.argv) > 8 else DEFAULT_TABLE_NAME_MUN
table_name_prov = sys.argv[9] if len(sys.argv) > 9 else DEFAULT_TABLE_NAME_PROV

# Build SQLAlchemy engine
engine = create_engine(
    f"mysql+mysqldb://{db_user}:{db_pass}@{db_host}:{db_port}/{season_prefix+db_name}"
)

# Read table into Polars DataFrame
dfProv = pl.read_database(f"SELECT * FROM {table_name_prov} ORDER BY regCode", engine)
dfMun = pl.read_database(f"SELECT * FROM {table_name_mun}", engine)
df = pl.read_database(f"SELECT * FROM {table_name_brgy} LIMIT 100", engine)

# Document Generation:
# Open PDF
doc = fitz.open()
page = doc.new_page()

# --- HEADER LOGO ---
# logo_path = "/mnt/data/598080dc-65df-4c01-a0e6-5ad6cf22cab8.png"
# page.insert_image(fitz.Rect(30, 20, 100, 70), filename=logo_path)  # adjust position

# --- HEADER TEXT ---
header_text = "Farmer Acknowledgement Receipt\nYear/Season: 2026 Dry Season\nDrop-off Point: SAN MATEO, ISABELA"
page.insert_text((120, 30), header_text, fontsize=10, fontname="helv", rotate=0)

# --- TABLE OUTLINE ---
x0, y0 = 30, 100  # top-left corner
x1, y1 = 570, 750  # bottom-right
page.draw_rect(fitz.Rect(x0, y0, x1, y1))  # outer rectangle

# --- TABLE ROWS ---
row_height = 20
num_rows = 16  # header + 15 farmers
for i in range(num_rows + 1):
    y = y0 + i * row_height
    page.draw_line((x0, y), (x1, y))  # horizontal lines

# --- TABLE COLUMNS ---
cols = [x0, 100, 200, 260, 310, 360, 410, 460, 510, x1]  # example column positions
for x in cols:
    page.draw_line((x, y0), (x, y0 + row_height * num_rows))  # vertical lines

# --- COLUMN HEADERS ---
headers = ["No.", "Farmer Name", "RSBSA No.", "Registered Municipal Rice Area", "Total Parcel Count", 
           "Area to be planted (ha)", "Number of bags (20kg/bag)", "Rice Variety Received", 
           "Crop Estab. [D/T]", "Expected Sowing Date [Month/Week]"]
for i, h in enumerate(headers):
    page.insert_text((cols[i] + 2, y0 + 2), h, fontsize=7)

# Save PDF
doc.save("farmer_receipt.pdf")
print("PDF generated: farmer_receipt.pdf")


# Convert datetime columns to strings
for col, dtype in dfProv.schema.items():
    if dtype in [pl.Date, pl.Datetime]:
        dfProv = dfProv.with_columns(dfProv[col].cast(pl.Utf8))

# Convert datetime columns to strings
for col, dtype in dfMun.schema.items():
    if dtype in [pl.Date, pl.Datetime]:
        dfMun = dfMun.with_columns(dfMun[col].cast(pl.Utf8))

# Convert datetime columns to strings
for col, dtype in df.schema.items():
    if dtype in [pl.Date, pl.Datetime]:
        df = df.with_columns(df[col].cast(pl.Utf8))

# Convert Polars DataFrame to JSON string
# json_output = dfMun.to_dicts()
# print(json.dumps(json_output))