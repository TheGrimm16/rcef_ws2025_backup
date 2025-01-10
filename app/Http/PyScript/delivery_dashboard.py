import os, sys
import traceback
import polars as pl
from urllib.parse import quote
import threading, time
import datetime
import requests
from xlsxwriter import Workbook
import subprocess

def install_package(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

# install_package("requests")

os.chdir("report/home/")
# os.chdir("D:\\Jannah Marie Althea R. Cortez\\Downloads")
api_url = "https://asia-southeast1-rcef-ims.cloudfunctions.net/api/rsms/getIarData?apikey=rc3f1m5-XApiKey"
uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')


_last_s = "ds2024" # change to last season in production
_season = sys.argv[1] if len(sys.argv) > 1 else None
_next_s = "ws2025" # change to next season in production
_coop_a = sys.argv[2] if len(sys.argv) > 1 else None

# globals 
_view_coop_deliveries = pl.DataFrame()
tbl_cooperatives = pl.DataFrame()
tbl_delivery = pl.DataFrame()
tbl_actual_delivery = pl.DataFrame()
tbl_rla_details = pl.DataFrame()
last_ssn_rla_details = pl.DataFrame()
tbl_delivery_transaction = pl.DataFrame()
tbl_breakdown_buffer = pl.DataFrame()
tbl_actual_delivery_breakdown = pl.DataFrame()
tbl_delivery_status = pl.DataFrame()
tbl_iar_print_logs = pl.DataFrame()
tbl_transfer_logs = pl.DataFrame()
ps_transfer_logs = pl.DataFrame()
lib_prv = pl.DataFrame()
next_ssn_tbl_actual_delivery = pl.DataFrame()
iar_details = pl.DataFrame()

replacement_arr = pl.DataFrame()
buffer_arr = pl.DataFrame()
bep_arr = pl.DataFrame()


def load_iar_details():
    global iar_details
    json_string = requests.get(api_url).json()
    iar_details = pl.from_dict(json_string)
def load_view_coop_deliveries():
    global _view_coop_deliveries
    _view_coop_deliveries = pl.read_database_uri(f"select a.batchTicketNumber AS'batchTicketNumber', a.coopAccreditation AS'coopAccreditation', a.seedVariety AS'seedVariety', a.deliveryDate AS'deliveryDate', a.dropOffPoint AS'dropOffPoint', a.region AS'region', a.province AS'province', a.municipality AS'municipality', a.seedTag AS'seedTag', a.isBuffer AS'isBuffer', a.sg_id AS'sg_id',( select b.seed_distribution_mode from {_season}_rcep_delivery_inspection.tbl_delivery_transaction b where a.coopAccreditation = b.accreditation_no and a.batchTicketNumber = b.batchTicketNumber and a.region = b.region limit 1 ) AS seed_distribution_mode from {_season}_rcep_delivery_inspection.tbl_delivery a where a.is_cancelled = 0 and a.isBuffer = 0 and a.coopAccreditation ='{_coop_a}'group by a.batchTicketNumber, a.seedVariety, a.seedTag order by a.deliveryDate desc;", uri)
    # print(_view_coop_deliveries)
def load_cooperatives():
    global tbl_cooperatives
    tbl_cooperatives = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_seed_cooperatives.tbl_cooperatives where accreditation_no = '{_coop_a}'", uri)

def load_delivery():
    global tbl_delivery
    tbl_delivery = pl.read_database_uri(f"SELECT `deliveryId`,`ticketNumber`,`batchTicketNumber`,`coopAccreditation`,`sgAccreditation`,`seedTag`,`seedVariety`,`seedClass`,`totalWeight`,`weightPerBag`,`totalBagCount`,`deliverTo`,coordinates,status,`inspectorAllocated`,`userId`, deliveryDate, `oldTicketNumber`,region,province,municipality,`dropOffPoint`,prv_dropoff_id,prv,moa_number,app_version,`batchSeries`,is_cancelled,cancelled_by,reason,sg_id,`isBuffer`,transpo_cost_per_bag FROM {_season}_rcep_delivery_inspection.tbl_delivery WHERE coopAccreditation = '{_coop_a}'", uri)

def load_actual_delivery():
    global tbl_actual_delivery
    tbl_actual_delivery = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_actual_delivery", uri)

def load_rla_details():
    global tbl_rla_details
    tbl_rla_details = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_rla_details where coopAccreditation = '{_coop_a}'", uri)

def load_last_ssn_rla_details():
    global last_ssn_rla_details
    last_ssn_rla_details = pl.read_database_uri(f"SELECT * FROM {_last_s}_rcep_delivery_inspection.tbl_rla_details", uri)

def load_delivery_transaction():
    global tbl_delivery_transaction
    tbl_delivery_transaction = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_delivery_transaction WHERE accreditation_no LIKE '{_coop_a}'", uri)

def load_tbl_actual_delivery_breakdown():
    global tbl_actual_delivery_breakdown
    tbl_actual_delivery_breakdown = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_actual_delivery_breakdown", uri)

def load_breakdown_buffer():
    global tbl_breakdown_buffer
    tbl_breakdown_buffer = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_breakdown_buffer", uri)

def load_deliver_status():
    global tbl_delivery_status
    tbl_delivery_status = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.tbl_delivery_status", uri)

def load_iar_print_logs():
    global tbl_iar_print_logs
    tbl_iar_print_logs = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.iar_print_logs", uri)

def load_lib_prv():
    global lib_prv
    lib_prv = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.lib_prv", uri)

def load_transfer_logs():
    global tbl_transfer_logs
    _tbl_transfer_logs = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_transfers.transfer_logs", uri)
    _lib_dropoff_points = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_delivery_inspection.lib_dropoff_point", uri)
    tbl_transfer_logs = _tbl_transfer_logs.join(_lib_dropoff_points, left_on=["origin_dop_id"], right_on=["prv_dropoff_id"], how="inner")

def load_ps_transfer_logs():
    global ps_transfer_logs
    ps_transfer_logs = pl.read_database_uri(f"SELECT * FROM {_season}_rcep_transfers_ps.transfer_logs WHERE coop_accreditation = '{_coop_a}'", uri)

def load_next_ssn_tbl_actual_delivery():
    global next_ssn_tbl_actual_delivery
    next_ssn_tbl_actual_delivery = pl.read_database_uri(f"SELECT * FROM {_next_s}_rcep_delivery_inspection.tbl_actual_delivery", uri)

def get_whole_data(batch, new_batch, seed_variety, seed_tag):
    if new_batch == "":
        batch_deliveries = tbl_actual_delivery.filter(pl.col("batchticketNumber").eq(batch) & pl.col("seedVariety").eq(seed_variety) & pl.col("seedTag").str.contains(f"(?i){seed_tag}") & pl.col("transferCategory").eq("W") & pl.col("is_transferred").eq(1))

        return_arr = []
        for row in batch_deliveries.iter_rows():
            transfer_category = row["transferCategory"]
            seed_type = row["seedType"]

            if transfer_category == "W":
                origin_info = tbl_transfer_logs.filter(pl.col("batch_number").eq(batch) & pl.col('seed_variety').eq("ALL_SEEDS_TRANSFER")).select(["province", "municipality", pl.col("dropOffPoint").alias("dopName")])[0]
                if not origin_info.is_empty():
                    origin_data = f"{origin_info['origin_province'], origin_info['origin_municipality']} -> {origin_info['dopName']}"
                else:
                    origin_data = " NO INFO ON LOGS"
                
                dest_info = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("transferCategory").eq(transfer_category)).select(["province", "municipality", pl.col("dropOffPoint").alias("dopName")])[0]
                if not dest_info.is_empty():
                    dest_data = f"{dest_info['province'], dest_info['municipality']} -> {dest_info['dopName']}"
                else:
                    dest_data = ""
                
                categoryTrans = "WHOLE TRANSFER"
            
            if seed_type == "I":
                sdt = "Inventory Seeds"
            elif seed_type == "B":
                sdt = "Buffer Seeds"
            else:
                sdt = ""

            batch_data = {
                "batch_num": batch,
                "origin": origin_data,
                "destination": dest_data,
                "seedVariety": row["seedVariety"],
                "seedTag": row["seedTag"],
                "seedType": sdt,
                "bags": f"row['totalBagCount'] bag(s)",
                "dateCreated": row["dateCreated"],
                "transferType": categoryTrans
            }

            return_arr.append(batch_data)
    else:
        batch_deliveries = tbl_actual_delivery.filter(
            pl.col("remarks").str.contains(f"(?i)transferred from previous batch: {batch}") & 
            pl.col("batchTicketNumber").eq(new_batch) & 
            pl.col("is_transferred").eq(1) & 
            pl.col("transferCategory").eq("W") & 
            pl.col("seedVariety").eq(seed_variety)
            ).sort(by=["batchTicketNumber"], descending=False)
        
        return_arr = []
        for batch_row in batch_deliveries.iter_rows(named=True):
            batchTicketNumber = batch_row['batchTicketNumber']
            transferCategory = batch_row['transferCategory']

            if transferCategory == "W": 
                origin_info = tbl_transfer_logs.filter(
                    pl.col("batch_number").eq(batchTicketNumber) & 
                    pl.col('seed_variety').eq("ALL_SEEDS_TRANSFER")
                    ).select(["batch_number", "province", "municipality", pl.col("dropOffPoint").alias("dopName")])[0]

                if not origin_info.is_empty():
                    origin_data = f"{origin_info['province'], origin_info['municipality']} -> {origin_info['dopName']}"
                else:
                    origin_data = " NO INFO ON LOGS"
                
                dest_data = f"{batch_row['province'], batch_row['municipality']} -> {batch_row['dropOffPoint']}"
                categoryTrans = "WHOLE TRANSFER"

            if seed_type == "I":
                sdt = "Inventory Seeds"
            elif seed_type == "B":
                sdt = "Buffer Seeds"
            else:
                sdt = ""
            
            batch_data = {
                "batch_num": batch_row["batchTicketNumber"],
                "origin": origin_data,
                "destination": dest_data,
                "seedVariety": batch_row["seedVariety"],
                "seedTag": batch_row["seedTag"],
                "seedType": sdt,
                "bags": f"{batch_row['totalBagCount']} bag(s)",
                "dateCreated": batch_row["dateCreated"],
                "transferType": categoryTrans
            }

            return_arr.append(batch_data)

    return pl.DataFrame(return_arr)

def get_partial_data(batch, seed_variety, seed_tag):
    batch_deliveries = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){batch}") & pl.col("seedVariety").eq(seed_variety) & pl.col("is_transferred").eq(1) & pl.col("transferCategory").eq("T") & pl.col("seedTag").str.contains(f"(?i){seed_tag}")).sort(by=["batchTicketNumber"], descending=False)

    return_arr = []
    for row in batch_deliveries.iter_rows(named=True):
        batchTicketNumber = row['batchTicketNumber']
        oldBatchTicketNumber = row['remarks'].replace("transferred from batch:", "").strip().replace(" ", "")
        transferCategory = row['transferCategory']
        prv_dropoff_id = row['prv_dropoff_id']
        totalBagCount = row['totalBagCount']
        province = row['province']
        municipality = row['municipality']
        dropOffPoint = row['dropOffPoint']
        seedType = row['seedType']
        bags = 0

        batch_deliveries_retransfer = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){batchTicketNumber}") & pl.col("seedVariety").eq(seed_variety) & pl.col("is_transferred").eq(1) & pl.col("transferCategory").eq("T") & pl.col("seedTag").str.contains(f"(?i){seed_tag}")).select(pl.col("totalBagCount")).sum().item()

        if transferCategory == "T":
            originInfo = tbl_transfer_logs.filter(pl.col("batch_number").eq(oldBatchTicketNumber) & pl.col('seed_variety').eq("PARTIAL_SEEDS_TRANSFER") & pl.col("destination_dop_id").eq(prv_dropoff_id)).select(["batch_number", "origin_province", "origin_municipality", pl.col("dropOffPoint").alias("dopName")])[0]
            
            if not originInfo.is_empty():
                origin_data = f"{originInfo['origin_province'].item()}, {originInfo['origin_municipality'].item()} -> {originInfo['dopName'].item()}"
            else:
                origin_data = " NO INFO ON LOGS"

            dest_data = f"{province}, {municipality} -> {dropOffPoint}"
            categoryTrans = "PARTIAL TRANSFER"

        if seedType == "I":
            sdt = "Inventory Seeds"
        elif seedType == "B":
            sdt = "Buffer Seeds"
        else:
            sdt = ""
        
        if batch_deliveries_retransfer > 0:
            bags = batch_deliveries_retransfer + totalBagCount
        else:
            bags = totalBagCount
        batch_data = {
            "batch_num": batchTicketNumber,
            "origin": origin_data,
            "destination": dest_data,
            "seedVariety": seed_variety,
            "seedTag": seed_tag,
            "seedType": sdt,
            "bags": f"{bags} bag(s)",
            "dateCreated": row["dateCreated"],
            "transferType": categoryTrans
        }

        return_arr.append(batch_data)
    return pl.DataFrame(return_arr)

def get_next_season_data(batch, seed_variety, seed_tag):
    batch_deliveries = next_ssn_tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("seedVariety").eq(seed_variety) & pl.col("seedTag").str.contains(f"(?i){seed_tag}") & pl.col("transferCategory").eq("W") & pl.col("is_transferred").eq(1) & pl.col("season").eq(_season)).select(["batchTicketNumber", "province", "municipality", pl.col("dropOffPoint").alias("dopName")])

    return_arr = []
    for row in batch_deliveries.iter_rows(named=True):
        batchTicketNumber = row['batchTicketNumber']
        oldBatchTicketNumber = row['remarks'].replace("Transferred from batch:", "").strip().replace(" ", "")
        province = row['province']
        municipality = row['municipality']
        dropOffPoint = row['dopName']
        bags = row['totalBagCount']
    
        originData = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(oldBatchTicketNumber)).select(["province", "municipality", "dropOffPoint", "isBuffer"])[0]

        if originData.select("isBuffer").item() == 1:
            sdt = "Buffer Seeds"
        else:
            sdt = "Inventory Seeds"
        
        if not originData.is_empty():
            originDataLoc = f"{originData['province'], originData['municipality']} -> {originData['dropOffPoint']}"
        else:
            originDataLoc = " NO INFO ON LOGS"
        
        dest_data = f"{province}, {municipality} -> {dropOffPoint}"
        categoryTrans = "TRANSFERRED TO NEXT SEASON"

        batch_data = {
            "batch_num": batchTicketNumber,
            "origin": originDataLoc,
            "destination": dest_data,
            "seedVariety": seed_variety,
            "seedTag": seed_tag,
            "seedType": sdt,
            "bags": f"{bags} bag(s)",
            "dateCreated": row["dateCreated"],
            "transferType": categoryTrans
        }

        return_arr.append(batch_data)
    return pl.DataFrame(return_arr)

def get_replacement_list(coop_accreditation):
    global replacement_arr
    batch_deliveries = tbl_delivery.filter(
            pl.col("is_cancelled").eq(0) &
            pl.col("coopAccreditation").eq(coop_accreditation) &
            pl.col("isBuffer").eq(9)
        ).group_by(
            "batchTicketNumber", 
            "seedVariety", 
            "seedTag"
        ).agg(
            pl.col("batchTicketNumber").first().alias("batchTicketNumber_"),
            pl.col("seedVariety").first().alias("seedVariety_"),
            pl.col("deliveryDate").first().alias("deliveryDate_"),
            pl.col("dropOffPoint").first().alias("dropOffPoint_"),
            pl.col("region").first().alias("region_"),
            pl.col("province").first().alias("province_"),
            pl.col("municipality").first().alias("municipality_"),
            pl.col("seedTag").first().alias("seedTag_"),
            pl.col("isBuffer").first().alias("isBuffer_")
        ).select([
            pl.col("batchTicketNumber_").alias("batchTicketNumber"), 
            pl.col("seedVariety_").alias("seedVariety"), 
            pl.col("deliveryDate_").alias("deliveryDate"), 
            pl.col("dropOffPoint_").alias("dropOffPoint"), 
            pl.col("region_").alias("region"), 
            pl.col("province_").alias("province"), 
            pl.col("municipality_").alias("municipality"), 
            pl.col("seedTag_").alias("seedTag"), 
            pl.col("isBuffer_").alias("isBuffer")
        ])
    
    total_confirmed = 0
    total_inspected = 0

    return_arr = []
    for batch_row in batch_deliveries.iter_rows(named=True):
        is_replacement = tbl_actual_delivery.filter(
                pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                pl.col("isBuffer").eq(9)
            )[0]

        if not is_replacement.is_empty():
            label = ""

            seed_grower = tbl_rla_details.filter(
                    pl.col("coopAccreditation").eq(coop_accreditation) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                ).select(pl.col("sg_name").first().alias("sg_name"))[0].item()

            confirmed_bags = tbl_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("coopAccreditation").eq(coop_accreditation) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                ).select(pl.col("totalBagCount")).sum().item()

            check_inspected = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                )
            
            if not check_inspected.is_empty():
                inspected_bags = check_inspected.select(pl.col("totalBagCount")).sum().item()

                batch_status = tbl_delivery_status.filter(
                        pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                    ).sort(["deliveryStatusId"], descending=True
                    ).select(pl.col("status").first().alias("status"))[0].item()
                
                if batch_status == 0:
                    batch_status = "Pending"
                elif batch_status == 1:
                    batch_status = "Passed"
                elif batch_status == 2:
                    batch_status = "Rejected"
                elif batch_status == 3:
                    batch_status = "In Transit"
                elif batch_status == 4:
                    batch_status = "Cancelled"
            else:
                inspected_bags = 0
                batch_status = "N/A"

            iar_number = tbl_iar_print_logs.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                ).sort(["logsId"], descending=True)[0]

            if not iar_number.is_empty():
                iar_number_str = iar_number.select(pl.col("iarNumber").first().alias("iarNumber")).item()
            else:
                iar_number_str = "N/A"
            
            replacement_batch = tbl_breakdown_buffer.filter(
                    pl.col("replacement_ticket").str.contains(f"(?i){batch_row['batchTicketNumber']}")
                )[0]

            if not replacement_batch.is_empty():
                origin_batch = replacement_batch.select(pl.col("batchTicketNumber").first().alias("batchTicketNumber")).item()
                originSeedTag = replacement_batch.select(pl.col("seedTag").first().alias("seedTag")).item()
                origin_bags = tbl_actual_delivery_breakdown.filter(
                        pl.col("batchTicketNumber").eq(origin_batch) &
                        pl.col("seedTag").eq(originSeedTag)
                    ).select(pl.col("totalBagCount")).sum().item()
            else:
                origin_batch = "N/A"
                originSeedTag = "N/A"
                origin_bags = "N/A"
            
            is_transfer_W = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("W")
                ).select(pl.col("transferCategory").first().alias("transferCategory"))[0].item()
            
            is_transfer_T = tbl_actual_delivery.filter(
                    pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("T")
                ).select(pl.col("batchTicketNumber").first().alias("batchTicketNumber"))[0].item()
        
            if not is_transfer_W.is_empty():
                arr_w = get_whole_data(batch_row["batchTicketNumber"], "", batch_row["seedVariety"], batch_row["seedTag"])

                for w_row in arr_w.iter_rows(named=True):

                    if w_row["seedTag"] == batch_row["seedTag"]:
                        bt = w_row["batch_num"]
                        o_r = w_row["origin"]
                        dt = w_row["destination"]
                        sv = w_row["seedVariety"]
                        st = w_row["seedTag"]
                        sdt = w_row["seedType"]
                        bg = w_row["bags"]
                        dc = w_row["dateCreated"]
                        tt = w_row["transferType"]

                        if dt != "" :
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv)
                                ).select(pl.col("regionName").first().alias("regionName"))[0].item()
                        else:
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                            dt_reg = "N/A"
                        
                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv)
                                ).select(pl.col("regionName").first().alias("regionName"))[0].item()
                        else:
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"
                            o_r_reg = "N/A"
                        
                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": bt,
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": int(confirmed_bags),
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": dc,
                            "batch_status": tt,
                            "remarks": label,
                            "origin_batch": origin_batch,
                            "origin_seedTag": originSeedTag,
                            "origin_bags": origin_bags
                        })
            else:
                return_arr.append({
                    "iar_number": iar_number_str,
                    "batchTicketNumber": batch_row["batchTicketNumber"],
                    "coopAccreditation": batch_row["coopAccreditation"],
                    "seedVariety": batch_row["seedVariety"],
                    "dropOffPoint": batch_row["dropOffPoint"],
                    "region": batch_row["region"],
                    "province": batch_row["province"],
                    "municipality": batch_row["municipality"],
                    "seedTag": batch_row["seedTag"],
                    "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                    "confirmed": int(confirmed_bags),
                    "inspected": int(inspected_bags),
                    "deliveryDate": batch_row["deliveryDate"],
                    "batch_status": batch_status,
                    "remarks": label,
                    "origin_batch": origin_batch,
                    "origin_seedTag": originSeedTag,
                    "origin_bags": origin_bags
                })

            if not is_transfer_T.is_empty():
                arr_t = get_partial_data(batch_row["batchTicketNumber"], batch_row["seedTag"], batch_row["seedVariety"])

                for t_row in arr_t.iter_rows(named=True):
                    bt = t_row["batch_num"]
                    o_r = t_row["origin"]
                    dt = t_row["destination"]
                    sv = t_row["seedVariety"]
                    st = t_row["seedTag"]
                    sdt = t_row["seedType"]
                    bg = t_row["bags"]
                    dc = t_row["dateCreated"]
                    tt = t_row["transferType"]

                    if dt != "" :
                        dt = dt.replace(",", "|")
                        dt = dt.replace("->", "|")
                        dt_split = dt.split("|")
                        dt_prv = dt_split[0]
                        dt_mun = dt_split[1]
                        dt_dop = dt_split[2]
                        dt_reg = lib_prv.filter(
                                pl.col("province").eq(dt_prv)
                            ).select(pl.col("regionName").first().alias("regionName"))[0].item()
                    else:
                        dt_prv = "N/A"
                        dt_mun = "N/A"
                        dt_dop = "N/A"
                        dt_reg = "N/A"
                    
                    if o_r != " NO INFO ON LOGS":
                        o_r = o_r.replace(",", "|")
                        o_r = o_r.replace("->", "|")
                        o_r_split = o_r.split("|")
                        o_r_prv = o_r_split[0]
                        o_r_mun = o_r_split[1]
                        o_r_dop = o_r_split[2]
                        o_r_reg = lib_prv.filter(
                                pl.col("province").eq(o_r_prv)
                            ).select(pl.col("regionName").first().alias("regionName"))[0].item()
                    else:
                        o_r_prv = "N/A"
                        o_r_mun = "N/A"
                        o_r_dop = "N/A"
                        o_r_reg = "N/A"
                    
                    is_replacement = tbl_actual_delivery.filter(
                            pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
                            pl.col("isBuffer").eq(0)
                        )[0]
                    
                    if is_replacement.is_empty():
                        continue
                    
                    return_arr.append({
                        "iar_number": iar_number_str,
                        "batchTicketNumber": batch_row["batchTicketNumber"],
                        "coopAccreditation": batch_row["coopAccreditation"],
                        "seedVariety": batch_row["seedVariety"],
                        "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                        "region": f"{o_r_reg} => {dt_reg}",
                        "province": f"{o_r_prv} => {dt_prv}",
                        "municipality": f"{o_r_mun} => {dt_mun}",
                        "seedTag": st,
                        "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                        "confirmed": int(confirmed_bags),
                        "inspected": int(bg.replace("bag(s)", "")),
                        "deliveryDate": dc,
                        "batch_status": tt,
                        "remarks": label,
                    })
            total_confirmed += int(confirmed_bags)
            total_inspected += int(bg.replace("bag(s)", ""))
    return_arr.append({
        "iar_number": "",
        "batchTicketNumber": "",
        "coopAccreditation": "",
        "seedVariety": "",
        "dropOffPoint": "",
        "region": "",
        "province": "",
        "municipality": "",
        "seedTag": "",
        "seed_grower": "TOTAL: ",
        "confirmed": total_confirmed,
        "inspected": total_inspected,
        "deliveryDate": "",
        "batch_status": "",
    })
    temp_df = pl.DataFrame(return_arr)
    replacement_arr = temp_df.join(iar_details, on=['iar_number'], how='left')

def get_buffer_list(coop_accreditation):
    global buffer_arr
    batch_deliveries = tbl_delivery.filter(
            pl.col("is_cancelled").eq(0) &
            pl.col("coopAccreditation").eq(coop_accreditation) &
            pl.col("isBuffer").eq(1)
        ).group_by(
            "batchTicketNumber", 
            "seedTag", 
            "seedVariety"
        ).agg([
            pl.col("batchTicketNumber").first().alias("batchTicketNumber_"),
            pl.col("coopAccreditation").first().alias("coopAccreditation_"),
            pl.col("seedVariety").first().alias("seedVariety_"),
            pl.col("deliveryDate").first().alias("deliveryDate_"),
            pl.col("dropOffPoint").first().alias("dropOffPoint_"),
            pl.col("region").first().alias("region_"),
            pl.col("province").first().alias("province_"),
            pl.col("municipality").first().alias("municipality_"),
            pl.col("seedTag").first().alias("seedTag_"),
            pl.col("isBuffer").first().alias("isBuffer_")
        ]).sort(
            by=["deliveryDate_"], 
            descending=True
        ).select([
            pl.col("batchTicketNumber_").alias("batchTicketNumber"),
            pl.col("coopAccreditation_").alias("coopAccreditation"),
            pl.col("seedVariety_").alias("seedVariety"),
            pl.col("deliveryDate_").alias("deliveryDate"),
            pl.col("dropOffPoint_").alias("dropOffPoint"),
            pl.col("region_").alias("region"),
            pl.col("province_").alias("province"),
            pl.col("municipality_").alias("municipality"),
            pl.col("seedTag_").alias("seedTag"),
            pl.col("isBuffer_").alias("isBuffer")
        ])
    
    total_confirmed = 0
    total_inspected = 0

    return_arr = []
    for batch_row in batch_deliveries.iter_rows(named=True):
        is_buffer = tbl_actual_delivery.filter(
                pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                pl.col("isBuffer").eq(1)
            )[0]
        
        if not is_buffer.is_empty():
            label = ""

            seed_grower = tbl_rla_details.filter(
                    pl.col("seedTag").eq(batch_row["seedTag"]) &
                    pl.col("coopAccreditation").eq(batch_row["coopAccreditation"])
                ).select(pl.col("sg_name").first().alias("sg_name"))[0].item()

            confirmed_bags = tbl_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("coopAccreditation").eq(coop_accreditation) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                ).select(pl.col("totalBagCount")).sum().item()

            check_inspected = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                )
            
            if not check_inspected.is_empty():
                inspected_bags = check_inspected.select(pl.col("totalBagCount").first().alias("totalBagCount")).sum().item()

                batch_status = tbl_delivery_status.filter(
                        pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                    ).sort(
                        by=["deliveryStatusId"],
                        descending=True
                    ).select(
                        pl.col("status").first().alias("status")
                    )[0].item()

                if batch_status == 0:
                    batch_status = "Pending"
                elif batch_status == 1:
                    batch_status = "Passed"
                elif batch_status == 2:
                    batch_status = "Rejected"
                elif batch_status == 3:
                    batch_status = "In Transit"
                elif batch_status == 4:
                    batch_status = "Cancelled"
            else:
                inspected_bags = 0
                batch_status = "N/A"
            
            iar_number = tbl_iar_print_logs.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                ).sort(
                    by="logsId",
                    descending=True
                )[0]

            if not iar_number.is_empty():
                iar_number_str = iar_number.select("iarCode").item()
            else:
                iar_number_str = "N/A"
            
            return_arr.append({
                "iar_number": iar_number_str,
                "batchTicketNumber": batch_row["batchTicketNumber"],
                "coopAccreditation": batch_row["coopAccreditation"],
                "seedVariety": batch_row["seedVariety"],
                "dropOffPoint": batch_row["dropOffPoint"],
                "region": batch_row["region"],
                "province": batch_row["province"],
                "municipality": batch_row["municipality"],
                "seedTag": batch_row["seedTag"],
                "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                "confirmed": int(confirmed_bags),
                "inspected": int(inspected_bags),
                "deliveryDate": batch_row["deliveryDate"],
                "batch_status": batch_status,
                "remarks": label,
            })

            is_transfer_W = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("W")
                ).select(["transferCategory"])[0]

            is_transfer_T = tbl_actual_delivery.filter(
                    pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("T")
                ).select(["batchTicketNumber"])[0]
            
            # is_transfer_N = tbl_actual_delivery.filter(
            #         pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
            #         pl.col("transferCategory").eq("P")
            #     ).select(["batchTicketNumber"])[0]

            is_transfer_N = pl.DataFrame()

            if not is_transfer_W.is_empty():
                arr_w = get_whole_data(batch_row['batchTicketNumber'], "", batch_row['seedVariety'], batch_row['seedTag'])

                for w_row in arr_w.iter_rows(named=True):
                    if w_row['seedTag'] == batch_row['seedTag']:
                        bt = w_row['batch_num']
                        o_r = w_row['origin']
                        dt = w_row['destination']
                        sv = w_row['seedVariety']
                        st = w_row['seedTag']
                        sdt = w_row['seedType']
                        bg = w_row['bags']
                        dc = w_row['dateCreated']
                        tt = w_row['transferType']

                        if dt != "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv)
                                ).select(["regionName"])[0].item()
                        else:
                            dt_reg = "N/A"
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"

                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"

                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": bt,
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": int(confirmed_bags),
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": dc,
                            "batch_status": tt,
                            "remarks": label
                        })

            if not is_transfer_T.is_empty():
                arr_t = get_partial_data(batch_row['batchTicketNumber'], batch_row['seedVariety'], batch_row['seedTag'])

                for t_row in arr_t.iter_rows(named=True):
                    if t_row['seedTag'] == batch_row['seedTag']:
                        bt = t_row['batch_num']
                        o_r = t_row['origin']
                        dt = t_row['destination']
                        sv = t_row['seedVariety']
                        st = t_row['seedTag']
                        sdt = t_row['seedType']
                        bg = t_row['bags']
                        dc = t_row['dateCreated']
                        tt = t_row['transferType']
                    
                        if dt!= "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            dt_reg = "N/A"
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                        
                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"
                        
                        is_buffer = tbl_actual_delivery.filter(
                                pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
                                pl.col("isBuffer").eq(1)
                            )[0]

                        # if is_buffer.is_empty():
                        #     continue

                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": bt,
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": int(confirmed_bags),
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": dc,
                            "batch_status": tt,
                            "remarks": label
                        })
                        inspected_bags = int(bg.replace("bag(s)", ""))

            if not is_transfer_N.is_empty():
                arr_n = get_next_season_data(batch_row['batchTicketNumber'], batch_row['seedVariety'], batch_row['seedTag'])

                for n_row in arr_n.iter_rows(named=True):
                    if n_row['seedTag'] == batch_row['seedTag']:
                        bt = n_row['batch_num']
                        o_r = n_row['origin']
                        dt = n_row['destination']
                        sv = n_row['seedVariety']
                        st = n_row['seedTag']
                        sdt = n_row['seedType']
                        bg = n_row['bags']
                        dc = n_row['dateCreated']
                        tt = n_row['transferType']
                    
                        if dt!= "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            dt_reg = "N/A"
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                        
                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"
                        
                        label = ""

                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": batch_row['batchTicketNumber'],
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": '-',
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": dc,
                            "batch_status": tt,
                            "remarks": label
                        })
                        inspected_bags = int(bg.replace("bag(s)", ""))

            total_confirmed += confirmed_bags
            total_inspected += inspected_bags    

    return_arr.append({
        "iar_number": "",
        "batchTicketNumber": "",
        "coopAccreditation": "",
        "seedVariety": "",
        "dropOffPoint": "",
        "region": "",
        "province": "",
        "municipality": "",
        "seedTag": "",
        "seed_grower": "TOTAL:",
        "confirmed": total_confirmed,
        "inspected": total_inspected,
        "deliveryDate": "",
        "batch_status": "",
    })
    temp_df = pl.DataFrame(return_arr)
    buffer_arr = temp_df.join(iar_details, on=['iar_number'], how='left')

def get_bep_list(coop_accreditation):
    global bep_arr
    batch_deliveries = tbl_delivery.filter(
            pl.col("is_cancelled").eq(0) &
            pl.col("coopAccreditation").eq(coop_accreditation) &
            pl.col("isBuffer").ne(1)
        ).group_by(
            "batchTicketNumber", 
            "seedTag", 
            "seedVariety"
        ).agg([
            pl.col("batchTicketNumber").first().alias("batchTicketNumber_"),
            pl.col("coopAccreditation").first().alias("coopAccreditation_"),
            pl.col("seedVariety").first().alias("seedVariety_"),
            pl.col("deliveryDate").first().alias("deliveryDate_"),
            pl.col("dropOffPoint").first().alias("dropOffPoint_"),
            pl.col("region").first().alias("region_"),
            pl.col("province").first().alias("province_"),
            pl.col("municipality").first().alias("municipality_"),
            pl.col("seedTag").first().alias("seedTag_"),
            pl.col("isBuffer").first().alias("isBuffer_")
        ]).sort(
            by=["deliveryDate_"], 
            descending=True
        ).select([
            pl.col("batchTicketNumber_").alias("batchTicketNumber"),
            pl.col("coopAccreditation_").alias("coopAccreditation"),
            pl.col("seedVariety_").alias("seedVariety"),
            pl.col("deliveryDate_").alias("deliveryDate"),
            pl.col("dropOffPoint_").alias("dropOffPoint"),
            pl.col("region_").alias("region"),
            pl.col("province_").alias("province"),
            pl.col("municipality_").alias("municipality"),
            pl.col("seedTag_").alias("seedTag"),
            pl.col("isBuffer_").alias("isBuffer")
        ])
    
    total_confirmed = 0
    total_inspected = 0

    return_arr = []
    for batch_row in batch_deliveries.iter_rows(named=True):
        bep_actual = tbl_actual_delivery.filter(
                pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                pl.col("seedTag").eq(batch_row['seedTag']) & 
                pl.col("qrValStart").ne("")
            )[0]
        
        if not bep_actual.is_empty():
            label = ""

            seed_tag = batch_row["seedTag"]
            seed_tag = seed_tag.split("/")[0]
            seed_grower = tbl_rla_details.filter(
                    pl.col("labNo").str.contains(f"(?i){seed_tag}") &
                    pl.col("coopAccreditation").eq(batch_row["coopAccreditation"])
                ).select(pl.col("sg_name").first().alias("sg_name"))[0].item()

            confirmed_bags = tbl_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("coopAccreditation").eq(coop_accreditation) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"])
                ).select(["totalBagCount"]).sum().item()

            check_inspected = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("seedVariety").eq(batch_row["seedVariety"]) &
                    pl.col("seedTag").eq(batch_row["seedTag"]) &
                    pl.col("qrValStart").ne("")
                )
            
            if not check_inspected.is_empty():
                inspected_bags = check_inspected.select(pl.col("totalBagCount")).sum().item()

                batch_status = tbl_delivery_status.filter(
                        pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                    ).sort(
                        by=["deliveryStatusId"],
                        descending=True
                    ).select(
                        pl.col("status").first().alias("status")
                    )[0].item()

                if batch_status == 0:
                    batch_status = "Pending"
                elif batch_status == 1:
                    batch_status = "Passed"
                elif batch_status == 2:
                    batch_status = "Rejected"
                elif batch_status == 3:
                    batch_status = "In Transit"
                elif batch_status == 4:
                    batch_status = "Cancelled"
            else:
                inspected_bags = 0
                batch_status = "N/A"
            
            iar_number = tbl_iar_print_logs.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"])
                ).sort(
                    by="logsId",
                    descending=True
                )[0]

            if not iar_number.is_empty():
                iar_number_str = iar_number.select("iarCode")[0].item()
            else:
                iar_number_str = "N/A"
            

            is_transfer_W = tbl_actual_delivery.filter(
                    pl.col("batchTicketNumber").eq(batch_row["batchTicketNumber"]) &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("W")
                ).select(["transferCategory"])[0]

            is_transfer_T = tbl_actual_delivery.filter(
                    pl.col("remarks").str.contains(f"(?i){batch_row['batchTicketNumber']}") &
                    pl.col("is_transferred").eq(1) &
                    pl.col("transferCategory").eq("T")
                ).select(["batchTicketNumber"])[0]

            if not is_transfer_W.is_empty():
                arr_w = get_whole_data(batch_row['batchTicketNumber'], "", batch_row['seedVariety'], batch_row['seedTag'])

                for w_row in arr_w.iter_rows(named=True):
                    if w_row['seedTag'] == batch_row['seedTag']:
                        bt = w_row['batch_num']
                        o_r = w_row['origin']
                        dt = w_row['destination']
                        sv = w_row['seedVariety']
                        st = w_row['seedTag']
                        sdt = w_row['seedType']
                        bg = w_row['bags']
                        dc = w_row['dateCreated']
                        tt = w_row['transferType']

                        if dt != "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv)
                                ).select(["regionName"])[0].item()
                        else:
                            dt_reg = "N/A"
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"

                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"

                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": bt,
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": int(confirmed_bags),
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": datetime.datetime.strptime(dc, "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                            "batch_status": tt,
                            "remarks": label
                        })
                        inspected_bags += int(bg.replace("bag(s)", ""))
            else:
                return_arr.append({
                    "iar_number": iar_number_str,
                    "batchTicketNumber": batch_row["batchTicketNumber"],
                    "coopAccreditation": batch_row["coopAccreditation"],
                    "seedVariety": batch_row["seedVariety"],
                    "dropOffPoint": batch_row["dropOffPoint"],
                    "region": batch_row["region"],
                    "province": batch_row["province"],
                    "municipality": batch_row["municipality"],
                    "seedTag": batch_row["seedTag"],
                    "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                    "confirmed": int(confirmed_bags),
                    "inspected": int(inspected_bags),
                    "deliveryDate": datetime.datetime.strptime(batch_row["deliveryDate"], "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                    "batch_status": batch_status,
                    "remarks": label
                })

            if not is_transfer_T.is_empty():
                arr_t = get_partial_data(batch_row['batchTicketNumber'], batch_row['seedVariety'], batch_row['seedTag'])

                for t_row in arr_t.iter_rows(named=True):
                    if t_row['seedTag'] == batch_row['seedTag']:
                        bt = t_row['batch_num']
                        o_r = t_row['origin']
                        dt = t_row['destination']
                        sv = t_row['seedVariety']
                        st = t_row['seedTag']
                        sdt = t_row['seedType']
                        bg = t_row['bags']
                        dc = t_row['dateCreated']
                        tt = t_row['transferType']
                    
                        if dt!= "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(
                                    pl.col("province").eq(dt_prv) 
                                ).select(["regionName"])[0].item()
                        else:
                            dt_reg = "N/A"
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                        
                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(
                                    pl.col("province").eq(o_r_prv) 
                                ).select(pl.col("regionName").first().alias("regionName"))[0].item()
                        else:
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"
                        
                        is_replacement = tbl_actual_delivery.filter(
                                pl.col("remarks").str.contains(f"(?i)transferred from batch: {batch_row['batchTicketNumber']}") &
                                pl.col("isBuffer").eq(0)
                            )[0]

                        if is_replacement.is_empty():
                            continue

                        return_arr.append({
                            "iar_number": iar_number_str,
                            "batchTicketNumber": bt,
                            "coopAccreditation": batch_row["coopAccreditation"],
                            "seedVariety": batch_row["seedVariety"],
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A" if seed_grower == "" else seed_grower,
                            "confirmed": 0,
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": dc,
                            "batch_status": tt,
                            "remarks": label
                        })
                        inspected_bags += int(bg.replace("bag(s)", ""))

            total_confirmed += confirmed_bags
            total_inspected += inspected_bags    

    return_arr.append({
        "iar_number": "",
        "batchTicketNumber": "",
        "coopAccreditation": "",
        "seedVariety": "",
        "dropOffPoint": "",
        "region": "",
        "province": "",
        "municipality": "",
        "seedTag": "",
        "seed_grower": "TOTAL:",
        "confirmed": total_confirmed,
        "inspected": total_inspected,
        "deliveryDate": "",
        "batch_status": "",
    })
    temp_df = pl.DataFrame(return_arr)
    bep_arr = temp_df.join(iar_details, on="iar_number", how="left")
    
def get_coop_name(coop_accreditation):
    name = tbl_cooperatives.filter(
        pl.col("accreditation_no").eq(coop_accreditation)
    ).select(pl.col("coopName").first().alias("coopName"))[0].item()
    return name

if __name__ == "__main__":
    try:
        # start a timer
        start_time = time.time()
        threading.Thread(target=load_view_coop_deliveries).start()
        threading.Thread(target=load_cooperatives).start()
        threading.Thread(target=load_delivery).start()
        threading.Thread(target=load_actual_delivery).start()
        threading.Thread(target=load_rla_details).start()
        threading.Thread(target=load_delivery_transaction).start()
        threading.Thread(target=load_deliver_status).start()
        threading.Thread(target=load_iar_print_logs).start()
        threading.Thread(target=load_transfer_logs).start()
        threading.Thread(target=load_next_ssn_tbl_actual_delivery).start()
        threading.Thread(target=load_ps_transfer_logs).start()
        threading.Thread(target=load_last_ssn_rla_details).start()
        threading.Thread(target=load_breakdown_buffer).start()
        threading.Thread(target=load_tbl_actual_delivery_breakdown).start()
        threading.Thread(target=load_lib_prv).start()
        threading.Thread(target=load_iar_details).start()

        while threading.active_count() > 1:
            time.sleep(0.005)
            pass
        
        # iar_details.write_csv("iar_details.csv")
        # raise Exception("HARD_STOP")
        # end timer
        _view_coop_delivery = _view_coop_deliveries.filter(pl.col("coopAccreditation").eq(_coop_a)).with_columns(pl.col("deliveryDate").str.strptime(pl.Datetime(time_unit="ms", time_zone=None), format='%Y-%m-%d %H:%M:%S', strict=False).alias("deliveryDate"))
        
        total_confirmed = 0
        total_inspected = 0

        return_arr_dl = []
        return_arr_nrp = []
        return_arr_qgs = []

        for row in _view_coop_delivery.iter_rows(named=True):
            batch = row["batchTicketNumber"]
            seed_variety = row["seedVariety"]
            seed_tag = row["seedTag"]
            coop_accred = row["coopAccreditation"]
            sg_id = row["sg_id"]
            isBuffer = row["isBuffer"]
            seed_distribution_mode = row["seed_distribution_mode"]
            dropOffPoint = row["dropOffPoint"]
            region = row["region"]
            province = row["province"]
            municipality = row["municipality"]
            deliveryDate = row["deliveryDate"]

            bep_delivery = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("seedVariety").eq(seed_variety) & pl.col("seedTag").eq(seed_tag) & pl.col("qrValStart").ne(""))

            # continue to next row if no BEP data found
            if not bep_delivery.is_empty():
                continue
            
            # get string value of the first result of the query
            labNo = seed_tag.split("/")[0]
            seed_grower = tbl_rla_details.filter(pl.col("coopAccreditation").eq(coop_accred) & pl.col("labNo").str.contains(f"(?i){labNo}") & pl.col("sg_id").eq(sg_id)).select(pl.col("sg_name").first().alias("sg_name")).item()

            confirmed_bags = tbl_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("coopAccreditation").eq(coop_accred) & pl.col("seedVariety").eq(seed_variety) & pl.col("seedTag").eq(seed_tag)).select(["totalBagCount"]).sum().item()
            
            check_inspected = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch))[0]

            if not check_inspected.is_empty():
                inspected_bags = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("seedVariety").eq(seed_variety) & pl.col("seedTag").eq(seed_tag) & pl.col("qrValStart").eq("")).select(["totalBagCount"]).sum().item()

                batch_status = tbl_delivery_status.filter(pl.col("batchTicketNumber").eq(batch)).sort(by=["deliveryStatusId"], descending=True).select(pl.col("status").first().alias("status"))[0].item()

                if batch_status == 0:
                    batch_status = "Pending"
                elif batch_status == 1:
                    batch_status = "Passed"
                elif batch_status == 2:
                    batch_status = "Rejected"
                elif batch_status == 3:
                    batch_status = "In Transit"
                elif batch_status == 4:
                    batch_status = "Cancelled"
            else:
                inspected_bags = 0
                batch_status = "N/A"
            
            iar_number = tbl_iar_print_logs.filter(pl.col("batchTicketNumber").eq(batch)).sort(by=["logsId"], descending=True)[0]

            if not iar_number.is_empty():
                iar_number_str = iar_number["iarCode"].item()
            else:
                iar_number_str = "N/A"
            
            if isBuffer == 1:
                is_replacement = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("isBuffer").eq(0))[0]
                label = "Replacement"
            else:
                label = ""

            is_transfer_W = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(batch) & pl.col("is_transferred").eq(1) & pl.col("transferCategory").eq("W")).select(["transferCategory"])[0]
            is_transfer_T = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){batch}") & pl.col("is_transferred").eq(1) & pl.col("transferCategory").eq("T")).select(["batchTicketNumber"])[0]
            is_transfer_N = pl.DataFrame()

            whole_count = len(is_transfer_W)
            partial_count = len(is_transfer_T)
            nxt_season = len(is_transfer_N)

            if whole_count > 0:
                arr = get_whole_data(batch, "", seed_variety, seed_tag)
                
                for r_row in arr.iter_rows(named=True):
                    if row['seedTag'] == seed_tag:
                        bt = r_row['batch_num']
                        o_r = r_row['origin']
                        dt = r_row['destination']
                        sv = r_row['seedVariety']
                        st = r_row['seedTag']
                        sdt = r_row['seedType']
                        bg = r_row['bags']
                        dc = r_row['dateCreated']
                        tt = r_row['transferType']

                        if dt != "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(pl.col("province").eq(dt_prv)).select(["regionName"])[0].item()
                        else:
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                            dt_reg = "N/A"

                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(pl.col("province").eq(o_r_prv)).select(["regionName"])[0].item()
                        else:
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                            o_r_dop = "N/A"
                            o_r_reg = "N/A"
                        
                        batch_data = {
                            "iar_number": iar_number_str,
                            "batch_ticket_number": bt,
                            "coopAcreditation": coop_accred,
                            "seedVariety": seed_variety,
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": seed_grower if seed_grower else "N/A",
                            "confirmed": confirmed_bags,
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": datetime.datetime.strptime(dc, "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                            "batchStatus": tt,
                            "remarks": label,
                            "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                        }

                        if seed_distribution_mode == 'NRP':
                            return_arr_nrp.append(batch_data)
                        elif seed_distribution_mode == 'Good Quality Seeds':
                            return_arr_qgs.append(batch_data)
                        else:
                            return_arr_dl.append(batch_data)
                        
                        inspected_bags += int(bg.replace("bag(s)", ""))
            else:
                batch_data = {
                    "iar_number": iar_number_str,
                    "batch_ticket_number": batch,
                    "coopAcreditation": coop_accred,
                    "seedVariety": seed_variety,
                    "dropOffPoint": dropOffPoint,
                    "region": region,
                    "province": province,
                    "municipality": municipality,
                    "seedTag": seed_tag,
                    "seed_grower": seed_grower if seed_grower else "N/A",
                    "confirmed": confirmed_bags,
                    "inspected": int(inspected_bags),
                    "deliveryDate": datetime.datetime.strptime(deliveryDate.strftime("%Y-%m-%d %H:%M:%S"), "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                    "batchStatus": batch_status,
                    "remarks": label,
                    "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                }

                if seed_distribution_mode == 'NRP':
                    return_arr_nrp.append(batch_data)
                elif seed_distribution_mode == 'Good Quality Seeds':
                    return_arr_qgs.append(batch_data)
                else:
                    return_arr_dl.append(batch_data)

            if partial_count > 0:
                arr = get_partial_data(batch, seed_variety, seed_tag)

                for r_row in arr.iter_rows(named=True):
                    bt = r_row['batch_num']
                    o_r = r_row['origin']
                    dt = r_row['destination']
                    sv = r_row['seedVariety']
                    st = r_row['seedTag']
                    sdt = r_row['seedType']
                    bg = r_row['bags']
                    dc = r_row['dateCreated']
                    tt = r_row['transferType']
                    
                    
                    if dt != "":
                        dt = dt.replace(",", "|")
                        dt = dt.replace("->", "|")
                        dt_split = dt.split("|")
                        dt_prv = dt_split[0]
                        dt_mun = dt_split[1]
                        dt_dop = dt_split[2]
                        dt_reg = lib_prv.filter(pl.col("province").eq(dt_prv)).select(["regionName"])[0].item()
                    else:
                        dt_prv = "N/A"
                        dt_mun = "N/A"
                        dt_dop = "N/A"
                        dt_reg = "N/A"
                    
                    if o_r != " NO INFO ON LOGS":
                        o_r = o_r.replace(",", "|")
                        o_r = o_r.replace("->", "|")
                        o_r_split = o_r.split("|")
                        o_r_prv = o_r_split[0]
                        o_r_mun = o_r_split[1]
                        o_r_dop = o_r_split[2]
                        o_r_reg = lib_prv.filter(pl.col("province").eq(o_r_prv)).select(pl.col("regionName").first().alias("regionName"))[0].item()
                    else:
                        o_r_prv = "N/A"
                        o_r_mun = "N/A"
                        o_r_dop = "N/A"
                        o_r_reg = "N/A"
                    
                    if isBuffer == 1:
                        is_replacement = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){batch}") & pl.col("isBuffer").eq(0))[0]
                        label = "Replacement"
                    else:
                        label = ""
                    
                    batch_data = {
                        "iar_number": iar_number_str,
                        "batch_ticket_number": bt,
                        "coopAcreditation": coop_accred,
                        "seedVariety": seed_variety,
                        "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                        "region": f"{o_r_reg} => {dt_reg}",
                        "province": f"{o_r_prv} => {dt_prv}",
                        "municipality": f"{o_r_mun} => {dt_mun}",
                        "seedTag": st,
                        "seed_grower": seed_grower if seed_grower else "N/A",
                        "confirmed": 0,
                        "inspected": int(bg.replace("bag(s)", "")),
                        "deliveryDate": datetime.datetime.strptime(f"{dc} 00:00:00" if len(dc) <= 10 else f"{dc}", "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                        "batchStatus": tt,
                        "remarks": label,
                        "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                    }

                    if seed_distribution_mode == 'NRP':
                        return_arr_nrp.append(batch_data)
                    elif seed_distribution_mode == 'Good Quality Seeds':
                        return_arr_qgs.append(batch_data)
                    else:
                        return_arr_dl.append(batch_data)
                    
                    inspected_bags += int(bg.replace("bag(s)", ""))
                    
                    retransferred = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){bt}") & pl.col("seedTag").eq(st))

                    if not retransferred.is_empty():
                        for par_row in retransferred.iter_rows(named=True):
                            if par_row['transferCategory'] == "T":
                                tt = "PARTIAL RE TRANSFER"
                            elif par_row['transferCategory'] == "W":
                                tt = "WHOLE RE TRANSFER"
                            else:
                                tt = ""
                                
                            batch_data = {
                                "iar_number": '-',
                                "batch_ticket_number": par_row['batchTicketNumber'],
                                "coopAcreditation": coop_accred,
                                "seedVariety": par_row['seedVariety'],
                                "dropOffPoint": f"{dt_dop} => {par_row['dropOffPoint']}",
                                "region": f"{dt_reg} => {par_row['region']}",
                                "province": f"{dt_prv} => {par_row['province']}",
                                "municipality": f"{dt_mun} => {par_row['municipality']}",
                                "seedTag": st,
                                "seed_grower": "N/A",
                                "confirmed": 0,
                                "inspected": par_row['totalBagCount'],
                                "deliveryDate": datetime.datetime.strptime(f"{par_row['dateCreated']} 00:00:00", "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                                "batchStatus": tt,
                                "remarks": "",
                                "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                            }

                            if seed_distribution_mode == 'NRP':
                                return_arr_nrp.append(batch_data)
                            elif seed_distribution_mode == 'Good Quality Seeds':
                                return_arr_qgs.append(batch_data)
                            else:
                                return_arr_dl.append(batch_data)
                            
                            inspected_bags += int(bg.replace("bag(s)", ""))

            if nxt_season > 0:
                arr = get_next_season_data(batch, seed_variety, seed_tag)

                for n_row in arr.iter_rows(named=True):
                    bt = n_row['batch_num']
                    o_r = n_row['origin']
                    dt = n_row['destination']
                    sv = n_row['seedVariety']
                    st = n_row['seedTag']
                    sdt = n_row['seedType']
                    bg = n_row['bags']
                    dc = n_row['dateCreated']
                    tt = n_row['transferType']

                    if dt != "":
                        dt = dt.replace(",", "|")
                        dt = dt.replace("->", "|")
                        dt_split = dt.split("|")
                        dt_prv = dt_split[0]
                        dt_mun = dt_split[1]
                        dt_dop = dt_split[2]
                        dt_reg = lib_prv.filter(pl.col("province").eq(dt_prv)).select(["regionName"])[0].item()
                    else:
                        dt_prv = "N/A"
                        dt_mun = "N/A"
                        dt_dop = "N/A"
                        dt_reg = "N/A"
                    
                    if o_r != " NO INFO ON LOGS":
                        o_r = o_r.replace(",", "|")
                        o_r = o_r.replace("->", "|")
                        o_r_split = o_r.split("|")
                        o_r_prv = o_r_split[0]
                        o_r_mun = o_r_split[1]
                        o_r_dop = o_r_split[2]
                        o_r_reg = lib_prv.filter(pl.col("province").eq(o_r_prv)).select(["regionName"])[0].item()
                    else:
                        o_r_prv = "N/A"
                        o_r_mun = "N/A"
                        o_r_dop = "N/A"
                        o_r_reg = "N/A"
                    
                    label = ""

                    batch_data = {
                        "iar_number": iar_number_str,
                        "batch_ticket_number": bt,
                        "coopAcreditation": coop_accred,
                        "seedVariety": seed_variety,
                        "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                        "region": f"{o_r_reg} => {dt_reg}",
                        "province": f"{o_r_prv} => {dt_prv}",
                        "municipality": f"{o_r_mun} => {dt_mun}",
                        "seedTag": st,
                        "seed_grower": seed_grower if seed_grower else "N/A",
                        "confirmed": confirmed_bags,
                        "inspected": int(bg.replace("bag(s)", "")),
                        "deliveryDate": datetime.datetime.strptime(dc, "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                        "batchStatus": tt,
                        "remarks": label,
                        "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                    }

                    if seed_distribution_mode == 'NRP':
                        return_arr_nrp.append(batch_data)
                    elif seed_distribution_mode == 'Good Quality Seeds':
                        return_arr_qgs.append(batch_data)
                    else:
                        return_arr_dl.append(batch_data)
                    
                    inspected_bags += int(bg.replace("bag(s)", ""))
            total_confirmed += confirmed_bags
            total_inspected += inspected_bags   
        
        prev_batch = ps_transfer_logs.filter(pl.col("coop_accreditation").eq(coop_accred)).group_by(["new_batch_number"]).agg(pl.col("new_batch_number").first().alias("new_batch_number_"), pl.col("batch_number").first().alias("batch_number"), pl.col("date_created").first().alias("date_created"))

        for prev_row in prev_batch.iter_rows(named=True):
            ls_batchNumber = prev_row['batch_number']
            new_batch_number = prev_row['new_batch_number_']
            deliveryDate = prev_row['date_created']

            actualList = tbl_actual_delivery.filter(pl.col("batchTicketNumber").eq(new_batch_number) & pl.col("remarks").str.contains(f"(?i){ls_batchNumber}"))

            for actual_row in actualList.iter_rows(named=True):
                inspected = 0
                inspected = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i){ls_batchNumber}") & pl.col("batchTicketNumber").eq(new_batch_number) & pl.col("seedTag").eq(actual_row['seedTag']) & pl.col("transferCategory").eq("P") & pl.col("is_transferred").eq(1)).select(["totalBagCount"]).sum().item()
                
                checkIfPartial = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i)transferred from batch: {new_batch_number}") & pl.col("seedTag").eq(actual_row['seedTag']) & pl.col("transferCategory").eq("T") & pl.col("is_transferred").eq(1))[0]
                checkIfWhole = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i)transferred from previous season batch: {ls_batchNumber}") & pl.col("batchTicketNumber").eq(new_batch_number) & pl.col("seedTag").eq(actual_row['seedTag']) & pl.col("transferCategory").eq("W") & pl.col("is_transferred").eq(1))[0]

                if not checkIfWhole.is_empty():
                    inspected += tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i)transferred from previous season batch: {ls_batchNumber}") & pl.col("batchTicketNumber").eq(new_batch_number) & pl.col("seedTag").eq(actual_row['seedTag']) & pl.col("transferCategory").eq("W") & pl.col("is_transferred").eq(1)).select(["totalBagCount"]).sum().item()

                if not checkIfPartial.is_empty():
                    inspected = tbl_actual_delivery.filter(pl.col("remarks").str.contains(f"(?i)transferred from batch: {new_batch_number}") & pl.col("seedTag").eq(actual_row['seedTag']) & pl.col("transferCategory").eq("T") & pl.col("is_transferred").eq(1)).select(["totalBagCount"]).sum().item()
                
                labLot = actual_row['seedTag'].split("/")
                sg = last_ssn_rla_details.filter(pl.col("labNo").eq(labLot[0]) & pl.col("lotNo").eq(labLot[1]) & pl.col("coopAccreditation").eq(coop_accred))[0]

                if not sg.is_empty():
                    sg = sg.select(pl.col("sg_name").first().alias("sg_name"))[0].item()
                else:
                    sg = "N/A"
                
                batch_data = {
                    "iar_number": f"Previous Season: {ls_batchNumber}",
                    "batch_ticket_number": new_batch_number,
                    "coopAcreditation": coop_accred,
                    "seedVariety": actual_row['seedVariety'],
                    "dropOffPoint": actual_row['dropOffPoint'],
                    "region": actual_row['region'],
                    "province": actual_row['province'],
                    "municipality": actual_row['municipality'],
                    "seedTag": actual_row['seedTag'],
                    "seed_grower": sg,
                    "confirmed": 0,
                    "inspected": inspected,
                    "deliveryDate": datetime.datetime.strptime(f"{deliveryDate}", "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                    "batchStatus": "",
                    "remarks": "Transferred from Previous Season",
                    "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                }
                
                if seed_distribution_mode == 'NRP':
                    return_arr_nrp.append(batch_data)
                elif seed_distribution_mode == 'Good Quality Seeds':
                    return_arr_qgs.append(batch_data)
                else:
                    return_arr_dl.append(batch_data)
                
                if not checkIfWhole.is_empty():
                    arr_w = get_whole_data(ls_batchNumber, new_batch_number, actual_row["seedVariety"], actual_row['seedTag'])

                    for w_row in arr_w.iter_rows(named=True):
                        bt = w_row['batch_num']
                        o_r = w_row['origin']
                        dt = w_row['destination']
                        sv = w_row['seedVariety']
                        st = w_row['seedTag']
                        sdt = w_row['seedType']
                        bg = w_row['bags']
                        dc = w_row['dateCreated']
                        tt = w_row['transferType']

                        if dt != "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(pl.col("province").eq(dt_prv)).select(["regionName"])[0].item()
                        else:
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                            dt_reg = "N/A"
                        
                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_dop = o_r_split[0]
                            o_r_reg = o_r_split[1]
                            o_r_prv = lib_prv.filter(pl.col("province").eq(o_r_dop)).select(["provinceName"])[0].item()
                        else:
                            o_r_dop = "N/A"
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"
                        
                        batch_data = {
                            "iar_number": "Previous Season",
                            "batch_ticket_number": ls_batchNumber,
                            "coopAcreditation": coop_accred,
                            "seedVariety": sv,
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A",
                            "confirmed": 0,
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": datetime.datetime.strptime(f"{dc}", "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                            "batchStatus": tt,
                            "remarks": "Transferred from Previous Season",
                            "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                        }

                        if seed_distribution_mode == 'NRP':
                            return_arr_nrp.append(batch_data)
                        elif seed_distribution_mode == 'Good Quality Seeds':
                            return_arr_qgs.append(batch_data)
                        else:
                            return_arr_dl.append(batch_data)
                
                if not checkIfPartial.is_empty():
                    arr_t = get_partial_data(new_batch_number, actual_row["seedVariety"], actual_row['seedTag'])

                    for t_row in arr_t.iter_rows(named=True):
                        bt = t_row['batch_num']
                        o_r = t_row['origin']
                        dt = t_row['destination']
                        sv = t_row['seedVariety']
                        st = t_row['seedTag']
                        sdt = t_row['seedType']
                        bg = t_row['bags']
                        dc = t_row['dateCreated']
                        tt = t_row['transferType']

                        if dt != "":
                            dt = dt.replace(",", "|")
                            dt = dt.replace("->", "|")
                            dt_split = dt.split("|")
                            dt_prv = dt_split[0]
                            dt_mun = dt_split[1]
                            dt_dop = dt_split[2]
                            dt_reg = lib_prv.filter(pl.col("province").eq(dt_prv)).select(["regionName"])[0].item()
                        else:
                            dt_prv = "N/A"
                            dt_mun = "N/A"
                            dt_dop = "N/A"
                            dt_reg = "N/A"

                        if o_r != " NO INFO ON LOGS":
                            o_r = o_r.replace(",", "|")
                            o_r = o_r.replace("->", "|")
                            o_r_split = o_r.split("|")
                            o_r_prv = o_r_split[0]
                            o_r_mun = o_r_split[1]
                            o_r_dop = o_r_split[2]
                            o_r_reg = lib_prv.filter(pl.col("province").eq(o_r_dop)).select(pl.col("regionName").first().alias("regionName"))[0].item()
                        else:
                            o_r_dop = "N/A"
                            o_r_reg = "N/A"
                            o_r_prv = "N/A"
                            o_r_mun = "N/A"

                        batch_data = {
                            "iar_number": "Previous Season",
                            "batch_ticket_number": ls_batchNumber,
                            "coopAcreditation": coop_accred,
                            "seedVariety": sv,
                            "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                            "region": f"{o_r_reg} => {dt_reg}",
                            "province": f"{o_r_prv} => {dt_prv}",
                            "municipality": f"{o_r_mun} => {dt_mun}",
                            "seedTag": st,
                            "seed_grower": "N/A",
                            "confirmed": 0,
                            "inspected": int(bg.replace("bag(s)", "")),
                            "deliveryDate": datetime.datetime.strptime(f"{dc}", "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                            "batchStatus": tt,
                            "remarks": "Transferred from Previous Season",
                            "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                        }    

                        if seed_distribution_mode == 'NRP':
                            return_arr_nrp.append(batch_data)
                        elif seed_distribution_mode == 'Good Quality Seeds':
                            return_arr_qgs.append(batch_data)
                        else:
                            return_arr_dl.append(batch_data)
                        
                        inspected += int(bg.replace("bag(s)", ""))

                        retransferred = tbl_actual_delivery.filter(
                            pl.col("remarks").str.contains(f"(?i){bt}") &
                            pl.col("seedTag").eq(st)
                            )

                        if not retransferred.is_empty():
                            for re_row in retransferred.iter_rows(named=True):
                                
                                if re_row["transferCategory"] == "T":
                                    tt = "PARTIAL RE TRANSFER"
                                elif re_row["transferCategory"] == "W":
                                    tt = "WHOLE RE TRANSFER"
                                else:
                                    tt = ""
                                
                                batch_data = {
                                    "iar_number": "Previous Season",
                                    "batch_ticket_number": ls_batchNumber,
                                    "coopAcreditation": coop_accred,
                                    "seedVariety": sv,
                                    "dropOffPoint": f"{o_r_dop} => {dt_dop}",
                                    "region": f"{o_r_reg} => {dt_reg}",
                                    "province": f"{o_r_prv} => {dt_prv}",
                                    "municipality": f"{o_r_mun} => {dt_mun}",
                                    "seedTag": st,
                                    "seed_grower": "N/A",
                                    "confirmed": "0",
                                    "inspected": int(bg.replace("bag(s)", "")),
                                    "deliveryDate": datetime.datetime.strptime(re_row["dateCreated"], "%Y-%m-%d %H:%M:%S").strftime("%Y-%m-%d"),
                                    "batchStatus": tt,
                                    "remarks": "Transferred from Previous Season",
                                    "category": 'SEED RESERVE' if seed_distribution_mode == 'NRP' else seed_distribution_mode
                                }

                                if seed_distribution_mode == 'NRP':
                                    return_arr_nrp.append(batch_data)
                                elif seed_distribution_mode == 'Good Quality Seeds':
                                    return_arr_qgs.append(batch_data)
                                else:
                                    return_arr_dl.append(batch_data)
                                
                                inspected += int(bg.replace("bag(s)", ""))
                
                total_inspected += inspected
            
        last_row = {
            "iar_number": "",
            "batchTicketNumber": "",
            "coopAcreditation": "",
            "seedVariety": "",
            "dropOffPoint": "",
            "region": "",
            "province": "",
            "municipality": "",
            "seedTag": "",
            "seed_grower": "TOTAL: ",
            "confirmed": int(total_confirmed),
            "inspected": int(total_inspected),
            "deliveryDate": "",
            "batchStatus": ""
        }
        
        def to_dl_df():
            global return_df
            return_df = pl.DataFrame(return_arr_dl)
            if not return_df.is_empty():
                return_df = return_df.join(iar_details, on=['iar_number'], how='left')

        def to_nrp_df():
            global return_df_nrp
            return_df_nrp = pl.DataFrame(return_arr_nrp)
            if not return_df_nrp.is_empty():
                return_df_nrp = return_df_nrp.join(iar_details, on=['iar_number'], how='left')

        def to_qgs_df():
            global return_df_qgs
            return_df_qgs = pl.DataFrame(return_arr_qgs)
            if not return_df_qgs.is_empty():
                return_df_qgs = return_df_qgs.join(iar_details, on=['iar_number'], how='left')


        threading.Thread(target=get_replacement_list, args=[coop_accred]).start()
        threading.Thread(target=get_buffer_list, args=[coop_accred]).start()
        threading.Thread(target=get_bep_list, args=[coop_accred]).start()
        threading.Thread(target=to_dl_df).start()
        threading.Thread(target=to_nrp_df).start()
        threading.Thread(target=to_qgs_df).start()

        while threading.active_count() > 1:
            time.sleep(0.005)
            pass

        coopName = get_coop_name(coop_accred)
        
        # get current date and time
        current_time = datetime.datetime.now().strftime("%Y-%m-%d_%H-%M-%S")

        with Workbook(f"{coopName}_{current_time}.xlsx") as wb:
            return_df.write_excel(workbook=wb, worksheet="DELIVERY_LIST", autofit=True, table_style='Table Style Light 11')
            return_df_nrp.write_excel(workbook=wb, worksheet="DELIVERY_LIST_SEED_RESERVE", autofit=True, table_style='Table Style Light 11')
            return_df_qgs.write_excel(workbook=wb, worksheet="DELIVERY_LIST_GQS", autofit=True, table_style='Table Style Light 11')
            replacement_arr.write_excel(workbook=wb, worksheet="REPLACEMENT_LIST", autofit=True, table_style='Table Style Light 11')
            buffer_arr.write_excel(workbook=wb, worksheet="BUFFER_LIST", autofit=True, table_style='Table Style Light 11')
            bep_arr.write_excel(workbook=wb, worksheet="BINHI_E_PADALA", autofit=True, table_style='Table Style Light 11')
            
        print(f"report/home/{coopName}_{current_time}.xlsx")
    except Exception as e:
        print(f"Err: ", e)
        traceback.print_exc()
    finally:
        exit(0)