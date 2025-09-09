<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;
use Hash;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;
use App\utility;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DeliveryStatusController extends Controller
{
    public function index()
    {
        $season = $GLOBALS['season_prefix'];
        $coops = DB::table($season.'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('isActive', 1)
        ->get();

        return view('deliveryStatus.index', compact('coops'));
    }

    public function getCoopData(Request $request)
    {
        $season = $GLOBALS['season_prefix'];
        $coop = $request->coop;


        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//coopDeliveryStatus.py');

		$escapedSeason = escapeshellarg($season);
		$escapedCoop = escapeshellarg($coop);
		$command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedCoop";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            $data = json_decode($process->getOutput(), true);
            return Datatables::of(collect($data))->make(true);
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

        
    }

    public function downloadCoopData($coop)
    {
        $season = $GLOBALS['season_prefix'];
        $coop = $coop;
        $coop = str_replace('*', '/', $coop);


        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//coopDeliveryStatusBreakdown.py');

		$escapedSeason = escapeshellarg($season);
		$escapedCoop = escapeshellarg($coop);
		$command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedCoop";
		
		$process = new Process($command);

        
		try {
            $process->mustRun();
            $data = $process->getOutput();
            $data = json_decode($data, true);
            
            $coopName = $data['coop_name'];
            $pending   = isset($data['pending']) ? $data['pending'] : [];
            $confirmed = isset($data['confirmed']) ? $data['confirmed'] : [];
            $inspected = isset($data['inspected']) ? $data['inspected'] : [];
            
            $filename = $coopName.'_Delivery_Status_' . Carbon::now()->format('Ymd_His');
            return Excel::create($filename, function($excel) use ($inspected, $confirmed, $pending) {

                // Inspected sheet
                $excel->sheet("Inspected", function($sheet) use ($inspected) {
                    if (!empty($inspected)) {
                        $sheet->fromArray($inspected);
                        $lastColumn = $sheet->getHighestColumn();
                        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
                            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('00B53F');
                        $border_style = [
                            'borders' => [
                                'allborders' => [
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];
                        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                            ->applyFromArray($border_style);
                    } else {
                        $sheet->setCellValue('A1', 'No inspected records found.');
                    }
                });

                // Confirmed sheet
                $excel->sheet("Confirmed", function($sheet) use ($confirmed) {
                    if (!empty($confirmed)) {
                        $sheet->fromArray($confirmed);
                        $lastColumn = $sheet->getHighestColumn();
                        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
                            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('00B53F');
                        $border_style = [
                            'borders' => [
                                'allborders' => [
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];
                        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                            ->applyFromArray($border_style);
                    } else {
                        $sheet->setCellValue('A1', 'No confirmed records found.');
                    }
                });

                // Pending sheet
                $excel->sheet("Pending", function($sheet) use ($pending) {
                    if (!empty($pending)) {
                        $sheet->fromArray($pending);
                        $lastColumn = $sheet->getHighestColumn();
                        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
                            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('00B53F');
                        $border_style = [
                            'borders' => [
                                'allborders' => [
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ];
                        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                            ->applyFromArray($border_style);
                    } else {
                        $sheet->setCellValue('A1', 'No pending records found.');
                    }
                });

            })->setActiveSheetIndex(0)->download('xlsx');

        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }

    }
}
