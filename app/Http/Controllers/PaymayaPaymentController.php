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
use Carbon\Carbon;
use DateTime;
use \NumberFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\Datatables\Facades\Datatables;

class PaymayaPaymentController extends Controller
{


  // RJ 08182023
  public function ebinhi_claimant_checker(){

    
              $publicDirectory = public_path("ebinhi\\");
          $return_array = array();
          if (File::exists($publicDirectory)) {
              $files = File::allFiles($publicDirectory);
              
              // Now $files contains an array of SplFileInfo objects representing the files in the directory.
              // You can loop through them or process them as needed.

            // dd($files);

            // str_contains('abc', '')
              foreach ($files as $file) {
                  $filePath = $file->getPathname(); // Full path of the file
                  $fileName = $file->getFilename(); // Name of the file
                  $lastModifiedTimestamp = File::lastModified($filePath);

                  array_push($return_array, array(
                      "file_name" => $fileName,
                      "date_generated" => date("Y-m-d", $lastModifiedTimestamp)
                  ));

              }
          }


          usort($return_array, function($a, $b) {
            if ($a['file_name'] > $b['file_name']) {
                return 1;
            } elseif ($a['file_name'] < $b['file_name']) {
                return -1;
            }
            return 0;
        });

        usort($return_array, function($a, $b) {
          if ($a['date_generated'] < $b['date_generated']) {
              return 1;
          } elseif ($a['date_generated'] > $b['date_generated']) {
              return -1;
          }
          return 0;
      });

          $dat = array();
         foreach($return_array as $arr){
       
              if(str_contains($arr["file_name"], '_RESULTS')){

              }else{
                  $dat[$arr["file_name"]]["origin"] = $arr["file_name"];
                  $dat[$arr["file_name"]]["date"] = $arr["date_generated"];
              }
         }

         foreach($return_array as $arr){
                $replace = str_replace("_RESULTS","",$arr["file_name"]);
                if(str_contains($arr["file_name"], '_RESULTS')){
                  $dat[$replace]["results"] = $arr["file_name"];
                }else{
                  
                }
          }

          
        
          return view("BePDashboard.claimant_checker")
              ->with("files", $dat);

  }


  public function ebinhi_claimant_checker_upload(Request $request)
  {

    $file = $request->file('excel_file');


    if ($file) {
  
            if ($file->getClientOriginalExtension() !== 'xlsx') {
              return back()->with('error', 'Invalid file format. Only XLSX files are allowed.');
           }


            $filenamewithextension = $file->getClientOriginalName();
          
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);

            //get file extension
            $extension = $file->getClientOriginalExtension();

            //filename to store
            $filenametostore = $filename.'.'.$extension;
            
           $store =  Storage::disk('ebinhi')->put($filenametostore, fopen($file, 'r+'));
            
            $store = true;
           if($store == true){
                  $filePath = public_path('ebinhi/'.$filenametostore);
                  $spreadsheet = IOFactory::load($filePath);
                  $worksheet = $spreadsheet->getActiveSheet();
                  $data = [];

                  $province = "";
                  $municipality = "";
                  $excel_details = array();


                  foreach ($worksheet->getRowIterator() as $key => $row) {
                      $rowData = [];
                      if($key <= 3){
                        continue;
                      }

                      $rsbsa = "";
                      $code = "";
                      $contact = "";

                      foreach ($row->getCellIterator() as $key2 => $cell) {
                          $rowData[] = $cell->getValue();
                          
                        if($province == ""){
                      
                          if($key2 == "J"){
                            $province = $cell->getValue();
                          }
                          }
                      if($municipality == ""){
                            if($key2 == "K"){
                              $municipality = $cell->getValue();
                            }
                      }

                      if($key2== "C"){
                          $rsbsa = $cell->getValue();
                      }

                      if($key2== "H"){
                        
                          $code = $cell->getValue();
                      }

                      if($key2== "N"){
                          $contact = $cell->getValue();
                      }
                      $data[] = $rowData;

                      $target_name = $code.$rsbsa.$contact;
                      array_push($excel_details, array(
                        "target_name" => $target_name
                      ));

                  }
                

                }
                  $claim_details = array();
            
                  if(count($claim_details)<=0){
                    $claim_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                        ->select("*",DB::raw("CONCAT(paymaya_code,rsbsa_control_no,phoneNumber) as target_name"))
                        ->where("province", "LIKE", $province)
                        // ->where("municipality", "LIKE", $municipality)
                        
                        ->get();
                    $claim_details = json_decode(json_encode($claim_details), true);
                    
                  }


                  $excel_data = array();
                  
              
                  foreach($claim_details as $claim){
                  
                        $res = $this->search_to_array($excel_details, "target_name", $claim["target_name"]);
                        
                      if(count($res)<= 0){
                        array_push($excel_data, $claim);
                      }

                    

                  } 
                  

                  $path = public_path("ebinhi\\");
                  $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
                   Excel::create($filename."_RESULTS", function($excel) use ($excel_data) {
                      $excel->sheet("Claim Results", function($sheet) use ($excel_data) {
                          $sheet->fromArray($excel_data);
                      }); 
                  })
                   ->save('xlsx',$path);



                  $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
                  return Excel::create("$province"."_".$municipality."_RESULTS", function($excel) use ($excel_data) {
                      $excel->sheet("BENEFICIARY NOT IN LIST", function($sheet) use ($excel_data) {
                          $sheet->fromArray($excel_data);
                          $sheet->freezeFirstRow();
                      });
                  })->download('xlsx');
      

     
     
        }



    


  }

  }

private function search_to_array($array, $key, $value) {
        $results = array();
    
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
    
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_to_array($subarray, $key, $value));
            }
        }
    
        return $results;
    }




    public function index(){

    return view("paymaya.reports.payments.home");
    
    }

    public function manual_payment(){
        $coop = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_coop_payment_details")
            ->where("is_active", 1)
            ->get();


      return view("paymaya.manual-payment")
        ->with("coop", $coop);
    }

    public function manual_form($type, $data, $date3){
      $bags_amount = 880;
      $retention_rate = 0.01;
      $towords = new NumberFormatter("en", NumberFormatter::SPELLOUT);


      $data_arr = explode(',',$data);
      $coop_data= array();

      $total_bags = 0;
      foreach($data_arr as $dat){
        $details = explode(";",$dat);

        $coop_id = $details[0];
        $total_bags += $details[1];

         $coop_details =  DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details')
            ->where("id", $coop_id)
            ->get();

          if(isset($coop_data[$coop_id])){

              $coop_data[$coop_id]["bags"] = $coop_data[$coop_id]["bags"] + $details[1];
              $inwords="";
              $sales =  $coop_data[$coop_id]["bags"] * $bags_amount;
              $temp_amount = explode(".", $sales);
              $whole = $temp_amount[0];
              // dd($whole);
              $whole_words = $towords->format($whole);
              $inwords .=  strtoupper($whole_words." PESOS");
                if(isset($temp_amount[1])){
                  $decimal = $temp_amount[1];
                  if(strlen($decimal) == 1){
                    $decimal = $decimal."0";
                  }
                  $decimal_words = $towords->format($decimal);
                  $decimal_words = strtoupper($decimal_words);
                  $inwords .= " and ".$decimal_words." CENTAVOS ONLY";
                }

                
              $coop_data[$coop_id]["amount_float"] = $sales;
              $coop_data[$coop_id]["amount"] = number_format($sales,2);
              $coop_data[$coop_id]["word"] = strtoupper($inwords);
              
          }else{
            $inwords="";
            $sales =  $details[1] * $bags_amount;
            $temp_amount = explode(".", $sales);
            $whole = $temp_amount[0];
         
            $whole_words = $towords->format($whole);
           
          
            $inwords .=  strtoupper($whole_words." PESOS");
              if(isset($temp_amount[1])){
                 $decimal = $temp_amount[1];
                 if(strlen($decimal) == 1){
                  $decimal = $decimal."0";
                }
                $decimal_words = $towords->format($decimal);
                $decimal_words = strtoupper($decimal_words);
                 $inwords .= " and ".$decimal_words." CENTAVOS ONLY";
              }
            
            $coop_data[$coop_id]["bags"] = $details[1];
            $coop_data[$coop_id]["amount_float"] = $sales;
            $coop_data[$coop_id]["amount"] = number_format($sales,2);
            $coop_data[$coop_id]["word"] = strtoupper($inwords);
            $coop_data[$coop_id]["details"] = $coop_details;
          }


      }
      
      $total_arrs = array();

      $total_arrs["total_bags"] = $total_bags;
           $inwords="";
              $sales =  $total_bags * $bags_amount;
              $temp_amount = explode(".", $sales);
              $whole = $temp_amount[0];
              $whole_words = $towords->format($whole);
              $inwords .=  strtoupper($whole_words." PESOS");
                if(isset($temp_amount[1])){
                  $decimal = $temp_amount[1];
                  if(strlen($decimal) == 1){
                    $decimal = $decimal."0";
                  }
                  $decimal_words = $towords->format($decimal);
                  $decimal_words = strtoupper($decimal_words);
                  $inwords .= " and ".$decimal_words." CENTAVOS ONLY";
                }
                
      $total_arrs["total_sales_float"] = $sales;      
      $total_arrs["total_sales"] = number_format($sales,2);
      $total_arrs["total_words"] = strtoupper($inwords);

      $total_arrs["net"] = $sales - ($sales * $retention_rate);
      $total_arrs["net_format"] = number_format($total_arrs["net"],2);
      
      
      $inwords="";
      $temp_amount = explode(".", $total_arrs["net"]);
      $whole = $temp_amount[0];
      $whole_words = $towords->format($whole);
      $inwords .=  strtoupper($whole_words." PESOS");
        if(isset($temp_amount[1])){
          $decimal = $temp_amount[1];
          
          if(strlen($decimal) == 1){
            $decimal = $decimal."0";
          }
          $decimal_words = $towords->format($decimal);
          $decimal_words = strtoupper($decimal_words);
          $inwords .= " and ".$decimal_words." CENTAVOS ONLY";
        }
        $total_arrs["net_words"] = $inwords;

        DB::beginTransaction();
        try{

    $signatory1 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',1)
        ->first();

    $signatory2 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',2)
        ->first();
    
    $signatory3 = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
        ->where('signatory_id','=',3)
        ->first();

   
     
        DB::commit();
      } catch (\Exception $e) {
        DB::rollback();

    }



      if($type == "oth"){
        $pdf = PDFTIM::loadView('paymaya.payment_manual_oth',
        compact('total_arrs','coop_data','date3','signatory1','signatory2','signatory3'))
        ->setPaper('A4', 'landscape');
        $pdf_name = "E-Binhi payment form Other bank".".pdf";
        return $pdf->stream($pdf_name); 
      }elseif($type == "dbp"){
        $pdf = PDFTIM::loadView('paymaya.dbp_manual_form',compact('total_arrs','coop_data','date3','signatory1','signatory2','signatory3'))
        ->setPaper('A4', 'portrait');
        $pdf_name = "E-Binhi payment form DBP".".pdf";
        return $pdf->stream($pdf_name);


      }

       

    }








    


    public function signatories(){

      return view("paymaya.reports.payments.signatories");
      
      }

    public function payment_frm_dpb($date1,$date2,$date3,$coop){
      
      $coop = explode(',',$coop);
      $coop_data= array();
      foreach ($coop as $value) {
        $value = str_replace("_","/",$value);
        array_push($coop_data, $value);
      }

        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1r3= explode('-',$date3);
        $date1 = $date1r[2].'-'.$date1r[0].'-'.$day = $date1r[1].' '.'16:00:01'; 
        $date1 = date('Y-m-d H:i:s',strtotime( '-1 day',strtotime($date1)));
        $date2 = $date2r[2].'-'.$date2r[0].'-'.$day = $date2r[1].' '.'16:00:00';
        $date3 = $date1r3[2].'-'.$date1r3[0].'-'.$date1r3[1];
        $date3 = date('F d, Y',strtotime($date3));

        DB::beginTransaction();
        try{

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
           ->whereIn('a.coopAccreditation',$coop_data)
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
            ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 880 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 880)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 880) * 0.99 as net_amount'))
                ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
                ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
                ->groupby('a.coopAccreditation')
                ->where('b.form_type','=','ADVICE TO DEBIT/CREDIT')
                ->whereIn('a.coopAccreditation',$coop_data)
                // ->where('a.is_paid','=','0')
                ->get();

            foreach ($table_details as $value) {
              DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                        ->where('coopAccreditation', $value->coopAccreditation)
                        ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
                        ->update([
                            'is_paid' => 1,
                        ]);
            }

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
      }

    $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_dbp_summary_PDF',compact('table_details','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3' ))
    ->setPaper('A4', 'landscape');
    $pdf_name = "E-Binhi payment form DBP".".pdf";
    return $pdf->stream($pdf_name);

    }

    public function payment_frm_dpb2($date1,$date2,$date3,$coop){

      
      $coop = explode(',',$coop);
      $coop_data= array();
      foreach ($coop as $value) {
        $value = str_replace("_","/",$value);
        array_push($coop_data, $value);
      }
      
        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1r3= explode('-',$date3);
        $date1 = $date1r[2].'-'.$date1r[0].'-'.$day = $date1r[1].' '.'16:00:01'; 
        $date1 = date('Y-m-d H:i:s',strtotime( '-1 day',strtotime($date1)));
        $date2 = $date2r[2].'-'.$date2r[0].'-'.$day = $date2r[1].' '.'16:00:00';
        $date3 = $date1r3[2].'-'.$date1r3[0].'-'.$date1r3[1];
        $date3 = date('F d, Y',strtotime($date3));

        DB::beginTransaction();
        try{

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
           ->whereIn('a.coopAccreditation',$coop_data)
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

              // dd($signatory1,$signatory2,$signatory3);

          $table_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
            ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 880 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 880)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 880) * 0.99 as net_amount'))
                ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
                ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
                ->groupby('a.coopAccreditation')
                ->where('b.form_type','=','ADVICE TO DEBIT/CREDIT')
                ->whereIn('a.coopAccreditation',$coop_data)
                // ->where('a.is_paid','=','0')
                ->limit(5)
                ->get();
                // dd($table_details);

            foreach ($table_details as $value) {
              DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                        ->where('coopAccreditation', $value->coopAccreditation)
                        ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
                        ->update([
                            'is_paid' => 1,
                        ]);
            }

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

        // dd($overall_net_amount2);
        
        $r=0;

        DB::commit();
        } catch (\Exception $e) {
          DB::rollback();
      }

    $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_dbp_summary_PDF2',compact('table_details','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3' ))
    ->setPaper('A4', 'portrait');
    $pdf_name = "E-Binhi payment form DBP".".pdf";
    return $pdf->stream($pdf_name);

    }
      //aaaaa
      public function payment_frm_other($date1,$date2,$date3,$coop){
        
        $coop = explode(',',$coop);
        $coop_data= array();
        foreach ($coop as $value) {
          $value = str_replace("_","/",$value);
          array_push($coop_data, $value);
        }

        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1r3= explode('-',$date3);
        $date1 = $date1r[2].'-'.$date1r[0].'-'.$day = $date1r[1].' '.'16:00:01'; 
        $date1 = date('Y-m-d H:i:s',strtotime( '-1 day',strtotime($date1)));
        $date2 = $date2r[2].'-'.$date2r[0].'-'.$day = $date2r[1].' '.'16:00:00';
        $date3 = $date1r3[2].'-'.$date1r3[0].'-'.$date1r3[1];
        $date3 = date('F d, Y',strtotime($date3));

        DB::beginTransaction();
        try{

        $button_dl = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
          ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
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
     ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 880 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 880)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 880) * 0.99 as net_amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        ->whereIn('a.coopAccreditation',$coop_data)
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

        $r=0;

        DB::commit();
      } catch (\Exception $e) {
        DB::rollback();

    }

        $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_others_PDF',compact('table_details','other','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3'))
        ->setPaper('A4', 'landscape');
        $pdf_name = "E-Binhi payment form Other bank".".pdf";
        return $pdf->stream($pdf_name);  
            
      }
  

      public function coop_table(Request $request){
        $date1 = $request->date1;
        $date2 = $request->date2;
        
        // $date1 = '07/16/2024';
        // $date2 = '07/16/2024';
        $date1 = date("Y-m-d", strtotime($date1));
        $date2 = date("Y-m-d", strtotime($date2));
        // $date1 = Carbon::createFromFormat('m/d/Y H:i:s', $request->date1 . ' 00:00:00')->format('Y-m-d H:i:s');
        // $date2 = Carbon::createFromFormat('m/d/Y H:i:s', $request->date2 . ' 00:00:00')->format('Y-m-d H:i:s');


        // if ($date1 == $date2) {
        //     $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $date2)->endOfDay()->format('Y-m-d H:i:s');
        // }
        $date1r= explode('-',$date1);
        $date2r= explode('-',$date2);
        $date1 = $date1r[0].'-'.$date1r[1].'-'.$day = $date1r[2].' '.'16:00:01'; 
        $date1 = date('Y-m-d H:i:s',strtotime( '-1 day',strtotime($date1)));
        $date2 = $date2r[0].'-'.$date2r[1].'-'.$day = $date2r[2].' '.'16:00:00';

        // $test = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
        //   ->select(DB::raw('COUNT(*) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("COUNT(CASE WHEN is_paid = 1 THEN 1 END) AS paid_count"))
        //   ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        //   ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        //   ->groupby('a.coopAccreditation')
        //   ->get();
          // dd($test);
        // dd($date1,$date2);
          return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim AS a')
          ->select(DB::raw('COUNT(*) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("COUNT(CASE WHEN is_paid = 1 THEN 1 END) AS paid_count"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->groupby('a.coopAccreditation')
          // ->where('a.is_paid','=','0')
          )

          ->addColumn('select', function($row){                   
            return '<input type="checkbox" class="coop_check form-group" name="selected_coop" value="'.$row->coopAccreditation.'" id="'.$row->coopAccreditation.'">';   
          }) 
          ->addColumn('coop', function($row){                   
              return $row->coop_name;
          })        
          ->addColumn('bags', function($row){    
            $show = "<label class='badge badge-warning'>".number_format($row->paid_count)." processed </label>";
            $show .= "<label class='badge badge-success'>".number_format($row->total_bags)." distributed </label>";

            return $show;
          })
          ->addColumn('amount', function($row){    
            $total_b = $row->total_bags * 880;
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

          ->addColumn('print_dbp', function($row){ 
            if($row->form_type=='ADVICE TO DEBIT/CREDIT'){
              return '<button class="btn btn-success btn-sm" data-id="'.$row->coop_name.'" id="btn_payment_dbp_individual"><i class="fa fa-download"></i> DBP Form</button>';
            }
            // else{
            //   return '<button class="btn btn-default btn-sm" data-id="'.$row->coop_name.'" id=""><i class="fa fa-download"></i> LBD Form</button>';
            // }
            
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
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        // ->where('a.is_paid','=','0')
        ->groupby('a.coopAccreditation')
        ->get();

       $db = 1;
       $other = 1;

       foreach ($button_dl as $value) {
    
          if($value->form_type == "FUND TRANSFER FORM"){
            $other++;
          }if($value->form_type == "ADVICE TO DEBIT/CREDIT"){
            $db++;
          }   
       }
       return compact('other','db');
      }

      public function generate_report($from,$to,$coop){
          
        $coop = explode(",",$coop);
        foreach($coop as $key =>  $coopa){
          $coop[$key] = str_replace("_","/",$coopa);
          // dd($coopa);
        }
        //  dd($coop);

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
                 ->whereIn("coopAccreditation", $coop)
                ->orderBy('paymaya_code', 'ASC')
                ->orderBy("date_created", 'ASC')
                ->get();
          
                //  dd($provincial_data);
  
            $province_arr = array();
            foreach($provincial_data as $row){
                $beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->where('paymaya_code', $row->paymaya_code)->first();
               // dd($beneficiary_details);
               
                if(count($beneficiary_details)<=0){
                     dd($row->beneficiary_id);
                    $coop_accre_new = "-";  
                }else{
                  $coop_accre_new = $beneficiary_details->coop_accreditation;
                }

                $seedtag_explode = explode("/",$row->seedTag);
                $int = (int) filter_var($seedtag_explode[0], FILTER_SANITIZE_NUMBER_INT);
                $int = str_ireplace("-","",$int);
//                    dd( $beneficiary_details->coop_accreditation);
                $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no", $coop_accre_new)->value("coopName");
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
            return Excel::create("eBinhi_"."report_".$GLOBALS['season_prefix'].date("Y-m-d g:i A"), function($excel) use ($excel_data) {
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

    public function payment_frm_dpb_indi($date1,$date2,$date3,$coop_name){
 
      $coop = str_replace("_"," ",$coop_name);
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
        ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->where("b.coop_name", $coop)
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
     ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 880 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 880)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 880) * 0.99 as net_amount'))
        ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
        ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
        ->where("b.coop_name", $coop)
        ->where('b.form_type','=','ADVICE TO DEBIT/CREDIT')
        ->groupby('a.coopAccreditation')
        ->first();

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
      $overall_bags  += $table_details->total_bags;
      $overall_amount += $table_details->amount;
      $overall_retention += $table_details->retention;
      $overall_net_amount += $table_details->net_amount;
        
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
      
      $word_number = 'sasa';
       if(isset($tmpData[1])){
        $word_number = strtoupper($FstringWord." PESOS"." and ".$LstringWord." CENTAVOS ONLY");
       }else{
        $word_number = strtoupper($FstringWord." PESOS ONLY");
       }

     $r=0;

     DB::commit();
    } catch (\Exception $e) {
      DB::rollback();
    }

    $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_dbp_PDF',compact('table_details','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3' ))
    ->setPaper('A4');
    $pdf_name = "E-Binhi payment form DBP".".pdf";
    return $pdf->stream($pdf_name); 
        
      }



      public function payment_frm_dpb_indi_v2($date1,$date2,$date3,$coop_name){
 
        $coop = str_replace("_"," ",$coop_name);
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
          ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.claimId as claimed_id','a.*','b.*', DB::raw('COUNT(a.date_created)  * 880 as amount'), DB::raw("STR_TO_DATE(a.date_created, '%Y-%m-%d') as actual_date"))
            ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
            ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
            ->where("b.coop_name", $coop)
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
       ->select(DB::raw('COUNT(a.beneficiary_id) as total_bags'),'a.*','b.*', DB::raw('COUNT(a.beneficiary_id)  * 880 as amount'), DB::raw('(COUNT(a.beneficiary_id)  * 880)* 0.01 as retention'),  DB::raw('(COUNT(a.beneficiary_id)  * 880) * 0.99 as net_amount'))
          ->join($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details as b', 'b.coop_ref' ,'=','a.coopAccreditation')
          ->whereRaw("STR_TO_DATE(a.date_created, '%Y-%m-%d %H:%i:%s') between STR_TO_DATE('".$date1."', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('".$date2."', '%Y-%m-%d %H:%i:%s')")
          ->where("b.coop_name", $coop)
          ->where('b.form_type','=','ADVICE TO DEBIT/CREDIT')
          ->groupby('a.coopAccreditation')
          ->first();
      // dd($table_details);
  
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
        $overall_bags  += $table_details->total_bags;
        $overall_amount += $table_details->amount;
        $overall_retention += $table_details->retention;
        $overall_net_amount += $table_details->net_amount;
          
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
        
        $word_number = 'sasa';
         if(isset($tmpData[1])){
          $word_number = strtoupper($FstringWord." PESOS"." and ".$LstringWord." CENTAVOS ONLY");
         }else{
          $word_number = strtoupper($FstringWord." PESOS ONLY");
         }
  
       $r=0;
  
       DB::commit();
      } catch (\Exception $e) {
        DB::rollback();
      }

      dd($table_details);
  
      $pdf = PDFTIM::loadView('paymaya.reports.payments.payment_dbp_PDF',compact('table_details','r','overall_bags','overall_amount','overall_retention','overall_net_amount','word_number','date3','signatory1','signatory2','signatory3' ))
      ->setPaper('A4');
      $pdf_name = "E-Binhi payment form DBP".".pdf";
      return $pdf->stream($pdf_name); 
          
        }

}
