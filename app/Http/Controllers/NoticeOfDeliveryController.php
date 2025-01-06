<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;

use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\RegistryFarmerRole;

class NoticeOfDeliveryController extends Controller
{
    public function seed_cooperatives_for_notice()
    {
        $cooperatives = new SeedCooperatives();
        $cooperatives_planted = $cooperatives->seed_cooperatives_planted();

        $transplanting = new Transplant();
        $seeds = new Seeds();

        $table_data = array();

        foreach ($cooperatives_planted as $item) {
            // Get seed coop
            $cooperative = $cooperatives->seed_cooperative($item->coopId);

            // Get minimum and maximum transplating date
            $min_transplanting_date = $transplanting->transplanting_date($item->coopId, "ASC");
            $max_transplanting_date = $transplanting->transplanting_date($item->coopId, "DESC");

            // Get planted seeds
            $planted_seeds = $seeds->planted_seeds($item->coopId);
            $harvest_dates = array();

            foreach ($planted_seeds as $item2) {
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $item2->maturity . ' days'));
                array_push($harvest_dates, $harvest_date);
            }

            $min_harvest_date = min($harvest_dates);
            $max_harvest_date = max($harvest_dates);
            $min_availability_date = date('Y-m-d', strtotime($min_harvest_date . ' + 1 month'));
            $max_availability_date = date('Y-m-d', strtotime($max_harvest_date . ' + 1 month'));

            // Get total area planted
            $total_area_planted = $cooperatives->seed_cooperatives_area_planted($item->coopId);

            if ($min_transplanting_date == $max_transplanting_date) {
                $transplanting_date_range = $min_transplanting_date;
            } else {
                $transplanting_date_range = '<center>'.$min_transplanting_date->Date_planted . '<br> to <br>' . $max_transplanting_date->Date_planted.'</center>';
            }

            if ($min_harvest_date == $max_harvest_date) {
                $harvesting_date_range = $min_harvest_date;
                $availability_date_range = $min_availability_date;
            } else {
                $harvesting_date_range = '<center>'.$min_harvest_date . '<br> to <br>' . $max_harvest_date.'</center>';
                $availability_date_range = '<center>'.$min_availability_date . '<br> to <br>' . $max_availability_date.'</center>';
            }

            $data = array(
                'coopId' => $item->coopId,
                'name' => $cooperative->coopName,
                'province' => $cooperative->provDesc,
                'area_planted' => $total_area_planted,
                'transplanting_date' => $transplanting_date_range,
                'harvesting_date' => $harvesting_date_range,
                'availability_date' => $availability_date_range
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return DataTables::of($table_data)
        ->addColumn('actions', function($table_data) {
            return "<button class='btn btn-success view_municipalities' id='send_btn' data-id='".$table_data['coopId']."' title='View'><i class='fa fa-send'></i> Send Alert</button>";
        })
        ->make(true);
    }

    public function index(){
        return view('notice.index');
    }
    
}
