<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;
use Excel;

use Yajra\Datatables\Facades\Datatables;


class CoopController extends Controller
{
    public function index(){
		$regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->orderBy('region_sort', 'ASC')->groupBy('regionName')->get();
        return view('coop.index')
			->with('region_list', $regions);
    }

    public function getCoopID(Request $request){
        return DB::connection('seed_coop_db')->table('tbl_commitment')
            ->where('id', '=', $request->commitmentID)
            ->value('coopID');
    }

    public function totalCommitment(Request $request){
        return DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
            ->value('total_value');
    }

    public function SubtotalCommitment(Request $request){
        return DB::connection('seed_coop_db')->table('tbl_commitment')
            ->select(DB::raw('SUM(commitment_value)'))
            ->where('coopID', '=', $request->coopID)
            ->where('variety_status', 1)
            ->value('total_value');
    }

    public function coopList(Request $request){
        $cooperatives = DB::connection('seed_coop_db')
            ->table('tbl_cooperatives')
            ->where('accreditation_no', '!=', '')
            ->orderBy('coopName', 'ASC')
            ->get();

        if($request->coop == 0){
            $return_str = '';
            foreach($cooperatives as $coop){
                $return_str .= "<option value='$coop->accreditation_no'>$coop->coopName</option>";
            }
        }else{
            $return_str = '';
            foreach($cooperatives as $coop){
                if($request->coop == $coop->accreditation_no){
                    $return_str .= "<option value='$coop->accreditation_no' selected>$coop->coopName</option>";
                }else{
                    $return_str .= "<option value='$coop->accreditation_no'>$coop->coopName</option>";
                }                
            }
        }

        return $return_str;
    }

    public function coop_tbl(){
        return Datatables::of(DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->orderBy('coopName', 'ASC')
        )
        ->addColumn('action', function($row){
            $commitments = DB::connection('seed_coop_db')->table('tbl_commitment')
                        ->where('coopID', '=', $row->coopId)
                        ->get();

            if(count($commitments) > 0){
                $bags = DB::connection('seed_coop_db')->table('tbl_total_commitment')
                    ->select('total_value')
                    ->where('coopID', '=', $row->coopId)
                    ->first();
					
				$regional_url = route('coop.regional_commitment', $row->coopId);
				$adjustment_logs_url = route('coop.adjustment.logs');
				$total_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')->where('coopID', $row->coopId)->value('total_value');
				$current_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $row->coopId)->sum('commitment_value');
				$available_balance = $total_commitments - $current_commitments;
                
                return '
                        <a href="" data-toggle="modal" data-target="#coopDetails" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" data-bags="'.$bags->total_value.'" data-acn="'.$row->accreditation_no.'" data-moa="'.$row->current_moa.'" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i></a>
                        <a href="#" data-toggle="modal" data-target="#moa_number_modal" data-id="'.$row->coopId.'" data-moa="'.$row->current_moa.'" class="btn btn-info btn-sm"><i class="fa fa-file"></i></a>
						<a href="" data-toggle="modal" data-target="#commitmentAdjustmentModal" class="btn btn-warning btn-sm" data-balance="'.$available_balance.'" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" data-acn="'.$row->accreditation_no.'" data-moa="'.$row->current_moa.'" data-toggle="modal" data-target="#commitmentAdjustmentModal"><i class="fa fa-exchange"></i></a>
					   ';
            }else{
                return '
                        <a href="" data-toggle="modal" data-target="#commitmentModal" data-backdrop="static" data-keyboard="false" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" data-acn="'.$row->current_moa.'" class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i></a>
                        <a href="#" data-toggle="modal" data-target="#moa_number_modal" data-id="'.$row->coopId.'" data-moa="'.$row->current_moa.'" class="btn btn-info btn-sm"><i class="fa fa-file"></i></a>
 					   ';
            }
        })
        ->make(true);
    }

    public function coopSaveTable(Request $request){
        return Datatables::of(DB::connection('seed_coop_db')->table('tbl_commitment')
            ->where('coopID', '=', $request->coopID)
            ->orderBy('date_added', 'ASC')
        )
        ->addColumn('action', function($row){
            return '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-danger btn-sm" onclick="removeRecord(this);" >Remove Seed Variety</a>';
        })
        ->addColumn('variety_bags', function($row){
            return number_format($row->commitment_value).' bags'; 
        })
        ->addColumn('date_add', function($row){
            return date("F j, Y", strtotime($row->date_added)); 
        })
		->addColumn('region', function($row){
            //tagged region
			return $region = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('commitmentID', $row->id)->value('region_name');
        })
        ->make(true);
    }

    public function coopVaritiesADDSubmit(Request $request){
        $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('coopId', '=', $request->coopDetailsID)->first();
        $commitmentID = DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insertGetId([
            'coopID' => $request->coopDetailsID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->seed_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s"),
            'variety_status' => 1,
            'moa_number' => $coop->current_moa
        ]);
		
		DB::connection('seed_coop_db')->table('tbl_commitment_regional')
        ->insert([
			'commitmentID' => $commitmentID,
            'coop_Id' => $request->coopDetailsID,
            'coop_name' => $coop->coopName,
			'accreditation_no' => $coop->accreditation_no,
			'region_name' => $request->region,
			'seed_variety' => $request->seed_variety,
			'volume' => $request->seed_value,
			'date_added' => date("Y-m-d H:i:s"),
			'date_updated' => date("Y-m-d H:i:s")
        ]);

        //add existing to total
        $coop_commitment = DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
            ->first();

        DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $coop_commitment->coopID)
            ->update(['total_value' => $coop_commitment->total_value + $request->seed_value]);
    }

    public function coopVaritiesADD(Request $request){
        $coop_commitments = DB::connection('seed_coop_db')
            ->table('tbl_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
            ->get();
        $return_str = '';

        $comm_filter_str = "";
        $comm_filter_str .= "WHERE variety != ";
        foreach($coop_commitments as $commitments){
            $comm_filter_str .= "'".$commitments->commitment_variety."' AND variety != ";
        }
        $comm_filter_str = rtrim($comm_filter_str, " AND variety != ");

        //$results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics $comm_filter_str GROUP BY variety ORDER BY variety"));
		$results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics GROUP BY variety ORDER BY variety"));

        //get new seed variety list
        foreach($results as $result){
            $return_str .= "<option value='$result->variety'>$result->variety</option>";
        }

        return $return_str;
    }

    public function coopVarities(Request $request){
        $coop_commitments = DB::connection('seed_coop_db')
            ->table('tbl_commitment')
            ->where('coopID', '=', $request->coopID)
            ->get();

        $return_str= '';
        if(count($coop_commitments) > 0){
            $comm_filter_str = "";
            $comm_filter_str .= "WHERE variety != ";
            foreach($coop_commitments as $commitments){
                $comm_filter_str .= "'".$commitments->commitment_variety."' AND variety != ";
            }
            $comm_filter_str = rtrim($comm_filter_str, " AND variety != ");

            //$results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics $comm_filter_str GROUP BY variety ORDER BY variety"));
			$results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics GROUP BY variety ORDER BY variety"));

            //get new seed variety list
            foreach($results as $result){
                $return_str .= "<option value='$result->variety'>$result->variety</option>";
            }

        }else{
            //get all seed varities
            $varities = DB::connection('seeds_db')->table('seed_characteristics')->groupBy('variety')->orderBy('variety')->get();

            foreach($varities as $variety){
                $return_str .= "<option value='$variety->variety'>$variety->variety</option>";
            }
        }
        
        
        return $return_str;
    }

    public function coopVaritiesDelete(Request $request){
        $commitment_data = DB::connection('seed_coop_db')->table('tbl_commitment')->where('id', '=', $request->commitmentID)->first();
        $coopID = $commitment_data->coopID;
        
        //delete data
        DB::connection('seed_coop_db')->table('tbl_commitment')->where('id', '=', $request->commitmentID)->delete();

        return $coopID;
    }

    public function coopVaritiesDeleteDetails(Request $request){
        $commitment_data = DB::connection('seed_coop_db')->table('tbl_commitment')->where('id', '=', $request->commitmentID)->first();
        $coopID = $commitment_data->coopID;
        
        //substract commitment value to existing delivery commitment
        $coop_details = DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $coopID)
            ->first();
        $coop_remaining_commit = $coop_details->total_value - $commitment_data->commitment_value;

        //update total commitment if value > 0 else delete record
        if($coop_remaining_commit == 0){
            return "zero_neg_rem";
        }else{
            //update data
            DB::connection('seed_coop_db')
                ->table('tbl_commitment')->where('id', '=', $request->commitmentID)
                ->update(['variety_status' => 0, 'date_updated' => date("Y-m-d H:i:s")]);

            DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_details->id)
                ->update(['total_value' => $coop_remaining_commit]);
            return $coopID;
            
            /*DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_details->id)
                ->delete();
            return 0;*/
        }
       
    }

    public function coop_details_tbl(Request $request){
        return Datatables::of(DB::connection('seed_coop_db')->table('tbl_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
        )
        ->addColumn('date_add', function($row){
            return date("F j, Y", strtotime($row->date_added)); 
        })
        ->addColumn('variety_bags', function($row){
            return number_format($row->commitment_value).' bags'; 
        })
        ->addColumn('action', function($row){
            return '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-danger btn-sm" onclick="removeRecordDetails(this);" ><i class="fa fa-toggle-down"></i> Set as Inactive Variety</a>';
        })
        ->addColumn('status_btn', function($row){
            if($row->variety_status == 1){
                return '<span class="badge badge-success" style="background-color: green">Active Seed Variety</span>';
            }else{
                return '<span class="badge badge-danger" style="background-color: red;">Inactive Seed Variety</span>';
            }
        })
		->addColumn('region', function($row){
            return DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('commitmentID', $row->id)->value('region_name');
        })
        ->make(true);
    }

    public function saveCoopCommitment(Request $request){
        $this->validate($request, array(
            'coopID' => 'required'
        ));

        $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->where('coopId', '=', $request->coopID)
            ->first();

        $commitmentID = DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insertGetId([
            'coopID' => $request->coopID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->commitment_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s"),
            'moa_number' => $coop->current_moa,
            'variety_status' => '1' 
        ]);
		
		DB::connection('seed_coop_db')->table('tbl_commitment_regional')
        ->insert([
			'commitmentID' => $commitmentID,
            'coop_Id' => $request->coopID,
            'coop_name' => $coop->coopName,
			'accreditation_no' => $coop->accreditation_no,
			'region_name' => $request->region,
			'seed_variety' => $request->seed_variety,
			'volume' => $request->commitment_value,
			'date_added' => date("Y-m-d H:i:s"),
			'date_updated' => date("Y-m-d H:i:s")
        ]);

        DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'COMMITMENT',
                'description' => 'Added seed delivery commitment for ('.$coop->acronym.'): '.$request->commitment_value.' bags of '.$request->seed_variety.'',
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);


        //insert to total commitment table
        /*DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->insert([
                'season_id' => $season_id,
                'total_value' => $total_commitments,
                'coopID' => $request->coopID
            ]);
        */

        //Session::flash('success', 'successfully added a delivery commitment for a seed cooperative');
        //return redirect()->route('coop.commitment');
    }

    public function saveTotalCoopCommitment(Request $request){
        $this->validate($request, array(
            'coopID' => 'required'
        ));

        //get total commitments
        $commitments = DB::connection('seed_coop_db')->table('tbl_commitment')->where('coopID', '=', $request->coopID)->get();
        $total_commitments =0;
        $value_chk = "";
        foreach($commitments as $commitment){
            $total_commitments += $commitment->commitment_value;
            if($commitment->commitment_value == ""){
                $value_chk = "zero_exists";
            }
        }

        if(count($commitments) > 0){
            $coop_details = DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->where('coopId', '=', $request->coopID)
            ->first();

            if($total_commitments == 0 AND $request->total_commitment == ''){
                return "zero_commit";
            }elseif($value_chk == "zero_exists" AND $request->total_commitment == ''){
                return "zero_commit";
            }elseif($value_chk == "zero_exists" AND $request->total_commitment != ''){
                //if at least one seed variety = 0
                if($total_commitments > $request->total_commitment){
                    return "small_commit";
                }else{
                    $log_total_value = $request->total_commitment;
                    DB::connection('seed_coop_db')->table('tbl_total_commitment')
                    ->insert([
                        'total_value' => $request->total_commitment,
                        'coopID' => $request->coopID,
                        'moa_number' => $coop_details->current_moa
                    ]);
                }
                
            }else{
                //insert to total commitment table
                $log_total_value = $total_commitments;
                DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->insert([
                    'total_value' => $total_commitments,
                    'coopID' => $request->coopID,
                    'moa_number' => $coop_details->current_moa
                ]);
            }

            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'COMMITMENT',
                'description' => 'Saved the seed delivery commitment of ('.$coop_details->acronym.') with a total of '.$log_total_value.' bags',
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            return route('coop.commitment');
        }else{
            
        }
    }

    public function CoopCommitmentCancel(Request $request){
        $coop_commitments = DB::connection('seed_coop_db')->table('tbl_commitment')->where('coopID', '=', $request->coopID)->get();

        if(count($coop_commitments) > 0){
            
            $varities = '';
            foreach($coop_commitments as $c_row){
                $varities .= $c_row->commitment_variety.', ';
            }

            DB::connection('seed_coop_db')->table('tbl_commitment')
                ->where('coopID', '=', $request->coopID)
                ->delete();

            $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
                ->where('coopId', '=', $request->coopID)
                ->first();
    
            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'COMMITMENT',
                'description' => 'Cancelled seed delivery commitment for ('.$coop->acronym.') with seed varieties: '.$varities,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
        }

        return route('coop.commitment');
    }

    public function updateCoopMOA(Request $request){
        DB::connection('seed_coop_db')->table('tbl_cooperatives')
        ->where('coopId', $request->coop_id)
        ->update([
            'current_moa' => $request->moa_number
        ]);

        return route('coop.commitment');
    }

    public function updateCoopCommitment(Request $request){
        $this->validate($request, array(
            'commitment_value_update' => 'required',
            'coopID_update' => 'required'
        ));

        DB::connection('seed_coop_db')->table('tbl_commitment')
        ->where('coopID', $request->coopID_update)
        ->update([
            'commitment_value' => $request->commitment_value_update,
            'date_updated' => date("Y-m-d H:i:s")
        ]);

        Session::flash('success', 'successfully updated a delivery commitment for a seed cooperative');
        return redirect()->route('coop.commitment');
    }

    /**
     * COOP DASHBOARD FUNCTIONS
     */
    public function coop_dashboard_home(Request $request){
        $coop_list = DB::connection('seed_coop_db')->table('tbl_cooperatives')->orderBy('coopName')->get();
        return view('coop.coop_dashboard')
            ->with('coop_list', $coop_list);
    }

    public function confirmed_delivery_tbl(Request $request){
        $confirmed_delivery = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('coopAccreditation', $request->coop_accre)
            ->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        //check if delivery is inspected
        $region_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 
                         'seedVariety', 'dateCreated')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->groupBy('batchTicketNumber')               
                ->first();

            if(count($actual_delivery) > 0){
                array_push($region_arr, $actual_delivery->region);
            }
        }

        $region_arr = array_unique($region_arr);

        return $region_arr;
    }

    public function coop_deliveries_per_region(Request $request){
        $confirmed_delivery = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('coopAccreditation', $request->coop_accre)
            ->where('region', $request->region)
            ->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber', 'seedVariety')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        //check if delivery is inspected
        $inspected_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber', 'province', 'municipality', 'dropOffPoint', 
                         'seedVariety', 'dateCreated', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as total_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('region', $row->region)
                ->groupBy('batchTicketNumber', 'seedVariety')               
                ->first();

            if(count($actual_delivery) > 0){
                $row_arr = array(
                    'batchTicketNumber' => $actual_delivery->batchTicketNumber,
                    'province' => $actual_delivery->province,
                    'municipality' => $actual_delivery->municipality,
                    'dropOffPoint' => $actual_delivery->dropOffPoint,
                    'seedVariety' => $actual_delivery->seedVariety,
                    'total_bags' => number_format($actual_delivery->total_bags)." bag(s)",
                    'date_inspected' => date("m-d-Y", strtotime($row->dateCreated))
                );
                array_push($inspected_arr, $row_arr);
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->make(true);
    }

    public function compute_variety_total($deliveries, $seed_variety){
        $total_variety = 0;
        foreach($deliveries as $row){
            $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
                ->where('ad.batchTicketNumber', "=", $row->batchTicketNumber)
                ->where('ad.seedVariety', "=", $seed_variety)
                ->sum('totalBagCount');
            
            $total_variety += $variety_count;
        }

        return $total_variety;
    }
	
	public function compute_confirmed_total($seed_variety, $accreditation){
        $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as cd')
            ->where('cd.seedVariety', "=", $seed_variety)
            ->where('cd.coopAccreditation', "=", $accreditation)
            ->sum('totalBagCount');

        return $variety_count;
    }

    public function coop_seeds_tbl(Request $request){
        $coop_id = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('accreditation_no', $request->coop_accre)->value('coopId');
        $commitments = DB::connection('seed_coop_db')->table('tbl_commitment')->where('coopID', $coop_id)->get();

        $confirmed_delivery_list = DB::connection('delivery_inspection_db')->table("tbl_delivery")
            ->select('batchTicketNumber', DB::raw("SUM(totalBagCount) as total_bags"))
            ->where('tbl_delivery.is_cancelled', '=', '0')
            ->where('tbl_delivery.coopAccreditation', '=', $request->coop_accre)
            ->groupBy('batchTicketNumber')
            ->get();

        $variety_list = array();
        $commitment_list = array();
        $delivered_list = array();
        $confirmed_list   = array();

        $total_commitment = 0;
        $total_delivered = 0;
        $total_confirmed = 0;

        foreach($confirmed_delivery_list as $batch){
            $total_confirmed += $batch->total_bags;
        }

        foreach($commitments as $row){
            $variety_total = $this->compute_variety_total($confirmed_delivery_list, $row->commitment_variety);
            $confirmed_total = $this->compute_confirmed_total($row->commitment_variety, $request->coop_accre);

            array_push($variety_list, $row->commitment_variety);
            array_push($commitment_list, intval($row->commitment_value));
            array_push($delivered_list, intval($variety_total));
            array_push($confirmed_list, intval($confirmed_total));

            $total_commitment += $row->commitment_value;
            $total_delivered += $variety_total;
        }
        
        return array(
            'variety_list' => $variety_list,
            'commitment_list' => $commitment_list,
            'delivered_list' => $delivered_list,
            'total_commitment' => number_format($total_commitment),
            'total_delivered' => number_format($total_delivered),
            'total_confirmed' => number_format($total_confirmed),
            'confirmed_list' => $confirmed_list
        );
    }

    public function coop_rla(){
        return view('coop.rla.home');
    }
	
	public function coop_rla_upload(Request $request){
        if ($request->hasFile('file')) {
            $image = $request->file('file');

            $filename = $_FILES["file"]["name"];
            $ext = end((explode(".", $filename)));

            $name = "RLA_DETAILS_".time().'.'.$ext;
            $destinationPath = public_path("rla");
            $image->move($destinationPath, $name);

            //read uploaded file
            $data_path = $destinationPath."//".$name;
            $data = Excel::load($data_path)->get();
            
            if($data->count()){
                foreach ($data as $key => $value) {
                    //check coop accreditation number
                    $coop_check = DB::connection('seed_coop_db')
                        ->table('tbl_cooperatives')
                        ->where('accreditation_no', $value->coop_accreditation)
                        ->first();
                    if(count($coop_check) > 0){
                        $coop_accreNo = $coop_check->accreditation_no;
                        $moa_number = $coop_check->current_moa;
                    }else{
                        $coop_accreNo = "no match.";
                        $moa_number = "no match.";
                    }

                    $seed_grower = trim($value->seed_grower) == '' ? $coop_check->coopName : $value->seed_grower;

                    //check seed grower porfile
                    $sg_check = DB::connection('delivery_inspection_db')
                        ->table('tbl_seed_grower')
                        ->where('full_name', $seed_grower)
                        ->where('coop_accred', $value->coop_accreditation)
                        ->first();
                    if(count($sg_check) > 0){
                        $sg_id = $sg_check->sg_id;
                    }else{
                        $sg_id = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->insertGetId([
                            'coop_accred' => $coop_accreNo,
                            'is_active' => 1,
                            'is_block' => 0,
                            'fname' => trim($value->first_name),
                            'mname' => trim($value->middle_name),
                            'lname' => trim($value->last_name),
                            'extension' => trim($value->extension),
                            'full_name' => $seed_grower
                        ]);
                    }
					
					$lab_number = preg_replace('/[^0-9]/', '', $value->laboratory_number);
					$lot_number_string = trim($value->lot_number);
					$lot_number_string = str_replace(' ', '', $lot_number_string);
					$lot_number_string = preg_replace('/[^A-Za-z0-9\-]/', '', $lot_number_string);

                    $excel_row = array(
                        'coop_name' => trim($value->cooperative), 
                        'coopAccreditation' => trim($coop_accreNo),
                        'sg_id' => $sg_id,
                        'sg_name' => $seed_grower,
                        'certificationDate' => date("Y-m-d", strtotime($value->date_test_completed)),
                        'labNo' => $lab_number,
                        //'lotNo' => trim($value->lot_number),
						'lotNo' => $lot_number_string,
                        'noOfBags' => $value->number_of_bags_passed,
                        'seedVariety' => trim($value->variety),
                        'moaNumber' => trim($moa_number)
                    );

                    if(!empty($excel_row)){
                        //check if lab & lot exists in tla_details_tbl
                        $check_lablot = DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                            ->where('labNo', trim($value->laboratory_number))
                            ->where('lotNo', trim($value->lot_number))
                            ->get();
                        if(count($check_lablot) > 0){
                            //do not insert
                        }else{
                            DB::connection('delivery_inspection_db')->table('tbl_rla_details')->insert($excel_row);
                        }
                        //dd('Insert Record successfully.');
                    }

                    //update name in rla_details table
                    /*$id = $key + 1;
                    DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                    ->where('rlaId', $id)
                    ->update([
                        'sg_name' => $value->seed_grower
                    ]);*/
                }
            }

            Session::flash("success", "you have successfully uploaded the file.");
            return redirect()->route('coop.rla');
        }else{
            echo "error";
        }
    }

    public function coop_rla_upload_old(Request $request){
        if ($request->hasFile('file')) {
            $image = $request->file('file');

            $filename = $_FILES["file"]["name"];
            $ext = end((explode(".", $filename)));

            $name = "RLA_DETAILS_".time().'.'.$ext;
            $destinationPath = public_path("rla");
            $image->move($destinationPath, $name);

            //read uploaded file
            $data_path = $destinationPath."//".$name;
            $data = Excel::load($data_path)->get();
            
            if($data->count()){
                foreach ($data as $key => $value) {
                    //check coop accreditation number
                    $coop_check = DB::connection('seed_coop_db')
                        ->table('tbl_cooperatives')
                        ->where('accreditation_no', $value->coop_accreditation)
                        ->first();
                    if(count($coop_check) > 0){
                        $coop_accreNo = $coop_check->accreditation_no;
                        $moa_number = $coop_check->current_moa;
                    }else{
                        $coop_accreNo = "no match.";
                        $moa_number = "no match.";
                    }

                    $seed_grower = trim($value->seed_grower) == '' ? $coop_check->coopName : $value->seed_grower;

                    //check seed grower porfile
                    $sg_check = DB::connection('delivery_inspection_db')
                        ->table('tbl_seed_grower')
                        ->where('full_name', $seed_grower)
                        ->where('coop_accred', $value->coop_accreditation)
                        ->first();
                    if(count($sg_check) > 0){
                        $sg_id = $sg_check->sg_id;
                    }else{
                        $sg_id = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->insertGetId([
                            'coop_accred' => $coop_accreNo,
                            'is_active' => 1,
                            'is_block' => 0,
                            'fname' => trim($value->first_name),
                            'mname' => trim($value->middle_name),
                            'lname' => trim($value->last_name),
                            'extension' => trim($value->extension),
                            'full_name' => $seed_grower
                        ]);
                    }

                    $excel_row = array(
                        'coop_name' => trim($value->cooperative), 
                        'coopAccreditation' => trim($coop_accreNo),
                        'sg_id' => $sg_id,
                        'sg_name' => $seed_grower,
                        'certificationDate' => date("Y-m-d", strtotime($value->date_test_completed)),
                        'labNo' => trim($value->laboratory_number),
                        'lotNo' => trim($value->lot_number),
                        'noOfBags' => $value->number_of_bags_passed,
                        'seedVariety' => trim($value->variety),
                        'moaNumber' => trim($moa_number)
                    );

                    if(!empty($excel_row)){
                        //check if lab & lot exists in tla_details_tbl
                        $check_lablot = DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                            ->where('labNo', trim($value->laboratory_number))
                            ->where('lotNo', trim($value->lot_number))
                            ->get();
                        if(count($check_lablot) > 0){
                            //do not insert
                        }else{
                            DB::connection('delivery_inspection_db')->table('tbl_rla_details')->insert($excel_row);
                        }
                        //dd('Insert Record successfully.');
                    }

                    //update name in rla_details table
                    /*$id = $key + 1;
                    DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                    ->where('rlaId', $id)
                    ->update([
                        'sg_name' => $value->seed_grower
                    ]);*/
                }
            }

            Session::flash("success", "you have successfully uploaded the file.");
            return redirect()->route('coop.rla');
        }else{
            echo "error";
        }
    }

    public function coop_rla_table(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_rla_details')
            ->orderBy('certificationDate', 'DESC')
        )
        ->addColumn('coop_name', function($row){
            $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
                ->where('accreditation_no', $row->coopAccreditation)->first(); 

            return count($coop) > 0 ? $coop->coopName : "No matching cooperative";
        })
        ->make(true);
    }

    public function sg_pofiling_function(){
        $sg_list = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->get();

        //1. update full_name field in tbl_seed_grower
        foreach($sg_list as $sg_row){
            DB::connection('delivery_inspection_db')->table('tbl_seed_grower')
            ->where('sg_id', $sg_row->sg_id)
            ->update([
                'full_name' => $sg_row->fname." ".$sg_row->mname." ".$sg_row->lname." ".$sg_row->extension
            ]);
        }
    }

    /** 
     * COOP MEMBERS
    */
    public function coop_members_home(Request $request){
        $coop_list = DB::connection('seed_coop_db')->table('tbl_cooperatives')->orderBy('coopName')->get();
        return view('coop.members.home')
            ->with('coop_list', $coop_list);
    }

    public function load_coop_members(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_seed_grower')
            ->select('sg_id', 'full_name', 'coop_accred', 'is_block', 'is_active')
            ->where('coop_accred', $request->coop_accre)
            ->where('full_name', '!=', '')
        )
        ->addColumn('blacklist_status', function($row){
           $is_active_str = $row->is_active == 1 ? 'Active' : 'Inactive';
           $is_block_str = $row->is_block == 0 ? 'Whitelisted' : 'Blacklisted';

           return $is_active_str." - ".$is_block_str;
        })
        ->addColumn('bags_passed', function($row){
            $total_bags_passed = DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                ->where('coopAccreditation', $row->coop_accred)
                ->where('sg_name', $row->full_name)
                ->sum('noOfBags');
                
            return number_format($total_bags_passed)." bag(s)";
        })
        ->addColumn('seed_tags', function($row){
            $total_bags_passed = DB::connection('delivery_inspection_db')->table('tbl_rla_details')
                ->select('seedVariety', DB::raw("SUM(noOfBags) as no_of_bags_passed"))
                ->where('coopAccreditation', $row->coop_accred)
                ->where('sg_name', $row->full_name)
                ->groupBy('seedVariety')
                ->limit(3)
                ->get();

            $variety_bags_str = '';
            foreach($total_bags_passed as $s_row){
                $variety_bags_str .= $s_row->seedVariety.", ";
            }
            
            $variety_bags_str = trim($variety_bags_str,", ");

            if(count($total_bags_passed) >= 3){
                $variety_bags_str .= ", etc.";
            }

            return $variety_bags_str == '' ? 'N/A' : $variety_bags_str;
        })
        ->addColumn('action', function($row){
            if($row->is_block == 0){
                $is_block_btn = "<a href='#' class='btn btn-success btn-xs' data-toggle='modal' data-target='#blacklist_sg_modal' data-id='".$row->sg_id."'><i class='fa fa-tags'></i></a>";
            }else{
                $is_block_btn = "<a href='#' class='btn btn-danger btn-xs'><i class='fa fa-tags'></i></a>";                
            }
             return '
                <center>
                    <a href="#" data-toggle="modal" data-target="#sg_tags_modal" data-id="'.$row->sg_id.'" data-coop="'.$row->coop_accred.'" class="btn btn-warning btn-xs"><i class="fa fa-tags"></i> View seed tags</a>
                </center>';
        })
        ->make(true);
    }

    public function set_sg_blacklist(Request $request){
        DB::connection('delivery_inspection_db')->table('tbl_seed_grower')
            ->where('sg_id', $request->sg_id)
            ->update([
                'is_block' => 1
            ]);

        //sg name
        $sg_name =  DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->where('sg_id', $request->sg_id)->value('full_name');

        DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'SG_BLACKLIST',
                'description' => 'Classified seed grower: ('.$sg_name.') as blacklisted || reason: '.$request->blacklist_reason,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
    }

    public function load_coop_schedule(Request $request){
        $confirmed_delivery = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('coopAccreditation', $request->coop_accre)
            ->where('is_cancelled', 0)
            ->where('region', '!=', '')
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        //check if delivery is inspected
        $region_arr = array();
        foreach($confirmed_delivery as $row){
            array_push($region_arr, $row->region);
        }

        $region_arr = array_unique($region_arr);

        return $region_arr;
    }

    public function load_coop_schedule_details(Request $request){
        $confirmed_delivery = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 
                DB::raw('SUM(totalBagCount) as expected_bags'), 'deliveryDate')
            ->where('coopAccreditation', $request->coop_accre)
            ->where('region', $request->region)
            ->where('is_cancelled', 0)
            ->where('region', '!=', '')
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        //check if delivery is inspected
        $inspected_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber', 'province', 'municipality', 'dropOffPoint', 
                         'seedVariety', 'dateCreated', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actual_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('region', $row->region)
            ->groupBy('batchTicketNumber')
                ->first();

            if(count($actual_delivery) > 0){
                $row_arr = array(
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate))
                );
                array_push($inspected_arr, $row_arr);
            }else{
                $row_arr = array(
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => "N/A",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate))
                );
                array_push($inspected_arr, $row_arr);
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->make(true);
    }

    public function load_sg_details(Request $request){
        $sg_details = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')
            ->where('sg_id', $request->sg_id)
            ->first();

        $coop_name = DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->where('accreditation_no', $sg_details->coop_accred)
            ->value('coopName');

        return array(
            "sg_name" => $sg_details->full_name,
            "coop_name" => $coop_name
        );
    }

    public function load_sg_tags(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')
            ->table('tbl_rla_details')
            ->where('sg_id', $request->sg_id)
            ->orderBy('certificationDate', 'DESC')
        )
        ->addColumn('seed_tag', function($row){
           return $row->labNo."/".$row->lotNo;
        })
        ->addColumn('bags_passed', function($row){
            return number_format($row->noOfBags)." bag(s)";
        })
        ->make(true);
    }

    public function farmer_list_upload(Request $request){
        if ($request->hasFile('file')) {
            $image = $request->file('file');

            $filename = $_FILES["file"]["name"];
            $ext = end((explode(".", $filename)));

            $name = "FARMER_LIST_".time().'.'.$ext;
            $destinationPath = public_path("farmer_list");
            $image->move($destinationPath, $name);

            //read uploaded file
            $data_path = $destinationPath."//".$name;            
            $data = Excel::load($data_path)->get();
            
            if($data->count()){
                $excel_ctr = 1;
                foreach ($data as $key => $value) {
                    $excel_row = array(
                        'rsbsa_number' => $value->rsbsa,
                        'full_name' => trim($value->full_name),
                        'sex' => $value->sex,
                        'province' => $value->province,
                        'municipality' => $value->municipality,
                        'dop' => $value->drop_off_point,
                        'bags_claimed' => $value->bags,
                        'seed_variety' => $value->seed_variety
                    );

                    if(!empty($excel_row)){
                        DB::table($GLOBALS['season_prefix'].'rcep_farmers.ds_2019_list')->insert($excel_row);
                        ++$excel_ctr;
                    }
                }
            }


        }else{
            echo "error";
        }
    }

    public function farmer_list_table(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_farmers.ds_2019_list')
        )
        ->addColumn('action', function($row){
            return "<a class='btn btn-warning btn-sm' href='#' data-toggle='modal' data-target='#list_details_modal' data-id='".$row->id."'><i class='fa fa-eye'></i> View Profile</a>";
        })
        ->make(true);
    }

    public function farmer_profile_2019(Request $request){
        $farmer_profile = DB::table($GLOBALS['season_prefix'].'rcep_farmers.ds_2019_list')->where('id', $request->profile_id)->first();

        return array(
            "rsbsa_number" => $farmer_profile->rsbsa_number,
            "full_name" => $farmer_profile->full_name,
            "province" => $farmer_profile->province,
            "municipality" => $farmer_profile->municipality,
            "dop" => $farmer_profile->dop,
            "bags_claimed" => $farmer_profile->bags_claimed,
            "seed_variety" => $farmer_profile->seed_variety
        );
    }
	
	/**
     * COOP RLA - MANUAL UPLOAD
     */
	 
	public function coop_rla_bpi(){
        $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->get();
        $variety_list = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->get();
        return view('coop.rla.bpi_form')
            ->with('coop_list', $coop_list)
            ->with('variety_list', $variety_list);
    }

    public function coop_rla_pmo(){
        $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
        return view('coop.rla.pmo_home')->with('coop_list', $coop_list);
    }

    public function coop_rla_pmo_getCoop(Request $request){
        $coop_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->accreditation_no)->first();
        
        $rla_requests = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')->where('coop_accreditation', $request->accreditation_no)->get();
        //count passed, rejected
        $passed = 0;
        $rejected = 0;
        $bpi = 0;
        foreach($rla_requests as $row){
            if($row->status == 2){
                $passed += 1;
            }else if($row->status == 3){
                $rejected += 1;
            }else if($row->status == 4){
                $bpi += 1;
            }
        }

        return array(
            "coop_name" => $coop_details->coopName,
            "total_requests" => number_format(count($rla_requests)),
            "total_passed" => number_format($passed),
            "total_rejected" => number_format($rejected),
            "total_bpi" => number_format($bpi),
        );
    }

    public function coop_rla_pmo_loadTbl(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')
            ->where('coop_accreditation', $request->accreditation_no)
            ->orderBy('date_recorded', 'DESC')
        )
        ->addColumn('rla_status', function($row){
            if($row->status == 1){
                return "Pending";
            }else if($row->status == 2){
                return "Approved";
            }else if($row->status == 3){
                return "Rejectetd";
            }else if($row->status == 4){
                return "Automatic-Approved";
            }
        })
        ->make(true);
    }
	
	public function coop_rla_manual_home(){
        $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->get();
        $variety_list = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->get();
        return view('coop.rla.manual_home')
            ->with('coop_list', $coop_list)
            ->with('variety_list', $variety_list);
    }

    public function coop_rla_manual_sgList(Request $request){
        $sg_members = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')->where('coop_accred', $request->coop)->orderBy("full_name")->get();

        $sg_str = '';
        foreach($sg_members as $s_row){
            $sg_str .= "<option value='$s_row->full_name'>$s_row->full_name</option>";
        }

        return $sg_str;
    }
	
	public function save_request_bpi(Request $request){
        $this->validate($request, array(
            'coop' => 'required',
            'sg_name' => 'required',
            'certification_date' => 'required',
            'lab_number' => 'required',
            'lot_number' => 'required',
            'variety' => 'required',
            'bags' => 'required|min:1|max:200'
        ));

        $clean_coop_accreditation = preg_replace("/[^a-zA-Z0-9]+/", "-", $request->coop);
        $seed_tag = $request->lab_number."/".$request->lot_number;

        $seedtag_total = 0;

        //check seedtag volume in database
        /*$seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('is_cancelled', 0)
            ->where('seedTag', 'like', '%' . $request->lab_number . '%')
            //->where('seedTag', $seed_tag)
            ->get();

        if(count($seedtag_data_tbl_delivery) > 0){
            foreach($seedtag_data_tbl_delivery as $row){
                $seedtag_total += $row->totalBagCount;
            }
        }else{
            $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                //->where('seedTag', $seed_tag)
                ->where('seedTag', 'like', '%' . $request->lab_number . '%')
                ->get();

            if(count($seedtag_data_tbl_actual_delivery) > 0){
                foreach($seedtag_data_tbl_actual_delivery as $row2){
                    $seedtag_total += $row2->totalBagCount;
                }
            }
        }*/
        
        //check volume in rla data
        $seed_labno_rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo', $request->lab_number)
			->where('lotNo', $request->lot_number)
            ->get();
        
        foreach($seed_labno_rla as $row){
            $seedtag_total += $row->noOfBags;
        }

        //check if seed tag exists in rla database
        $check_rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo', $request->lab_number)
            ->where('lotNo', $request->lot_number)
            ->get();

        if(count($check_rla) > 0){
            Session::flash("error_msg", "The seed tag (laboratory # & lot #) already exists in the database!");
            return redirect()->route('coop.rla.bpi');
        }else{
            $volume_after_edit = $seedtag_total + $request->bags;
            if($volume_after_edit > 240){
                Session::flash("error_msg", "The system detected that the specified laboratory & lot number is already recorded: (current count: $seedtag_total), your input exceeds the maximum allocation of 240 bags.");
                return redirect()->route('coop.rla.bpi');
            }else{           

				$lot_number_string = trim($request->lot_number);
				$lot_number_string = str_replace(' ', '', $lot_number_string);
				$lot_number_string = preg_replace('/[^A-Za-z0-9\-]/', '', $lot_number_string);
			
                //insert to rla_requests table
                $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->coop)->first(); 
                $insert_data = array(
                    'coop_accreditation' => $request->coop,
                    'coop_moa' => $coop->current_moa,
                    'sg_name' => $request->sg_name,
                    'certification_date' => $request->certification_date,
                    'lab_number' => $request->lab_number,
                    //'lot_number' => $request->lot_number,
					'lot_number' => $lot_number_string,
                    'seed_tag' => $seed_tag,
                    'seed_variety' => $request->variety,
                    'no_of_bags' => $request->bags,
                    'rla_file' => 'BPI_INSERT',
                    'status' => 4,
                    'rla_type' => 'N/A'
                );
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')->insert($insert_data);

                //insert to rla_details table
                /*$sg_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
                    ->where('full_name', $request->sg_name)
                    ->where('coop_accred', $request->coop)
                    ->value('sg_id');*/
					
				$sg_check = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
                    ->where('full_name', $request->sg_name)
                    ->where('coop_accred', $request->coop)
                    ->first();

                $sg_id = 0;
                if(count($sg_check) > 0){
                    $sg_id = $sg_check->sg_id;
                }else{
                    $sg_id = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->insertGetId([
                        'coop_accred' => $request->coop,
                        'is_active' => 1,
                        'is_block' => 0,
                        'fname' => '',
                        'mname' => '',
                        'lname' => '',
                        'extension' => '',
                        'full_name' => $request->sg_name
                    ]);
                }

                //$request_details = DB::connection('delivery_inspection_db')->table('rla_requests')->where('id', $request_id)->first();
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->insert([
                    'coop_name' => $coop->coopName,
                    'coopAccreditation' => $coop->accreditation_no,
                    'sg_id' => $sg_id,
                    'sg_name' => $request->sg_name,
                    'certificationDate' => $request->certification_date,
                    'labNo' => $request->lab_number,
                    //'lotNo' => $request->lot_number,
                    'lotNo' => $lot_number_string,
					'noOfBags' => $request->bags,
                    'seedVariety' => $request->variety,
                    'moaNumber' => $coop->current_moa
                ]);

                Session::flash("success", "You have successfully uploaded an RLA, this has been automatically saved to our database.");
                return redirect()->route('coop.rla.bpi');
            }
        }     
    }

    public function save_request(Request $request){
        $this->validate($request, array(
            'coop' => 'required',
            'sg_name' => 'required',
            'certification_date' => 'required',
            'lab_number' => 'required',
            'lot_number' => 'required',
            'variety' => 'required',
            'bags' => 'required|min:1|max:200',
            'rla_file' => 'required'
        ));

        if ($request->hasFile('rla_file')) {

            $clean_coop_accreditation = preg_replace("/[^a-zA-Z0-9]+/", "-", $request->coop);
            $seed_tag = $request->lab_number."/".$request->lot_number;
            $seedtag_total = 0;

            //check seedtag volume in database
            /*$seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('is_cancelled', 0)
            ->where('seedTag', 'like', '%' . $request->lab_number . '%')
            //->where('seedTag', $seed_tag)
            ->get();

           
            if(count($seedtag_data_tbl_delivery) > 0){
                foreach($seedtag_data_tbl_delivery as $row){
                    $seedtag_total += $row->totalBagCount;
                }
            }else{
                $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    //->where('seedTag', $seed_tag)
                    ->where('seedTag', 'like', '%' . $request->lab_number . '%')
                    ->get();

                if(count($seedtag_data_tbl_actual_delivery) > 0){
                    foreach($seedtag_data_tbl_actual_delivery as $row2){
                        $seedtag_total += $row2->totalBagCount;
                    }
                }
            }*/

            //check volume in rla data
            $seed_labno_rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('labNo', $request->lab_number)
                ->get();
            
            foreach($seed_labno_rla as $row){
                $seedtag_total += $row->noOfBags;
            }

            //check if seed tag exists in rla database
            $check_rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('labNo', $request->lab_number)
                ->where('lotNo', $request->lot_number)
                ->get();

            if(count($check_rla) > 0){
                Session::flash("error_msg", "The seed tag (laboratory # & lot #) already exists in the database!");
                return redirect()->route('coop.rla.manual');
            
            }else{
                $volume_after_edit = $seedtag_total + $request->bags;
                if($volume_after_edit > 240){
                    Session::flash("error_msg", "The system detected that the specified laboratory number is already recorded: (current count: $seedtag_total), your input exceeds the maximum allocation of 240 bags.");
                    return redirect()->route('coop.rla.manual');
                }else{
                    $image = $request->file('rla_file');
                    $name = "RLA_UPLOAD_".$clean_coop_accreditation."_".$request->lab_number."_".$request->lot_number.time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path("rla_manual");
                    $image->move($destinationPath, $name);

                    $coop_moa = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->coop)->value('current_moa');
                
                    $insert_data = array(
                        'coop_accreditation' => $request->coop,
                        'coop_moa' => $coop_moa,
                        'sg_name' => $request->sg_name,
                        'certification_date' => $request->certification_date,
                        'lab_number' => $request->lab_number,
                        'lot_number' => $request->lot_number,
                        'seed_tag' => $seed_tag,
                        'seed_variety' => $request->variety,
                        'no_of_bags' => $request->bags,
                        'rla_file' => $name,
                        'status' => 1,
                        'rla_type' => $image->getClientOriginalExtension()
                    );
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')->insert($insert_data);

                    Session::flash("success", "You have successfully sent a request for RLA Upload.");
                    return redirect()->route('coop.rla.manual');
                }
            }


            
        }else{
            Session::flash("error_msg", "There was an error in uploading your file, please try again..");
            return redirect()->route('coop.rla.manual');
        }
    }


    public function rla_request_list(){
        return view('coop.rla.manual_approve');
    }

    public function rla_request_table(Request $request){
        if($request->status == 1){
            return Datatables::of(DB::connection('delivery_inspection_db')->table('rla_requests')
                ->where('status', 1)
                ->orderBy('date_recorded', 'DESC')
            )
            ->addColumn('coop_name', function($row){
                return DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $row->coop_accreditation)->value('coopName');
            })
            ->addColumn('action', function($row){
                $url = asset('public/rla_manual/'.$row->rla_file);
                $view_btn = "<a href='$url' target='_blank' class='btn btn-warning btn-xs btn-block' style='margin: 0;'><i class='fa fa-eye'> View RLA</i></a>";
                $approve_btn = "<a href='#' data-toggle='modal' data-target='#approve_modal' data-id='$row->id' class='btn btn-success btn-xs btn-block' style='margin-top: -15px;'><i class='fa fa-thumbs-up'> Approve RLA</i></a>";
                $reject_btn = "<a href='#' data-toggle='modal' data-target='#reject_modal' data-id='$row->id' class='btn btn-danger btn-xs btn-block' style='margin-top: -21px;'><i class='fa fa-thumbs-down'> Reject RLA</i></a>";

                return "<center>$view_btn.$approve_btn.$reject_btn</center>";
            })
            ->make(true);
        
        }else if($request->status == 2 || $request->status == 3){
            return Datatables::of(DB::connection('delivery_inspection_db')->table('rla_requests')
                ->where('status', $request->status)
                ->orderBy('date_recorded', 'DESC')
            )
            ->addColumn('coop_name', function($row){
                return DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $row->coop_accreditation)->value('coopName');
            })
            ->addColumn('action', function($row){
                $url = asset('public/rla_manual/'.$row->rla_file);
                $view_btn = "<a href='$url' target='_blank' class='btn btn-warning btn-xs' style='margin: 0;'><i class='fa fa-eye'></i></a>";
                return "<center>$view_btn</center>";
            })
            ->make(true);
        }
        
    }

    public function rla_request_details(Request $request){
        $request_id = $request->request_id;
        $request_details = DB::connection('delivery_inspection_db')->table('rla_requests')->where('id', $request_id)->first();
        $coop_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request_details->coop_accreditation)->first();

        return array(
            'seedCoop' => $coop_details->coopName,
            'moaNumber' => $coop_details->current_moa,
            'seedGrower' => $request_details->sg_name,
            'seedVariety' => $request_details->seed_variety,
            'seedTag' => $request_details->seed_tag,
            'bags' => $request_details->no_of_bags,
            'certificationDate' => $request_details->certification_date
        );
    }

    public function rla_request_confirm(Request $request){
        $request_id = $request->request_id;
        $request_details =  DB::connection('delivery_inspection_db')->table('rla_requests')->where('id', $request_id)->first();
        $coop_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request_details->coop_accreditation)->first();

        //check if seed grower is registered in database
        $sg_check = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
            ->where('full_name', $request_details->sg_name)
            ->where('coop_accred', $request_details->coop_accreditation)
            ->first();

        $sg_id = 0;
        if(count($sg_check) > 0){
            $sg_id = $sg_check->sg_id;
        }else{
            $sg_id = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->insertGetId([
                'coop_accred' => $request_details->coop_accreditation,
                'is_active' => 1,
                'is_block' => 0,
                'fname' => '',
                'mname' => '',
                'lname' => '',
                'extension' => '',
                'full_name' => $request_details->sg_name
            ]);
        }

        //check seedtag volume in database
        /*$seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('is_cancelled', 0)
            ->where('seedTag', 'like', '%' . $request_details->lab_number . '%')
            //->where('seedTag', $seed_tag)
            ->get();

        $seedtag_total = 0;
        if(count($seedtag_data_tbl_delivery) > 0){
            foreach($seedtag_data_tbl_delivery as $row){
                $seedtag_total += $row->totalBagCount;
            }
        }else{
            $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                //->where('seedTag', $seed_tag)
                ->where('seedTag', 'like', '%' . $request_details->lab_number . '%')
                ->get();

            if(count($seedtag_data_tbl_actual_delivery) > 0){
                foreach($seedtag_data_tbl_actual_delivery as $row2){
                    $seedtag_total += $row2->totalBagCount;
                }
            }
        }
        $volume_after_computation = $seedtag_total + $request_details->no_of_bags;
        */

        //check volume in rla data
        $seedtag_total = 0;
        $seed_labno_rla = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo', $request_details->lab_number)
            ->get();
        
        foreach($seed_labno_rla as $row){
            $seedtag_total += $row->noOfBags;
        }
        $volume_after_computation = $seedtag_total + $request_details->no_of_bags;

        if($volume_after_computation > 240){
            //Session::flash("error_msg", "The system detected that the specified laboratory number is already recorded: (current count: $seedtag_total), your input exceeds the maximum allocation of 240 bags.");
            //return redirect()->route('coop.rla.approve_home');
            return array(
                "msg" => "exceed_volume",
                "count" =>  $seedtag_total
            );
        }else{
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->insert([
                'coop_name' => $coop_details->coopName,
                'coopAccreditation' => $request_details->coop_accreditation,
                'sg_id' => $sg_id,
                'sg_name' => $request_details->sg_name,
                'certificationDate' => $request_details->certification_date,
                'labNo' => $request_details->lab_number,
                'lotNo' => $request_details->lot_number,
                'noOfBags' => $request_details->no_of_bags,
                'seedVariety' => $request_details->seed_variety,
                'moaNumber' => $request_details->coop_moa
            ]);
    
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')
            ->where('id', $request_id)
            ->update([
                'status' => 2
            ]);

            return array(
                "msg" => "approve_ok",
            );
        }
    }

    public function rla_request_reject(Request $request){
        $request_id = $request->request_id;
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')
        ->where('id', $request_id)
        ->update([
            'status' => 3
        ]);

        return array(
            "msg" => "reject_ok",
        );
    }
    /** END */
	
	/**
     * 10-27-2020
     */
    public function coop_rla_edit(){
        return view('coop.rla.edit_tbl');
    }

    public function coop_rla_edit_tbl(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->orderBy('coopAccreditation', 'ASC')
        )
        ->addColumn('cooperative_name', function($row){
            return DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $row->coopAccreditation)->value('coopName');
        })
        ->addColumn('action', function($row){
            $edit_btn = "<a href='".route('coop.rla.edit.form', $row->rlaId)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-edit'></i> EDIT RLA</a>";
            return $edit_btn;
        })
        ->make(true);
    }

    public function coop_rla_edit_form($id){
        $rla_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->where('rlaId', $id)->first();
        $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->get();
        $variety_list = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->get();
        return view('coop.rla.edit_form')
            ->with('rla_details', $rla_details)
            ->with('coop_list', $coop_list)
            ->with('variety_list', $variety_list);
    }   

    public function confirm_edit_rla(Request $request){
        //layers of checkin
        //1. check if seedtag is being used, if yes must not exceed 240 bags
        //2. check seed grower if exists, if yes return id if no insert profile and return id
        
        $request_seedTag = $request->lab_number."/".$request->lot_number;
        $current_seedTag = $request->rla_old_seedtag;

        $check_seedtag = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo', $request->lab_number)
			->where('lotNo', $request->lot_number)
            ->where('rlaId', '!=', $request->rla_id)
            ->first();

        if(count($check_seedtag) > 0){
            $seed_volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('labNo', $request->lab_number)
				->where('lotNo', $request->lot_number)
                ->sum('noOfBags');
        }else{
            $seed_volume = 0;
        }
		
		
		//check if seed tag has delivery data
		$delivery_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
			->where('seedTag', $current_seedTag)
			->get();
			
		if(count($delivery_data) > 0){
			Session::flash('error_msg', 'The seed tag already has delivery data, this is no longer available for editting.');
            return redirect()->route('coop.rla.edit.form', $request->rla_id);
		}else{
			$seed_volume = $seed_volume + $request->bags;
			if($seed_volume <= 240){
				//check seed grower profile
				if($request->sg_name != ""){
					$seed_grower_profile = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')->where('full_name', $request->sg_name)->first();
					if(count($seed_grower_profile) > 0){
						$sg_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')->where('full_name', $request->sg_name)->value('sg_id');
					}else{
						$sg_id = DB::connection('delivery_inspection_db')->table('tbl_seed_grower')->insertGetId([
							'coop_accred' => $request->coop,
							'is_active' => 1,
							'is_block' => 0,
							'fname' => '',
							'mname' => '',
							'lname' => '',
							'extension' => '',
							'full_name' => $request->sg_name
						]);
					}

					$coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->coop)->value('coopName');
					$coop_moa = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->coop)->value('current_moa');

					DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
					->where('rlaId', $request->rla_id)
					->update([
						'coop_name' => $coop_name,
						'coopAccreditation' => $request->coop,
						'sg_id' => $sg_id,
						'sg_name' => $request->sg_name,
						'certificationDate' => $request->certification_date,
						'labNo' => $request->lab_number,
						'lotNo' => $request->lot_number,
						'noOfBags' => $request->bags,
						'seedVariety' => $request->variety,
						'moaNumber' => $coop_moa
					]);

					DB::connection('mysql')->table('lib_logs')
					->insert([
						'category' => 'EDIT_RLA',
						'description' => 'Editted rla of seedtag: `'.$current_seedTag.'` with details | seed cooperative: '.$coop_name.', seed tag: '.$request->lab_number."/".$request->lot_number.", seed grower: ".$request->sg_name.", seed variety: ".$request->variety.", number of bags: ".$request->bags,
						'author' => Auth::user()->username,
						'ip_address' => $_SERVER['REMOTE_ADDR']
					]);
					DB::commit();

					Session::flash('success', 'You have successfully updated an RLA');
					return redirect()->route('coop.rla.edit');

				}else{
					Session::flash('error_msg', 'Please specify a seed grower');
					return redirect()->route('coop.rla.edit.form', $request->rla_id);
				}            

			}else{
				Session::flash('error_msg', 'Exceeded maximum volume for the inputted seedtag (240 bags)');
				return redirect()->route('coop.rla.edit.form', $request->rla_id);
			}
		}
    }
	
	//01-06-2021
    public function download_commitment_of_coops(Request $request){
        $seed_coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->get();
        $return_arr = array();

        $nsic_rc_27_total = 0;
        $nsic_rc_226_total = 0;
        $nsic_rc_358_total = 0;
        $nsic_rc_400_total = 0;
        $nsic_rc_402_total = 0;
        $nsic_rc_440_total = 0;
        $nsic_rc_480_total = 0;
        $nsic_rc_158_total = 0;
        $nsic_rc_160_total = 0;
        $nsic_rc_216_total = 0;
        $nsic_rc_218_total = 0;
        $nsic_rc_222_total = 0;
        $nsic_rc_442_total = 0;
        $psb_rc_10_total = 0;
        $psb_rc_18_total = 0;
        $psb_rc_82_total = 0;
        
        foreach($seed_coops as $sc){

            $nsic_rc_27 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2014 Rc 27')->sum('commitment_value');
            $nsic_rc_226 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2010 Rc 226')->sum('commitment_value');
            $nsic_rc_358 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2014 Rc 358')->sum('commitment_value');
            $nsic_rc_400 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2015 Rc 400')->sum('commitment_value');
            $nsic_rc_402 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2015 Rc 402')->sum('commitment_value');
            $nsic_rc_440 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2016 Rc 440')->sum('commitment_value');
            $nsic_rc_480 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC 2016 Rc 480')->sum('commitment_value');
            $nsic_rc_158 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 158')->sum('commitment_value');
            $nsic_rc_160 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 160')->sum('commitment_value');
            $nsic_rc_216 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 216')->sum('commitment_value');
            $nsic_rc_218 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 218')->sum('commitment_value');
            $nsic_rc_222 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 222')->sum('commitment_value');
            $nsic_rc_442 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'NSIC Rc 442')->sum('commitment_value');
            $psb_rc_10 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'PSB Rc 10')->sum('commitment_value');
            $psb_rc_18 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'PSB Rc 18')->sum('commitment_value');
            $psb_rc_82 = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->where('commitment_variety', 'PSB Rc 82')->sum('commitment_value');

            $row_array = array(
                "Seed Coop" => $sc->coopName,
                "Accreditation Number" => $sc->accreditation_no,
                "MOA Number" => $sc->current_moa,
                "NSIC Rc 226" => $nsic_rc_226 != 0 ? number_format($nsic_rc_226) : '0',
                "NSIC Rc 27" => $nsic_rc_27 != 0 ? number_format($nsic_rc_27) : '0',
                "NSIC Rc 358" => $nsic_rc_358 != 0 ? number_format($nsic_rc_358) : '0',
                "NSIC Rc 400" => $nsic_rc_400 != 0 ? number_format($nsic_rc_400) : '0',
                "NSIC Rc 402" => $nsic_rc_402 != 0 ? number_format($nsic_rc_402) : '0',
                "NSIC Rc 440" => $nsic_rc_440 != 0 ? number_format($nsic_rc_440) : '0',
                "NSIC Rc 480" => $nsic_rc_480 != 0 ? number_format($nsic_rc_480) : '0',
                "NSIC RC 158" => $nsic_rc_158 != 0 ? number_format($nsic_rc_158) : '0',
                "NSIC Rc 160" => $nsic_rc_160 != 0 ? number_format($nsic_rc_160) : '0',
                "NSIC Rc 216" => $nsic_rc_216 != 0 ? number_format($nsic_rc_216) : '0',
                "NSIC Rc 218" => $nsic_rc_218 != 0 ? number_format($nsic_rc_218) : '0',
                "NSIC Rc 222" => $nsic_rc_222 != 0 ? number_format($nsic_rc_222) : '0',
                "NSIC Rc 442" => $nsic_rc_442 != 0 ? number_format($nsic_rc_442) : '0',
                "PSB Rc 10" => $psb_rc_10 != 0 ? number_format($psb_rc_10) : '0',
                "PSB Rc 18" => $psb_rc_18 != 0 ? number_format($psb_rc_18) : '0',
                "PSB Rc 82" => $psb_rc_82 != 0 ? number_format($psb_rc_82) : '0'
            );
            array_push($return_arr, $row_array);
            
            $nsic_rc_226_total += $nsic_rc_226;
            $nsic_rc_27_total += $nsic_rc_27;
            $nsic_rc_358_total += $nsic_rc_358;
            $nsic_rc_400_total += $nsic_rc_400;
            $nsic_rc_402_total += $nsic_rc_402;
            $nsic_rc_440_total += $nsic_rc_440;
            $nsic_rc_480_total += $nsic_rc_480;
            $nsic_rc_158_total += $nsic_rc_158;
            $nsic_rc_160_total += $nsic_rc_160;
            $nsic_rc_216_total += $nsic_rc_216;
            $nsic_rc_218_total += $nsic_rc_218;
            $nsic_rc_222_total += $nsic_rc_222;
            $nsic_rc_442_total += $nsic_rc_442;
            $psb_rc_10_total += $psb_rc_10;
            $psb_rc_18_total += $psb_rc_18;
            $psb_rc_82_total += $psb_rc_82;
        }

        $last_row = array(
            "Seed Coop" => '',
            "Accreditation Number" => '',
            "MOA Number" => 'TOTAL: ',
            "NSIC Rc 226" => $nsic_rc_226_total != 0 ? number_format($nsic_rc_226_total) : '0',
            "NSIC Rc 27" => $nsic_rc_27_total != 0 ? number_format($nsic_rc_27_total) : '0',
            "NSIC Rc 358" => $nsic_rc_358_total != 0 ? number_format($nsic_rc_358_total) : '0',
            "NSIC Rc 400" => $nsic_rc_400_total != 0 ? number_format($nsic_rc_400_total) : '0',
            "NSIC Rc 402" => $nsic_rc_402_total != 0 ? number_format($nsic_rc_402_total) : '0',
            "NSIC Rc 440" => $nsic_rc_440_total != 0 ? number_format($nsic_rc_440_total) : '0',
            "NSIC Rc 480" => $nsic_rc_480_total != 0 ? number_format($nsic_rc_480_total) : '0',
            "NSIC RC 158" => $nsic_rc_158_total != 0 ? number_format($nsic_rc_158_total) : '0',
            "NSIC Rc 160" => $nsic_rc_160_total != 0 ? number_format($nsic_rc_160_total) : '0',
            "NSIC Rc 216" => $nsic_rc_216_total != 0 ? number_format($nsic_rc_216_total) : '0',
            "NSIC Rc 218" => $nsic_rc_218_total != 0 ? number_format($nsic_rc_218_total) : '0',
            "NSIC Rc 222" => $nsic_rc_222_total != 0 ? number_format($nsic_rc_222_total) : '0',
            "NSIC Rc 442" => $nsic_rc_442_total != 0 ? number_format($nsic_rc_442_total) : '0',
            "PSB Rc 10" => $psb_rc_10_total != 0 ? number_format($psb_rc_10_total) : '0',
            "PSB Rc 18" => $psb_rc_18_total != 0 ? number_format($psb_rc_18_total) : '0',
            "PSB Rc 82" => $psb_rc_82_total != 0 ? number_format($psb_rc_82_total) : '0'
        );
        array_push($return_arr, $last_row);

        $myFile = Excel::create('SEED_COOP_COMMITMENTS', function($excel) use ($return_arr) {
            $excel->sheet("COOP_LIST", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });

            $seed_coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->get();
            foreach($seed_coops as $sc){
                $coop_arr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $sc->coopId)->get();
                $coop_arr_data = array();
                foreach($coop_arr as $sheet_row){
                    $sheet_data = array(
                        "Seed Variety" => $sheet_row->commitment_variety,
                        "Total Commitment" => number_format($sheet_row->commitment_value)
                    );
                    array_push($coop_arr_data, $sheet_data);
                }

                $coop_sheet_name = str_replace("/","-",$sc->accreditation_no);
                $coopName = $sc->coopName;
                $coop_accreditation = "ACCREDITATION #: ".$sc->accreditation_no;
                $coop_moa = "MOA #: ".$sc->current_moa;

                $excel->sheet($coop_sheet_name, function($sheet) use ($coop_arr_data, $coopName, $coop_accreditation, $coop_moa) {
                    $sheet->fromArray($coop_arr_data);
                    $sheet->prependRow(1, array($coopName));
                    $sheet->prependRow(2, array($coop_accreditation));
                    $sheet->prependRow(3, array($coop_moa));
                    $sheet->prependRow(4, array(''));
                    $sheet->mergeCells('A1:B1');
                    $sheet->mergeCells('A2:B2');
                    $sheet->mergeCells('A3:B3');
                });
            }            
        });

        $file_name = "SEED_COOP_COMMITMENTS"."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

    public function regional_commitments($coopID){
        $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('coopId', $coopID)->value('coopName');
        $coop_accreditaion_no = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('coopId', $coopID)->value('accreditation_no');
        $total_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')->where('coopID', $coopID)->value('total_value');
        $commitment_breakdown = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $coopID)->get();
        
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')
            ->where('accreditation_no', $coop_accreditaion_no)
            ->groupBy('region')->get();
        
        $current_allocations = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('coop_Id', $coopID)->get();

        return view('coop.regional.home')
            ->with('total_commitment', $total_commitments)
            ->with('commitment_breakdown', $commitment_breakdown)
            ->with('coop_name', $coop_name)
            ->with('regions', $regions)
            ->with('coopID', $coopID)
            ->with('current_allocations', $current_allocations);
    }

    public function regional_commitments_save(Request $request){
        DB::beginTransaction();
        try {
            $regional_allocation = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->where('coop_Id', '=', $request->coopID)
                ->where('seed_variety', $request->seed_variety)
                ->sum('volume');

            $commitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                ->where('coopID', $request->coopID)
                ->where('commitment_variety', $request->seed_variety)
                ->sum('commitment_value');

            if($commitment >= ($regional_allocation + $request->seed_volume)){
                $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('coopId', $request->coopID)->value('coopName');
                $station_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('region', $request->region)->first();
                $insert_arr = array(
                    "station_id" => $station_details->station_id,
                    "station_name" => $station_details->station_name,
                    "coop_Id" => $request->coopID,
                    "coop_name" => $coop_name,
                    "region_name" => $request->region,
                    "seed_variety" => $request->seed_variety,
                    "volume" => $request->seed_volume,
                    "date_added" => date("Y-m-d H:i:s"),
                    "date_updated" => date("Y-m-d H:i:s")
                );
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->insert($insert_arr);
                DB::commit();
                return "Allocation Saved!";
            }else{
                DB::rollback();
                return "Allocation Exceeds";
            }
        } catch (\Exception $e) {
            DB::rollback();
            return "Allocation error";
        }
    }

    public function regional_commitments_delete($regional_commitment_ID){
        DB::beginTransaction();
        try {
            $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', '=', $regional_commitment_ID)->first();
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', '=', $regional_commitment_ID)->delete();
            DB::commit();
            return redirect()->route('coop.regional_commitment', $coop->coop_Id);

        } catch (\Exception $e) {
            DB::rollback();
            //return "Error encountered";
            return dd($e);
        }       
    }

    public function regional_check_seeds(Request $request){
        $regional_allocation = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', '=', $request->coopID)->where('seed_variety', $request->seed_variety)
            ->sum('volume');

        $commitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
            ->where('coopID', $request->coopID)
            ->where('commitment_variety', $request->seed_variety)
            ->sum('commitment_value');

        $current_value = $commitment - $regional_allocation;
        return number_format($current_value);
    }

    public function regional_search_allocations(Request $request){
        $allocations = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', $request->coopID)
            ->where('region_name', 'like', '%' . $request->keyboard . '%')
            ->get();

        $return_arr = array();
        foreach($allocations as $row){
            array_push($return_arr, array(
                "region" => $row->region_name,
                "station" => $row->station_name,
                "seed_variety" => $row->seed_variety,
                "volume" => $row->volume,
                "allocation_id" => $row->id
            ));
        }

        return $return_arr;
    }
	
	//01-29-2021
    public function coopAdjustmentVarities(Request $request){
        $coop_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', '=', $request->coop_id)
            ->where('region_name', $request->region)
            ->get();

        $array = array();
        foreach($coop_commitments as $row){
            array_push($array, array(
                "seed_variety" => $row->seed_variety,
                "seed_value" => number_format($row->volume),
                "seed_allocation_id" => $row->id,
				"coopID" => $row->coop_Id,
				"coop_acreditation" => $row->accreditation_no,
            ));
        }
        
        return $array;
    }


    public function coopAdjustmentRegions(Request $request){
        $coop_region_data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', $request->coop_id)
            ->groupBy('region_name')
            ->get();

        if(count($coop_region_data) > 0){
            $array = array();
            foreach($coop_region_data as $row){
                array_push($array, array(
                    "coop_id" => $row->coop_Id,
                    "coop_name" => $row->coop_name,
                    "region_name" => $row->region_name
                ));
            }
        }else{
            $array = 0;
        }
    
        return $array;
    }

    public function coopAdjustmentAddAllocation(Request $request){
        DB::beginTransaction();
        try{	
			$current_value = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("volume");
            $current_seed_variety = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("seed_variety");
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('id', $request->allocation_id)
            ->update([
                'volume' => $current_value + $request->input_value
            ]);
			$new_val = $current_value - $request->input_value;
			
			//get commitment ID based on regional allocationID
			$commitment_id = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("commitmentID");
			$commitment_volume = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('id', $commitment_id)->value('commitment_value');
			DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
            ->where('id', $commitment_id)
            ->update([
                'commitment_value' => $commitment_volume + $request->input_value
            ]);
			
			$for_logging = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->first(); 
            $insert_arr = array(
                "coopID" => $for_logging->coop_Id,
                "coop_accreditation" => $for_logging->accreditation_no,
                "coop_name" => $for_logging->coop_name,
                "region_name" => $for_logging->region_name,
                "seed_variety" => $for_logging->seed_variety,
                "data_category" => "ADD ALLOCATION",
                "no_of_bags" => $request->input_value,
                "date_recorded" => date("Y-m-d"),
                "performed_by" => Auth::user()->username
            );
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_adjustments')->insert($insert_arr);
			DB::commit();
			
			$coop_id = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("coop_Id");
			$total_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')->where('coopID', $coop_id)->value('total_value');
			$current_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $coop_id)->sum('commitment_value');
			$available_balance = $total_commitments - $current_commitments;

            return json_encode(array(
                "return_msg" => "add_success",
                "return_value" => number_format($new_val),
                "return_sv" => $current_seed_variety,
				"return_balance" => $available_balance
            ));

        } catch (\Exception $e) {
            DB::rollback();
            return array(
                //"return_msg" => "add_error",
                "return_msg" => $e,
                "return_value" => 0
            );
        }
    }

    public function coopAdjustmentDeductAllocation(Request $request){
        DB::beginTransaction();
        try{
            $current_value = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("volume");
            $current_seed_variety = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("seed_variety");
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('id', $request->allocation_id)
            ->update([
                'volume' => $current_value - $request->input_value
            ]);

            $new_val = $current_value - $request->input_value;
			
			//get commitment ID based on regional allocationID
			$commitment_id = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("commitmentID");
			$commitment_volume = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('id', $commitment_id)->value('commitment_value');
			DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
            ->where('id', $commitment_id)
            ->update([
                'commitment_value' => $commitment_volume - $request->input_value
            ]);

            /**
             * INSERT LOGS
             */
            $for_logging = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->first(); 
            $insert_arr = array(
                "coopID" => $for_logging->coop_Id,
                "coop_accreditation" => $for_logging->accreditation_no,
                "coop_name" => $for_logging->coop_name,
                "region_name" => $for_logging->region_name,
                "seed_variety" => $for_logging->seed_variety,
                "data_category" => "DEDUCT ALLOCATION",
                "no_of_bags" => $request->input_value,
                "date_recorded" => date("Y-m-d"),
                "performed_by" => Auth::user()->username
            );
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_adjustments')->insert($insert_arr);
            DB::commit();
			
			$coop_id = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')->where('id', $request->allocation_id)->value("coop_Id");
			$total_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')->where('coopID', $coop_id)->value('total_value');
			$current_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $coop_id)->sum('commitment_value');
			$available_balance = $total_commitments - $current_commitments;
			
            return json_encode(array(
                "return_msg" => "deduct_success",
                "return_value" => number_format($new_val),
                "return_sv" => $current_seed_variety,
				"return_balance" => $available_balance
            ));

        } catch (\Exception $e) {
            DB::rollback();
            return array(
                "return_msg" => "deduct_error",
                "return_value" => 0
            );
        }
    }

    public function coopAdjustmentLogs(Request $request){
        $list_of_dates =  DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_adjustments')
            ->where('coopID', $request->coop_id)
            ->groupBy('date_recorded')
            ->get();

        $date_array = array();
        foreach($list_of_dates as $row_date){
            $list_of_varieties = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_adjustments')
                ->select(DB::raw('SUM(no_of_bags) as total_value'),
                   'date_recorded', 'seed_variety', 'data_category', 'region_name')
                ->where('coopID', $request->coop_id)
                ->where('date_recorded', $row_date->date_recorded)
                ->groupBy('seed_variety', 'data_category', 'region_name')
                ->orderBy('seed_variety')
                ->get();

            $variety_array = array();
            $variety_list = "";
            foreach($list_of_varieties as $row_varities){
                $data_cat = "";
                if($row_varities->data_category == "ADD ALLOCATION"){
                    $data_cat = "+";
                }elseif($row_varities->data_category == "DEDUCT ALLOCATION"){
                    $data_cat = "-";
                }

                $variety_list .= $row_varities->region_name.": ".$row_varities->seed_variety." (".$data_cat."".number_format($row_varities->total_value).")<br>";
                /*array_push($variety_array, array(
                    "date_recorded" => $row_varities->date_recorded,
                    "seed_variety" => $row_varities->seed_variety,
                    "data_category" => $row_varities->data_category,
                    "total_value" => $row_varities->total_value
                ));*/
            }

            array_push($date_array, array(
                "date_recorded" => $row_date->date_recorded,
                "seed_list" => $variety_list
            ));
        }
        
        return count($date_array) == 0 ? 0 : $date_array;
    }
}
