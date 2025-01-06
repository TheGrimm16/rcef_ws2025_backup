<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use PhpOffice\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

use DB;
use Session;
use Auth;
use Excel;

use Yajra\Datatables\Facades\Datatables;


class ImportController extends Controller
{

    public function release_uploader_post(Request $request){
        //  dd($request->all());
        $province = $request->province_release;
        $municipality = $request->municipality_release;
        $dop_release = $request->dop_release;
        
        $inbred_count = $request->inbred_count;
        $hybrid_count = $request->hybrid_count;
        $inbred_count = str_replace(",","", $inbred_count);
        $hybrid_count = str_replace(",","", $hybrid_count);

      

        $prv_id = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            ->where("municipality", $municipality)
            ->first();

            // dd($prv_id);

            $check_table = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."uploaded_distribution")
                ->where("TABLE_NAME", "prv_".$prv_id->prv_code)
                ->first();

                if($check_table == null){
                    $raw = "CREATE TABLE ".$GLOBALS['season_prefix']."uploaded_distribution.prv_".$prv_id->prv_code." like ".$GLOBALS['season_prefix']."uploaded_distribution.prv_template";
                    DB::connection("delivery_inspection_db")->select(DB::raw($raw));
                }

        
        $file = $request->file('excel_file_up');
    
        $date = date("Y-m-d H:i:s");
        // Read the Excel file data
        $data_excel = Excel::load($file)->get();
            $array = array();
            // dd($data_excel);
        $insert_array = array();
        $row_start = 3;
            $excel_inbred = 0;
            $excel_hybrid = 0;
                //  return ($data_excel[0]);
        //    dd(count($data_excel[0]));


        foreach ($data_excel[0]->toArray() as $key=> $row) {
                if($key < $row_start){
                    continue;
                }
           
            $data = $row;
            // dd($row[5]);
            $excel_row_label = $row_start + 2;
                // dd($data["rcef_id"]);
                if(!isset($data["rcef_id"])){
                    // dd($data);
                //    continue;
                }


            if($data["rcef_id"]){
                if($data["seed_class"] == "Inbred"){
                    $bags_claimed = ceil($data['area_claimed'] * 2);
                    $excel_inbred += $bags_claimed;
                }elseif($data["seed_class"] == "Hybrid"){
                    $excel_hybrid++;
                }else{
                    return json_encode("Seed Class is undefined at row: "+$excel_row_label);
                }

                if($hybrid_count < $excel_hybrid){ }//return json_encode("Hybrid count exceed on available stocks"); }
                if($inbred_count < $excel_inbred){  return json_encode("Inbred count exceed on available stocks"); }
                // dd($data);
                if(isset($data["birthdate"])){
                    $bday = $data['birthdate']->toArray();
                    $bday = date("Y-m-d", strtotime($bday["formatted"]));
                }else{
                    $bday = "";
                }
            
               array_push($insert_array, array(
                'seed_class' => $data['seed_class'],
                'rcef_id' => $data['rcef_id'],
                'rsbsa_control_no' => $data['rsbsa_control_no'],
                'lastName' => $data['lastname'],
                'firstName' => $data['firstname'],
                'midName' => $data['midname'],
                'extName' => $data['extname'],
                'birthdate' => $bday,
                'province' => $data['province'],
                'municipality' => $data['municipality'],
                'brgy_name' => $data['brgy_name'],
                'final_area' => $data['final_area'],
                'area_claimed' => $data['area_claimed'],
                'seed_variety' => $data['seed_variety'],
                'with_fertilizer_voucher' => $data['with_fertilizer_voucher'],
                'da_intervention_card' => $data['da_intervention_card'],
                'is_recipient_ls' => $data['is_recipient_ls'],
                'yield_planted_variety' => $data['yield_planted_variety'],
                'yield_seed_class' => $data['yield_seed_class'],
                'yield_planted_area' => $data['yield_planted_area'],
                'yield_bags_harvested' => $data['yield_bags_harvested'],
                'yield_weight' => $data['yield_weight'],
                'crop_establishment' => $data['crop_establishment'],
                'eco_system' => $data['eco_system'],
                'eco_system_source' => $data['eco_system_source'],
                'sowing_month' => $data['sowing_month'],
                'sowing_week' => $data['sowing_week'],
                'lot_no' => $data['lot_no'],
                'series_no' => $data['series_no'],
                'kp_kit_received' => $data['kp_kit_received'],
                'oth_fert' => $data['oth_fert'],
                'oth_cash' => $data['oth_cash'],
                'oth_loan' => $data['oth_loan'],
                'date_received' => $data['date_received'],
                'rep_name' => $data['rep_name'],
                'rep_id' => $data['rep_id'],
                'rep_relation' => $data['rep_relation'],
                'status' => 0,
                'uploaded_by' => Auth::user()->username,
                'date_created' => $date,
                "upload_status" => "PENDING"
               )); 





            }


            $row_start ++ ;
        }
    
        $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            ->where("municipality", $municipality)
            ->first();
            
            if($lib_prv != null){
                $prv_code = $lib_prv->prv_code;
                $db_upload_pending = $GLOBALS['season_prefix']."prv_".$prv_code.".upload_pending";

                DB::beginTransaction();

                try {
                    DB::table($db_upload_pending)
                        ->insert($insert_array);
                    DB::commit();
                    return json_encode("Success Uploading");
                } catch (\Throwable $th) {
                    //throw $th;
                    DB::rollback();

                    return json_encode($th->getMessage());
                }
                

               





            }else{
                return json_encode("Check Location");
            }



    }


    public function release_uploader(){

        $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->select("province")
                ->groupBy("province")
                ->get();
            $provinces = json_decode(json_encode($provinces), true);

        $nrp_provinces = DB::table($GLOBALS['season_prefix']."nrp_seeds.tbl_actual_delivery_nrp")
                ->select("province")
                ->groupby("province")
                ->whereNotIn("province", $provinces)
                ->get();
              
            $nrp_provinces = json_decode(json_encode($nrp_provinces), true);
            
            $provinces = array_merge($provinces, $nrp_provinces);
            
            //  dd($provinces);
        return view("import.release_data")
            ->with("provinces",$provinces)

            ;
    }

    public function municipal_list(Request $request){
        $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        ->select("municipality")
        ->groupBy("municipality")
        ->where("province", $request->province)
        ->get();
            $municipality = json_decode(json_encode($municipality), true);

        $nrp_municipality = DB::table($GLOBALS['season_prefix']."nrp_seeds.tbl_actual_delivery_nrp")
            ->select("municipality")
            ->groupby("municipality")
            ->whereNotIn("municipality", $municipality)
            ->where("province", $request->province)
            ->get();  
            $nrp_municipality = json_decode(json_encode($nrp_municipality), true);

            $municipality = array_merge($municipality, $nrp_municipality);

            return json_encode($municipality);
    }

    public function dop_list(Request $request){
        $dop = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        ->select("dropOffPoint")
        ->groupBy("dropOffPoint")
        ->where("province", $request->province)
        ->where("municipality", $request->municipality)
        
        ->get();
            $dop = json_decode(json_encode($dop), true);

            if(count($dop)<=0){

               $dop = array(array("dropOffPoint"=>"NRP Stock")); 
            }

            return json_encode($dop);
    }

    public function get_stocks(Request $request){
        $municipal_stocks = 0;

        $inbred_stocks = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            // ->select("seedVariety",DB::raw("SUM(totalBagCount) as totalBagCount"))
            ->where("province", $request->province)
            ->where("municipality", $request->municipality)
            ->where("dropOffPoint", $request->dop)
            // ->groupBy("seedVariety")
            // ->get();
            ->sum("totalBagCount");
            // $inbred_stocks = 0;
            $municipal_stocks += $inbred_stocks;

        $inbred_stocks = number_format($inbred_stocks);

        $nrp_stocks = DB::table($GLOBALS['season_prefix']."nrp_seeds.tbl_actual_delivery_nrp")
            // ->select("seed_variety",DB::raw("SUM(volume) as totalBagCount"))
            ->where("province", $request->province)
            ->where("municipality", $request->municipality)
            // ->groupBy("seed_variety")
            // ->get();
            ->sum("volume");

            // $nrp_stocks = 0;
            $nrp_stocks = number_format($nrp_stocks);

            $municipal_stocks += $nrp_stocks;


        $stocks = compact('inbred_stocks', 'nrp_stocks', 'municipal_stocks');
        // dd($stocks);

        return json_encode($stocks);
            

    }
    


    private $seed_growers = ['first_name', 'middle_name', 'last_name', 'extension_name', 'full_name', 'accreditation_number', 'coop_accreditation_number', 'area', 'rcef_area','cooperative_name' ];
    private $rla = ['coop_name', 'coopAccreditation', 'sg_id', 'sg_name', 'certificationDate', 'labNo', 'lotNo', 'noOfBags', 'seedVariety', 'moaNumber' ];
    private $ebinhi = [
        "NewInsertByExcel", "farmer_id","fname","midname","lname","extename","rsbsa_control_number",
        "region","province_name","municipality_name","prv_code","muni_code","barangay_code","contact_no","farm_area_ws2021",
        "farm_area_ds2022","committed_area","ver_sex","yield_no_bags","yield_weight_bags","yield_area","yield","mother_lname","mother_fname","mother_mname" 
    ];

    private $ebinhi_update_status = [
        "mobile", "lastname", "sowing_month", "sowing_week"
    ];
    
    public function seed_growers(){
        return view('import.seed_growers')
        ->with('col_data', $this->seed_growers);
    }

    public function rla(){
        return view('import.rla')
        ->with('col_data', $this->rla);
    }

    public function ebinhi(){
        return view('import.ebinhi')
        ->with('col_data', $this->ebinhi);
    }

    public function ebinhi_update_status(){
        return view('import.ebinhi-update-status')
        ->with('col_data', $this->ebinhi_update_status);
    }

    public function import_data_rla(Request $request)
    {  
       // dd($request->all());
       $return_excel = array();
      $path = $request->file('inputFile')->getRealPath();
      $data = array_map('str_getcsv', file($path));
        unset($data[0]);
        // 0 => "lab"
        // 1 => "sg_full"
        // 2 => "sg_first"
        // 3 => "sg_last"
        // 4 => "sg_prefix"
        // 5 => "COOP_ACCREDITAION"
        // 6 => "coop_name"
        // 7 => "variety"
        // 8 => "lot"
        // 9 => "volume"
        // 10 => "Certified"
        // 11 => "certification"
        // 12 => "harvest"
        foreach ($data as $rla_data){
            $lab = trim(str_replace("RSC","",$rla_data[0]));
            $sg_full = $rla_data[1];
            $sg_first = $rla_data[2];
            $sg_last = $rla_data[3];
            $sg_prefix = $rla_data[4];
            $coop_accre = $rla_data[5];
            $coop_name = $rla_data[6];
            $variety=$rla_data[7];
            $lot = trim($rla_data[8]);
            $volume = $rla_data[9];
            $note = $rla_data[10];
            $certified_date = $rla_data[11];
            $harvest_date = $rla_data[12];
        
            $sg_search = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                ->where("coop_accred", $coop_accre)
                ->where("is_active", 1)
                ->where("full_name", "LIKE", "%".$sg_first."%")
                ->where("full_name", "LIKE", "%".$sg_last."%")
                ->limit(1)
                ->get();
            if(count($sg_search)>0){
                $sg_id = $sg_search[0]->sg_id;
                $sg_name = $sg_search[0]->full_name;
            }else{
                $sg_id =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                ->insertGetId([
                    "coop_accred" => $coop_accre,
                    "is_active" => 1,
                    "is_block" => 0,
                    "full_name" => $sg_full
                ]);
                $sg_name = $sg_full;
            }

            $check_dual_coop =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                ->where("is_active", 1)
                ->where("full_name", "LIKE", "%".$sg_first."%")
                ->where("full_name", "LIKE", "%".$sg_last."%")
                ->groupBy("coop_accred")
                ->get();

            $coop_count = count($check_dual_coop);
            
            $coop_info = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("accreditation_no", $coop_accre)
                ->first();

            if(count($coop_info)>0){
                $coop_moa = $coop_info->current_moa;
                $coop_name = $coop_info->coopName;
                
            }else{
                array_push($return_excel, array(
                    "Lab" => $lab,
                    "Lot" =>  $lot,
                    "COOP" => $coop_accre,
                    "status" => "NO COOP"
                ));

                continue;
            }

            $check_rla_exist = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                ->where("coopAccreditation", $coop_accre)
                ->where("labNo", $lab)
                ->where("lotNo", $lot)
                ->get();

                if(count($check_rla_exist)>0){
                    $get_id = "exist";

                    $get_id = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                    ->where("coopAccreditation", $coop_accre)
                    ->where("labNo", $lab)
                    ->where("lotNo", $lot)
                    ->update([
                        "enrolled_coop_count" => $coop_count
                    ]);


                    
                }else{
                    
                    $get_id = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                    ->insertGetId([
                        "coop_name" => $coop_name,
                        "coopAccreditation" => $coop_accre,
                        "sg_id" => $sg_id,
                        "sg_name" => $sg_name,
                        "certificationDate" => date("Y-m-d", strtotime($certified_date)),
                        "labNo" => $lab,
                        "lotNo" =>$lot,
                        "noOfBags" => $volume,
                        "seedVariety" => $variety,
                        "moaNumber" => $coop_moa,
                        "is_rejected" => 0,
                        "enrolled_coop_count" => $coop_count
                    ]);
                }
                
                array_push($return_excel, array(
                    "Lab" => $lab,
                    "Lot" =>  $lot,
                    "COOP" => $coop_accre,
                    "status" => $get_id
                ));
              
        }

        return Excel::create("RLA_RESULT".date("Y-m-d g:i A"), function($excel) use ($return_excel) {
            $excel->sheet("RLA_RESULT", function($sheet) use ($return_excel) {
                $sheet->fromArray($return_excel);
                $sheet->freezeFirstRow();
                
            });
        })->download('xlsx');

  
  
    } 

    public function import_file(Request $request){
        // $reader = PhpSpreadsheet\IOFactory::createReader('Excel2007');
        // $reader->setReadDataOnly(true);
        if(isset($_FILES["file"]["name"])){
            $path = isset($_FILES["file"]['tmp_name']) ? $_FILES["file"]['tmp_name'] : '';
            $objPHPExcel = PhpSpreadsheet\IOFactory::load($path);;
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $header=true;
            if ($header) {
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $headingsArray = $objWorksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
                $headingsArray = $headingsArray[1];
                $r = -1;
                $namedDataArray = array();
                for ($row = 2; $row <= $highestRow; ++$row) {
                    $dataRow = $objWorksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, true);
                    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                        ++$r;
                        foreach ($headingsArray as $columnKey => $columnHeading) {
                            $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                        }
                    }
                }
            } else {
                //excel sheet with no header
                $namedDataArray = $objWorksheet->toArray(null, true, true, true);
            }
            // $data = md5(json_encode($namedDataArray));
            // $path = 'public';
            // $name = $data.'.json';
            // if (!Storage::disk($path)->put($name, response()->json($namedDataArray)))
            // {
            //         echo json_encode('Unable to write the file');
            // }                
            
            // echo json_encode(array('table_data' => $namedDataArray, 'data' => $data));
            // return $namedDataArray;

                //dd($namedDataArray);

            if($request->function == "seed_grower"){
                return $this->import_seed_growers($namedDataArray);
            }

            if($request->function == "rla"){
                return $this->import_rla($namedDataArray);
            }

            if($request->function == "ebinhi"){
                return $this->import_ebinhi_farmers($namedDataArray);
            }

            if($request->function == "ebinhi-update-status"){
                return $this->import_ebinhi_farmers_update_status($namedDataArray);
            }
            
        }
    }

    private function import_seed_growers($data){
        $inserted = 0;
        foreach($data as $d){
            $d['first_name'] = preg_replace('/[^A-Za-z0-9_ -]/', "", $d['first_name']);
            $d['middle_name'] = preg_replace('/[^A-Za-z0-9_ -]/', "", $d['middle_name']);
            $d['last_name'] = preg_replace('/[^A-Za-z0-9_ -]/', "", $d['last_name']);
            
            $db = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
            ->where([
                'first_name' => $d['first_name'],
                'middle_name' => $d['middle_name'],
                'last_name' => $d['last_name'],
                'accreditation_number' => $d['accreditation_number'],
            ])
            ->get();

            if($d['area'] == "" || $d['area'] == null){
                $d['area'] = 0.00;
            }

            if($d['rcef_area'] == "" || $d['rcef_area'] == null){
                $d['rcef_area'] = 0.00;
            }

            if(count($db) == 0){
                try {
                    DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->insert($d);
                    $inserted++;
                } catch (Throwable $e) {
                    report($e);
            
                    return $e;
                }
            }
        }
        return json_encode(["insert_no" => $inserted]);
    }

    public function sg_commitment_table(Request $request){
        $commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
                ->select('*');
                
        return Datatables::of($commitments)
        ->make(true);
    }

    private function import_rla($data){
        $inserted = 0;
       // dd($data);
        foreach($data as $d){
            // $coopname = explode("(", $d['COOPERATIVE']);
            // $coopname = trim($coopname[0], " ");
            $moa = $d['moaNumber'];
            //dd($moa);
            
            $coopname = $d['coop_name']; //coop_name
            $coopaccred = $d['coopAccreditation']; //coopAccreditation
                $get_old_accred = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                    ->where("updated_accreditation_no", "LIKE", "%".$coopaccred."%")
                    ->first();

                    if(count($get_old_accred)>0){
                        $coopname = $get_old_accred->coopName;
                        $coopaccred = $get_old_accred->accreditation_no;
                        $moa = $get_old_accred->current_moa;
                    }else{
                        $get_moa = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                        ->where("accreditation_no", "LIKE", "%".$coopaccred."%")
                        ->first();
                            if(count($get_moa)>0){
                                $coopname = $get_moa->coopName;
                                $moa = $get_moa->current_moa;
                            }
                    }
        
                    $cleanLab= preg_replace("/[^0-9]/", "",$d['labNo']); //labNo
                    $cleanLot= str_replace(" ", "", $d['lotNo']); //lotNo




            // $repcoopname = trim($coopname, "Inc.");
            // $repcoopname = trim($repcoopname, " ");
            
            //$names = explode(" ", $d['sg_name']);
            //$fname = $names[0];
            //$mname = $names[1];
            //$lname = $names[2];
            //$ename = "";
            
            //dd($names);


            $certDate = $d['certificationDate'];
            $certDate = date("Y-m-d", strtotime($certDate));
            
            $rla = [];
            $sgwhere = [];
            $rla = [
                'coop_name' => $coopname,
                'coopAccreditation' => $coopaccred,
                'sg_name' => $d['sg_name'], //sg_name
                'seedVariety' => $d['seedVariety'], //seedVariety
                'labNo' =>  $cleanLab,
                'lotNo' =>  $cleanLot,
                'noOfBags' => $d['noOfBags'], //noOfBags
                'certificationDate' => $certDate, //certificationDate
                'is_rejected' => 0,
                'moaNumber' => $moa,
            ];

            $sgwhere = [
                //'fname' => $d['FIRST_NAME'], //?
                //'lname' => $d['LAST_NAME'], //?
                //'extension' => $d['EXT_NAME'], //?
                'full_name' => $d['sg_name'],
                'coop_accred' => $coopaccred,
            ];

            // // $coopdb = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            // // ->whereRaw('coopName LIKE "%'.$repcoopname.'%"')
            // // ->get();
                
            // $coopAccre = "";
            // if(count($coopdb) > 0){
            //     $coopAccre = $coopdb[0]->accreditation_no;

            //     $sgwhere['coop_accred'] = $coopdb[0]->accreditation_no;
            //     $rla['coopAccreditation'] = $coopdb[0]->accreditation_no;
            //     $rla['moaNumber'] = $coopdb[0]->current_moa;
            // }

            
            $sgdb = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
            ->where($sgwhere)
            ->get();

            if(count($sgdb) > 0){
                $rla['sg_id'] = $sgdb[0]->sg_id;
            }else{
                $rla['sg_id'] = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')->insertGetId(
                    [
                        'coop_accred' => $coopaccred,
                        'is_active' => 1,
                        'is_block' => 0,
                        //'fname' => $d['FIRST_NAME'], //?
                        //'mname' => $d['MIDDLE_NAME'], //?
                        //'lname' => $d['LAST_NAME'], //?
                        //'extension' => $d['EXT_NAME'], //?
                        'full_name' => $d['sg_name'], //sg_name
                    ]
                );
            }

            $db = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where([
                'labNo' => $cleanLab,
                'lotNo' => $cleanLot,
            ])
            ->where("coopAccreditation",$coopaccred)
           // ->whereRaw('coop_name LIKE "%'.$coopaccred.'%"')
            ->first();

            
            if(count($db) <= 0){
                try {
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->insert($rla);
                    $inserted++;
                } catch (Throwable $e) {
                    report($e);
            
                    return $e;
                }
            }
        }
        return json_encode(["insert_no" => $inserted]);
    }

    public function rla_table(Request $request){
        $rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->select('*');
                
                
        return Datatables::of($rla)
        ->make(true);
    }

    public function ebinhi_table(Request $request){
        $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('*')
                ->orderBy('NewInsertByExcel', 'DESC');
                
                
        return Datatables::of($ebinhi)
        ->make(true);
    }

    private function import_ebinhi_farmers($data){
        $inserted = 0;
        $duplicate = [];
        $batch = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')->max('NewInsertByExcel');
        $batch = $batch + 1;
        // dd($batch);
        $farmer_id = 1;
        $isActive = 0;
        
        $farmer_id_query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where("muni_code","like",$data[0]['muni_code'])
                ->max('farmer_id');
        if(!is_numeric($farmer_id_query)){
            $farmer_id = 0;
        }else{
			$farmer_id = $farmer_id_query + 1;
		}
		
        foreach($data as $d){
                      
            $db = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where(
                'rcef_id', "like", $d['rcef_id']
            )
            ->get();

            // $isPrevious_query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified_ds2022')
            // ->where([
            //     'rsbsa_control_number' => $d['rsbsa_control_number'],
            //     "status" => 1
            // ])
            // ->get();
            // if(count($isPrevious_query) > 0){
            //     $isActive = 3;
            // }
            $prvdb = 'prv_'.$d['prv_code'];
            
            // $rcef_id = DB::table($GLOBALS['season_prefix'].$prvdb.'.farmer_information')
            //     ->where("rsbsa_control_number", $d['rsbsa_control_number'])
            //     ->first();
            
                
            if(count($db) == 0){
                try {
                    $farmer_id++;
                    DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')->insert([
                        "farmer_id" => $farmer_id,
                        "fname" => $d['fnam'],
                        "midname" => $d['midname'] == null ? "" : $d['midname'],
                        "lname" => $d['lname'],
                        "extename" => $d['extename'] == null ? "" : $d['extename'],
                        "rsbsa_control_number" => $d['rsbsa_control_number'],
                        "region" => $d['region'],
                        "province_name" => $d['province_name'],
                        "municipality_name" => $d['municipality_name'],
                        "prv_code" => $d['prv_code'],
                        "muni_code" => $d['muni_code'],
                        "barangay_code" => $d['barangay_code'],
                        "contact_no" => strval($d['contact_no']),
                        "farm_area_ws2021" => $d['farm_area_prev'],
                        "farm_area_ds2021" => $d['farm_area_current'],
                        "committed_area" => $d['committed_area'] == null ? 0 : $d['committed_area'],
                        'farmer_declared_area' => $d['committed_area'] == null ? 0 : $d['committed_area'],
                        "ver_sex" => $d['ver_sex'],
                        "yield_no_bags" => $d['yield_no_bags'],
                        "yield_weight_bags" => $d['yield_weight_bags'],
                        "yield_area" => $d['yield_area'],
                        "yield" => $d['yield'] == null ? 0 : $d['yield'],
                        "mother_lname" => $d['mother_lname'],
                        "mother_fname" => $d['mother_fname'],
                        "mother_mname" => $d['mother_mname'], 
                        "sowing_year" => $d['sowing_year'], 
                        "sowing_month" => $d['sowing_month'], 
                        "sowing_week" => $d['sowing_week'], 
                        "NewInsertByExcel" => $batch,
                        "isActive" => '1',
                        "status" => '1',
                        "isPush" => '1',
                        "rcef_id" => $d['rcef_id'],
                        "created_by" => 'system'
                    ]);
                    // $table = $GLOBALS['season_prefix']. $prvdb . ".farmer_information_final";
                    // $tag = DB::table($table)
                    //     ->where('rcef_id', 'like', '%' . $d['rcef_id'] . '%')
                    //     ->update([
                    //         'is_ebinhi' => 1,
                    //     ]);
    
                        // if ($tag) {
                        //     $tag = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                        //         ->where('sed_id', $d->sed_id)
                        //         ->update([
                        //             'isTag' => 1,
                        //         ]);
                        // }
                    $inserted++;
                } catch (Throwable $e) {
                    report($e);
            
                    return $e;
                }
            }else{
                $duplicate[] = $d['rcef_id'];
            }
        }
        return json_encode(["insert_no" => $inserted, "duplicate" => $duplicate]);
    }

    public function ebinhi_update_status_table(Request $request){
        $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('*')
                ->where('isActive', 3)
                ->orderBy('NewInsertByExcel', 'DESC');
                
                
        return Datatables::of($ebinhi)
        ->make(true);
    }

    private function import_ebinhi_farmers_update_status($data){
        $inserted = 0;
        // dd($data);
        foreach($data as $d){

            try {
                $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where([
                    "lname" => $d['lastname'],
                    "fname" => $d['firstname'],
                    "contact_no" => $d['mobile'],
                    "isActive" => 3
                ])
                ->update([
                    "sowing_year" => $d['sowing_year'],
                    "sowing_month" => $d['sowing_month'],
                    "sowing_week" => $d['sowing_week'],
                    "status" => 1,
                    "created_by" => DB::raw("CONCAT(created_by,'-sms')")
                ]);
                
                if($farmer){
                    $inserted++;
                }
                
            } catch (Throwable $e) {
                report($e);
                return $e;
            }
           
        }
        return json_encode(["insert_no" => $inserted]);
    }
}