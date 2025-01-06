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

class PaymayaPaymentSignatoriesController extends Controller
{


    public function index(){
      return view("paymaya.reports.payments.signatories");  
      }

      public function signatories_tbl(){

        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories')
        ->select('tbl_payment_signatories.signatory_id', 'tbl_payment_signatories.full_name', 'tbl_payment_signatories.designation','tbl_payment_signatories.date_created')
        )
        ->addColumn('status', function($row){  
            return '<button class="btn btn-success" id="edit_modal" data-id='.$row->signatory_id.' ><i class="fa fa-pencil-square-o"></i> Update</button>';
        })
        
        ->make(true);
      }


      public function update(Request $request){

        $signatory_id = $request->signatory_id;
        $full_name = $request->full_name;
        $designation = $request->designation;

        DB::beginTransaction();
        try {
            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories')
                    ->where('signatory_id', $signatory_id)
                    ->update([
                        'full_name' => $full_name,
                        'designation' => $designation
            ]);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        } 
      }



      public function get(Request $request){
        $signatory_id = $request->signatory_id;

       return  $signatory = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_payment_signatories') 
              ->where('signatory_id',$signatory_id)
              ->get();

   
      }
    


      
}


