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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FarmerIDController extends Controller
{
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

    public function generateQR(Request $request){
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
                ->orderBy('batchID', 'DESC')
                ->first();

            $batchID = $batch_details->batchID;
			
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
            $pdf = PDFTIM::loadView('farmer.id2', $data)->setPaper('a4', 'landscape');
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

    public function Home(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        $areas = DB::connection('farmer_id_db')->table('area_list')
            ->select('area_list.areaID as areaID', 'regions.regDesc', 'provinces.provDesc', 'area_list.currentCount')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_regions as regions', 'area_list.region', '=', 'regions.regCode')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as provinces', 'area_list.province', '=', 'provinces.provCode')
            ->get();
        return view('farmer.home')
            ->with('regions', $regions)
            ->with('areas', $areas);
    }
    
}
