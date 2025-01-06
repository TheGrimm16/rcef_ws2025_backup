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

class EbinhiCoopsController extends Controller
{


    public function index(){
      return view("paymaya.reports.payments.coop_details");  
      }

      public function coop_tbl(){

        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details')
        ->select('id','coop_name', 'acronym', 'address_1','branch','account_no','form_type')
        ->orderby('coop_name')
        )
        ->addColumn('id', function($row){  
            return '<button class="btn btn-success" id="edit_modal" data-id='.$row->id.' ><i class="fa fa-pencil-square-o"></i> Update</button>';
        })
        
        ->make(true);
      }


      public function update(Request $request){
        DB::beginTransaction();
        try {
            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details')
                    ->where('id',$request->coop_id)
                    ->update([
                        'coop_name' => $request->coop_name,
                        'acronym' => $request->acronym,
                        'address_1' => $request->address,
                        'branch' => $request->branch,
                        'account_no' => $request->account_no,
                        'is_dbp' => $request->is_dbp,
                        'coop_ref' => $request->accreditation_no,
                        'form_type' => $request->form_type         
            ]);

            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        } 
      }


      public function isert(Request $request){
        DB::beginTransaction();
        try {
            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details')
                    ->where('id',$request->coop_id)
                    ->update([
                        'coop_name' => $request->coop_name,
                        'acronym' => $request->acronym,
                        'address_1' => $request->address,
                        'branch' => $request->branch,
                        'account_no' => $request->account_no,
                        'is_dbp' => $request->is_dbp,
                        'coop_ref' => $request->accreditation_no,
                        'form_type' => $request->form_type         
            ]);

            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        } 
      }



      public function get(Request $request){

       return  $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_coop_payment_details') 
              ->where('id',$request->coop_id)
              ->get();

   
      }
    


      
}


