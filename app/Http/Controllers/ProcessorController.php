<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use ZipArchive;
use Illuminate\Support\Facades\File;
use RecursiveIteratorIterator;


class ProcessorController extends Controller
{
    public function downloadByBatch($batchticket){
   
        $attachment = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')->where('batch_ticket',$batchticket)->get();
        $dir = 'public/dro_upload/'.$batchticket.'';
        $zip_file = ''.$batchticket.'.zip';

        // Get real path for our folder
        $rootPath = realpath($dir);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);


        foreach (File::allFiles($rootPath) as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
            
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

       /*  foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
            
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        } */

        // Zip archive will be created only after closing object
        $zip->close();


        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($zip_file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);
        unlink($zip_file);
    }
    public function index(){

        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

        $select2_data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

        $data = $this->get_top_10_deliveries();
        
            return view("ces_payment_eveluator.home")
            ->with("data", $data)
            // ->with("regions", $regions)
            ->with("select2_data", $select2_data)
            ->with("coops", $coops);
        }

        public function get_top_10_deliveries() {
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                    ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                    ->where('a.has_rla', '=', '1')
                    ->where('moa_number','=','MOA-DS22-06-19')
                    ->groupBy('a.batchTicketNumber')
                    ->orderBy('a.dateCreated', 'decs')
                    ->get();
                    return $data;
        }


        public function load_sg_deliveries(Request $request){

            $sg = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('isActive', 1)
                ->where('current_moa',$request->current_moa)
                ->orderBy('coopName')
                ->get();

            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                ->where('a.has_rla', '=', '1')
                ->where('a.moa_number','=',$request->current_moa)
                ->groupBy('a.batchTicketNumber')
                ->orderBy('a.dateCreated','decs')
                ->get();

            return compact('data','sg');

        }
        
        public function filter(Request $request) {

            

            $region = $request->region;
            $province = $request->province;
            $municipality = $request->municipality;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $attachement_status = $request-> attachement_status;
            $current_moa = $request->current_moa;
            
            // coop only
            // if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                
            //     $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
            //                 ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
            //                 ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
            //                 ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
            //                 ->where('a.has_rla', '=', '1')
            //                 ->where('a.moa_number','=',$current_moa)
            //                 ->groupBy('a.batchTicketNumber')
            //                 ->orderBy('a.dateCreated','decs')
            //                 ->get();

            // // coop + status
            if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){                  
                // dd($request->attachement_status);
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();                            
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();    

                }

            // coop date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.has_rla', '=', '1')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

             // coop stat date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status','=',null)
                                ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status', '=', $attachement_status)
                                ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }

            // coop date reg 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
            
            // coop date reg stat              
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status != "0"){   
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status','=',null)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status', '=', $attachement_status)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }
            //  coop date reg prov 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            //  coop date reg prov stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }

            // coop date reg prov muni
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop date reg prov muni stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }
                
            // coop reg prov muni
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status == "0"){  
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop reg prov muni stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
       
             // coop reg prov 
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg prov stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }

            // coop reg
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
            }

            $sg = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('isActive', 1)
                ->where('current_moa',$current_moa)
                ->orderBy('coopName')
                ->get();

            $data_count = count($data);

        return compact('data','sg','data_count');
        }

        public function for_assesment(Request $request){
            $attach1 = '';
            $attach2 = '';
            $attach3 = '';

            $attach1_status = '';
            $attach2_status = '';
            $attach3_status = '';

            $final=array();
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as a')
                ->where('has_rla', '=', '1')
                ->where('a.batchTicketNumber', '=', $request->batch_ticket)
                ->get();
        
            $inspection_att1 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
                ->where('tbl_payments_attachements.is_batch','=', 1)
                ->where('tbl_payments_attachements.is_batch_type','=', 1)
                ->get();

            if(count($inspection_att1)>0){
                $attach1 = 1;
                foreach ($inspection_att1 as $row) {
                    $attach1_status = $row->status;
                }
                
            }else{
                $attach1 = 0;
            }

            $inspection_att2 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
                ->where('tbl_payments_attachements.is_batch','=', 1)
                ->where('tbl_payments_attachements.is_batch_type','=', 2)
                ->get();

            if(count($inspection_att2)>0){
                $attach2 = 1;

                foreach ($inspection_att2 as $row) {
                    $attach2_status = $row->status;
                }
            }else{
                $attach2 = 0;
            }

            $inspection_att3 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
                ->where('tbl_payments_attachements.is_batch','=', 1)
                ->where('tbl_payments_attachements.is_batch_type','=', 3)
                ->get();
        
            if(count($inspection_att3)>0){
                $attach3 = 1;
                foreach ($inspection_att3 as $row) {
                    $attach3_status = $row->status;
                }
            }else{
                $attach3 = 0;
            }

            $batch_stat =[
                'batch_ticket'=>$request->batch_ticket,
                'attach1'=>$attach1,
                'attach2'=>$attach2,
                'attach3'=>$attach3,
                'attach1_status'=>$attach1_status,
                'attach2_status'=>$attach2_status,
                'attach3_status'=>$attach3_status
            ];
 
                      foreach ($data as $value) {
                          $path="";
                          $remarksDRO="";
                          $remarksCES="";
                          $status="";
                          $stat_color="";
                          $is_batch="";
                          $is_batch_type="";

                          $seedTags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                              ->where('tbl_payments_attachements.seed_tag', $value->seedTag)
                              ->where('tbl_payments_attachements.is_seed_tag','=',1)
                              ->orderBy('id', 'DECS')
                              ->first();
                           
                          if(count($seedTags )>0){
                              $path=$seedTags->file_path;
                              $remarksDRO=$seedTags->remarks_dro;
                              $remarksCES=$seedTags->remarks_ces;
      
                                  if($seedTags->status == 0){
                                      $status = 'For Assesment';
                                      $stat_color ='btn-warning btn-xs btn-block';
                                      
                                  }if($seedTags->status == 1){
                                      $status = 'Passed';
                                      $stat_color ='btn-success btn-xs btn-block';
                                  }if($seedTags->status == 2){
                                      $status = 'Failed';
                                      $stat_color ='btn-danger btn-xs btn-block';
                                  }if($seedTags->status ==''){
                                      $status = 'For Assesment';
                                      $stat_color ='btn-warning btn-xs btn-block';
      
                                  }    
                          }
                          $tmp =[
                              'seed_tag'=>$value->seedTag,
                              'variety'=>$value->seedVariety,
                              'volume'=>$value->totalBagCount,
                              'path'=>$path,
                              'remarks_dro'=>$remarksDRO,
                              'remarks_CES'=>$remarksCES,
                              'batch_number'=>$request->batch_ticket,
                              'status'=>$status,
                              'stat_color'=> $stat_color,
                              'is_batch'=> $is_batch,
                              'is_batch_type'=> $is_batch_type
                       
                            ];
                      array_push($final,$tmp);
                      }
            return compact('final','batch_stat');
          }
 
          public function update_status(Request $request){
            if($request->seed_tag !=''){
                DB::beginTransaction();
               try{
                   DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                       ->where('batch_ticket', $request->batch_num)
                       ->where('seed_tag', $request->seed_tag)
                       ->update([
                           'status' => $request->status,
                           'remarks_ces' => $request->remarks
                       ]);
   
               DB::commit();
                   return 1;
                       } catch (\Exception $e) {
                       DB::rollback();
                       }
            }
            if($request->batch_type !=''){

                DB::beginTransaction();
                try{
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                        ->where('batch_ticket', $request->batch_num)
                        ->where('is_batch_type', $request->batch_type)
                        ->update([
                            'status' => $request->status,
                            'remarks_ces' => $request->remarks
                        ]);
    
                DB::commit();
                    return 1;
                        } catch (\Exception $e) {
                        DB::rollback();
                        }
            }

          }

        public function update_status_overall(Request $request){
            return  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status')
            ->whereIn('batchTicketNumber', $request->checkboxValues)
            ->update([
                'status' => $request->status
                
            ]);

      
            DB::beginTransaction();
            try{
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status')
                    ->whereIn('batchTicketNumber', $request->checkboxValues)
                    ->update([
                        'status' => $request->status,
                        'remarks_ces' => $request->remarks
                    ]);

                DB::commit();
                return 1;
                    } catch (\Exception $e) {
                    DB::rollback();
                    }
            
        }

          public function get_attachements_batch(Request $request){
            return $attachements = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
               ->where('tbl_payments_attachements.is_batch_type', '=',$request->batch_type)
               ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
               ->get();
          }

          public function get_attachements_seed_tag(Request $request){
            return $attachements = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
               ->where('tbl_payments_attachements.seed_tag', '=', $request->seed_tag)
               ->where('tbl_payments_attachements.batch_ticket','=', $request->batch_ticket)
               ->where('tbl_payments_attachements.is_seed_tag','=', 1)
               ->get();
          }
}
