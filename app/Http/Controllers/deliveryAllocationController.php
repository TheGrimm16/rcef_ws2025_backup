<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

use App\SeedCooperatives;
use App\SeedGrowers;
use Config;
use DB;
use Excel;
use Session;
use Auth;

class deliveryAllocationController extends Controller
{

    public function loadChart(Request $request){
        $x_list = array();
        $allocation_list = array();
        $delivered_list = array();
        $total_allocation = 0;
        $total_delivered = 0;
        if($request->region =="all"){
            $region = "%";
        }else{$region = $request->region;}
        if($request->province =="all"){
            $province = "%";
        }else{$province = $request->province;}
        if($request->municipality =="all"){
            $municipality = "%";
        }else{$municipality=$request->municipality;}


        if($request->level == "municipal"){
                if($province == "0"){
                      $data = $this->regionalData("%");
                        foreach($data as $datainfo){
                            $total_allocation += intval($datainfo['allocated_bags']);
                            $total_delivered += intval($datainfo['total_delivered']);
                            array_push($x_list, $datainfo['region']);
                            array_push($allocation_list, $datainfo['allocated_bags']);
                            array_push($delivered_list, $datainfo['total_delivered']);                      
                        }
                        $ret =  array(
                            'x_list' => $x_list,
                            'allocation_list' => $allocation_list,
                            'delivered_list' => $delivered_list,
                            'total_allocation' => number_format($total_allocation),
                            'total_delivered' => number_format($total_delivered)
                        );
                }else{

                     $data = $this->municipalData($province, $municipality);
                        foreach($data as $datainfo){
                            $total_allocation += intval($datainfo['allocated_bags']);
                            $total_delivered += intval($datainfo['total_delivered']);
                            array_push($x_list, $datainfo['municipality']);
                            array_push($allocation_list, $datainfo['allocated_bags']);
                            array_push($delivered_list, $datainfo['total_delivered']);                      
                        }
                        $ret =  array(
                            'x_list' => $x_list,
                            'allocation_list' => $allocation_list,
                            'delivered_list' => $delivered_list,
                            'total_allocation' => number_format($total_allocation),
                            'total_delivered' => number_format($total_delivered)
                        );
                }
            return $ret;
        }elseif($request->level == "provincial"){
                if($region == "0"){
                      $data = $this->regionalData("%");
                        foreach($data as $datainfo){
                            $total_allocation += intval($datainfo['allocated_bags']);
                            $total_delivered += intval($datainfo['total_delivered']);
                            array_push($x_list, $datainfo['region']);
                            array_push($allocation_list, $datainfo['allocated_bags']);
                            array_push($delivered_list, $datainfo['total_delivered']);                      
                        }
                        $ret =  array(
                            'x_list' => $x_list,
                            'allocation_list' => $allocation_list,
                            'delivered_list' => $delivered_list,
                            'total_allocation' => number_format($total_allocation),
                            'total_delivered' => number_format($total_delivered)
                        );
                }else{

                     $data = $this->provincialData($region, $province);
                        foreach($data as $datainfo){
                            $total_allocation += intval($datainfo['allocated_bags']);
                            $total_delivered += intval($datainfo['total_delivered']);
                            array_push($x_list, $datainfo['province']);
                            array_push($allocation_list, $datainfo['allocated_bags']);
                            array_push($delivered_list, $datainfo['total_delivered']);                      
                        }
                        $ret =  array(
                            'x_list' => $x_list,
                            'allocation_list' => $allocation_list,
                            'delivered_list' => $delivered_list,
                            'total_allocation' => number_format($total_allocation),
                            'total_delivered' => number_format($total_delivered)
                        );
                }
            return $ret;
        }elseif($request->level == "regional"){
                      $data = $this->regionalData($region);
                        foreach($data as $datainfo){
                            $total_allocation += intval($datainfo['allocated_bags']);
                            $total_delivered += intval($datainfo['total_delivered']);
                            array_push($x_list, $datainfo['region']);
                            array_push($allocation_list, $datainfo['allocated_bags']);
                            array_push($delivered_list, $datainfo['total_delivered']);                      
                        }
                        $ret =  array(
                            'x_list' => $x_list,
                            'allocation_list' => $allocation_list,
                            'delivered_list' => $delivered_list,
                            'total_allocation' => number_format($total_allocation),
                            'total_delivered' => number_format($total_delivered)
                        );
                
            return $ret;
        }


    }










    public function viewAllocation($level){
         if($level =="municipal"){
                $provinceList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                ->select("lib_target_datasets.*")
                 ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                        $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_target_datasets.region');
                    })
                    ->groupBy("lib_target_datasets.province")
                    ->orderBy('lib_prv.region_sort', 'ASC')
                    ->get();
                    return view("deliveryallocation.municipality")
                        ->with("province", $provinceList);
         }elseif($level =="provincial"){
                $regionList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                ->select("lib_target_datasets.*")
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                        $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_target_datasets.region');
                    })
                    ->groupBy("lib_target_datasets.region")
                    ->orderBy('lib_prv.region_sort', 'ASC')
                    ->get();
                    return view("deliveryallocation.province")
                        ->with("region", $regionList);
         }elseif($level =="regional"){
                $regionList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                ->select("lib_target_datasets.*")
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                        $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_target_datasets.region');
                    })
                    ->groupBy("lib_target_datasets.region")
                    ->orderBy('lib_prv.region_sort', 'ASC')
                    ->get();
                    return view("deliveryallocation.regional")
                        ->with("region", $regionList);
         }
    }

    public function load_table(Request $request){
        if($request->region =="all"){
            $region = "%";
        }else{$region = $request->region;}
        if($request->province =="all"){
            $province = "%";
        }else{$province = $request->province;}
        if($request->municipality =="all"){
            $municipality = "%";
        }else{$municipality=$request->municipality;}

        if($request->level == "municipal"){
            $data = $this->municipalData($province, $municipality);
            $data = collect($data);
            return Datatables::of($data)
            ->addColumn('allocated_bags', function($row){
                return number_format($row["allocated_bags"]);
            })
            ->addColumn('total_delivered', function($row){
                return number_format($row["total_delivered"]);
            })
            ->make(true);
        }elseif($request->level == "provincial"){
            $data = $this->provincialData($region, $province);
            $data = collect($data);
            return Datatables::of($data)
            ->addColumn('allocated_bags', function($row){
                return number_format($row["allocated_bags"]);
            })
            ->addColumn('total_delivered', function($row){
                return number_format($row["total_delivered"]);
            })
            ->make(true);
        }elseif($request->level == "regional"){
            $data = $this->regionalData($region);
            $data = collect($data);
            return Datatables::of($data)
            ->addColumn('allocated_bags', function($row){
                return number_format($row["allocated_bags"]);
            })
            ->addColumn('total_delivered', function($row){
                return number_format($row["total_delivered"]);
            })
            ->make(true);
        }



    }


    public function municipalityList(Request $request){
        $municipalityList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
            ->where("province", $request->province)
            ->groupBy("municipality")
            ->get();
        return json_encode($municipalityList);
    }

    public function provinceList(Request $request){
        $provinceList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
            ->where("region", $request->region)
            ->groupBy("province")
            ->get();
        return json_encode($provinceList);
    }



    public function municipalData($province, $municipality){
        $Allocation = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
            ->where("totalBagCount", ">", 0)
            ->where("province", "like",$province)
            ->where("municipality", "like", $municipality)
            ->groupBy("province")
            ->groupBy("municipality")
            ->orderBy("municipality", "ASC")
            ->get();
        $data = array();
            foreach ($Allocation as $target) {
                $totalAcceptedDelivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->where("province", $target->province)
                    ->where("municipality", "LIKE", "%".$target->municipality."%")
                    ->sum("totalBagCount");
                    if($totalAcceptedDelivery>0){
                        $totalAcceptedDelivery = intval($totalAcceptedDelivery);
                    }else{
                        $totalAcceptedDelivery = 0;
                    }

                    $diff = intval($target->totalBagCount) - intval($totalAcceptedDelivery);
                    $percentage = (intval($totalAcceptedDelivery) /  intval($target->totalBagCount)) * 100;

                     if($percentage > 100){
                        $color = "color:red;";
                    }else{
                        $color="";
                    }

                    $percentage = round($percentage,2);

                    $percentage = "<font style='".$color."'>".$percentage."%"."</font>";



                    array_push($data, array(
                        "region" => $target->region,
                        "province" => $target->province,
                        "municipality" => $target->municipality,
                        "allocated_bags" => $target->totalBagCount,
                        "total_delivered" => $totalAcceptedDelivery,
                        "difference" => number_format($diff),
                        "percentage" => $percentage
                    ));
            }

           

        return $data;
    }



    public function provincialData($region, $province){
        $Allocation = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
            ->where("totalBagCount", ">", 0)
            ->where("region", "like",$region)
            ->where("province", "like", $province)
            ->groupBy("province")
            ->orderBy("province", "ASC")
            ->get();

        $data = array();

            foreach ($Allocation as $target) {
                $totalAcceptedDelivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->where("region", $target->region)
                    ->where("province", $target->province)
                    ->sum("totalBagCount");

                    if($totalAcceptedDelivery>0){
                        $totalAcceptedDelivery = intval($totalAcceptedDelivery);
                    }else{
                        $totalAcceptedDelivery = 0;
                    }

                    $area_coverage = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("province", $target->province)
                            ->where("region", $target->region)
                            ->sum("area_coverage");

                    $totalBagCount = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("province", $target->province)
                            ->where("region", $target->region)
                            ->sum("totalBagCount");

                    $no_of_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("province", $target->province)
                            ->where("region", $target->region)
                            ->sum("no_of_beneficiary");


                    $diff = intval($totalBagCount) - intval($totalAcceptedDelivery);
                    $percentage = (intval($totalAcceptedDelivery) /  intval($totalBagCount)) * 100;

                    if($percentage > 100){
                        $color = "color:red;";
                    }else{
                        $color="";
                    }

                    $percentage = round($percentage,2);

                    $percentage = "<font style='".$color."'>".$percentage."%"."</font>";





                    array_push($data, array(
                        "region" => $target->region,
                        "province" => $target->province,
                        "allocated_bags" =>  intval($totalBagCount),
                        "total_delivered" => $totalAcceptedDelivery,
                        "difference" => number_format($diff),
                        "percentage" => $percentage
                    ));



            }

        return $data;
    }



     public function regionalData($region){
        $Allocation = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
            ->select("lib_target_datasets.*")
             ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_target_datasets.region');
            })
            ->where("lib_target_datasets.totalBagCount", ">", 0)
            ->where("lib_target_datasets.region", "like",$region)
            ->groupBy("lib_target_datasets.region")
            ->orderBy('lib_prv.region_sort', 'ASC')
            ->get();

        $data = array();

            foreach ($Allocation as $target) {
                $totalAcceptedDelivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->where("region", $target->region)
                    ->sum("totalBagCount");

                    if($totalAcceptedDelivery>0){
                        $totalAcceptedDelivery = intval($totalAcceptedDelivery);
                    }else{
                        $totalAcceptedDelivery = 0;
                    }

                    $area_coverage = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("region", $target->region)
                            ->sum("area_coverage");

                    $totalBagCount = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("region", $target->region)
                            ->sum("totalBagCount");

                    $no_of_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                            ->where("region", $target->region)
                            ->sum("no_of_beneficiary");

                    $diff = intval($totalBagCount) - intval($totalAcceptedDelivery);
                    $percentage = (intval($totalAcceptedDelivery) /  intval($totalBagCount)) * 100;

                   
                    if($percentage > 100){
                        $color = "color:red;";
                    }else{
                        $color="";
                    }

                    $percentage = round($percentage,2);

                    $percentage = "<font style='".$color."'>".$percentage."%"."</font>";






                    array_push($data, array(
                        "region" => $target->region,
                        "allocated_bags" =>  intval($totalBagCount),
                        "total_delivered" => $totalAcceptedDelivery,
                        "difference" => number_format($diff),
                        "percentage" => $percentage
                    ));

            }

        return $data;
    }




}
