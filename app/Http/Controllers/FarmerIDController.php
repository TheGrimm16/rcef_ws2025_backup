<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use QrCode;
use PDF;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

use PDFTIM;
use DOMPDF;
use Session;
use Auth;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\Datatables\Facades\Datatables;


class FarmerIDController extends Controller
{
	public function extension_qr(){
		$calendar_filename = "https://rcef-seed.philrice.gov.ph/rcef_ws2022/public/images/extension/calendar.jpg";
		return view('extension.qr_sample.home')->with('calendar_filename', $calendar_filename);
	}
	
    public function FarmerList(){
        $farmers = DB::connection('farmer_db')->table('tbl_farmer_profile')
                        ->join('tbl_area_history', 'tbl_farmer_profile.farmerId', '=', 'tbl_area_history.farmerId')
                        ->get();
        dd($farmers);
    }

    public function QRCode(){
        //QrCode::size(400);
        //$qrCode = QrCode::generate('030490371600001');
        
        $pdf = PDF::loadView('farmer.id')->setPaper('a4','landscape');
        return $pdf->stream();
    
        //return view('farmer.id');
    }

    public function generateDocx(){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($spreadsheet);
        $writer->save('hello world.xlsx');
    }

    public function generateQR2(Request $request){
        $status = DB::connection('farmer_id_db')->table('area_list')
                    ->where('region', '=', $request->region)
                    ->where('province', '=', $request->province)
                    ->first();
					
        if(count($status) > 0){
            $areaID = $status->areaID;
            $previous_count = $status->currentCount;
            
            
            $set_max_batch = DB::connection('mysql')->table('lib_settings')
                ->where('setting_code', '=', 'QRMAX')
                ->first()->setting_value;

            //get last batchID
            $batch_details = DB::connection('farmer_id_db')->table('id_list')
                ->where('areaID', '=', $request->region.$request->province)
                ->orderBy('batch_number', 'DESC')
                ->first();

            //for integration of old data
            if(count($batch_details) > 0){
                $batchID = $batch_details->batchID;
            }else{
                $batchID = $request->province."-0";
            }
			
            DB::connection('farmer_id_db')->table('area_list')
                ->where('areaID', $status->areaID)
                ->update(array(
                    'currentCount' => $status->currentCount + $request->QRLimit,
                    'dateUpdated' => date("Y-m-d H:i:s")
                ));
            

            //create ID
            $set_max_batch = DB::connection('mysql')->table('lib_settings')
                ->where('setting_code', '=', 'QRMAX')
                ->first()->setting_value + $status->currentCount;

            $new_batch_postfix = (int)substr($batchID,5) + 1;
            $new_batchID = $request->province.'-'.$new_batch_postfix;

            for ($i=$status->currentCount + 1; $i <= $status->currentCount + $request->QRLimit; ++$i) { 
                
                if($i > $set_max_batch){
                    $new_batch_postfix = (int)substr($new_batchID,5) + 1;
                    $new_batchID = $request->province.'-'.$new_batch_postfix;

                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$request->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $areaID,
                        'batchID' => $new_batchID,
                        'batch_number' => $new_batch_postfix,
                        'is_printed' => '0',
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);

                    $new_batchID = $new_batchID;
                    $set_max_batch += $set_max_batch;
                }else{
                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$request->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $areaID,
                        'batchID' => $new_batchID,
                        'batch_number' => $new_batch_postfix,
                        'is_printed' => '0',
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);
                }
            }
			
        }else{
            $area_id = DB::connection('farmer_id_db')->table('area_list')
            ->insertGetId([
                'areaID' => $request->region.$request->province,
                'region' => $request->region,
                'province' => $request->province,
                'currentCount' => $request->QRLimit,
                'dateCreated' => date("Y-m-d H:i:s"),
                'dateUpdated' => date("Y-m-d H:i:s")    
            ]);
			
			$previous_count = 0;

            //get areaID
            $areaID = DB::connection('farmer_id_db')->table('area_list')
                    ->where('id', '=', $area_id)
                    ->first()
                    ->areaID;

            //create ID
            $set_max_batch = DB::connection('mysql')->table('lib_settings')
                ->where('setting_code', '=', 'QRMAX')
                ->first()->setting_value;
                
            $batchID = $request->province.'-1';
            for ($i=1; $i <= $request->QRLimit; ++$i) {
                if($i > $set_max_batch){

                    $new_batch_postfix = (int)substr($batchID,5) + 1;
                    $new_batchID = $request->province.'-'.$new_batch_postfix;

                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$request->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $areaID,
                        'batchID' => $new_batchID,
                        'batch_number' => $new_batch_postfix,
                        'is_printed' => '0',
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);

                    $batchID = $new_batchID;
                    $set_max_batch += $set_max_batch;
                }else{
                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$request->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $areaID,
                        'batchID' => $batchID,
                        'batch_number' => 1,
                        'is_printed' => '0',
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);
                } 
                
            }
        }

        //save files by batch
        $batch_groups = DB::connection('farmer_id_db')->table('id_list')
            ->where('is_printed', '=', 0)
            ->groupBy('batchID')
            ->get();

        $data_array = array();
        foreach($batch_groups as $batch){
            $qr_per_batch = DB::connection('farmer_id_db')->table('id_list')
                ->where('is_printed', '=', 0)
                ->where('batchID', '=', $batch->batchID)
                ->orderBy('generatedID', 'ASC')
                ->get();
            
            $data = [ 'id_list' => $qr_per_batch ];
            $pdf = PDFTIM::loadView('farmer.idNew', $data)->setPaper('a4', 'landscape');
            $pdf->save("public/file/$batch->batchID.pdf");
            //$pdf->download("$batch->batchID.pdf");

            //after saving the file update the is_printed flag to 1
            DB::connection('farmer_id_db')->table('id_list')
                ->where('is_printed', '=', 0)
                ->where('batchID', '=', $batch->batchID)
                ->update(array(
                    'is_printed'=>1,
                ));
            $data_array[] = $batch->batchID.".pdf";
        }

        return $data_array;

        //return view('farmer.id');
		/*$id_summary = DB::connection('farmer_id_db')->table('id_list')
            ->where('areaID', '=', $areaID)
            ->where('idCount', '>', $previous_count)
            ->get();

        $data = [ 'id_list' => $id_summary ];
        //$pdf_obj = new PDFTIM;
        $pdf = PDFTIM::loadView('farmer.id2', $data)->setPaper('a4', 'landscape');
        $pdf->save("public/file/$request->province.pdf");
        //return $pdf->stream("$request->province.pdf");
        */
    }
	
	public function generateQR_ws2020(Request $request){

        if($request->QRLimit <= 200){
            $status = DB::connection('farmer_id_db')->table('area_list')
                    ->where('region', '=', $request->region)
                    ->where('province', '=', $request->province)
                    ->first();
                        
            if(count($status) > 0){
                $areaID = $status->areaID;
                $previous_count = $status->currentCount;
                
                DB::connection('farmer_id_db')->table('area_list')
                    ->where('areaID', $status->areaID)
                    ->update(array(
                        'currentCount' => $status->currentCount + $request->QRLimit,
                        'dateUpdated' => date("Y-m-d H:i:s")
                    ));
                //create ID
                for ($i=$status->currentCount + 1; $i <= $status->currentCount + $request->QRLimit; ++$i) { 
                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$status->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $status->areaID,
                        'batchID' => 0,
                        'batch_number' => 0,
                        'is_printed' => 0,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);
                }
                
            }else{
                $area_id = DB::connection('farmer_id_db')->table('area_list')
                ->insertGetId([
                    'areaID' => $request->region.$request->province,
                    'region' => $request->region,
                    'province' => $request->province,
                    'currentCount' => $request->QRLimit,
                    'dateCreated' => date("Y-m-d H:i:s"),
                    'dateUpdated' => date("Y-m-d H:i:s")    
                ]);
                
                $previous_count = 0;

                //get areaID
                $areaID = DB::connection('farmer_id_db')->table('area_list')
                        ->where('id', '=', $area_id)
                        ->first()
                        ->areaID;

                //create ID
                for ($i=1; $i <= $request->QRLimit; ++$i) { 
                    $count = sprintf("%'06d", $i);
                    DB::connection('farmer_id_db')->table('id_list')
                    ->insert([
                        'idCount' => $i,
                        'generatedID' => 'P63'.$request->province.'000'.$count,
                        'farmerID' => 0,
                        'areaID' => $areaID,
                        'batchID' => 0,
                        'batch_number' => 0,
                        'is_printed' => 0,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'dateUpdated' => date("Y-m-d H:i:s")   
                    ]);
                }
            }
            
            //fetch all IDs in area
            /*$id_list = DB::connection('farmer_id_db')->table('id_list')->where('areaID', '=', $areaID)->where('farmerID', '=', '0')->get();

            $pdf = PDF::loadView('farmer.id',['id_list' => $id_list])->setPaper('a4')->setOrientation('landscape');
            $pdf->setOption('margin-top',0.25);
            $pdf->setOption('margin-bottom',10);
            $pdf->setOption('margin-left',0.25);
            $pdf->setOption('margin-right',0.25);
            return $pdf->inline('farmerID.pdf');*/
        
            //return view('farmer.id');
            $no_to_print = $request->QRLimit;
            
            $id_summary = DB::connection('farmer_id_db')->table('id_list')->where('areaID', '=', $areaID)->where('idCount', '>', $previous_count)->get();

            $data = [ 'id_list' => $id_summary ];
            $pdf = PDFTIM::loadView('farmer.idNew', $data)->setPaper('a4', 'landscape');
            return $pdf->stream('farmer_qr_code.pdf');
        }else{
            Session::flash('error_msg', 'Exceeded maximum QR Print');
            return redirect()->route('farmer.id.home');
        }
    }
	
	public function generateQR(Request $request){
       //dd($request->region);

       if($request->region == "0"){
            Session::flash('error_msg', 'Please select a region.');
            return redirect()->route('farmer.id.home');

       }else{
            if($request->QRLimit <= 200 AND $request->QRLimit > 0){
                $no_to_print = $request->QRLimit;

                //get current count of region
                $region_current_count = DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_list')->where('region_code', $request->region)->first();
                $start_count = 0;
                $end_count = 0;

                if(count($region_current_count) > 0){
                    //update current record
                    $region_details = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_regions')->where('regCode', $request->region)->first();
                    DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_list')
                    ->where('id', $region_current_count->id)
                    ->update(array(
                        'current_count' => $region_current_count->current_count + $no_to_print,
                        'date_updated' => date("Y-m-d H:i:s")
                    ));

                    //log data
                    $log_data = array(
                        "username" => Auth::user()->username,
                        "remarks" => "Requested to generate $no_to_print QR Codes.",
                        "qr_volume" => $no_to_print,
                        "region" => $region_details->regDesc,
                        "date_recorded" => date("Y-m-d H:i:s")
                    );
                    DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_logs')->insert($log_data);

                    $start_count = $region_current_count->current_count + 1;
                    $end_count = $region_current_count->current_count + $no_to_print;
                }else{
                    //for insert of new record
                    $region_details = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_regions')->where('regCode', $request->region)->first();
                    
                    //qr data
                    $insert_data = array(
                        "region" => $region_details->regDesc,
                        "region_code" => $region_details->regCode,
                        "current_count" => $no_to_print,
                        "date_updated" => date("Y-m-d H:i:s")
                    );
                    DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_list')->insert($insert_data);

                    //log data
                    $log_data = array(
                        "username" => Auth::user()->username,
                        "remarks" => "Requested to generate $no_to_print QR Codes.",
                        "qr_volume" => $no_to_print,
                        "region" => $region_details->regDesc,
                        "date_recorded" => date("Y-m-d H:i:s")
                    );
                    DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_logs')->insert($log_data);

                    $start_count = 1;
                    $end_count = $no_to_print;
                }

                $pdf = PDFTIM::loadView('farmer.QID', ['start_count' => $start_count, 'end_count' => $end_count, 'region_code' => $region_details->regCode])->setPaper('a4', 'landscape');        
                $pdf_name = "R".$region_details->regCode."_".$start_count."_to_".$end_count.".pdf";
                return $pdf->stream($pdf_name);
            }else{
                Session::flash('error_msg', 'Please observe the following: (1) You must select a region. (2) The volume of QR code for generation must not be set to zero. (3) The volume of QR Code for generation must not exceed the maximum amount of 200 QR Codes per execution.');
                return redirect()->route('farmer.id.home');
            }
       }        
    }
	
	public function get_QRlogs(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_logs')
            ->orderBy('date_recorded', 'DESC')
        )
        ->addColumn('dateRecorded', function($row){
            return date("F j, Y g:i A", strtotime($row->date_recorded));
        })
        ->make(true);
    }

    public function get_QRChart(Request $request){
        $region_list = DB::table($GLOBALS['season_prefix'].'rcep_distribution_id_new.region_logs')
            ->select(DB::raw('SUM(qr_volume) as total_volume'), 'region')
            ->groupBy('region')
            ->orderBy('total_volume', 'DESC')
            ->get();

        $region_array = array();
        $volume_array = array();

        foreach($region_list as $row){
            array_push($region_array, $row->region);
            array_push($volume_array, intval($row->total_volume));
        }

        return array(
            "regions" => $region_array,
            "volumes" => $volume_array
        );
    }

    public function areaGenerateQR($areaID){
       /* $id_list = DB::connection('farmer_id_db')->table('id_list')->where('areaID', '=', $areaID)->get();
        $pdf = PDF::loadView('farmer.id',['id_list' => $id_list])->setPaper('a4')->setOrientation('landscape');
        $pdf->setOption('margin-top',4);
        $pdf->setOption('margin-bottom',10);
        $pdf->setOption('margin-left',2);
        $pdf->setOption('margin-right',2);
        return $pdf->inline('farmerID.pdf');*/

        $id_summary = DB::connection('farmer_id_db')->table('id_list')
            ->where('areaID', '=', $areaID)
            ->where('farmerID', '=', '0')
            ->get();

        $data = [ 'id_list' => $id_summary ];
        $pdf = PDFTIM::loadView('farmer.id2', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('id2.pdf');

        //return view('farmer.id2', $data);

        //$id_list = DB::connection('farmer_id_db')->table('id_list')->where('areaID', '=', $areaID)->get();
        //$pdf = DOMPDF::loadView('farmer.id',['id_list' => $id_list]);
        //$pdf = DOMPDF::loadView('farmer.id',['id_list' => $id_list])->setPaper('a4', 'landscape')->setWarnings(false)->stream();
        //$pdf->stream("filename.pdf", array("Attachment" => false));
        //return $pdf->download('farmer.id');
        
        //return view('farmer.id',['id_list' => $id_list]);
    }

    public function QRCodeCoop(){
        $pdf = PDF::loadView('farmer.coop')->setPaper('a4')->setOrientation('landscape');
        $pdf->setOption('margin-top',4);
        $pdf->setOption('margin-bottom',10);
        $pdf->setOption('margin-left',2);
        $pdf->setOption('margin-right',2);
        return $pdf->inline('farmerID.pdf');
    }

    public function Home_ws2020(){

        //$data = [ 'id_list' => $qr_per_batch ];
        //$pdf = PDFTIM::loadView('farmer.id2', $data)->setPaper('a4', 'landscape');
       // $qr_count = 10;
        //return view('farmer.id')->with('qr_count', $qr_count);

        $regions = DB::connection('mysql')->table('lib_regions')->get();
        $areas = DB::connection('farmer_id_db')->table('area_list')
            ->select('area_list.areaID as areaID', 'regions.regDesc', 'provinces.provDesc', 'area_list.currentCount')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_regions as regions', 'area_list.region', '=', 'regions.regCode')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as provinces', 'area_list.province', '=', 'provinces.provCode')
            ->get();
        return view('farmer.home_new')
            ->with('regions', $regions)
            ->with('areas', $areas);
    }
	
	public function Home(){
        $regions = DB::connection('mysql')->table('lib_regions')->orderBy('order')->get();
        return view('farmer.QID_home')->with('regions', $regions);
    }
    
}
