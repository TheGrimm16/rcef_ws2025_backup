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
        return view('coop.index');
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
                
                return '
                        <a href="" data-toggle="modal" data-target="#coopDetails" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" data-bags="'.$bags->total_value.'" data-acn="'.$row->accreditation_no.'" data-moa="'.$row->current_moa.'" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i></a>
                        <a href="#" data-toggle="modal" data-target="#moa_number_modal" data-id="'.$row->coopId.'" data-moa="'.$row->current_moa.'" class="btn btn-info btn-sm"><i class="fa fa-file"></i></a>
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
            return date("F j, Y h:i:s A", strtotime($row->date_added)); 
        })
        ->make(true);
    }

    public function coopVaritiesADDSubmit(Request $request){
        $coop_moa = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('coopId', '=', $request->coopDetailsID)->value('current_moa');
        DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insert([
            'coopID' => $request->coopDetailsID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->seed_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s"),
            'variety_status' => 1,
            'moa_number' => $coop_moa
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

        $results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics $comm_filter_str GROUP BY variety ORDER BY variety"));

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

            $results = DB::connection('seeds_db')->select( DB::raw("SELECT * FROM seed_characteristics $comm_filter_str GROUP BY variety ORDER BY variety"));

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
            return date("F j, Y h:i:s A", strtotime($row->date_added)); 
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
        ->make(true);
    }

    public function saveCoopCommitment(Request $request){
        $this->validate($request, array(
            'coopID' => 'required'
        ));

        $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->where('coopId', '=', $request->coopID)
            ->first();

        DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insert([
            'coopID' => $request->coopID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->commitment_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s"),
            'moa_number' => $coop->current_moa,
            'variety_status' => '1' 
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

        //check seedtag volume in database
        $seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('is_cancelled', 0)
            ->where('seedTag', $seed_tag)
            ->get();

        $seedtag_total = 0;

        if(count($seedtag_data_tbl_delivery) > 0){
            foreach($seedtag_data_tbl_delivery as $row){
                $seedtag_total += $row->totalBagCount;
            }
        }else{
            $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('seedTag', $seed_tag)
                ->get();

            if(count($seedtag_data_tbl_actual_delivery) > 0){
                foreach($seedtag_data_tbl_actual_delivery as $row2){
                    $seedtag_total += $row2->totalBagCount;
                }
            }
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
                Session::flash("error_msg", "The system detected that the specified seed tag is already in use (current count: $seedtag_total), your input exceeds the maximum allocation of 240 bags.");
                return redirect()->route('coop.rla.bpi');
            }else{           

                //insert to rla_requests table
                $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->coop)->first(); 
                $insert_data = array(
                    'coop_accreditation' => $request->coop,
                    'coop_moa' => $coop->current_moa,
                    'sg_name' => $request->sg_name,
                    'certification_date' => $request->certification_date,
                    'lab_number' => $request->lab_number,
                    'lot_number' => $request->lot_number,
                    'seed_tag' => $seed_tag,
                    'seed_variety' => $request->variety,
                    'no_of_bags' => $request->bags,
                    'rla_file' => 'BPI_INSERT',
                    'status' => 4,
                    'rla_type' => 'N/A'
                );
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')->insert($insert_data);

                //insert to rla_details table
                $sg_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
                    ->where('full_name', $request->sg_name)
                    ->where('coop_accred', $request->coop)
                    ->value('sg_id');

                //$request_details = DB::connection('delivery_inspection_db')->table('rla_requests')->where('id', $request_id)->first();
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->insert([
                    'coop_name' => $coop->coopName,
                    'coopAccreditation' => $coop->accreditation_no,
                    'sg_id' => $sg_id,
                    'sg_name' => $request->sg_name,
                    'certificationDate' => $request->certification_date,
                    'labNo' => $request->lab_number,
                    'lotNo' => $request->lot_number,
                    'noOfBags' => $request->bags,
                    'seedVariety' => $request->variety,
                    'moaNumber' => $coop->current_moa
                ]);

                Session::flash("success", "You have successfully sent a request for RLA Upload.");
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

            //check seedtag volume in database
            $seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('is_cancelled', 0)
                ->where('seedTag', $seed_tag)
                ->get();

            $seedtag_total = 0;

            if(count($seedtag_data_tbl_delivery) > 0){
                foreach($seedtag_data_tbl_delivery as $row){
                    $seedtag_total += $row->totalBagCount;
                }
            }else{
                $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('seedTag', $seed_tag)
                    ->get();

                if(count($seedtag_data_tbl_actual_delivery) > 0){
                    foreach($seedtag_data_tbl_actual_delivery as $row2){
                        $seedtag_total += $row2->totalBagCount;
                    }
                }
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
                    Session::flash("error_msg", "The system detected that the specified seed tag is already in use (current count: $seedtag_total), your input exceeds the maximum allocation of 240 bags.");
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
                $view_btn = "<a href='$url' target='_blank' class='btn btn-warning btn-xs' style='margin: 0;'><i class='fa fa-eye'></i></a>";
                $approve_btn = "<a href='#' data-toggle='modal' data-target='#approve_modal' data-id='$row->id' class='btn btn-success btn-xs' style='margin: 0;'><i class='fa fa-thumbs-up'></i></a>";
                $reject_btn = "<a href='#' data-toggle='modal' data-target='#reject_modal' data-id='$row->id' class='btn btn-danger btn-xs' style='margin: 0;'><i class='fa fa-thumbs-down'></i></a>";

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
    }

    public function rla_request_reject(Request $request){
        $request_id = $request->request_id;
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.rla_requests')
        ->where('id', $request_id)
        ->update([
            'status' => 3
        ]);
    }
    /** END */
}
