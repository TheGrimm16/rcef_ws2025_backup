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


class SGController extends Controller
{
    public function display_sg_list(Request $request){
        $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->get();
        return view('sg.home')
            ->with('cooperatives', $cooperatives);
    }

    public function display_sg_table(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_seed_grower')
            ->select('sg_id', 'full_name', 'coop_accred', 'is_block', 'is_active')
            ->where('full_name', '!=', '')
            ->where('coop_accred', $request->coop)
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
                    '.$is_block_btn.'
                    <a href="#" data-toggle="modal" data-target="#sg_tags_modal" data-id="'.$row->sg_id.'" data-coop="'.$row->coop_accred.'" class="btn btn-warning btn-xs"><i class="fa fa-eye"></i> </a>
                </center>';
        })
        ->make(true);
    }
}
