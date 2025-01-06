<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Datatables;
use Auth;
use Carbon\Carbon;
class farmerVerificationController extends Controller
{
    public function index(){
        $user = Auth::user()->username;
        $dateNow = Carbon::now();
        $formattedDate = $dateNow->format('Y-m-d H:i:s');

        $getPrvs = DB::table('information_schema.TABLES')
        ->select('TABLE_NAME')
        ->where('TABLE_SCHEMA','LIKE','mongodb_data%')
        ->where('TABLE_NAME','LIKE','prv_%')
        ->where('TABLE_NAME','NOT LIKE','%ai')
        ->where('TABLE_NAME','NOT LIKE','%merge')
        ->where('TABLE_NAME','NOT LIKE','%backup')
        ->where('TABLE_ROWS','>',0)
        ->get();

        $prvCodes = [];
        $prvs = [];
        foreach($getPrvs as $prv)
        {
            $code = str_replace('prv_','',$prv->TABLE_NAME);
            
            $checkTbl = DB::table('mongodb_data.prv_'.$code.'_ai')
            ->where('profile_status','FOR VERIFICATION')
            ->first();
            if($checkTbl)
            {
                array_push($prvCodes,$code);
            }
            else
            {
                continue;
            }
        }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('regionName','province')
                ->whereIn('prv_code',$prvCodes)
                ->groupBy('province')
                ->orderBy('region_sort')
                ->get();

        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{
                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();


        
                foreach($user_provinces as $provinces){
                    array_push($prvs,$provinces->province);
                }

                $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('regionName','province')
                ->whereIn('prv_code',$prvCodes)
                ->whereIn('province',$prvs)
                ->groupBy('province')
                ->orderBy('region_sort')
                ->get();
           
            }
        }


        return view("farmerVerification.index",compact('provinces'));
    }

    public function getMuni(Request $request)
    {
        $getPrvCode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province',$request->prov)
        ->first();
        $prv = $getPrvCode->prv_code;
        
        $getMuniAi = DB::table('mongodb_data.prv_'.$prv.'_ai')
        ->select(DB::raw('SUBSTRING(rsbsa_control_no, 1,8) as muni_code'))
        ->where('profile_status','FOR VERIFICATION')
        ->groupBy('muni_code')
        ->get();

        $muniCodes = [];

        foreach($getMuniAi as $muniAi)
        {
            $muni = str_replace('-','',$muniAi->muni_code);
            array_push($muniCodes,$muni);
        }

        
        $getMuni = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('municipality','prv',DB::raw("CONCAT(regCode,'-',provCode,'-',munCode) as geocode"))
        ->where('province',$request->prov)
        ->whereIn('prv',$muniCodes)
        ->groupBy('municipality')
        ->get();

        // dd($getMuni);

        return json_encode($getMuni);
    }

    public function getProfiles(Request $request)
    {
        $returnArray = [];
        $totalForValidation = 0;
        $totalValidated = 0;
        $totalPending = 0;

        $code = substr(str_replace('-','',$request->mun),0,4);
        // dd($code);

        $countMerged = count(DB::table('mongodb_data.prv_'.$code.'_merge')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalValidated = $totalValidated + $countMerged;

        $countForVerify = count(DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalForValidation = $totalForValidation + $countForVerify;
        
        $countPending = count(DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','NOT LIKE','FOR VERIFICATION')
        ->where('profile_status','NOT LIKE','MERGED')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalPending = $totalPending + $countPending;

        $getCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->orderBy('cluster_id','ASC')
        ->orderBy('id','ASC')
        // ->where('cluster_id',1834) //for testing purposes -  Pangasinan - Mangatarem
        ->first();
        // dd($getCluster,$request->all());
        

        if(!$getCluster)
        {
            return ('No data.');
        }

        $clusters = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->select('cluster_id')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->groupBy('cluster_id')
        ->get();

        $clusterIndex = 1;
        foreach($clusters as $cluster)
        {
            $cluster->index = $clusterIndex;
            $clusterIndex++;
        }

        $noOfClusters = count($clusters);

        $getClusterProfile = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('cluster_id',$getCluster->cluster_id)
        ->orderBy('id','ASC')
        ->get();

        $totalForValidation = number_format($totalForValidation);
        $totalValidated = number_format($totalValidated);
        $totalPending = number_format($totalPending);
        $noOfClusters = number_format($noOfClusters);
        

        array_push($returnArray,$getClusterProfile);
        array_push($returnArray,$totalForValidation);
        array_push($returnArray,$totalValidated);
        array_push($returnArray,$totalPending);
        array_push($returnArray,$noOfClusters);
        array_push($returnArray,$clusters);
        return $returnArray;
    }

    public function getProfiles2(Request $request)
    {
        $returnArray = [];
        $totalForValidation = 0;
        $totalValidated = 0;
        $totalPending = 0;

        $code = substr(str_replace('-','',$request->mun),0,4);
        // dd($code);

        $countMerged = count(DB::table('mongodb_data.prv_'.$code.'_merge')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalValidated = $totalValidated + $countMerged;

        $countForVerify = count(DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalForValidation = $totalForValidation + $countForVerify;
        
        $countPending = count(DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','NOT LIKE','FOR VERIFICATION')
        ->where('profile_status','NOT LIKE','MERGED')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->get());
        $totalPending = $totalPending + $countPending;

        $getCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->where('cluster_id',$request->findCluster)
        ->first();
        // dd($getCluster,$request->all());
        

        if(!$getCluster)
        {
            return ('No data.');
        }

        $clusters = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->select('cluster_id')
        ->where('profile_status','FOR VERIFICATION')
        ->where('home_geocode','LIKE',$request->mun.'%')
        ->groupBy('cluster_id')
        ->get();

        $clusterIndex = 1;
        foreach($clusters as $cluster)
        {
            $cluster->index = $clusterIndex;
            $clusterIndex++;
        }

        $noOfClusters = count($clusters);

        $getClusterProfile = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','FOR VERIFICATION')
        ->where('cluster_id',$getCluster->cluster_id)
        ->orderBy('cluster_id','ASC')
        ->orderBy('id','ASC')
        ->get();

        $totalForValidation = number_format($totalForValidation);
        $totalValidated = number_format($totalValidated);
        $totalPending = number_format($totalPending);
        $noOfClusters = number_format($noOfClusters);
        

        array_push($returnArray,$getClusterProfile);
        array_push($returnArray,$totalForValidation);
        array_push($returnArray,$totalValidated);
        array_push($returnArray,$totalPending);
        array_push($returnArray,$noOfClusters);
        array_push($returnArray,$clusters);
        return $returnArray;
    }

    public function getSuggestions(Request $request)
    {
        $userName = Auth::user()->username;
        $dateNow = Carbon::now();
        $formattedDate = $dateNow->format('Y-m-d H:i:s');
        // dd($request->all());
        $code = substr(str_replace('-','',$request->mun),0,4);
        $getSuggestedProfile = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->where('profile_status','MERGED')
        ->where('cluster_id',$request->cluster)
        ->get();

        if(!$getSuggestedProfile)
        {
            $updateNoSuggested = DB::table('mongodb_data.prv_'.$code.'_ai')
            ->where('profile_status','FOR VERIFICATION')
            ->where('cluster_id',$request->cluster)
            ->update([
                "profile_status" => "FOR APPROVAL",
                "updatedBy" => $userName,
                "dateUpdated" => $formattedDate
            ]);
            return "No suggested data.";
        }
        else
        {
            return $getSuggestedProfile;
        }
        
    }

    public function skipProfile(Request $request)
    {
        // dd($request->all_profiles, $request->all());
        $userName = Auth::user()->username;
        $dateNow = Carbon::now();
        $formattedDate = $dateNow->format('Y-m-d H:i:s');
        $profiles = [];
        array_push($profiles,$request->tempProfile);
        foreach($request->all_profiles as $profile)
        {
            array_push($profiles,$profile);
        }
        $code = substr(str_replace('-','',$request->mun),0,4);
        $skipReason = $request->skipReason;
        $skipProfiles = DB::table('mongodb_data.prv_'.$code.'_ai')
        ->whereIn('id',$profiles)
        ->where('profile_status','FOR VERIFICATION')
        ->update([
            "profile_status" => "FOR RCEF CHECKING",
            "skipReason" => $skipReason,
            "updatedBy" => $userName,
            "dateUpdated" => $formattedDate
        ]);

        return 1;

    }

    public function updateProfiles(Request $request)
    {
        $userName = Auth::user()->username;
        $dateNow = Carbon::now();
        $formattedDate = $dateNow->format('Y-m-d H:i:s');
        // dd($request->all());
        $code = substr(str_replace('-','',$request->mun),0,4);
        $getSub = [];
        $getNew = [];

        if($request->profileCount == 1)
        {
            $profileId = $request->tempProfile;
            $selectedId = $request->main_profile;

            if($profileId == $selectedId)
            {
                $getMaxNewCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->max('new_cluster_id');

                if(!$getMaxNewCluster)
                {
                    $getMaxCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                    ->max('cluster_id');
                    $newCluster = $getMaxCluster + 1;
                }
                else
                {
                    $newCluster = $getMaxNewCluster + 1;
                }
                $updateProfile = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->where('id',$profileId)
                ->update([
                    "new_cluster_id" => $newCluster,
                    "profile_status" => "FOR APPROVAL",
                    "updatedBy" => $userName,
                    "dateUpdated" => $formattedDate
                ]);
            }
            else
            {
                $getMaxNewCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->max('new_cluster_id');

                if(!$getMaxNewCluster)
                {
                    $getMaxCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                    ->max('cluster_id');
                    $newCluster = $getMaxCluster + 1;
                }
                else
                {
                    $newCluster = $getMaxNewCluster + 1;
                }
                $updateProfile = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->where('id',$profileId)
                ->update([
                    "profile_link_id" => $selectedId,
                    "profile_status" => "FOR APPROVAL",
                    "updatedBy" => $userName,
                    "dateUpdated" => $formattedDate
                ]);
            }
        }
        else
        {
            $getMain = DB::table('mongodb_data.prv_'.$code.'_ai')
            ->where('id',$request->main_profile)
            ->update([
                "main_profile" => 1,
                "profile_status" => "FOR APPROVAL",
                "updatedBy" => $userName,
                "dateUpdated" => $formattedDate
            ]);

            if($request->sub_profiles)
            {
                $getSub = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->whereIn('id',$request->sub_profiles)
                ->update([
                    "profile_link_id" => $request->main_profile,
                    "profile_status" => "FOR APPROVAL",
                    "updatedBy" => $userName,
                    "dateUpdated" => $formattedDate
                ]);
            }
            if($request->new_profiles)
            {
                
                $getMaxNewCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->max('new_cluster_id');

                if(!$getMaxNewCluster)
                {
                    $getMaxCluster = DB::table('mongodb_data.prv_'.$code.'_ai')
                    ->max('cluster_id');
                    $newCluster = $getMaxCluster + 1;
                }
                else
                {
                    $newCluster = $getMaxNewCluster + 1;
                }

                $newProfiles = $request->new_profiles;
                $getNew = DB::table('mongodb_data.prv_'.$code.'_ai')
                ->whereIn('id',$request->new_profiles)
                ->update([
                    "new_cluster_id" => $newCluster,
                    "updatedBy" => $userName,
                    "dateUpdated" => $formattedDate
                ]);

            }
        }
        return 1;
    }

}
