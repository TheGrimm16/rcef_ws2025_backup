<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;


class CoopController extends Controller
{
    public function index(){
        return view('coop.index');
    }

    public function totalCommitment(Request $request){
        return DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
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
            ->where('accreditation_no', '!=', '')
            ->orderBy('coopName', 'ASC')
        )
        ->addColumn('coop_alias', function($row){
            return $row->coopName." ".$row->acronym; 
        })
        ->addColumn('action', function($row){
            $commitments = DB::connection('seed_coop_db')->table('tbl_commitment')
                        ->where('coopID', '=', $row->coopId)
                        ->get();

            if(count($commitments) > 0){
                $bags = DB::connection('seed_coop_db')->table('tbl_total_commitment')
                    ->select('total_value')
                    ->where('coopID', '=', $row->coopId)
                    ->first();
                
                return '<a href="" data-toggle="modal" data-target="#coopDetails" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" data-bags="'.$bags->total_value.'" data-acn="'.$row->accreditation_no.'" data-moa="'.$row->current_moa.'" class="btn btn-warning btn-xs btn-block"><i class="fa fa-eye"></i> View Commitment</a> ';
            }else{
                return '<a href="" data-toggle="modal" data-target="#commitmentModal" data-id="'.$row->coopId.'" data-name="'.$row->coopName.'" class="btn btn-success btn-xs btn-block"><i class="fa fa-plus-circle"></i> Add Commitment</a> ';
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
            return '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-danger btn-sm" onclick="removeRecord(this);" >Delete Record</a>';
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
        DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insert([
            'coopID' => $request->coopDetailsID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->seed_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s")
        ]);

        //add existing to total
        $coop_commitment = DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $request->coopDetailsID)
            ->first();

        if($request->total_commit != ""){
            DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_commitment->id)
                ->update(['total_value' => $coop_commitment->total_value + $request->total_commit]);
        }else{
            DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_commitment->id)
                ->update(['total_value' => $coop_commitment->total_value + $request->seed_value]);
        }
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
        
        //delete data
        DB::connection('seed_coop_db')->table('tbl_commitment')->where('id', '=', $request->commitmentID)->delete();

        //substract commitment value to existing delivery commitment
        $coop_details = DB::connection('seed_coop_db')->table('tbl_total_commitment')
            ->where('coopID', '=', $coopID)
            ->first();
        $coop_remaining_commit = $coop_details->total_value - $commitment_data->commitment_value;

        //update total commitment if value > 0 else delete record
        if($coop_remaining_commit > 0){
            DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_details->id)
                ->update(['total_value' => $coop_remaining_commit]);
            return $coopID;
        }else{
            DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->where('id', '=', $coop_details->id)
                ->delete();
            return 0;
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
            return '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-danger btn-sm" onclick="removeRecordDetails(this);" >Delete Delivery Commitment</a>';
        })
        ->make(true);
    }

    public function saveCoopCommitment(Request $request){
        $this->validate($request, array(
            'coopID' => 'required'
        ));

        DB::connection('seed_coop_db')->table('tbl_commitment')
        ->insert([
            'coopID' => $request->coopID,
            'commitment_variety' => $request->seed_variety,
            'commitment_value' => $request->commitment_value,
            'addedBy' => Auth::user()->username,
            'date_added' => date("Y-m-d H:i:s")
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
                DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->insert([
                    'total_value' => $request->total_commitment,
                    'coopID' => $request->coopID,
                    'moa_number' => $coop_details->current_moa
                ]);
            }elseif($total_commitments == 0 AND $request->total_commitment != ''){
                //if all seed variety = 0
                DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->insert([
                    'total_value' => $request->total_commitment,
                    'coopID' => $request->coopID,
                    'moa_number' => $coop_details->current_moa
                ]);
            }else{
                //insert to total commitment table
                DB::connection('seed_coop_db')->table('tbl_total_commitment')
                ->insert([
                    'total_value' => $total_commitments,
                    'coopID' => $request->coopID,
                    'moa_number' => $coop_details->current_moa
                ]);
            }

            return route('coop.commitment');
        }else{
            return "no_commit";
        }
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
}
