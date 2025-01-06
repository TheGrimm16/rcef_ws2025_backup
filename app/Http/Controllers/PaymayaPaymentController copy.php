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

}
