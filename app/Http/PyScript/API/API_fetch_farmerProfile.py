import polars as pl
import sys
import json
from urllib.parse import quote

uri = "mysql://json:%s@192.168.10.44:3306/information_schema" % quote('Zeijan@13')

database = sys.argv[1]
rsbsa = sys.argv[2]
start_index = sys.argv[3]
area_cap = 10.00
# database = 'ws2025_prv_0231'
# rsbsa = '02-31-01'
# start_index = 1

if __name__ == "__main__":
    profiles = pl.read_database_uri(f"SELECT *, IF(is_new = 9, 1, 0) as is_fca, 0 as inbred_balance, 0 as hybrid_balance, CONCAT('4') as version_list, ROUND(final_area,2) as origin_crop_area, ROUND(final_area,2) as crop_area, final_claimable as origin_total_claimable, final_claimable as rcef_total_bags FROM {database}.farmer_information_final WHERE claiming_prv LIKE '{rsbsa}%' AND rcef_id != '' AND is_new != 2 and id > {start_index} ORDER BY municipality,lastName,firstName,midName", uri)
    area_history_inbred = pl.read_database_uri(f"SELECT *, CONCAT(LEFT(date,7),' - v',version )AS list_version FROM {database}.area_history", uri)
    area_history_hybrid = area_history_inbred.sort('version',descending=True).group_by('db_ref').agg(pl.col('id').first().alias('id_x'), pl.col('db_ref').first().alias('db_ref_x'), pl.col('area').first().alias('area_x'),pl.col('date').first().alias('date_x'),pl.col('version').first().alias('version_x'),pl.col('list_version').first().alias('list_version_x')).select(["id_x", "db_ref_x", "area_x", "date_x", "version_x","list_version_x"]).rename({'id_x':'id', 'db_ref_x':'db_ref', 'area_x':'total_rsbsa_area','date_x': 'date','version_x': 'version','list_version_x':'list_version_hybrid'})
    # released = pl.read_database_uri(f"SELECT * FROM {database}.new_released", uri)
    releasedInbred = pl.read_database_uri(f"SELECT db_ref, SUM(claimed_area) as rcef_area_claimed, SUM(bags_claimed) as rcef_claimed_bags, category FROM {database}.new_released WHERE category LIKE 'INBRED' GROUP BY db_ref", uri)
    releasedHybrid = pl.read_database_uri(f"SELECT db_ref, SUM(claimed_area) as hybrid_area_claimed, SUM(bags_claimed) as hybrid_claimed_bags, category FROM {database}.new_released WHERE category LIKE 'HYBRID' GROUP BY db_ref", uri)

    profilesMerged = area_history_inbred.filter(pl.col('version').eq(1.00)).join(profiles, on=['db_ref'], how='right')
    profilesMerged = area_history_hybrid.select(['db_ref','total_rsbsa_area','list_version_hybrid']).join(profilesMerged, on=['db_ref'], how='right')
    profilesMerged = profilesMerged.with_columns(pl.when((pl.col('list_version').eq(pl.col('list_version_hybrid')))).then(pl.col('list_version')).otherwise((pl.col('list_version')+" / "+pl.col('list_version_hybrid'))).alias('list_version'))
    profilesMerged = profilesMerged.join(releasedInbred, on=['db_ref'], how='left')
    profilesMerged = profilesMerged.with_columns(inbred_balance = (pl.col('rcef_total_bags') - pl.when(pl.col('rcef_claimed_bags').is_null()).then(0).otherwise(pl.col('rcef_claimed_bags'))))
    profilesMerged = profilesMerged.join(releasedHybrid, on=['db_ref'], how='left')
    profilesMerged = profilesMerged.with_columns(hybrid_balance = (pl.col('total_rsbsa_area') - pl.when(pl.col('hybrid_area_claimed').is_null()).then(0).otherwise(pl.col('hybrid_area_claimed'))).round(2))

    filteredProfiles = profilesMerged.select(['id','is_new','is_dq','claiming_prv','claiming_brgy','no_of_parcels','parcel_brgy_info','rsbsa_control_no','db_ref','rcef_id','new_rcef_id','assigned_rsbsa','farmer_id','distributionID','da_intervention_card','lastName','firstName','midName','extName','fullName','sex','birthdate','region','province','municipality','brgy_name','mother_name','spouse','tel_no','geo_code','civil_status','fca_name','is_pwd','is_arb','is_ip','tribe_name','ben_4ps','is_cluster_member','data_source','sync_date','crop_establishment_cs','ecosystem_cs','ecosystem_source_cs','planting_week','final_area','final_claimable','is_claimed','total_claimed','total_claimed_area','is_replacement','replacement_area','replacement_bags','replacement_bags_claimed','replacement_area_claimed','replacement_reason','prev_claimable','prev_final_area','prev_claimed','prev_claimed_area','dq_reason','is_ebinhi','print_count','to_prv_code','inbred_balance','hybrid_balance','version_list','origin_crop_area','crop_area','origin_total_claimable','rcef_total_bags','area','total_rsbsa_area','rcef_area_claimed','rcef_claimed_bags','hybrid_area_claimed','list_version']).with_columns(pl.lit(area_cap).alias('area_cap'))

    filteredProfiles = filteredProfiles.cast({"claiming_brgy" : pl.Utf8, "parcel_brgy_info" : pl.Utf8, "da_intervention_card" : pl.Utf8, "dq_reason" : pl.Utf8, "sync_date": pl.Utf8})
    
    filteredProfiles = filteredProfiles.with_columns(pl.col("total_rsbsa_area").round(2))
    filteredProfiles = filteredProfiles.with_columns(pl.col("area").round(2))
    filteredProfiles = filteredProfiles.sort(['municipality','lastName','firstName','midName'], descending=False).rename({"area": "rcef_area"})

    filteredProfiles = filteredProfiles.with_columns(pl.col("final_area").round(2))
    
    filteredProfiles = filteredProfiles.with_columns(pl.col('rcef_area_claimed').fill_null(0),pl.col('rcef_claimed_bags').fill_null(0),pl.col('hybrid_area_claimed').fill_null(0),pl.col('inbred_balance').fill_null(0),pl.col('hybrid_balance').fill_null(0))
    

    json_string = json.dumps(filteredProfiles.to_dicts())
    print(json_string)