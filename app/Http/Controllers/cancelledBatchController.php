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
use Config;
use DB;
use Excel;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;

use Session;
use Auth;

class cancelledBatchController extends Controller
{
      public function index()
    {
    	$monthlyfrom = date('Y-m-01');
    	$monthlyto = date('Y-m-d');

         $cancel_list = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs AS l')
         	->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction as d', 'l.description', '=', 'd.id')
         	->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'd.moa_number','=','c.current_moa')
				->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point AS p', 'd.prv_dropoff_id','=', 'p.prv_dropoff_id')
         	
            ->select('l.author as user','l.description as id', 'l.date_recorded as dateRecorded', 'd.delivery_date as dateDeliver', 'd.prv_dropoff_id', DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS point"),'d.instructed_delivery_volume as volume','c.coopName as coop','d.batchTicketNumber as batch_ticket')
            ->where('l.category', 'CANCEL_PENDING_BATCH')
            ->whereBetween(DB::raw('cast(d.delivery_date as Date)'),[$monthlyfrom,$monthlyto])
            ->groupBy('id')
            ->orderBy('d.delivery_date', 'DESC')
            ->get();   //FOR PSTOCS
			
        return view('cancelledBatch.home')
             ->with('cancel_list', $cancel_list)
             ->with('filterFrom', strtotime($monthlyfrom))
             ->with('filterTo', strtotime($monthlyto));
    }



    public function generateHistoryData(Request $request){
    	$monthlyfrom = date('Y-m-d', strtotime($request->datefrom));
		$monthlyto = date('Y-m-d', strtotime($request->dateto));
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs AS l')
         	->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction as d', 'l.description', '=', 'd.id')
         	->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'd.moa_number','=','c.current_moa')
				->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point AS p', 'd.prv_dropoff_id','=', 'p.prv_dropoff_id')
         	
            ->select('l.author as user','l.description as id', 'l.date_recorded as dateRecorded', 'd.delivery_date as dateDeliver', 'd.prv_dropoff_id', DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS point"),'d.instructed_delivery_volume as volume','c.coopName as coop','d.batchTicketNumber as batch_ticket')
            ->where('l.category', 'CANCEL_PENDING_BATCH')
            ->whereBetween(DB::raw('cast(d.delivery_date as Date)'),[$monthlyfrom,$monthlyto])
            ->groupBy('id')
            ->orderBy('d.delivery_date', 'DESC')         
            )
            //->addColumn('action', function($row){	
            //    return "  <button class='btn btn-warning btn-xs' onclick='redoMe(this.value);' name='redo".$row->id."' id='redo' value='".$row->id."''>Redo</button>";

            //})
             ->make(true);
    }



    public function processRedo(Request $request){ //CSTOCS ALL

    	$batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
		->where('id', $request->id)
		->update(['status'=>0]); 

		//LOGS
          DB::connection('mysql')->table('lib_logs')
          ->where('description', $request->id)
          ->where('category', 'CANCEL_PENDING_BATCH')
          ->update(['category'=>'REDO_PENDING_BATCH']);

		return "SUCCESS CANCEL";
    }
	
	 public function reAuditLog(){ //CSTOCS ALL

        $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction as t')
        ->whereNotExists(function($query)
        {

            $query->select(DB::raw(1))
                  ->from('sdms_db_dev.lib_logs as l')
                  ->whereRaw('l.description = t.id');
        }   
            )
        ->where('t.status',3)
        ->get();

        $arr_data = json_decode(json_encode($batch), true); //convert collection to associative array to be converted to excel
             
        $affectedRows = "";
        foreach ($arr_data as $key => $value) {
           $transactionId = $arr_data[$key]['id'];
           $affectedRows .= $transactionId.'| |';
           //LOGS
             DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'CANCEL_PENDING_BATCH',
                'description' => $transactionId, //TABLE DELIVERY TRANSACTION
                'author' => 'from_cancel_v1',
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }
        return $affectedRows;
    }





}
