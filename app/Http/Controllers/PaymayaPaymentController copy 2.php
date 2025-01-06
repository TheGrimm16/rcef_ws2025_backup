<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Session;
use Excel;
use PDFTIM;
use DateTime;
use \NumberFormatter;

use Yajra\Datatables\Facades\Datatables;

class PaymayaPaymentController extends Controller
{

    public function index(){

    return view("paymaya.reports.payments.home");
    
    }


    public function signatories(){

      return view("paymaya.reports.payments.signatories");
      
      }

    public function payment_frm_dpb($date1,$date2,$date3){
      $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1r3= explode('-',$date3);
        $date1 = $date1r[2].'-'.$date1r[0].'-'.$day = ($date1r[1]-1).' '.'16:00:01';
        $date2 = $date2r[2].'-'.$date2r[0].'-'.$day = $date2r[1].' '.'16:00:00';
        $date3 = $date1r3[2].'-'.$date1r3[0].'-'.$date1r3[1];
        $date3 = date('F d, Y',strtotime($date3));

        DB::beginTransaction();
        try{

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 760 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->groupby('a.coopAccreditation')
          ->where('a.is_paid','=','0')
          ->get();

       $db = 0;
       $other = 0;

       foreach ($button_dl as $value) {
    
          if($value->form_type == "FUND TRANSFER FORM"){
            $other++;
          }if($value->form_type == "ADVICE TO DEBIT/CREDIT"){
            $db++;

          }
           
       }


    $signatory1 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',1)
        ->first();

    $signatory2 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',2)
        ->first();
    
    $signatory3 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',3)
        ->first();



      $table_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
     ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 760 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 760)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 760) * 0.99 as net_amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        ->groupby('a.coopAccreditation')
        ->where('b.form_type','=','ADVICE TO DEBIT/CREDIT')
        ->where('a.is_paid','=','0')
        ->get();

      //  foreach ($table_details as $value) {
   
      //   DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
      //              ->where('coopAccreditation', $value->coopAccreditation)
      //              ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
      //              ->update([
      //                  'is_paid' => 1,
      //              ]);
      //  }



      $overall_bags = 0;
      $overall_amount = 0;
      $overall_retention = 0;
      $overall_net_amount = 0;
      $overall_net_amount_in_word = '';

     if($table_details >0){
      foreach ($table_details as $row) {
        $overall_bags  += $row->total_bags;
        $overall_amount += $row->amount;
        $overall_retention += $row->retention;
        $overall_net_amount += $row->net_amount;
        
      }

      $overall_net_amount=$overall_net_amount;
      $overall_net_amount2=$overall_net_amount;

      $tmpData=explode(".",$overall_net_amount);
      $tmpData2=explode(".",number_format($overall_net_amount2,'2'));
      
      $stringWord="";
      $fdigit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
      $FstringWord=  $fdigit->format($tmpData[0]);
      if(isset($tmpData[1])){
        $ldigit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $LstringWord=  $ldigit->format($tmpData2[1]);
      }
      
      $word_number = '';
       if(isset($tmpData[1])){
        $word_number = strtoupper($FstringWord." PESOS"." and ".$LstringWord." CENTAVOS ONLY");
       }else{
        $word_number = strtoupper($FstringWord." PESOS ONLY");
       }
     }
    
     $r=0;

     DB::commit();
    } catch (\Exception $e) {
      DB::rollback();
      // dd($e);
  }

    $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_dbp_PDF',compact('table_details','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3' ))
    ->setPaper('A4');
    $pdf_name = "E-Binhi payment form DBP".".pdf";
    return $pdf->stream($pdf_name);
     
        
      }
      
        //other
      public function payment_frm_other($date1,$date2,$date3){
        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1r3= explode('-',$date3);
        $date1 = $date1r[2].'-'.$date1r[0].'-'.$day = ($date1r[1]-1).' '.'16:00:01';
        $date2 = $date2r[2].'-'.$date2r[0].'-'.$day = $date2r[1].' '.'16:00:00';
        $date3 = $date1r3[2].'-'.$date1r3[0].'-'.$date1r3[1];
        $date3 = date('F d, Y',strtotime($date3));

        DB::beginTransaction();
        try{

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 760 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->groupby('a.coopAccreditation')
          // ->where('a.is_paid','=','0')
          ->get();

       $db = 0;
       $other = 0;

       foreach ($button_dl as $value) {
    
          if($value->form_type == "FUND TRANSFER FORM"){
            $other++;
          }if($value->form_type == "ADVICE TO DEBIT/CREDIT"){
            $db++;

          }
           
       }


    $signatory1 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',1)
        ->first();

    $signatory2 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',2)
        ->first();
    
    $signatory3 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',3)
        ->first();

   
     $table_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
     ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 760 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 760)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 760) * 0.99 as net_amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        ->groupby('a.coopAccreditation')
        ->where('b.form_type','=','FUND TRANSFER FORM')
        ->where('a.is_paid','=','0')
        ->get();

      //  foreach ($table_details as $value) {
   
      //   DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
      //              ->where('coopAccreditation', $value->coopAccreditation)
      //              ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
      //              ->update([
      //                  'is_paid' => 1,
      //              ]);
      //  }
       
  
        $overall_bags = 0;
        $overall_amount = 0;
        $overall_retention = 0;
        $overall_net_amount = 0;
        $overall_net_amount_in_word = '';
  
       if($table_details >0){
        foreach ($table_details as $row) {
          $overall_bags  += $row->total_bags;
          $overall_amount += $row->amount;
          $overall_retention += $row->retention;
          $overall_net_amount += $row->net_amount;
          
        }
  
        $overall_net_amount=$overall_net_amount;
        $overall_net_amount2=$overall_net_amount;
  
        $tmpData=explode(".",$overall_net_amount);
        $tmpData2=explode(".",number_format($overall_net_amount2,'2'));
        
        $stringWord="";
        $fdigit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $FstringWord=  $fdigit->format($tmpData[0]);
        if(isset($tmpData[1])){
          $ldigit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
          $LstringWord=  $ldigit->format($tmpData2[1]);
        }
        
        $word_number = '';
         if(isset($tmpData[1])){
          $word_number = strtoupper($FstringWord." PESOS"." and ".$LstringWord." CENTAVOS ONLY");
         }else{
          $word_number = strtoupper($FstringWord." PESOS ONLY");
         }
       }
        //
        $r=0;

        DB::commit();
      } catch (\Exception $e) {
        DB::rollback();
        // dd($e);
    }

        $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_others_PDF',compact('table_details','other','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3'))
        ->setPaper('A4', 'landscape');
        $pdf_name = "E-Binhi payment form Other bank".".pdf";
        return $pdf->stream($pdf_name);  
            
      }


     

      public function coop_table(Request $request){
        $date1 = $request->date1;
        $date2 = $request->date2;
        $date1 = date("Y-m-d", strtotime($date1));
        $date2 = date("Y-m-d", strtotime($date2));
        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1 = $date1r[0].'-'.$date1r[1].'-'.$day = $date1r[2].' '.'16:00:01'; 
        $date1 = date('Y-m-d H:i:s',strtotime( '-1 day',strtotime($date1)));
        // dd($date1);
        $date2 = $date2r[0].'-'.$date2r[1].'-'.$day = $date2r[2].' '.'16:00:00';

          return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
          ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.date_created)  * 760 as amount'))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->groupby('a.coopAccreditation')
          // ->where('a.is_paid','=','0')
          )
          ->addColumn('coop', function($row){                   
              return $row->coop_name;
          })        
          ->addColumn('bags', function($row){    
            return number_format($row->total_bags);
          })
          ->addColumn('amount', function($row){    
            $total_b = $row->total_bags * 760;
            $total_ret = $total_b * 0.01;
           return number_format($amount = $total_b - $total_ret,2);
  
          })
          ->addColumn('account', function($row){  
            return $row->account_no;
          })
          ->addColumn('bank', function($row){  
            return $row->branch;
          })
          ->addColumn('form_type', function($row){           
            return $row->form_type;
          })
          ->addColumn('date', function($row){  
            return date('F j, Y H:i a',strtotime($row->date_created));
            // return $row->date_created;
          })
          ->addColumn('is_paid', function($row){  
            if($row->is_paid == 1){
              return '<div style="color:green"><strong>Printed- For Payment</strong></div>';
            }else{
              return '<div style="color:orange"><strong>Pending</strong></div>';
            }
            
          })
          ->addColumn('date_paid', function($row){  
           
            if(isset($row->date_paid)){
              return date('F j, Y H:i a',strtotime($row->date_paid));
            }else{
              return 'N/A';
            }
           
            
          })
          ->make(true);
       
      }
      
      public function coop_table_dl(Request $request){
        $date1 = $request->date1;
        $date2 = $request->date2;
        $date1 = date("Y-m-d", strtotime($date1));
        $date2 = date("Y-m-d", strtotime($date2));
        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1 = $date1r[0].'-'.$date1r[1].'-'.$day = ($date1r[2]-1).' '.'16:00:01';
        $date2 = $date2r[0].'-'.$date2r[1].'-'.$day = $date2r[2].' '.'16:00:00';

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.date_created)  * 760 as amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        ->groupby('a.coopAccreditation')
        ->where('a.is_paid','=','0')
        ->groupby('a.coopAccreditation')
        ->get();

       $db = 0;
       $other = 0;

       foreach ($button_dl as $value) {
    
          if($value->form_type == "FUND TRANSFER FORM"){
            $other++;
          }if($value->form_type == "ADVICE TO DEBIT/CREDIT"){
            $db++;

          }
           
       }
       return compact('other','db');
      }

      public function generate_report($from,$to){

        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {            
            $from_ex = explode("-", $from);
            $from_date = date($from_ex[2]."-".$from_ex[0]."-".$from_ex[1]." 16:00:01");

            $to_ex = explode("-", $to);
            $to_date = date($to_ex[2]."-".$to_ex[0]."-".$to_ex[1]." 16:00:00");
            //            dd(date("M-d-y",strtotime($from_date)));

            $from = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($from_date))); 
            $to = date("Y-m-d H:i:s", strtotime($to_date));

            //dd($from." ".$to);
            $provincial_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->select("*", DB::raw("count(beneficiary_id) as bags"))
                ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE('".$from."', '%Y-%m-%d %H:%i:%s')")
                ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d %H:%i:%s') <= STR_TO_DATE('".$to."', '%Y-%m-%d %H:%i:%s')")
                // ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE('".$from_date."', '%Y-%m-%d %H:%i:%s') &&" STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d %H:%i:%s') <= STR_TO_DATE('".$to_date."', '%Y-%m-%d %H:%i:%s')")
                // ->whereBetween("date_created", [$from, $to])
                ->groupBy('seedVariety')
                ->groupBy('paymaya_code')
                ->groupBy('coopAccreditation')
                ->orderBy('paymaya_code', 'ASC')
                ->orderBy("date_created", 'ASC')
                ->get();
            
                // dd($provincial_data);
  
            $province_arr = array();
            foreach($provincial_data as $row){
                $beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->where('beneficiary_id', $row->beneficiary_id)->first();
               // dd($beneficiary_details);
               
                if(count($beneficiary_details)<=0){
                    //dd($row->beneficiary_id);
                }

                $seedtag_explode = explode("/",$row->seedTag);
                $int = (int) filter_var($seedtag_explode[0], FILTER_SANITIZE_NUMBER_INT);
                $int = str_ireplace("-","",$int);
//                    dd( $beneficiary_details->coop_accreditation);
                $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                     // dd($coop_name);
                    if($coop_name == null){
                        $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("updated_accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                        if($coop_name == null){
                            $coop_name = "N/A";
                        }
                    }



                /*$seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                    ->where('labNo',"LIKE", "%".$int."%")
                    ->where('lotNo',"LIKE", "%".$seedtag_explode[1]."%")
                    ->value('sg_name'); */
                    $rowDate = date("Y-m-d H:i:s", strtotime($row->date_created));

                    // if(strtotime($rowDate)>= strtotime($from) AND strtotime($rowDate) <= strtotime($to)){
                        if(count($beneficiary_details) > 0){
                                array_push($province_arr, array(
                                    "coop_name" => $coop_name,
                                    "shcedule" => date("M j, Y", strtotime($beneficiary_details->schedule_start))." - ".date("M j, Y", strtotime($beneficiary_details->schedule_end)),
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => $beneficiary_details->firstname,
                                    "middname" => $beneficiary_details->middname,
                                    "lastname" => $beneficiary_details->lastname,
                                    "extname" => $beneficiary_details->extname,
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => $beneficiary_details->drop_off_point,
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                    "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                    
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }else{
                                array_push($province_arr, array(
                                    "coop_name" => "N/A",
                                    "shcedule" => "N/A",
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => "N/A",
                                    "middname" => "N/A",
                                    "lastname" => "N/A",
                                    "extname" => "N/A",
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => "N/A",
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                     "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                   
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }
             
                    // }

            }

            $excel_data = json_decode(json_encode($province_arr), true); //convert collection to associative array to be converted to excel
            return Excel::create("eBinhi_"."report_WS"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                   // $sheet->fromArray($excel_data, null, 'A1', false, false);
                    /*$sheet->prependRow(1, array(
                        "Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "Paymaya Code",
                        "QR Code", "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area", "Seed Variety", "Seed Grower", "Seed Tag"
                    ));*/
                    $row = 1;
                    $sheet->prependRow($row, array(
                        "Cooperative","Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "e-Binhi Code",
                         "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area",  "Bags", "Seed Variety 1"
                    ));

                    $paycode = "";
                    $bags = 0;
                    $finalCol = "Q";
                    $varityCount = 1;
                    $fr =1;
                    $columnwithdata = array();
                    foreach ($excel_data as $key => $value) {
                        
                        if($paycode==$value["paymaya_code"]){
                            $bags += $value["bags"];
                            $lc++;
                            if($finalCol<$lc){
                               $finalCol = $lc;
                            }

                            $varityCount ++;
                            $sheet->cell($lc.$row, function($cell) use ($value) {
                            $cell->setValue($value['seedVariety']);
                            });
                            $columnwithdata[$row] = $lc.$row;
                            $sheet->cell("P".$row, function($cell) use ($bags) {
                            $cell->setValue($bags);
                            });
                            $sheet->cell($lc."1", function($cell) use ($value,$varityCount) {
                            $cell->setValue("Seed Variety ".$varityCount);
                            });
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 });         
                            }else{
                                //$sheet->row($row,$value); 
                            }
                        }else{
                            $varityCount = 1;
                            $bags = $value["bags"];
                            $fc = "A";
                            $lc = "Q";
                            $paycode = $value["paymaya_code"];
                            $row++;
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 }); 

                                $sheet->row($row,$value);           
                            }else{
                                   $sheet->row($row,$value); 
                            }  
                        }    
                    $lr = $row;
                    }

                    $row2= 1;
                    foreach ($excel_data as $key => $value) {
                        $row2++;
                        if($row2<=$lr){
                          $finalCol;
                            $lcr="Q"; 
                            if($finalCol>$lcr){
                                    if(!isset($columnwithdata[$row2])){
                                        do{
                                            $lcr++;
                                             $sheet->cell($lcr.$row2, function($cell){
                                            $cell->setValue("N/A");
                                            });
                                        }
                                        while($finalCol > $lcr);
                                    }  
                               }
                        }   
                    }
                     
                            



                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

}
