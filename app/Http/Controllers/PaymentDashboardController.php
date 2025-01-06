<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;

use DB;
use Auth;
use Session;
;
class PaymentDashboardController extends Controller
{


    //FMIS API
    public function getFmisApi(){      
        $url = "http://192.168.10.112/intranet/fmisApi";
        $fmisApi = stripslashes(file_get_contents($url));
        // dd($fmisApi);

        if($fmisApi !== ""){
             $fmisApi = str_replace("<br>","",$fmisApi);
        $fmisApi = str_replace("\r\n","",$fmisApi);
        $fmisApi = str_replace("}", "", $fmisApi);
        $fmisApi = str_replace("[", "", $fmisApi);
        $fmisApi = str_replace("]", "", $fmisApi);
        $fmisApi = trim($fmisApi);
        $fmisApi = str_replace("null", '""', $fmisApi);
        //dd($fmisApi);
        $fmisApi = explode('"', $fmisApi);
    //dd($fmisApi);
        $restring = "";
            foreach ($fmisApi as $dat) {
                $dat = trim($dat);
                if($dat == '' OR $dat == ':' OR $dat == ',' OR $dat == '{'){
                    $convData = $dat;
                }else{ 
                    $convData = str_replace(",", ";", $dat);   
                }

               $restring .= $convData;
            }
        $fmisApi = explode("{", $restring);  
        unset($fmisApi[0]);
        $fmis_api = array();
         $i = 0;
        foreach ($fmisApi as $key  => $value) {
            $w = explode(",", $value);
                foreach ($w as $value2) {
                    $isParticulars = stripos($value2, "Particulars:");
                    //dd($iarPOS);
                        if($isParticulars !== false){
                            $iarPOS = stripos($value2, "IAR:");
                                if($iarPOS !== false){
                                    $particularsIAR = explode("|", $value2);
                                }else{
                                    $particularsIAR = $value2; 
                                }
                              foreach ($particularsIAR as $pI) {
                                    $d = explode(":", $pI);
                                        if(isset($d[0])){
                                                if(stripos($d[0],"IAR") !== false){
                                                    $title = "IAR";
                                                }else{
                                                    $title = str_replace('"', "", $d[0]);
                                                }
                                        }else{$title = "";}

                                        if(isset($d[1])){
                                            $val =  str_replace('"', "", $d[1]);
                                            $val = str_replace('}', "", $val);
                                        }else{$val = "";}

                                        if($title != "" ){
                                            $title = trim($title);
                                            $val = trim($val);
                                             $fmis_api[$i][$title] = $val;
                                        }
                              }
                        }else{
                            $d = explode(":", $value2);
                                if(isset($d[0])){
                                     $title = str_replace('"', "", $d[0]);
                                }else{
                                     $title = "";
                                }

                                if(isset($d[1])){
                                    $val =  str_replace('"', "", $d[1]);
                                    $val = str_replace('}', "", $val);
                                }else{
                                    $val = "";
                                }
                                if($title != ""){
                                    $title = trim($title);
                                    $val = trim($val);
                                     $fmis_api[$i][$title] = $val;
                                }
                        }
                }
            $i++;
        }        
         DB::table($GLOBALS['season_prefix'].'rcep_api_fmis.tbl_api')->truncate();    
         //Payment for 1,000 bags of certified seeds for 2021 DS as per seed delivery dtd 11/27/2020 amounting to 760,000.00 | Attached IAR: 2020-11-1945
        foreach ($fmis_api as $key => $value) {
            if(isset($fmis_api[$key]['DVDate'])){$fmis_api[$key]['DVDate']=substr($fmis_api[$key]['DVDate'], 0, 10);}
            if(isset($fmis_api[$key]['CheckDate'])){$fmis_api[$key]['CheckDate']=substr($fmis_api[$key]['CheckDate'], 0, 10);}
            if(isset($fmis_api[$key]['Particulars'])){$fmis_api[$key]['Particulars']=str_replace(";", ",", $fmis_api[$key]['Particulars']);}
            if(isset($fmis_api[$key]['IAR'])){$fmis_api[$key]['IAR']=str_replace(";", ",", $fmis_api[$key]['IAR']);}
            
            if(isset($fmis_api[$key]['EarnAmount'])){$fmis_api[$key]['EarnAmount']=str_replace(";", "", $fmis_api[$key]['EarnAmount']);}
            if(isset($fmis_api[$key]['DedAmount'])){$fmis_api[$key]['DedAmount']=str_replace(";", "", $fmis_api[$key]['DedAmount']);}
            if(isset($fmis_api[$key]['NetAmount'])){$fmis_api[$key]['NetAmount']=str_replace(";", "", $fmis_api[$key]['NetAmount']);}
            if(isset($fmis_api[$key]['CheckNo'])){$fmis_api[$key]['CheckNo']=str_replace("    ", "|", $fmis_api[$key]['CheckNo']);}
            if(isset($fmis_api[$key]['AccountCode'])){$fmis_api[$key]['AccountCode']=str_replace("    ", "|", $fmis_api[$key]['AccountCode']);}
            if(isset($fmis_api[$key]['DedAmount'])){$fmis_api[$key]['DedAmount']=str_replace("    ", "|", $fmis_api[$key]['DedAmount']);}
            if(isset($fmis_api[$key]['NetAmount'])){$fmis_api[$key]['NetAmount']=str_replace("    ", "|", $fmis_api[$key]['NetAmount']);}
                if(isset($fmis_api[$key]["IAR"])){
                    if($fmis_api[$key]["IAR"]!==""){
                        //$fmis_api[$key]['IAR'] = "2020-04-04";
                         $iArList = explode(",", $fmis_api[$key]['IAR']);
                         if(count($iArList)>0){
                             foreach ($iArList as $iarNo) {
                                DB::table($GLOBALS['season_prefix'].'rcep_api_fmis.tbl_api')
                                        ->insert([
                                            'dvDate' => $fmis_api[$key]['DVDate'],
                                            'projectCode' => $fmis_api[$key]['ProjectCode'],
                                            'Iar' =>  $iarNo,
                                            'programCode' => $fmis_api[$key]['ProgramCode'],
                                            'dvNo' => $fmis_api[$key]['DVNo'],
                                            'burNo' => $fmis_api[$key]['BURNo'],
                                            'particulars' => $fmis_api[$key]['Particulars'],
                                            'checkNo' => $fmis_api[$key]['CheckNo'],
                                            'checkDate' => $fmis_api[$key]['CheckDate'],
                                            'accountNo' => $fmis_api[$key]['AccountNo'],
                                            'accountCode' => $fmis_api[$key]['AccountCode'],
                                            'earnAmount' => $fmis_api[$key]['EarnAmount'],
                                            'dedAmount' => $fmis_api[$key]['DedAmount'],
                                            'netAmount' => $fmis_api[$key]['NetAmount'],
                                        ]);
                            }
                         }
                    }
                }
        }
        //SAVE LOG
        if(isset(Auth::user()->username)){$user=Auth::user()->username;}else{$user='Scheduled Process';}

        DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'FMIS_API_DOWNLOAD',
                'description' => $i.' DATA DOWNLOADED',
                'author' =>$user,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);  
        }
       

    }




    public function index()
    {
        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')->get();
        return view('payment_dashboard.home')
            ->with("coops", $coops);
    }

     public function iar_tbl_home(Request $request){
        $table_data = array();
        $tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('coopAccreditation', $request->coop_accre)
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        foreach($tbl_delivery as $tbl_delivery_row){
            $iar_numnber = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->where('batchTicketNumber', $tbl_delivery_row->batchTicketNumber)
                ->orderBy('logsId', 'DESC')
                ->first();

            $tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where([
                    'batchTicketNumber' => $tbl_delivery_row->batchTicketNumber,
                    'has_rla' => 0
                ])
                ->groupBy('batchTicketNumber')
                ->count();

            $tbl_actual_delivery_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where([
                    'batchTicketNumber' => $tbl_delivery_row->batchTicketNumber,
                ])
                ->groupBy('batchTicketNumber')
                ->count();

            if(count($iar_numnber) > 0){
                $iar_numnber = $iar_numnber->iarCode;
            }else{
                $iar_numnber = "N/A";
            }
            if($tbl_actual_delivery == 0 && $tbl_actual_delivery_count > 0){
                array_push($table_data, array(
                    "iar_number" => $iar_numnber,
                    "batch_code" => $tbl_delivery_row->batchTicketNumber,
                    "region" => $tbl_delivery_row->region,
                    "province" => $tbl_delivery_row->province,
                    "municipality" => $tbl_delivery_row->municipality,
                    "dop" => $tbl_delivery_row->dropOffPoint,
                    "delivery_date" => date("F j, Y", strtotime($tbl_delivery_row->deliveryDate))
                ));
            }
           
        }
        
        $table_data = collect($table_data);
        return Datatables::of($table_data)
        ->addColumn('action', function($table_data) {
            $btn = "<a type='button' data-toggle='modal' data-target='#show_iar_modal' data-iar_code='".$table_data['iar_number']."' data-iar='".$table_data['batch_code']."' class='btn btn-success btn-xs'><i class='fa fa-book'></i></a>";


                $dvData = DB::table($GLOBALS['season_prefix'].'rcep_api_fmis.tbl_api')
                        ->where('Iar', $table_data['iar_number'])
                        ->get();
                        if(count($dvData)>0){
                            $iarList = "";
                            $batchList = "";
                           // dd($dvData);
                            
                            foreach ($dvData as $key => $value) {
                                $dvNo = $value->dvNo;
                                $dvDate = $value->dvDate;
                                $projectCode=$value->projectCode;
                                $programCode=$value->programCode;
                                $burNo=$value->burNo;
                                $particulars=$value->particulars;
                                $checkNo=$value->checkNo;
                                    if($value->checkDate == "0000-00-00 00:00:00"){
                                     $checkDate="N/A"; }
                                     else{ $checkDate = date('Y-m-d', strtotime($value->checkDate)); }
                                     
                                $accountNo=$value->accountNo;
                                $accountCode=$value->accountCode;
                                $earnAmount=$value->earnAmount;
                                $dedAmount=$value->dedAmount;
                                $netAmount=$value->netAmount;
                            }

                           
                            $checkAllIAR = DB::table($GLOBALS['season_prefix'].'rcep_api_fmis.tbl_api')
                                ->where('dvNo', $dvNo)
                                ->get();

                                foreach ($checkAllIAR as $iarData) {
                                     if($iarList!=''){$iarList .= ' | ';}
                                        $iar = $iarData->Iar; 
                                        $iar = trim($iar);

                                        $iarList .= $iar;

                                          //GET BACTHNUMBER
                                        $checkAllBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                                            ->where('iarCode', $iar)
                                            ->get();
                                        if(count($checkAllBatch)>0){
                                             if($batchList!=''){$batchList .= ' | ';}
                                                if(count($checkAllBatch)>1){
                                                    $batchList .= '[';
                                                }
                                                foreach ($checkAllBatch as $key => $batchNoData) {
                                                     if($key > 0){ $batchList .= ' | ';}
                                                     $batchList .= $batchNoData->batchTicketNumber;
                                                }
                                                if(count($checkAllBatch)>1){
                                                    $batchList .= ']';
                                                }
                                        }
                                }


                         $btn .= "<a type='button' data-toggle='modal' data-target='#show_dv_info_modal' data-iar_code='".$table_data['iar_number']."' data-iar='".$table_data['batch_code']."' 
                                data-iarlist='".$iarList."'
                                data-batchlist ='".$batchList."'
                                data-dvno='".$dvNo."'
                                data-dvdate='".date('Y-m-d', strtotime($dvDate))."'
                                data-projectcode='".$projectCode."'
                                data-programcode='".$programCode."'
                                data-burno='".$burNo."'
                                data-particulars='".$particulars."'
                                data-checkno='".$checkNo."'
                                data-checkdate='".$checkDate."'
                                data-accountno='".$accountNo."'
                                data-accountcode='".$accountCode."'
                                data-earnamount='".$earnAmount."'
                                data-dedamount='".$dedAmount."'
                                data-netamount='".$netAmount."'
                                class='btn btn-success btn-xs'><i class='fa fa-list-alt'></i></a>";
                        }
           
            return $btn;

        })->make(true);
    }

    public function particularsPreview(Request $request){
        $tbl_delivery_data = DB::connection('ds2024')->table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'region', 'province', 'municipality', 'deliveryDate')
            ->where('batchTicketNumber', $request->batch_code)
            ->groupBy('batchTicketNumber')
            ->first();

        $cost = $tbl_delivery_data->total_bags * 760;
        $particulars = "Payment for ".number_format($tbl_delivery_data->total_bags)." bags of certified seeds for 2021 DS as per seed delivery dtd ".date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate))." amounting to ".number_format($cost,2,".",",")." | Attached IAR: ".$request->iar_code;
        return json_encode($particulars);
    }
}   
