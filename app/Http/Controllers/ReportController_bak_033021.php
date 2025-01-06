<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Schema;

use App\SeedCooperatives;
use App\SeedGrowers;

use Config;
use DB;
use Excel;

use Session;
use Auth;

class ReportController extends Controller
{
    public function Home(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.home')->with('d_provinces', $d_provinces);
    }

    public function Home_scheduled(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.schedule.report')->with('d_provinces', $d_provinces);
    }

    public function set_database($database_name){
        try {
            \Config::set('database.connections.reports_db.database', $database_name);
            DB::purge('reports_db');

            DB::connection('reports_db')->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }
	
	/**
     * MIRROR DATABASE AUTO-UPDATE
     */
    function lib_dropoff_point_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->get(); 
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;

        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `LIB_DROPOFF_POINT` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "coop_accreditation" => $row->coop_accreditation,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "prv" => $row->prv,
                        "is_active" => $row->is_active,
                        "date_created" => $row->date_created,
                        "dateUpdated" => $row->dateUpdated,
                        "created_by" => $row->created_by
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: prv_dropoff_id = $row->prv_dropoff_id, province: $row->province, municipality: $row->municipality, dropoffPoint: $row->dropOffPoint<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->prv_dropoff_id)<br>";
                }
            }

            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `LIB_DROPOFF_POINT` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";

        }else{
            echo "<strong>NO DATA IN DATABASE: `LIB_DROPOFF_POINT`</strong><br><br>";
        }
    }

    function lib_prv_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_prv')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `LIB_PRV` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "region" => $row->region,
                        "regCode" => $row->regCode,
                        "provCode" => $row->provCode,
                        "munCode" => $row->munCode,
                        "regionName" => $row->regionName,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "prv" => $row->prv,
                        "dateCreated" => $row->dateCreated,
                        "region_sort" => $row->region_sort,
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_prv')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: prv = $row->prv, region = $row->regionName, province: $row->province, municipality: $row->municipality<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->prv)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `LIB_PRV` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `LIB_PRV`</strong><br><br>";
        }
    }

    function tbl_actual_delivery_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_ACTUAL_DELIVERY` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "seedVariety" => $row->seedVariety,
                        "totalBagCount" => $row->totalBagCount,
                        "send" => $row->send,
                        "seedTag" => $row->seedTag,
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "prv" => $row->prv,
                        "moa_number" => $row->moa_number,
                        "app_version" => $row->app_version,
                        "batchSeries" => $row->batchSeries,
                        "date_modified" => $row->date_modified,
                        "remarks" => $row->remarks,
                        "isRejected" => $row->isRejected,
                        "is_transferred" => $row->is_transferred
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, province = $row->province, municipality: $row->municipality, totalBagCount: $row->totalBagCount<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->batchTicketNumber)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_ACTUAL_DELIVERY` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_ACTUAL_DELIVERY`</strong><br><br>";
        }
    }

    function tbl_delivery_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_DELIVERY` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "ticketNumber" => $row->ticketNumber,
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "coopAccreditation" => $row->coopAccreditation,
                        "sgAccreditation" => $row->sgAccreditation,
                        "seedTag" => $row->seedTag,
                        "seedVariety" => $row->seedVariety,
                        "seedClass" => $row->seedClass,
                        "totalWeight" => $row->totalWeight,
                        "weightPerBag" => $row->weightPerBag,
                        "totalBagCount" => $row->totalBagCount,
                        "deliveryDate" => $row->deliveryDate,
                        "deliverTo" => $row->deliverTo,
                        "coordinates" => $row->coordinates,
                        "status" => $row->status,
                        "inspectorAllocated" => $row->inspectorAllocated,
                        "userId" => $row->userId,
                        "dateCreated" => $row->dateCreated,
                        "oldTicketNumber" => $row->oldTicketNumber,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "prv" => $row->prv,
                        "moa_number" => $row->moa_number,
                        "app_version" => $row->app_version,
                        "batchSeries" => $row->batchSeries,
                        "is_cancelled" => $row->is_cancelled,
                        "cancelled_by" => $row->cancelled_by,
                        "reason" => $row->reason,
                        "date_updated" => $row->date_updated,
                        "sg_id" => $row->sg_id
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, province = $row->province, municipality: $row->municipality, totalBagCount: $row->totalBagCount<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->batchTicketNumber)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_DELIVERY` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_DELIVERY`</strong><br><br>";
        }
    }

    function tbl_delivery_status_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_status')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery_status')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_DELIVERY_STATUS` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "status" => $row->status,
                        "dateCreated" => $row->dateCreated,
                        "send" => $row->send
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery_status')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, status = $row->status, dateCreated = $row->dateCreated<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data (batchTicketNumber = $row->batchTicketNumber, status = $row->status)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_DELIVERY_STATUS` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_DELIVERY_STATUS`</strong><br><br>";
        }
    }

    public function execute_mirror_db(){
        DB::connection('mysql')->table('lib_logs')
        ->insert([
            'category' => 'DELIVERY_DATA_MIRROR',
            'description' => 'Succedssful update of mirror database (rcep_delivery_inspection)',
            'author' => 'SYSTEM',
            'ip_address' => 'LOCAL'
        ]);
        
        $this->lib_dropoff_point_mirror();
        $this->lib_prv_mirror();
        $this->tbl_actual_delivery_mirror();
        $this->tbl_delivery_mirror();
        $this->tbl_delivery_status_mirror();
    }
    /**
     * MIRROR DATABASE AUTO-UPDATE
     */

    function generate_all_data(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get();

        foreach($municipalities as $municipality_row){
            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
			$municipal_yield = 0;

            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);
            $prv_dist_data = DB::table($database.".released")->first();
			$farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    ->get();

                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->orderBy('farmerID')
                        ->first();
                        
                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
						$yield = 0;
						if($farmer_profile->yield <= 5 && $farmer_profile->yield != 0){
							$yield = $farmer_profile->yield * 20;
						}else{
							$yield = $farmer_profile->yield;
						}
						
						if($yield > 0 && $farmer_profile->actual_area > 0){
							if($yield < 50 || $yield > 120){
								$yield = $yield / $farmer_profile->actual_area;
							}else{
								$yield = $yield;
							}
							$farmer_dividend += 1;
							
						}else{
							$yield = $farmer_profile->yield;
						}
						
						$municipal_yield += $yield;
						
                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
						$municipal_yield += 0;
                    }
                }
            }
			
			if($municipal_yield > 0){
				$municipal_yield = $municipal_yield / $farmer_dividend;
			}

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
				'yield'             => $municipal_yield,
				'farmers_with_yield'=> $farmer_dividend,
                'date_generated'    => date("Y-m-d H:i:s")
            ]);
        }

        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
				DB::raw('SUM(yield) as total_yield'),
				DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'))
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        foreach($provinces as $p_row){

            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('province', $p_row->province)
                ->where('total_bags', '!=', 0)
                ->count();
				
			$total_municipalities_with_yield= DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('province', $p_row->province)
				->where('yield', '!=', 0)
                ->where('total_bags', '!=', 0)
                ->get();
			
			if($p_row->total_farmers_with_yield > 0){
				//$total_yield =  $p_row->total_yield / $p_row->total_farmers_with_yield;
				$total_yield =  $p_row->total_yield / count($total_municipalities_with_yield);
			}else{
				$total_yield = 0;
			}			
			

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
				'yield'             => $total_yield,
				'farmers_with_yield'=> $p_row->total_farmers_with_yield,
				'total_yield_of_municipalities' => $p_row->total_yield,
                'date_generated'    => date("Y-m-d H:i:s")
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
				DB::raw('SUM(yield) as total_yield'),
				DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'))
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        ##national data variables
        $ntl_region = 0;
        $ntl_province = 0;
        $ntl_municipalities = 0;
        $ntl_farmers = 0;
        $ntl_bags = 0;
        $ntl_dist_area = 0;
        $ntl_actual_area = 0;
        $ntl_male = 0;
        $ntl_female = 0;
		$ntl_yield = 0;

        foreach($regions as $r_row){

            if($r_row->total_farmers > 0){ ++$ntl_region; }

            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->count();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->groupBy('province')
                ->count();

			$total_yield_of_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->where('region', $r_row->region)->sum('yield');
			$total_provinces_with_yield = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('yield', '!=', 0)
                ->groupBy('province')
                ->get();
				
			if($r_row->total_farmers_with_yield > 0){
				//$total_yield =  $r_row->total_yield / $total_provinces_with_yield;
				$total_yield =  $total_yield_of_provinces / count($total_provinces_with_yield);
			}else{
				$total_yield = 0;
			}
				
            $ntl_province += $total_provinces;
            $ntl_municipalities += $total_municipalities;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => $total_provinces,
                'total_municipalities' => $total_municipalities,
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
				'yield'             => $total_yield,
				'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s")
            ]);

            $ntl_farmers += $r_row->total_farmers;
            $ntl_bags += $r_row->total_bags;
            $ntl_dist_area += $r_row->total_dist_area;
            $ntl_actual_area += $r_row->total_actual_area;
            $ntl_male += $r_row->total_male;
            $ntl_female += $r_row->total_female;
			$ntl_yield += $r_row->total_yield;
        }

        //after saving region data save national
		$total_yield_of_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->sum('yield');
		$total_regions_with_yield = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->where('yield', '!=', 0)->get();
		$total_yield =  $total_yield_of_regions / count($total_regions_with_yield);
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'               => $ntl_region,
            'provinces'      => $ntl_province,
            'municipalities' => $ntl_municipalities,
            'total_farmers'     => $ntl_farmers,
            'total_bags'        => $ntl_bags,
            'total_dist_area'   => $ntl_dist_area,
            'total_actual_area' => $ntl_actual_area,
            'total_male'        => $ntl_male,
            'total_female'      => $ntl_female,
			'yield'             => $total_yield,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);

        //after all report data is saved, save data to mirror database
        //$this->mirror_database();
    }

    /**
     * SAVE EXCEL FILE TO SERVER
     */

    function generate_provinceData_sheet($province){
        $province_sheet = array();
        /** PROVINCE SUMMARY DATA */

        //get overall summary for province
        $province_summary = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('province', $province)
            ->first();

        $province_data = [
            'Province Name' => $province,
            'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
            'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
            'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
            'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
            'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
            'Total Male' => (string) number_format($province_summary->total_male),
            'Total Female' => (string) number_format($province_summary->total_female)
        ];
        array_push($province_sheet, $province_data);

        $blank_row = [
            'Province Name' => '',
            'Covered Municipalities' => '',
            'Total Beneficiaries' => '',
            'Total Distribution Area' => '',
            'Total Actual Area' => '',
            'Total Bags Distributed' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($province_sheet, $blank_row);

        return $province_sheet;
    }

    function generate_municipalData_sheet($province){
        $municipal_table = array();
        $municipal_summary = DB::connection('rcep_reports_db')
            ->table('lib_municipal_reports')
            ->where('province', $province)
            ->orderBy('municipality')
            ->get();

        $mun_total_farmers = 0;
        $mun_total_distArea = 0;
        $mun_total_actArea = 0;
        $mun_total_male = 0;
        $mun_total_female = 0;
        $mun_total_bags = 0;

        $mun_cnt = 1;
        foreach($municipal_summary as $m_row){
            $municipal_data = [
                '#' => $mun_cnt,
                'Municipality Name' => $m_row->municipality,
                'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                'Total Male' => (string) number_format($m_row->total_male),
                'Total Female' => (string) number_format($m_row->total_female)
            ];
            array_push($municipal_table, $municipal_data);

            ++$mun_cnt;
            $mun_total_farmers += $m_row->total_farmers;
            $mun_total_distArea += $m_row->total_dist_area;
            $mun_total_actArea += $m_row->total_actual_area;
            $mun_total_male += $m_row->total_male;
            $mun_total_female += $m_row->total_female;
            $mun_total_bags += $m_row->total_bags;
        }

        $total_municipal_data = [
            '#' => '',
            'Municipality Name' => 'TOTAL: ',
            'Total Beneficiaries' => (string) number_format($mun_total_farmers),
            'Total Distribution Area' => (string) number_format($mun_total_distArea),
            'Total Actual Area' => (string) number_format($mun_total_actArea),
            'Total Bags Distributed' => (string) number_format($mun_total_bags),
            'Total Male' => (string) number_format($mun_total_male),
            'Total Female' => (string) number_format($mun_total_female)
        ];
        array_push($municipal_table, $total_municipal_data);

        return $municipal_table;
    }

    function generate_provinceList_sheet($province){
        $table_data = array();

        $province_farmer_list = DB::connection('reports_db')->table("released")
            ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by')
            ->where('released.bags', '!=', '0')
            ->where('released.province', '=', $province)
            ->orderBy('released.province', 'ASC')
            ->get();

        $total_dist_area = 0;
        $total_actual_area = 0;
        $total_bags = 0;

        foreach ($province_farmer_list as  $row) {

            //check other_info table
            $other_info_data = DB::connection('reports_db')->table("other_info")
                ->where('farmer_id', $row->farmer_id)
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->first();

            if(count($other_info_data) > 0){
                $birthdate = $other_info_data->birthdate;
                $mother_fname = $other_info_data->mother_fname;
                $mother_mname = $other_info_data->mother_mname;
                $mother_lname = $other_info_data->mother_lname;
                $mother_suffix = $other_info_data->mother_suffix;

                if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                    $phone_number = "";
                }else{
                    $phone_number = $other_info_data->phone;
                }
            }else{
                $birthdate = '';
                $mother_fname = '';
                $mother_mname = '';
                $mother_lname = '';
                $mother_suffix = '';
                $phone_number = '';
            }

            //get farmer_profile
            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->where('lastName', '!=', '')
                ->where('firstName', '!=', '')
                ->orderBy('farmerID')
                ->first();

            if(count($farmer_profile) > 0){
                $qr_code = $farmer_profile->distributionID;
                $farmer_fname = $farmer_profile->firstName;
                $farmer_mname = $farmer_profile->midName;
                $farmer_lname = $farmer_profile->lastName;
                $farmer_extname = $farmer_profile->extName;
                $dist_area = $farmer_profile->area;
                $actual_area = $farmer_profile->actual_area;
                $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                $total_dist_area += $farmer_profile->area;
                $total_actual_area += $farmer_profile->actual_area;
            }else{
                $qr_code = "N/A";
                $farmer_fname = "N/A";
                $farmer_mname = "N/A";
                $farmer_lname = "N/A";
                $farmer_extname = "N/A";
                $dist_area = 0;
                $actual_area = 0;
                $sex = "N/A";

                $total_dist_area += 0;
                $total_actual_area += 0;
            }

            //get name of encoder using released.by in sdms_db_dev
            $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
            if(count($encoder_name) > 0){
                if($encoder_name->middleName == ''){
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }else{
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }
            }else{
                $encoder_name = '';
            }

            //compute totals
            $total_bags += $row->bags;

            $data = [
                'RSBSA #' => $row->rsbsa_control_no,
                'QR Code' => $qr_code,
                "Farmer's First Name" => $farmer_fname,
                "Farmer's Middle Name" => $farmer_mname,
                "Farmer's Last Name" => $farmer_lname,
                "Farmer's Extension Name" => $farmer_extname,
                'Sex' => $sex,
                'Birth Date' => $birthdate,
                'Telephone Number' => $phone_number,
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                "Mother's First Name" => $mother_fname,
                "Mother's Middle Name" => $mother_mname,
                "Mother's Last Name" => $mother_lname,
                "Mother's Suffix" => $mother_suffix,
                'Distribution Area' => $dist_area,
                'Actual Area' => $actual_area,
                'Bags' => $row->bags,
                'Seed Variety' => $row->seed_variety,
                'Date Released' => $row->date_released,
                'Farmer ID' => $row->farmer_id,
                'Released By' => $encoder_name
            ];
            array_push($table_data, $data);
        }

        $data2 = [
            'RSBSA #' => '',
            'QR Code' => '',
            "Farmer's First Name" => '',
            "Farmer's Middle Name" => '',
            "Farmer's Last Name" => '',
            "Farmer's Extension Name" => '',
            'Sex' => '',
            'Birth Date' => '',
            'Telephone Number' => '',
            'Province' => '',
            'Municipality' => '',
            "Mother's First Name" => '',
            "Mother's Middle Name" => '',
            "Mother's Last Name" => '',
            "Mother's Suffix" => 'TOTAL: ',
            'Distribution Area' => $total_dist_area,
            'Actual Area' => $total_actual_area,
            'Bags' => $total_bags,
            'Seed Variety' => '',
            'Date Released' => '',
            'Farmer ID' => '',
            'Released By' => ''
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    public function generate_excel_server_list(){

        //clear all data in exports folder
        $file = new Filesystem;
        $file->cleanDirectory('public/excel_storage');

        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('region_sort')
            ->get();

        foreach($provinces as $p_row){
            $database = $GLOBALS['season_prefix']."prv_".substr($p_row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    $province_sheet = $this->generate_provinceData_sheet($p_row->province);
                    $municipal_table = $this->generate_municipalData_sheet($p_row->province);
                    $table_data = $this->generate_provinceList_sheet($p_row->province);
                    
                    Excel::create($p_row->province, function($excel) use ($table_data, $province_sheet, $municipal_table) {
                        $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                            $sheet->fromArray($province_sheet);
                            $sheet->fromArray($municipal_table);
                        });
    
                        $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                            $sheet->fromArray($table_data);
                        });
                    })->store('xlsx', public_path('excel_storage'));
                }
            }
        }
    }

    public function generate_excel_server_list_improvement(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('region_sort')
            ->get();

        foreach($provinces as $p_row){
            $database = $GLOBALS['season_prefix']."prv_".substr($p_row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $province_farmer_list = DB::connection('reports_db')->table("released")
                        ->select('released.province', 'released.municipality', 'released.seed_variety', 
                                'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                                'released.farmer_id', 'released.released_by', 'released.release_id')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $p_row->province)
                        ->where('released.is_processed', 1)
                        ->orderBy('released.province', 'ASC')
                        ->get();

                    foreach ($province_farmer_list as  $row) {

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;

                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->where('lastName', '!=', '')
                            ->where('firstName', '!=', '')
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if(count($encoder_name) > 0){
                            if($encoder_name->middleName == ''){
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }else{
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }
                        }else{
                            $encoder_name = '';
                        }


                        $data = [
                            'rsbsa_control_number' => $row->rsbsa_control_no,
                            'qr_code' => $qr_code,
                            "farmer_fname" => $farmer_fname,
                            "farmer_mname" => $farmer_mname,
                            "farmer_lname" => $farmer_lname,
                            "farmer_ext" => $farmer_extname,
                            'sex' => $sex,
                            'birthdate' => $birthdate,
                            'tel_number' => $phone_number,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            "mother_fname" => $mother_fname,
                            "mother_mname" => $mother_mname,
                            "mother_lname" => $mother_lname,
                            "mother_ext" => $mother_suffix,
                            'dist_area' => $dist_area,
                            'actual_area' => $actual_area,
                            'bags' => $row->bags,
                            'seed_variety' => $row->seed_variety,
                            'date_released' => $row->date_released,
                            'farmer_id' => $row->farmer_id,
                            'released_by' => $encoder_name
                        ];
                        DB::table($GLOBALS['season_prefix'].'rcep_excel.beneficiary_list')->insert($data);

                        //after processing to seed beneficiary list DB update is_processed flag to 0
                        DB::table($database.'.released')->where('release_id', $row->release_id)->update([
                            'is_processed' => 0
                        ]);
                    }

                }
            }
        }
    }

	
	function generate_all_data2(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->truncate();

        $region_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->groupBy('regionName')
            ->orderBy('region_sort', 'ASC')
            ->get();

        //national-report variables
        $male_count = 0;
        $female_count = 0;
        $total_farmers_count = 0;
        $total_bags_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_regions = 1;
        $covered_municipalities = 0;
        $covered_provinces = 0;

        foreach($region_list as $row){
            $total_regions += 1;
            //get province list
            $province_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->where('region', $row->regionName)
                ->groupBy('province')
                ->get();

            //regional-report variables
            $region_provinces = 0;
            $region_municipalities = 0;
            $region_farmers = 0;
            $region_bags = 0;
            $region_dis_area = 0;
            $region_actual_area = 0;
            $region_male = 0;
            $region_female = 0;

            foreach($province_list as $prv_list){

                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);
                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->where('region', $prv_list->region)
                    ->where('province', $prv_list->province)
                    ->groupBy('municipality')
                    ->get();

                foreach($municipalities as $municipality_row){
                    $municipal_farmers = 0;
                    $municipal_bags = 0;
                    $municipal_dis_area = 0;
                    $municipal_actual_area = 0;
                    $municipal_male = 0;
                    $municipal_female = 0;

                    $prv_dist_data = DB::table($database.".released")->first();
                    if(count($prv_dist_data) > 0){
                        $m_list = DB::table($database.".released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $municipality_row->province)
                            ->where('released.municipality', '=', $municipality_row->municipality)
                            ->get();

                        foreach($m_list as $municipal_row){
                            $municipal_farmers += 1;
                            $municipal_bags += $municipal_row->bags;

                            $farmer_profile = DB::table($database.".farmer_profile")
                                ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();
                                
                            if(count($farmer_profile) > 0){
                                $municipal_dis_area += $farmer_profile->area;
                                $municipal_actual_area += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $municipal_male += 1;
                                }else{
                                    $municipal_female += 1;
                                }
                            }else{
                                $municipal_dis_area += 0;
                                $municipal_actual_area += 0;
                                $municipal_male += 0;
                                $municipal_female += 0;
                            }
                        }
                    }

                    DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                    ->insert([
                        'province'          => $prv_list->province,
                        'municipality'      => $municipality_row->municipality,
                        'total_farmers'     => $municipal_farmers,
                        'total_bags'        => $municipal_bags,
                        'total_dist_area'   => $municipal_dis_area,
                        'total_actual_area' => $municipal_actual_area,
                        'total_male'        => $municipal_male,
                        'total_female'      => $municipal_female,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

                //province-report variables
                $province_municipalities = 0;
                $province_farmers = 0;
                $province_bags = 0;
                $province_dis_area = 0;
                $province_actual_area = 0;
                $province_male = 0;
                $province_female = 0;

                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $covered_provinces += 1;
                    $region_provinces += 1;
                    
                    //get municipalities
                    $number_of_municipalities = DB::table($database.".released")
                        ->select('released.municipality')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->groupBy('municipality')
                        ->get();

                    $covered_municipalities += count($number_of_municipalities);
                    $region_municipalities += count($number_of_municipalities);
                    $province_municipalities += count($number_of_municipalities);

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::table($database.".released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->get();

                    foreach($report_list as $report_row){
                        $total_farmers_count += 1;
                        $total_bags_count += $report_row->bags;

                        $region_farmers += 1;
                        $region_bags += $report_row->bags;

                        $province_farmers += 1;
                        $province_bags += $report_row->bags;

                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;
                            
                            $region_dis_area += $farmer_profile->area;
                            $region_actual_area += $farmer_profile->actual_area;

                            $province_dis_area += $farmer_profile->area;
                            $province_actual_area += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                                $region_male += 1;
                                $province_male += 1;
                            }else{
                                $female_count += 1;
                                $region_female += 1;
                                $province_female += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;

                            $region_dis_area += 0;
                            $region_actual_area += 0;
                            $region_male += 0;
                            $region_female += 0;

                            $province_dis_area += 0;
                            $province_actual_area += 0;
                            $province_male += 0;
                            $province_female += 0;
                        }
                    }
                }

                DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->region,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $province_municipalities,
                    'total_farmers'     => $province_farmers,
                    'total_bags'        => $province_bags,
                    'total_dist_area'   => $province_dis_area,
                    'total_actual_area' => $province_actual_area,
                    'total_male'        => $province_male,
                    'total_female'      => $province_female,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }

            //insert the data | regional report
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'            => $prv_list->region,
                'total_provinces'   => $region_provinces,
                'total_municipalities' => $region_municipalities,
                'total_farmers'     => $region_farmers,
                'total_bags'        => $region_bags,
                'total_dist_area'   => $region_dis_area,
                'total_actual_area' => $region_actual_area,
                'total_male'        => $region_male,
                'total_female'      => $region_female,
                'date_generated'    => date("Y-m-d H:i:s")
            ]);
        }

        //finally insert the data | national report
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);


        $this->mirror_database();
    }

    function generate_national_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_national_reports')->truncate();
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')->truncate();

        //get all regions
        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort', 'ASC')->get();
        
        //report variables
        $male_count = 0;
        $female_count = 0;
        $total_farmers_count = 0;
        $total_bags_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_regions = 1;

        $covered_municipalities = 0;
        $covered_provinces = 0;

        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //count all regions
            $total_regions += 1;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){
                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        $covered_provinces += 1;

                        //get municipalities
                        $number_of_municipalities = DB::connection('reports_db')->table("released")
                            ->select('released.municipality')
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('municipality')
                            ->get();

                        $covered_municipalities += count($number_of_municipalities);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            if(count($farmer_profile) > 0){
                                $total_dist_area_count += $farmer_profile->area;
                                $total_actual_area_count += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $male_count += 1;
                                }else{
                                    $female_count += 1;
                                }

                            }else{
                                $total_dist_area_count += 0;
                                $total_actual_area_count += 0;
                                $male_count += 0;
                                $female_count += 0;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                        //$total_dist_area_count += $report_res->dist_area;
                        //$total_actual_area_count += $report_res->actual_area;
                    }
                }
            }
        }

        //finally insert the data
        //save region data to database
        DB::connection('rcep_reports_db')->table('lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
		
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
    }

    function generate_regional_data(){

        //empty database
        DB::connection('rcep_reports_db')->table('lib_regional_reports')->truncate();
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->truncate();

        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort')->get();
        
        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //report variables
            $male_count = 0;
            $female_count = 0;
            $total_farmers_count = 0;
            $total_bags_count = 0;
            $total_dist_area_count = 0;
            $total_actual_area_count = 0;

            $total_provinces = 0;
            $total_municipalities = 0;

            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){

                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        //total provinces covered...
                        $province_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('province')
                            ->get();
                        $total_provinces += count($province_count);

                        //total municipalities covered...
                        $mun_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('municipality')
                            ->get();
                        $total_municipalities += count($mun_count);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            if(count($farmer_profile) > 0){
                                $total_dist_area_count += $farmer_profile->area;
                                $total_actual_area_count += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $male_count += 1;
                                }else{
                                    $female_count += 1;
                                }

                            }else{
                                $total_dist_area_count += 0;
                                $total_actual_area_count += 0;
                                $male_count += 0;
                                $female_count += 0;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                    }
                }
            }

            //save region data to database
            DB::connection('rcep_reports_db')->table('lib_regional_reports')
                ->insert([
                    'region'            => $r_prv->regionName,
                    'total_provinces'   => $total_provinces,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => $total_farmers_count,
                    'total_bags'        => $total_bags_count,
                    'total_dist_area'   => $total_dist_area_count,
                    'total_actual_area' => $total_actual_area_count,
                    'total_male'        => $male_count,
                    'total_female'      => $female_count,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
				
			DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->insert([
                    'region'            => $r_prv->regionName,
                    'total_provinces'   => $total_provinces,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => $total_farmers_count,
                    'total_bags'        => $total_bags_count,
                    'total_dist_area'   => $total_dist_area_count,
                    'total_actual_area' => $total_actual_area_count,
                    'total_male'        => $male_count,
                    'total_female'      => $female_count,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            
        }
    }

    function generate_provincial_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_provincial_reports')->truncate();
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')->truncate();
    
        $province_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->groupBy('province')
            ->orderBy('regCode')
            ->orderBy('province', 'DESC')
            ->get();

        foreach($province_prv as $prv_list){
            $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;
                $total_municipalities = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){

                    //total municipalities covered...
                    $mun_count =  DB::connection('reports_db')->table("released")
                        ->groupBy('municipality')
                        ->get();
                    $total_municipalities += count($mun_count);

                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->get();

                    foreach($report_list as $report_row){
                        /*$farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        $total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }*/

                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;
                        }
                    }

                    //save province data to database
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
					
					DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save province data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
					
					DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save province data to database || no database
                DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->regionName,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
				
				DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->regionName,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }

        }
    }

    function generate_municipal_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_municipal_reports')->truncate();
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')->truncate();

        //list of municipalities
        $mun_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->orderBy('regCode')
            ->orderBy('provCode')
            ->orderBy('municipality', 'ASC')
            ->get();

        foreach($mun_prv as $row){
            $database = $GLOBALS['season_prefix']."prv_".substr($row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $row->province)
                            ->where('released.municipality', '=', $row->municipality)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res =  DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->get();

                    foreach($report_list as $report_row){
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        /*$total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }*/

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;
                        }
                    }

                    //save municipal data to database
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
					
					DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save municipal data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
					
					DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save municipal data to database || no database
                DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->insert([
                    'province'          => $row->province,
                    'municipality'      => $row->municipality,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
				
				DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                ->insert([
                    'province'          => $row->province,
                    'municipality'      => $row->municipality,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }
        }
    }

    public function generate_variety_data(){
        DB::connection('rcep_reports_db')->table('lib_variety_report')->truncate();
		DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')->truncate();
		
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();

        //get all prv(s) in the dropoff point list and getting distinct values
        $prv_list = array();
        foreach($drop_off_list as $dop_row){
            $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4);     
        }
        $prv_list = array_unique($prv_list);

        //loop through all prv databases
        foreach($prv_list as $prv_database){
            $table_conn = $this->set_database($prv_database);
            if($table_conn == "Connection Established!"){
                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('SUM(released.bags) as total_seed_bags'),'seed_variety', 'province', 'municipality')
                        ->groupBy('seed_variety', 'municipality')
                        ->get();

                    foreach($variety_res as $v_row){

                        //get region
                        $region_name = DB::connection('delivery_inspection_db')
                            ->table('lib_dropoff_point')
                            ->where('province', $v_row->province)
                            ->groupBy('province')
                            ->value('region');

                        //save to database per total variety of each prv database
                        DB::connection('rcep_reports_db')->table('lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                        ]);
						
						DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')
						->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags
                        ]);
                    }
                }
            }
        }
    }
	
	function mirror_database(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')->truncate();

        $national_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->get();
        foreach($national_report as $n_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
            ->insert([
                'regions' => $n_row->regions,
                'provinces' => $n_row->provinces,
                'municipalities' => $n_row->municipalities,
                'total_farmers' => $n_row->total_farmers,
                'total_bags' => $n_row->total_bags,
                'total_dist_area' => $n_row->total_dist_area,
                'total_actual_area' => $n_row->total_actual_area,
                'total_male' => $n_row->total_male,
                'total_female' => $n_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $regional_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->get();
        foreach($regional_report as $r_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
            ->insert([
                'region' => $r_row->region,
                'total_provinces' => $r_row->total_provinces,
                'total_municipalities' => $r_row->total_municipalities,
                'total_farmers' => $r_row->total_farmers,
                'total_bags' => $r_row->total_bags,
                'total_dist_area' => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male' => $r_row->total_male,
                'total_female' => $r_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $province_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->get();
        foreach($province_report as $p_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
            ->insert([
                'region' => $p_row->region,
                'province' => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers' => $p_row->total_farmers,
                'total_bags' => $p_row->total_bags,
                'total_dist_area' => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male' => $p_row->total_male,
                'total_female' => $p_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $municipal_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->get();
        foreach($municipal_report as $m_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
            ->insert([
                'province' => $m_row->province,
                'municipality' => $m_row->municipality,
                'total_farmers' => $m_row->total_farmers,
                'total_bags' => $m_row->total_bags,
                'total_dist_area' => $m_row->total_dist_area,
                'total_actual_area' => $m_row->total_actual_area,
                'total_male' => $m_row->total_male,
                'total_female' => $m_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }
    }

    //API for auto-insertion of generated report data every 12 MN
    public function scheduledReport(Request $request){
		$this->generate_all_data();
        //$this->generate_national_data();
        //$this->generate_regional_data();
        //$this->generate_provincial_data();
        //$this->generate_municipal_data();
        $this->generate_variety_data();

        DB::connection('mysql')->table('lib_logs')
        ->insert([
            'category' => 'REPORT',
            'description' => 'Pre-Scheduled report generation successfull',
            'author' => 'SYSTEM',
            'ip_address' => 'LOCAL'
        ]);
    }
	
	public function forceUpdateVarities(){
		$this->generate_variety_data();
	}

    public function municipalityDropOff(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $return_str;
    }

    public function dropOffName(Request $request){
        $dropoffs = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->orderBy('dropOffPoint', 'asc')
            ->groupBy('prv_dropoff_id')
            ->get();
        $return_str= '';
        foreach($dropoffs as $dropoff){
            $return_str .= "<option value='$dropoff->prv_dropoff_id'>$dropoff->dropOffPoint</option>";
        }
        return $return_str;
    }

    public function Home_Provincial(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home2')->with('regions', $regions);
    }

    public function Home_Regional(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home3')->with('regions', $regions);
    }

    public function SeedBeneficiariesVarieties(Request $request){
        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }   

        if($table_conn == "established_connection"){
            //generate data table
            return Datatables::of(DB::connection('reports_db')->table("released")
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiariesVarietiesProvincial(Request $request){
        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }   

        if($table_conn == "established_connection"){
            //generate data table
            return Datatables::of(DB::connection('reports_db')->table("released")
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiaries(Request $request){

        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }

        if($table_conn == "established_connection"){
            $male_count = 0;
            $female_count = 0;
            $report_res = DB::connection('reports_db')->table("released")
                ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                        DB::raw('SUM(released.bags) as total_bags'),
                        DB::raw('SUM(farmer_profile.area) as dist_area'),
                        DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                ->join('farmer_profile', function ($table_join) {
                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                })
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->first();

            if(count($report_res) > 0){
                $sex_res = DB::connection('reports_db')->table("released")
                    ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                    ->join('farmer_profile', function ($table_join) {
                        $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                        $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                    })
                    ->where('released.province', '=', $request->province)
                    ->where('released.municipality', '=', $request->municipality)
                    ->where('released.prv_dropoff_id', '=', $request->dropoff)
                    ->groupBy('sex')
                    ->orderBy('sex', 'DESC')
                    ->get();

                foreach($sex_res as $s_row){
                    if($s_row->sex == 'Male'){
                        $male_count = $s_row->sex_count;
                    }elseif($s_row->sex == 'Femal'){
                        $female_count = $s_row->sex_count;
                    }
                }
            }
            
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => number_format($report_res->total_bags),
                'dist_area' => number_format($report_res->dist_area, '2'),
                'actual_area' => number_format($report_res->actual_area, '2'),
                'total_farmers' => number_format($report_res->total_farmers),
                'total_male' => number_format($male_count),
                'total_female' => number_format($female_count)
            );

            return $data_arr;
        }else{
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => '',
                'total_area' => '',
                'total_farmers' => ''
            );

            return $data_arr;
        }

    }

    /* REPORT HEADER */
    public function TotalValues(Request $request){
        $municipalities = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('municipality')
            ->groupBy('municipality')
            ->get()); 
            
        $provinces = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('province')
            ->groupBy('province')
            ->get()); 

        //to get total_beneficiaries, actual area, distribution area & bags_distributed
        $prv_databases = DB::connection('mysql')->table('lib_seasons_data')->get();

        $total_farmers = 0;
        $total_bags = 0;
        $dist_area = 0;
        $actual_area = 0;
        $male_count = 0;
        $female_count = 0;

        foreach($prv_databases as $prv_row){
            $db_name = $GLOBALS['season_prefix']."prv_".$prv_row->prv_code;
            $test = DB::select("SELECT SCHEMA_NAME
                                    FROM INFORMATION_SCHEMA.SCHEMATA
                                WHERE SCHEMA_NAME = '$db_name'");
            if(empty($test)){
                // no prv database found
            }else{
                //DB exists
                \Config::set('database.connections.reports_db.database', $db_name);
                DB::purge('reports_db');

                try{
                    $tbl_check = DB::connection('reports_db')->table("pending_release")->first();
        
                    if(count($tbl_check) > 0){
                        $table_conn = "established_connection";
                    }else{
                        $table_conn = "no_table_found";
                    }
                }catch(\Illuminate\Database\QueryException $ex){
                    $table_conn = "no_table_found";
                }

                if($table_conn == 'established_connection'){
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                            ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                                ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
        
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }
                    
                    $total_farmers += $report_res->total_farmers;
                    $total_bags += $report_res->total_bags;
                    $dist_area += $report_res->dist_area;
                    $actual_area += $report_res->actual_area;
                }
            }
        }

        $data_arr = array(
            "total_municipalities" => $municipalities,
            "total_provinces" => $provinces,
            "total_farmers" => number_format($total_farmers),
            "total_bags" => number_format($total_bags),
            "dist_area" => number_format($dist_area, '2'),
            "actual_area" => number_format($actual_area, '2'),
            "total_male" => $male_count,
            "total_female" => $female_count
        );
        
        return $data_arr;
    }
    /* REPORT HEADER */

    // input by timothy
    public function coop_summary_view(){
        return view('cooperative_summary.index');
    }

    public function coop_sg_count_total(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
        }
        $data = [
            'sgTotal' => $sgCountTotal,
            'haTotal' => $haCountTotal,
            'coopTotal' => count($coop),
        ];

        return $data;
    }

    public function coop_sg_count(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'name' => $value->coopName,
                'haCount' => $haCount,
                'sgCount' => $sgCount,
                'sgTotal' => $sgCountTotal,
                'haTotal' => $haCountTotal,
                'coopTotal' => count($coop),
            ];
            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->make(true);
    }

    public function coop_sg_count_excel(){
        $excel = new excel;
        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'Cooperative Name' => $value->coopName,
                'No. of Seed Growers' => $sgCount,
                'Commited Area ( ha )' => $haCount,
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Cooperative Name' => "TOTAL",
            'No. of Seed Growers' => $sgCountTotal,
            'Commited Area ( ha )' => $haCountTotal,
        ];
        array_push($table_data, $data2);

        return Excel::create('Cooperatives Seed Grower Counts', function($excel) use ($table_data) {
            $excel->sheet('Sheet1', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });
        })->download('xlsx');
    }

    /** MODIFIED REPORTS */

    function load_regional_data(){        
        /*if(Auth::user()->roles->first()->name == "da-icts"){
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->where('total_farmers', '!=', 0)
                ->orderBy('region', 'ASC')
                ->get();
        }else{
            $regional_data = DB::connection('rcep_reports_db')
                ->table('lib_regional_reports')
                ->where('total_farmers', '!=', 0)
                ->orderBy('region', 'ASC')
                ->get();
        }*/
		
		 if(Auth::user()->roles->first()->name == "da-icts"){
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->select('lib_regional_reports.region', 'lib_regional_reports.total_provinces', 'lib_regional_reports.total_municipalities',
                        'lib_regional_reports.total_farmers', 'lib_regional_reports.total_bags', 'lib_regional_reports.total_dist_area',
                        'lib_regional_reports.total_actual_area', 'lib_regional_reports.total_male', 'lib_regional_reports.total_female',
                        'lib_regional_reports.report_id')
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports.region');
                })
                ->where('lib_regional_reports.total_farmers', '!=', 0)
                ->orderBy('lib_prv.region_sort', 'ASC')
                ->groupBy('lib_prv.region')
                ->get();
        }else{
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
                ->select('lib_regional_reports.region', 'lib_regional_reports.total_provinces', 'lib_regional_reports.total_municipalities',
                    'lib_regional_reports.total_farmers', 'lib_regional_reports.total_bags', 'lib_regional_reports.total_dist_area',
                    'lib_regional_reports.total_actual_area', 'lib_regional_reports.total_male', 'lib_regional_reports.total_female',
                    'lib_regional_reports.report_id', 'lib_regional_reports.yield')
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports.region');
                })
                ->where('lib_regional_reports.total_farmers', '!=', 0)
                ->orderBy('lib_prv.region_sort', 'ASC')
                ->groupBy('lib_prv.region')
                ->get();
        } 

        return $regional_data;
    }

    function load_provincial_data(){        
        $province_data = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
			->where('total_farmers', '!=', 0)
            ->orderBy('province', 'ASC')
            ->get();

        return $province_data;
    }

    function load_municipal_data(){        
        $municipal_data = DB::connection('rcep_reports_db')
            ->table('lib_municipal_reports')
			->where('total_farmers', '!=', 0)
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();

        return $municipal_data;
    }

    function load_national_data(){        
        if(Auth::user()->roles->first()->name == "da-icts"){
            $national_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
                ->get();
        }else{
            $national_data = DB::connection('rcep_reports_db')
                ->table('lib_national_reports')
                ->first();
        }

        return $national_data;
    }

    function municipal_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => '',
            'Municipality' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function provincial_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function regional_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        $total_provinces = 0;
        $total_municipalities = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;
            $total_provinces += $row->total_provinces;
            $total_municipalities += $row->total_municipalities;

            $data = [
                'Region' => $row->region,
                'Covered Provinces' => $row->total_provinces == '' ? '0' : (string) $row->total_provinces,
                'Covered Municipaloities' => $row->total_municipalities == '' ? '0' : (string) $row->total_municipalities,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Region' => 'TOTAL:',
            'Covered Provinces' => (string) $total_provinces,
            'Covered Municipaloities' => (string) $total_municipalities,
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function national_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $data = [
                'OVERALL SUMMARY: ' => '',
                'Covered Provinces' => (string) number_format($row->provinces),
                'Covered Municipalities' => (string) number_format($row->municipalities),
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) number_format($row->total_farmers),
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) number_format($row->total_dist_area),
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) number_format($row->total_actual_area),
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) number_format($row->total_bags),
                'Total Male' => $row->total_male == '' ? '0' : (string) number_format($row->total_male),
                'Total Female' => $row->total_female == '' ? '0' : (string) number_format($row->total_female)
            ];
            array_push($table_data, $data);
        }

       $empty_row = [
            'OVERALL SUMMARY: ' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total beneficiaries' => '',
            'Distribution Area' => '',
            'Actual Area' => '',
            'Bags Distributed (20kg/bag)' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($table_data, $empty_row);

        return $table_data;
    }

    public function Home_Report2(){
        $regional_data = $this->load_regional_data();
        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.home')
                ->with('regional_data', $regional_data);
        }else{
			$excel_data = json_decode(json_encode($regional_data), true);
            foreach ($excel_data as $key => $value) {
                 $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                     ->where('region', $excel_data[$key]['region'])
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', '!=', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
                
                $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('region', $excel_data[$key]['region'])
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                    $accepted_transferred = "Accepted: ".number_format($accepted);
                    if(number_format($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$excel_data[$key]['region']."'
                        data-province = '%'
                        data-municipality = '%'

                         > ".$accepted_transferred." </a>";
                    }else{
                        $linkBreakDown = $accepted_transferred;
                    }

                $excel_data[$key]['linkBreakDown'] = $linkBreakDown;
            }

            $regional_data = json_decode(json_encode($excel_data), false);
			
            return view('reports.modified.home')
                ->with('regional_data', $regional_data);		
        }
    }

    public function Home_Report2_provincial(){
        //$provincial_data = $this->load_provincial_data();
        $region_list = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_provincial_reports')
            ->select('lib_provincial_reports.region', 'lib_provincial_reports.province', 'lib_provincial_reports.total_municipalities',
                'lib_provincial_reports.total_farmers', 'lib_provincial_reports.total_bags', 'lib_provincial_reports.total_dist_area',
                'lib_provincial_reports.total_actual_area', 'lib_provincial_reports.total_male', 'lib_provincial_reports.total_female',
                'lib_provincial_reports.report_id')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports.region');
            })
            ->where('lib_provincial_reports.total_farmers', '!=', 0)
            ->orderBy('lib_prv.region_sort', 'ASC')
            ->groupBy('lib_prv.region')
            ->get();
			
        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.province')
                ->with('region_list', $region_list);
        }else{
            return view('reports.modified.province')
                ->with('region_list', $region_list)
                ->with('user_level', Auth::user()->roles->first()->name);
        }
    }

   public function Home_Report2_municipal(){
        //$municipal_data = $this->load_municipal_data();
        $municipal_list = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_provincial_reports')
            ->select('*')
            ->where('total_farmers', '!=', 0)
            ->orderBy('province','ASC')
            ->groupBy('province')
            ->get();
            
        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.municipality')
                ->with('municipal_list', $municipal_list);
        }else{
            return view('reports.modified.municipality')
                ->with('municipal_list', $municipal_list)
                ->with('user_level', Auth::user()->roles->first()->name);
        }
    }


    public function Home_Report2_national(){
        $national_data = $this->load_national_data();
		
        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.national')
                ->with('national_data', $national_data);
        }else{
            return view('reports.modified.national')
                ->with('national_data', $national_data);
        }
    }

    public function convert_to_excel(Request $request){

        $excel = new Excel;

        if($request->excel_type == "regional"){
            /*$data = $this->load_regional_data();
            $table_data = $this->regional_excel_content($data);
            $myFile = Excel::create('Regional Report', function($excel) use ($table_data) {
                $excel->sheet('REGIONS', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "REGIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";*/

        }else if($request->excel_type == "provincial"){
            $data = $this->load_provincial_data();
            $table_data = $this->provincial_excel_content($data);

            $myFile = Excel::create('Provincial Report', function($excel) use ($table_data) {
                $excel->sheet('PROVINCES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "PROVINCIAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "municipal"){
            $data = $this->load_municipal_data();
            $table_data = $this->municipal_excel_content($data);

            $myFile = Excel::create('Municipal Report', function($excel) use ($table_data) {
                $excel->sheet('MUNICIPALITIES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "MUNICIPAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "national"){
            //national data
            $data = $this->load_national_data();
            $table_data = $this->national_excel_content($data);

            //regional data
            $regional_data = $this->load_regional_data();
            $regional_table_data = $this->regional_excel_content($regional_data);

            $myFile = Excel::create('National Report', function($excel) use ($table_data, $regional_table_data) {
                $excel->sheet('OVERALL & REGIONAL', function($sheet) use ($table_data, $regional_table_data) {
                    $sheet->fromArray($table_data);
                    $sheet->fromArray($regional_table_data);
                });
            });

            $file_name = "NATIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";
        }

        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

    public function convert_to_excel_province_Old($province){
        $passed_str = explode("___", $province);
        $province = $passed_str[0];
        $limit_options = $passed_str[1];

        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->where('province', $province)->groupBy('province')->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                //array container
                $table_data = array();
                $province_sheet = array();
                $municipal_table = array();

                /** PROVINCE SUMMARY DATA */
                //get overall summary for province
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_provincial_reports')
                    ->where('province', $province)
                    ->first();

                $province_data = [
                    'Province Name' => $province,
                    'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($province_sheet, $province_data);

                $blank_row = [
                    'Province Name' => '',
                    'Covered Municipalities' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($province_sheet, $blank_row);
                /** PROVINCE SUMMARY DATA */

                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */
                $municipal_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->orderBy('municipality')
                    ->get();

                $mun_total_farmers = 0;
                $mun_total_distArea = 0;
                $mun_total_actArea = 0;
                $mun_total_male = 0;
                $mun_total_female = 0;
                $mun_total_bags = 0;

                $mun_cnt = 1;
                foreach($municipal_summary as $m_row){
                    $municipal_data = [
                        '#' => $mun_cnt,
                        'Municipality Name' => $m_row->municipality,
                        'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                        'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                        'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                        'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                        'Total Male' => (string) number_format($m_row->total_male),
                        'Total Female' => (string) number_format($m_row->total_female)
                    ];
                    array_push($municipal_table, $municipal_data);

                    ++$mun_cnt;
                    $mun_total_farmers += $m_row->total_farmers;
                    $mun_total_distArea += $m_row->total_dist_area;
                    $mun_total_actArea += $m_row->total_actual_area;
                    $mun_total_male += $m_row->total_male;
                    $mun_total_female += $m_row->total_female;
                    $mun_total_bags += $m_row->total_bags;
                }

                $total_municipal_data = [
                    '#' => '',
                    'Municipality Name' => 'TOTAL: ',
                    'Total Beneficiaries' => (string) number_format($mun_total_farmers),
                    'Total Distribution Area' => (string) number_format($mun_total_distArea),
                    'Total Actual Area' => (string) number_format($mun_total_actArea),
                    'Total Bags Distributed' => (string) number_format($mun_total_bags),
                    'Total Male' => (string) number_format($mun_total_male),
                    'Total Female' => (string) number_format($mun_total_female)
                ];
                array_push($municipal_table, $total_municipal_data);
                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */


                $limit_options = explode("_", $limit_options);
                $offset = $limit_options[0];
                
                if($offset == 1){
                    $offset = $limit_options[0];
                }else{
                    $offset = $limit_options[0] - 1;
                }

                $limit = $limit_options[1];

                $province_farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                        'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                        'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->orderBy('released.province', 'ASC')
                //->offset($offset)
                //->limit(3000)
                ->skip($offset)
                ->take(1000)
                ->get();

                $total_dist_area = 0;
                $total_actual_area = 0;
                $total_bags = 0;

                foreach ($province_farmer_list as  $row) {

                    //check other_info table
                    $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                    if(count($other_info_data) > 0){
                        $birthdate = $other_info_data->birthdate;
                        $mother_fname = $other_info_data->mother_fname;
                        $mother_mname = $other_info_data->mother_mname;
                        $mother_lname = $other_info_data->mother_lname;
                        $mother_suffix = $other_info_data->mother_suffix;

                        if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                            $phone_number = "";
                        }else{
                            $phone_number = $other_info_data->phone;
                        }
                    }else{
                        $birthdate = '';
                        $mother_fname = '';
                        $mother_mname = '';
                        $mother_lname = '';
                        $mother_suffix = '';
                        $phone_number = '';
                    }

                    //get farmer_profile
                    $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
                        ->where('distributionID', 'like', 'R%')
						->orderBy('farmerID', 'DESC')
                        ->first();

                    if(count($farmer_profile) > 0){
                        $qr_code = $farmer_profile->distributionID;
                        $farmer_fname = $farmer_profile->firstName;
                        $farmer_mname = $farmer_profile->midName;
                        $farmer_lname = $farmer_profile->lastName;
                        $farmer_extname = $farmer_profile->extName;
                        $dist_area = $farmer_profile->area;
                        $actual_area = $farmer_profile->actual_area;
                        $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                        $total_dist_area += $farmer_profile->area;
                        $total_actual_area += $farmer_profile->actual_area;
                    }else{
                        $qr_code = "N/A";
                        $farmer_fname = "N/A";
                        $farmer_mname = "N/A";
                        $farmer_lname = "N/A";
                        $farmer_extname = "N/A";
                        $dist_area = 0;
                        $actual_area = 0;
                        $sex = "N/A";

                        $total_dist_area += 0;
                        $total_actual_area += 0;
                    }

                    //get name of encoder using released.by in sdms_db_dev
                    $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                    if(count($encoder_name) > 0){
                        if($encoder_name->middleName == ''){
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }else{
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }
                    }else{
                        $encoder_name = '';
                    }

                    //compute totals
                    $total_bags += $row->bags;

                    $data = [
                        'RSBSA #' => $row->rsbsa_control_no,
                        'QR Code' => $qr_code,
                        "Farmer's First Name" => $farmer_fname,
                        "Farmer's Middle Name" => $farmer_mname,
                        "Farmer's Last Name" => $farmer_lname,
                        "Farmer's Extension Name" => $farmer_extname,
                        'Sex' => $sex,
                        'Birth Date' => $birthdate,
                        'Telephone Number' => $phone_number,
                        'Province' => $row->province,
                        'Municipality' => $row->municipality,
                        "Mother's First Name" => $mother_fname,
                        "Mother's Middle Name" => $mother_mname,
                        "Mother's Last Name" => $mother_lname,
                        "Mother's Suffix" => $mother_suffix,
                        'Distribution Area' => $dist_area,
                        'Actual Area' => $actual_area,
                        'Bags' => $row->bags,
                        'Seed Variety' => $row->seed_variety,
                        'Date Released' => $row->date_released,
                        'Farmer ID' => $row->farmer_id,
                        'Released By' => $encoder_name
                    ];
                    array_push($table_data, $data);
                }

                $data2 = [
                    'RSBSA #' => '',
                    'QR Code' => '',
                    "Farmer's First Name" => '',
                    "Farmer's Middle Name" => '',
                    "Farmer's Last Name" => '',
                    "Farmer's Extension Name" => '',
                    'Sex' => '',
                    'Birth Date' => '',
                    'Telephone Number' => '',
                    'Province' => '',
                    'Municipality' => '',
                    "Mother's First Name" => '',
                    "Mother's Middle Name" => '',
                    "Mother's Last Name" => '',
                    "Mother's Suffix" => 'TOTAL: ',
                    'Distribution Area' => $total_dist_area,
                    'Actual Area' => $total_actual_area,
                    'Bags' => $total_bags,
                    'Seed Variety' => '',
                    'Date Released' => '',
                    'Farmer ID' => '',
                    'Released By' => ''
                ];
                array_push($table_data, $data2);

                return Excel::create($province."_".$offset."_".$limit."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $province_sheet, $municipal_table) {
                    $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                        $sheet->fromArray($province_sheet);
                        $sheet->fromArray($municipal_table);
                    });

                    $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                        $sheet->fromArray($table_data);
                    });
                })->download('xlsx');
            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.province');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.province');
        }
    }

    public function convert_to_excel_province2($province){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->where('province', $province)->groupBy('province')->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                //array container
                $table_data = array();
                $province_sheet = array();
                $municipal_table = array();

                /** PROVINCE SUMMARY DATA */
                //get overall summary for province
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_provincial_reports')
                    ->where('province', $province)
                    ->first();

                $province_data = [
                    'Province Name' => $province,
                    'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($province_sheet, $province_data);

                $blank_row = [
                    'Province Name' => '',
                    'Covered Municipalities' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($province_sheet, $blank_row);
                /** PROVINCE SUMMARY DATA */

                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */
                $municipal_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->orderBy('municipality')
                    ->get();

                $mun_total_farmers = 0;
                $mun_total_distArea = 0;
                $mun_total_actArea = 0;
                $mun_total_male = 0;
                $mun_total_female = 0;
                $mun_total_bags = 0;

                $mun_cnt = 1;
                foreach($municipal_summary as $m_row){
                    $municipal_data = [
                        '#' => $mun_cnt,
                        'Municipality Name' => $m_row->municipality,
                        'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                        'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                        'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                        'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                        'Total Male' => (string) number_format($m_row->total_male),
                        'Total Female' => (string) number_format($m_row->total_female)
                    ];
                    array_push($municipal_table, $municipal_data);

                    ++$mun_cnt;
                    $mun_total_farmers += $m_row->total_farmers;
                    $mun_total_distArea += $m_row->total_dist_area;
                    $mun_total_actArea += $m_row->total_actual_area;
                    $mun_total_male += $m_row->total_male;
                    $mun_total_female += $m_row->total_female;
                    $mun_total_bags += $m_row->total_bags;
                }

                $total_municipal_data = [
                    '#' => '',
                    'Municipality Name' => 'TOTAL: ',
                    'Total Beneficiaries' => (string) number_format($mun_total_farmers),
                    'Total Distribution Area' => (string) number_format($mun_total_distArea),
                    'Total Actual Area' => (string) number_format($mun_total_actArea),
                    'Total Bags Distributed' => (string) number_format($mun_total_bags),
                    'Total Male' => (string) number_format($mun_total_male),
                    'Total Female' => (string) number_format($mun_total_female)
                ];
                array_push($municipal_table, $total_municipal_data);
                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */

                $province_farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                        'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                        'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->orderBy('released.province', 'ASC')
                ->get();

                $total_dist_area = 0;
                $total_actual_area = 0;
                $total_bags = 0;

                foreach ($province_farmer_list as  $row) {

                    //check other_info table
                    $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                    if(count($other_info_data) > 0){
                        $birthdate = $other_info_data->birthdate;
                        $mother_fname = $other_info_data->mother_fname;
                        $mother_mname = $other_info_data->mother_mname;
                        $mother_lname = $other_info_data->mother_lname;
                        $mother_suffix = $other_info_data->mother_suffix;

                        if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                            $phone_number = "";
                        }else{
                            $phone_number = $other_info_data->phone;
                        }
                    }else{
                        $birthdate = '';
                        $mother_fname = '';
                        $mother_mname = '';
                        $mother_lname = '';
                        $mother_suffix = '';
                        $phone_number = '';
                    }

                    //get farmer_profile
                    $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
                        ->where('distributionID', 'like', 'R%')
						->orderBy('farmerID', 'DESC')
                        ->first();

                    if(count($farmer_profile) > 0){
                        $qr_code = $farmer_profile->distributionID;
                        $farmer_fname = $farmer_profile->firstName;
                        $farmer_mname = $farmer_profile->midName;
                        $farmer_lname = $farmer_profile->lastName;
                        $farmer_extname = $farmer_profile->extName;
                        $dist_area = $farmer_profile->area;
                        $actual_area = $farmer_profile->actual_area;
                        $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                        $total_dist_area += $farmer_profile->area;
                        $total_actual_area += $farmer_profile->actual_area;
                    }else{
                        $qr_code = "N/A";
                        $farmer_fname = "N/A";
                        $farmer_mname = "N/A";
                        $farmer_lname = "N/A";
                        $farmer_extname = "N/A";
                        $dist_area = 0;
                        $actual_area = 0;
                        $sex = "N/A";

                        $total_dist_area += 0;
                        $total_actual_area += 0;
                    }

                    //get name of encoder using released.by in sdms_db_dev
                    $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                    if(count($encoder_name) > 0){
                        if($encoder_name->middleName == ''){
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }else{
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }
                    }else{
                        $encoder_name = '';
                    }

                    //compute totals
                    $total_bags += $row->bags;

                    $data = [
                        'RSBSA #' => $row->rsbsa_control_no,
                        'QR Code' => $qr_code,
                        "Farmer's First Name" => $farmer_fname,
                        "Farmer's Middle Name" => $farmer_mname,
                        "Farmer's Last Name" => $farmer_lname,
                        "Farmer's Extension Name" => $farmer_extname,
                        'Sex' => $sex,
                        'Birth Date' => $birthdate,
                        'Telephone Number' => $phone_number,
                        'Province' => $row->province,
                        'Municipality' => $row->municipality,
                        "Mother's First Name" => $mother_fname,
                        "Mother's Middle Name" => $mother_mname,
                        "Mother's Last Name" => $mother_lname,
                        "Mother's Suffix" => $mother_suffix,
                        'Distribution Area' => $dist_area,
                        'Actual Area' => $actual_area,
                        'Bags' => $row->bags,
                        'Seed Variety' => $row->seed_variety,
                        'Date Released' => $row->date_released,
                        'Farmer ID' => $row->farmer_id,
                        'Released By' => $encoder_name
                    ];
                    array_push($table_data, $data);
                }

                $data2 = [
                    'RSBSA #' => '',
                    'QR Code' => '',
                    "Farmer's First Name" => '',
                    "Farmer's Middle Name" => '',
                    "Farmer's Last Name" => '',
                    "Farmer's Extension Name" => '',
                    'Sex' => '',
                    'Birth Date' => '',
                    'Telephone Number' => '',
                    'Province' => '',
                    'Municipality' => '',
                    "Mother's First Name" => '',
                    "Mother's Middle Name" => '',
                    "Mother's Last Name" => '',
                    "Mother's Suffix" => 'TOTAL: ',
                    'Distribution Area' => $total_dist_area,
                    'Actual Area' => $total_actual_area,
                    'Bags' => $total_bags,
                    'Seed Variety' => '',
                    'Date Released' => '',
                    'Farmer ID' => '',
                    'Released By' => ''
                ];
                array_push($table_data, $data2);

                return Excel::create($province."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $province_sheet, $municipal_table) {
                    $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                        $sheet->fromArray($province_sheet);
                        $sheet->fromArray($municipal_table);
                    });

                    $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                        $sheet->fromArray($table_data);
                    });
                })->download('xlsx');
            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.province');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.province');
        }
    }

    public function convert_to_excel_municipality($province, $municipality){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->where('province', $province)
            ->where('municipality', $municipality)
            ->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                $mun_data_arr = array();
                /** MUNICIPAL SUMMARY DATA */
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->where('municipality', $municipality)
                    ->first();

                $mun_data = [
                    'Province Name' => $province,
                    'Municipality Name' => $municipality,
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($mun_data_arr, $mun_data);

                $blank_row = [
                    'Province Name' => '',
                    'Municipality Name' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($mun_data_arr, $blank_row);
                /** MUNICIPAL SUMMARY DATA */

                $farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->where('released.municipality', '=', $municipality)
                ->get();

                //check if there is data returned after inserting parameters
                if(count($farmer_list) > 0){
                     //generate array to be passed to the excel file
                    $table_data = array();
                    $total_dist_area = 0;
                    $total_actual_area = 0;
                    $total_bags = 0;

                    foreach ($farmer_list as  $row) {
                        /*
                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->birthdate;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                        }

                        //compute totals
                        $total_dist_area += $row->area;
                        $total_actual_area += $row->actual_area;
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $row->distributionID,
                            "Farmer's First Name" => $row->firstName,
                            "Farmer's Middle Name" => $row->midName,
                            "Farmer's Last Name" => $row->lastName,
                            "Farmer's Extension Name" => $row->extName,
                            'Sex' => $row->sex == 'Femal' ? 'Female' : $row->sex,
                            'Birth Date' => $birthdate,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $row->area,
                            'Actual Area' => $row->actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id
                        ];
                        array_push($table_data, $data);
                        */

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
                            
                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
							->where('lastName', '!=', '')
							->where('distributionID', 'like', 'R%')
							->orderBy('farmerID', 'DESC')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
    
                            $total_dist_area += $farmer_profile->area;
                            $total_actual_area += $farmer_profile->actual_area;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
    
                            $total_dist_area += 0;
                            $total_actual_area += 0;
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
						if(count($encoder_name) > 0){
							if($encoder_name->middleName == ''){
								$encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
							}else{
								$encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
							}
						}else{
							$encoder_name = '';
						}

                        //compute totals
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $qr_code,
                            "Farmer's First Name" => $farmer_fname,
                            "Farmer's Middle Name" => $farmer_mname,
                            "Farmer's Last Name" => $farmer_lname,
                            "Farmer's Extension Name" => $farmer_extname,
                            'Sex' => $sex,
                            'Birth Date' => $birthdate,
                            'Telephone Number' => $phone_number,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $dist_area,
                            'Actual Area' => $actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id,
                            'Released By' => $encoder_name
                        ];
                        array_push($table_data, $data);
                    }

                    $data2 = [
                        'RSBSA #' => '',
                        'QR Code' => '',
                        "Farmer's First Name" => '',
                        "Farmer's Middle Name" => '',
                        "Farmer's Last Name" => '',
                        "Farmer's Extension Name" => '',
                        'Sex' => '',
                        'Birth Date' => '',
                        'Telephone Number' => '',
                        'Province' => '',
                        'Municipality' => '',
                        "Mother's First Name" => '',
                        "Mother's Middle Name" => '',
                        "Mother's Last Name" => '',
                        "Mother's Suffix" => 'TOTAL: ',
                        'Distribution Area' => $total_dist_area,
                        'Actual Area' => $total_actual_area,
                        'Bags' => $total_bags,
                        'Seed Variety' => '',
                        'Date Released' => '',
                        'Farmer ID' => '',
                        'Released By' => ''
                    ];
                    array_push($table_data, $data2);

                    return Excel::create($municipality."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $mun_data_arr) {
                        $excel->sheet('MUNICIPALITY SUMMARY', function($sheet) use ($mun_data_arr) {
                            $sheet->fromArray($mun_data_arr);
                        });

                        $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                            $sheet->fromArray($table_data);
                        });
                    })->download('xlsx');
                }else{
                    Session::flash('error_msg', "The selected municipality has no distribution data.");
                    return redirect()->route('rcep.report2.municipality');
                }

            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.municipality');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.municipality');
        }
    }

    public function convert_to_excel_region($region){
        $excel_data = array();
        $region_summary = DB::connection('rcep_reports_db')
            ->table('lib_regional_reports')
            ->where('region', $region)
            ->first();

        $region_summary_data = [
            'Region Name' => $region_summary->region,
            'Covered Provinces' => (string) number_format($region_summary->total_provinces),
            'Covered Municipalities' => (string) number_format($region_summary->total_municipalities),
            'Total Farmers' => (string) number_format($region_summary->total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($region_summary->total_bags),
            'Total Distribution Area' => (string) number_format($region_summary->total_dist_area),
            'Total Actual Area' => (string) number_format($region_summary->total_actual_area),
            'Total Male' => (string) number_format($region_summary->total_male),
            'Total Female' => (string) number_format($region_summary->total_female)
        ];
        array_push($excel_data, $region_summary_data);

        $blanK_row = [
            'Region Name' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total Farmers' => '',
            'Total Bags Distributed (20kg/bag)' => '',
            'Total Distribution Area' => '',
            'Total Actual Area' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($excel_data, $blanK_row);

        $province_data = array();
        $selected_region_province_summary = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('region', $region)
            ->get();

        //gloabl variables for provincial data
        $total_municipalities = 0;
        $total_farmers = 0;
        $total_bags = 0;
        $total_dist_area = 0;
        $total_actual_area = 0;
        $total_male = 0;
        $total_female = 0;

        foreach($selected_region_province_summary as $p_row){
            $p_data= [
                'Region' => $p_row->region,
                'Province' => $p_row->province,
                'Covered Municipalities' => (string) number_format($p_row->total_municipalities),
                'Total Farmers' => (string) number_format($p_row->total_farmers),
                'Total Bags Distributed (20kg/bag)' => (string) number_format($p_row->total_bags),
                'Total Distribution Area' => (string) number_format($p_row->total_dist_area),
                'Total Actual Area' => (string) number_format($p_row->total_actual_area),
                'Total Male' => (string) number_format($p_row->total_male),
                'Total Female' => (string) number_format($p_row->total_female)
            ];
            array_push($province_data, $p_data);

            $total_municipalities += $p_row->total_municipalities;
            $total_farmers += $p_row->total_farmers;
            $total_bags += $p_row->total_bags;
            $total_dist_area += $p_row->total_dist_area;
            $total_actual_area +=$p_row->total_actual_area;
            $total_male += $p_row->total_male;
            $total_female += $p_row->total_female;
        }

        $total_p_data= [
            'Region' => '',
            'Province' => 'TOTAL: ',
            'Covered Municipalities' =>(string) number_format($total_municipalities),
            'Total Farmers' => (string) number_format($total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($total_bags),
            'Total Distribution Area' => (string) number_format($total_dist_area),
            'Total Actual Area' => (string) number_format($total_actual_area),
            'Total Male' => (string) number_format($total_male),
            'Total Female' => (string) number_format($total_female)
        ];
        array_push($province_data, $total_p_data);

        return Excel::create($region_summary->region."_".date("Y-m-d H:i:s"), function($excel) use ($excel_data, $province_data) {
            $excel->sheet("REGION_SUMMARY", function($sheet) use ($excel_data, $province_data) {
                $sheet->fromArray($excel_data);
                $sheet->fromArray($province_data);
            });
        })->download('xlsx');
    }

    /**NEW FUNCTIONS FOR SEED BENEFICIARY REPORTS - jpalileo */
    public function generateProvincialReportData(Request $request){
        if(Auth::user()->roles->first()->name == "da-icts"){
            return Datatables::of(
                DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->where('region', '=', $request->region)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('province', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);

        }else{
            return Datatables::of(
                DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->where('region', '=', $request->region)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('province', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.province', $row->province);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";            
                    /*if($row->total_farmers > 20000){
                        return "<a class='btn btn-success btn-sm' data-toggle='modal' data-target='#export_option_modal' data-province='".$row->province."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    }else{
                        $url = route('rcef.report.excel.province_2', $row->province);
                        return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";                        
                    }*/

                    return "<a class='btn btn-success btn-xs' data-province='$row->province' data-toggle='modal' data-target='#confirm_export_pmo'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                })
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                   // return number_format($row->total_bags);   

					$accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', '!=', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
                
					$transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                    $accepted_transferred = "Accepted: ".number_format($accepted);
                    if(number_format($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$row->region."'
                        data-province = '".$row->province."'
                        data-municipality = '%'

                         > ".$accepted_transferred." </a>";
                    }else{
                        $linkBreakDown = $accepted_transferred;
                    }

                return $linkBreakDown; 				   
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
				->addColumn('total_yield', function($row){
					return number_format($row->yield, '2', '.', ',');       
				})
                ->make(true);
        }
    }

    public function generateMunicipalReportData(Request $request){
        if(Auth::user()->roles->first()->name == "da-icts"){
            return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->where('province', '=', $request->province)
                ->where('total_farmers', '!=', 0)
                ->orderBy('municipality', 'ASC')
            )
            ->addColumn('total_beneficiaries', function($row){
                return number_format($row->total_farmers);       
            })
            ->addColumn('total_registered_area', function($row){
                return number_format($row->total_actual_area, '2', '.', ',');       
            })
            ->addColumn('total_estimated_area', function($row){
                return number_format($row->total_dist_area, '2', '.', ',');       
            })
            ->addColumn('total_bags_distributed', function($row){
                return number_format($row->total_bags);       
            })
            ->addColumn('total_male_count', function($row){
                return number_format($row->total_male);       
            })
            ->addColumn('total_female_count', function($row){
                return number_format($row->total_female);       
            })
            ->make(true);
        
        }else{
            return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->where('province', '=', $request->province)
                ->where('total_farmers', '!=', 0)
                ->orderBy('municipality', 'ASC')
            )
            ->addColumn('action', function($row){
                //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                return "<a class='btn btn-success btn-xs' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
            })
            ->addColumn('total_beneficiaries', function($row){
                return number_format($row->total_farmers);       
            })
            ->addColumn('total_registered_area', function($row){
                return number_format($row->total_actual_area, '2', '.', ',');       
            })
            ->addColumn('total_estimated_area', function($row){
                return number_format($row->total_dist_area, '2', '.', ',');       
            })
            ->addColumn('total_bags_distributed', function($row){
               // return number_format($row->total_bags);  
				$accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', '!=', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
                
                $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                    $accepted_transferred = "Accepted: ".number_format($accepted);
                    if(number_format($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$row->region."'
                        data-province = '".$row->province."'
                        data-municipality = '".$row->municipality."'

                         > ".$accepted_transferred." </a>";
                    }else{
                        $linkBreakDown = $accepted_transferred;
                    }

                return $linkBreakDown;
			   
            })
            ->addColumn('total_male_count', function($row){
                return number_format($row->total_male);       
            })
            ->addColumn('total_female_count', function($row){
                return number_format($row->total_female);       
            })
			->addColumn('total_yield', function($row){
                return number_format($row->yield, '2', '.', ',');       
            })
            ->make(true);
        }
    }
	
	public function download_variety_report(Request $request){
        $excel = new Excel;

        $seed_data = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->orderBy('province', 'municipality')
            ->get();

        $table_data = array();
        $total_seed_volume = 0;

        foreach ($seed_data as  $row) {
            $data = [
                'Region' => $row->region,
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                'Seed Variety' => $row->seed_variety,
                'Total Volume (20kg/bag)' => number_format($row->total_volume)
            ];

            $total_seed_volume += $row->total_volume;
            array_push($table_data, $data);
        }
        $data2 = [
            'Region' => '',
            'Province' => '',
            'Municipality' => '',
            'Seed Variety' => 'TOTAL: ',
            'Total Volume (20kg/bag)' => number_format($total_seed_volume)
        ];
        array_push($table_data, $data2);

        $myFile = Excel::create('VARIETY REPORT', function($excel) use ($table_data) {
            $excel->sheet('SEED_VARITIES', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });
        });

        $file_name = "SEED_VARIETY_REPORT"."_".date("Y-m-d H:i:s").".xlsx";


        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }


    public function check_prv_data_for_excel(Request $request){
        $region = $request->region;
        $province = $request->province;
        $per_part = 1000;

        $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('region', $region)->where('province', $province)->groupBy('province')->value('prv');
        $database = $GLOBALS['season_prefix']."prv_".substr($prv,0,4);

        $prv_volume = ceil(count(DB::table($database.".released")->get()) / $per_part);

        return $prv_volume;
    }



    /**
     * NEW CODE - 09/04/2020
     */

    

    

    public function convert_to_excel_province_listDependent_v1($province){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {
            $table_name = "tbl_".substr(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->value('prv'), 0, 4);
            \Config::set('database.connections.reports_db.database', $GLOBALS['season_prefix'].'rcep_excel');
            DB::purge('reports_db');

            $table_name = $table_name;
            $primary_key = 'id';
            $fields = [
                ['name' => 'rsbsa_control_number', 'type' => 'string', 'limit' => '100'],
                ['name' => 'qr_code', 'type' => 'string', 'limit' => '100'],
                ['name' => 'farmer_fname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_mname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_lname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_ext', 'type' => 'string', 'limit' => '255'],
                ['name' => 'sex', 'type' => 'string', 'limit' => '6'],
                ['name' => 'birthdate', 'type' => 'string', 'limit' => '100'],
                ['name' => 'tel_number', 'type' => 'string', 'limit' => '100'],
                ['name' => 'province', 'type' => 'string', 'limit' => '100'],
                ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
                ['name' => 'mother_fname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_mname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_lname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_ext', 'type' => 'string', 'limit' => '255'],
                ['name' => 'dist_area', 'type' => 'float', 'limit' => '10'],
                ['name' => 'actual_area', 'type' => 'float', 'limit' => '10'],
                ['name' => 'bags', 'type' => 'integer', 'limit' => '10'],
                ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
                ['name' => 'date_released', 'type' => 'string', 'limit' => '100'],  
                ['name' => 'farmer_id', 'type' => 'string', 'limit' => '100'],
                ['name' => 'released_by', 'type' => 'text'],
                ['name' => 'date_generated', 'type' => 'timestamp'],   
            ];
            $this->createTable($table_name, $fields, $primary_key);


            /**
             * PROCESS DATA - pending data (START)
             */
            $database = $GLOBALS['season_prefix']."prv_".substr(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->value('prv'), 0, 4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $province_farmer_list = DB::connection('reports_db')->table("released")
                        ->select('released.province', 'released.municipality', 'released.seed_variety', 
                                'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                                'released.farmer_id', 'released.released_by', 'released.release_id')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $province)
                        ->where('released.is_processed', 1)
                        ->orderBy('released.province', 'ASC')
                        ->get();

                    foreach ($province_farmer_list as  $row) {

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;

                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->where('lastName', '!=', '')
                            ->where('firstName', '!=', '')
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if(count($encoder_name) > 0){
                            if($encoder_name->middleName == ''){
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }else{
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }
                        }else{
                            $encoder_name = '';
                        }


                        $data = [
                            'rsbsa_control_number' => $row->rsbsa_control_no,
                            'qr_code' => $qr_code,
                            "farmer_fname" => $farmer_fname,
                            "farmer_mname" => $farmer_mname,
                            "farmer_lname" => $farmer_lname,
                            "farmer_ext" => $farmer_extname,
                            'sex' => $sex,
                            'birthdate' => $birthdate,
                            'tel_number' => $phone_number,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            "mother_fname" => $mother_fname,
                            "mother_mname" => $mother_mname,
                            "mother_lname" => $mother_lname,
                            "mother_ext" => $mother_suffix,
                            'dist_area' => $dist_area,
                            'actual_area' => $actual_area,
                            'bags' => $row->bags,
                            'seed_variety' => $row->seed_variety,
                            'date_released' => $row->date_released,
                            'farmer_id' => $row->farmer_id,
                            'released_by' => $encoder_name
                        ];
                        DB::table($GLOBALS['season_prefix']."rcep_excel.$table_name")->insert($data);

                        //after processing to seed beneficiary list DB update is_processed flag to 0
                        DB::table($database.'.released')->where('release_id', $row->release_id)->update([
                            'is_processed' => 0
                        ]);

                        DB::commit();
                    }

                }
                 /**
                 * PROCESS DATA - pending data (END)
                 */

                
                /**
                 * CONVERT TO EXCEL (START)
                */
                $province_data = json_decode(
                                    json_encode(
                                        DB::table($GLOBALS['season_prefix']."rcep_excel.$table_name")
                                            ->select('rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                                                'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_fname',
                                                'mother_mname', 'mother_lname', 'mother_ext', 'dist_area', 'actual_area', 'bags', 'seed_variety',
                                                'date_released', 'farmer_id', 'released_by')    
                                            ->get()
                                    ), 
                                true);

                return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($province_data) {
                    $excel->sheet("BENEFICIARY LIST", function($sheet) use ($province_data) {
                        
                        /*$sheet->fromArray($province_data, null, 'A1', false, false);
                        $sheet->prependRow(1, array(
                            'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                            "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                            'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                            "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                            'Actual Area', 'Bags', 'Seed Variety', 'Date Released', 'Farmer ID', 'Released By'
                        ));
                        $sheet->freezeFirstRow();*/


                    });
                })->download('xlsx');
                /**
                  * CONVERT TO EXCEL (END)
                  */
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }
    }
	
	/**
     * 11-03-2020
     */
    public function nrp_provinces(Request $request){
         $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
			->orderBy('region_sort', 'ASC')
            //->orderBy('province')
            ->get();

        $return_str= '';
        foreach($provinces as $province){
            $prv_name  = $GLOBALS['season_prefix']."prv_".substr($province->prv, 0, 4);
            $nrp_count = count(DB::table($prv_name.".nrp_profile")->get());
            if($nrp_count > 0){
                $return_str .= "<option value='$prv_name'>$province->province</option>";
            }   
        }
        return $return_str;
    }
	
	/**
     * 11-05-2020
     */
    public function areaRange_home(Request $request){
        return view('reports.area_range.home');
    }

    public function areaRange_municipalTBL(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality'))
        ->addColumn('total_beneficiarries', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            return count(DB::table($database.".".$table)->get());
        })
        ->addColumn('one_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_one_hectare = count(DB::table($database.".".$table)->where('actual_area', '<=', 1)->get());

            if($total_one_hectare > 0){
                $equivalent_percentage = ($total_one_hectare / $total_beneficiaries) *  100;
                return $total_one_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_one_hectare;
            }
            
        })
        ->addColumn('two_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_two_hectare = count(DB::table($database.".".$table)
                ->where('actual_area', '>', 1)
                ->where('actual_area', '<=', 2)
                ->get());

            if($total_two_hectare > 0){
                $equivalent_percentage = ($total_two_hectare / $total_beneficiaries) *  100;
                return $total_two_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_two_hectare;
            }
        })
        ->addColumn('three_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_three_hectare = count(DB::table($database.".".$table)
                ->where('actual_area', '>', 2)
                ->where('actual_area', '<=', 3)
                ->get());

            if($total_three_hectare > 0){
                $equivalent_percentage = ($total_three_hectare / $total_beneficiaries) *  100;
                return $total_three_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_three_hectare;
            }
        })
        ->addColumn('last_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_last = count(DB::table($database.".".$table)->where('actual_area', '>', 3)->get());

            if($total_last > 0){
                $equivalent_percentage = ($total_last / $total_beneficiaries) *  100;
                return $total_last." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_last;
            }
        })
        ->make(true);
    }

    /** END-- */
}
