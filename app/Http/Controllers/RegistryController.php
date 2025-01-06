<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use DB;
use Schema;
use Yajra\Datatables\Datatables;

use App\RegistryFarmerProfile;
use App\RegistryFarmDetails;
use App\RegistryFarmPerformance;
use App\RegistryFarmerAffiliation;
use App\RegistryFarmerRole;

class RegistryController extends Controller
{
    public function index(){
        $registry_farmer_roles = RegistryFarmerRole::all();
        $geo_provinces = DB::connection('mysql')->table('lib_provinces')->select('provDesc', 'provCode')->orderBy('provDesc', 'asc')->get();
        return view('registry.form')
            ->with('registry_farmer_roles', $registry_farmer_roles)
            ->with('geo_provinces', $geo_provinces);
    }

    public function SaveRegistry(Request $request){
        $this->validate($request, array(
            //farmer profile
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required', //format to date
            'farmer_gender' => 'required',

            //farm details
            'farm_area' => 'required',
            'rice_area' => 'required',
            'tenurial_status' => 'required',
            'tenurial_type' => 'required',
            'province' => 'required',
            'municipality' => 'required',

            //farm performance
            'variety_used' => 'required',
            'seed_usage' => 'required',
            'yield' => 'required',

            //affiliation
            'affiliation_type' => 'required',
            'affiliation_name' => 'required',
            'farm_accreditation' => 'required'
        ));
        
        $profile = DB::connection('registry_db')->table('farmer_profiles')->select('*','farmer_profiles.id as farmerProfileID')
                        ->join('farm_details', 'farmer_profiles.farmer_details_id', '=', 'farm_details.id')
                        ->join('farm_performances', 'farmer_profiles.farm_performance_id', '=', 'farm_performances.id')
                        ->join('farmer_affiliations', 'farmer_profiles.farmer_affiliation_id', '=', 'farmer_affiliations.id')
                    ->where('farmer_profiles.farmer_first_name', '=', $request->first_name)
                    ->where('farmer_profiles.farmer_middle_name', '=', $request->middle_name)
                    ->where('farmer_profiles.farmer_last_name', '=', $request->last_name)
                    ->where('farmer_profiles.farmer_birth_date', '=', date("Y-m-d", strtotime($request->birth_date)))
                    ->first();

        if(count($profile) > 0){
            $farmer_details = RegistryFarmDetails::find($profile->farmer_details_id);
            $farmer_details->farm_area = $request->farm_area;
            $farmer_details->rice_area = $request->rice_area;
            $farmer_details->tenurial_status = $request->tenurial_status;
            $farmer_details->tenurial_type = $request->tenurial_type;
            $farmer_details->farm_province = $request->province;
            $farmer_details->farm_municipality = $request->municipality;
            $farmer_details->farm_brgy = $request->brgy;
            $farmer_details->save();

            $farm_performance = RegistryFarmPerformance::find($profile->farm_performance_id);
            $farm_performance->variety_used = $request->variety_used_prefix.' '.$request->variety_used;
            $farm_performance->seed_usage = $request->seed_usage;
            $farm_performance->yield = $request->yield;
            $farm_performance->save();

            $farmer_affiliation = RegistryFarmerAffiliation::find($profile->farmer_affiliation_id);
            $farmer_affiliation->affiliation_type = $request->affiliation_type;
            $farmer_affiliation->affiliation_type_others = $request->affiliation_type == 4 ? $request->affiliation_type_others : 'N/A';
            $farmer_affiliation->affiliation_name = $request->affiliation_name;
            $farmer_affiliation->farm_accreditation = $request->farm_accreditation;
            $farmer_affiliation->farm_accreditation_others = $request->farm_accreditation == 5 ? $request->farm_accreditation_others : 'N/A';
            $farmer_affiliation->save();

            $farmer_profile = RegistryFarmerProfile::find($profile->farmerProfileID);
            $farmer_profile->rsbsa_stub_control = $request->rsbsa_stub_control;
            $farmer_profile->farmer_first_name = strtoupper($request->first_name);
            $farmer_profile->farmer_middle_name = strtoupper($request->middle_name);
            $farmer_profile->farmer_last_name = strtoupper($request->last_name);
            $farmer_profile->farmer_suffix_name = strtoupper($request->suffix_name);
            $farmer_profile->farmer_birth_date = date("Y-m-d", strtotime($request->birth_date));
            $farmer_profile->farmer_gender = $request->farmer_gender;
            $farmer_profile->farmer_contact_number = $request->contact_number;
            $farmer_profile->farmer_details_id = $farmer_details->id;
            $farmer_profile->farm_performance_id = $farm_performance->id;
            $farmer_profile->farmer_affiliation_id = $farmer_affiliation->id;
            $farmer_profile->save();

            Session::flash("success", "you have successfully `updated` the record of ($farmer_profile->farmer_first_name $farmer_profile->farmer_last_name) to the Registry!");
            return redirect()->route('rcef.registry');

        }else{
            $farmer_details = new RegistryFarmDetails;
            $farmer_details->farm_area = $request->farm_area;
            $farmer_details->rice_area = $request->rice_area;
            $farmer_details->tenurial_status = $request->tenurial_status;
            $farmer_details->tenurial_type = $request->tenurial_type;
            $farmer_details->farm_province = $request->province;
            $farmer_details->farm_municipality = $request->municipality;
            $farmer_details->farm_brgy = $request->brgy;
            $farmer_details->save();
    
            $farm_performance = new RegistryFarmPerformance;
            $farm_performance->variety_used = $request->variety_used_prefix.' '.$request->variety_used;
            $farm_performance->seed_usage = $request->seed_usage;
            $farm_performance->yield = $request->yield;
            $farm_performance->save();
    
            $farmer_affiliation = new RegistryFarmerAffiliation;
            $farmer_affiliation->affiliation_type = $request->affiliation_type;
            $farmer_affiliation->affiliation_type_others = $request->affiliation_type == 4 ? $request->affiliation_type_others : 'N/A';
            $farmer_affiliation->affiliation_name = $request->affiliation_name;
            $farmer_affiliation->farm_accreditation = $request->farm_accreditation;
            $farmer_affiliation->farm_accreditation_others = $request->farm_accreditation == 5 ? $request->farm_accreditation_others : 'N/A';
            $farmer_affiliation->save();
    
            $farmer_profile = new RegistryFarmerProfile;
            $farmer_profile->rsbsa_stub_control = $request->rsbsa_stub_control;
            $farmer_profile->farmer_first_name = strtoupper($request->first_name);
            $farmer_profile->farmer_middle_name = strtoupper($request->middle_name);
            $farmer_profile->farmer_last_name = strtoupper($request->last_name);
            $farmer_profile->farmer_suffix_name = strtoupper($request->suffix_name);
            $farmer_profile->farmer_birth_date = date("Y-m-d", strtotime($request->birth_date));
            $farmer_profile->farmer_gender = $request->farmer_gender;
            $farmer_profile->farmer_contact_number = $request->contact_number;
            $farmer_profile->farmer_details_id = $farmer_details->id;
            $farmer_profile->farm_performance_id = $farm_performance->id;
            $farmer_profile->farmer_affiliation_id = $farmer_affiliation->id;
            $farmer_profile->save();
    
            Session::flash("success", "you have successfully saved the record of ($farmer_profile->farmer_first_name $farmer_profile->farmer_last_name) to the Registry!");
            return redirect()->route('rcef.registry');
        }
    }

    public function SaveRegistry2(Request $request){
        $this->validate($request, array(
            //farmer profile
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required', //format to date
            'farmer_gender' => 'required',

            //farm details
            'farm_area' => 'required',
            'rice_area' => 'required',
            'tenurial_status' => 'required',
            'tenurial_type' => 'required',
            'province' => 'required',
            'municipality' => 'required',

            //farm performance
            'variety_used' => 'required',
            'seed_usage' => 'required',
            'yield' => 'required',

            //affiliation
            'affiliation_type' => 'required',
            'affiliation_name' => 'required',
            'farm_accreditation' => 'required'
        ));

        //if rsbsa control is given
        if($request->rsbsa_stub_control != ""){
            //check if area has an rsbsa list
            $table_id = 'prv'.substr($request->province, -2).'_farmer_profile';

            $farmer_profile_table = 'prv'.substr($request->province, -2).'_farmer_profile';
            $farmer_area_history = 'prv'.substr($request->province, -2).'_area_history';
            $farmer_performance = 'prv'.substr($request->province, -2).'_performance';

            try{
                $table_check = DB::connection('distribution_db')->table($table_id)->limit(1)->orderBy('farmerID','desc')->first();
                $last_id = (int)substr($table_check->farmerID, -9) + 1;
                $prefix = substr($table_check->farmerID, 0, 6);
                $newFarmerID = $prefix.sprintf("%'09d", $last_id);

                $region_code = DB::connection('mysql')->table('lib_provinces')->where('provCode','=',$request->province)->first()->regCode;
                $region_name = DB::connection('mysql')->table('lib_regions')->where('regCode','=',$region_code)->first()->regDesc;                
                $province_name = DB::connection('mysql')->table('lib_provinces')->where('provCode','=',$request->province)->first()->provDesc;
                $municipality_name = DB::connection('mysql')->table('lib_municipalities')->where('citymunCode', '=', $request->municipality)->first()->citymunDesc;
                $brgy_name = $request->brgy;

                //affiliation
                if($request->affiliation_type == 1){
                    $affiliationType = 'IA';
                }elseif($request->affiliation_type == 2){
                    $affiliationType = 'COOP';
                }elseif($request->affiliation_type == 3){
                    $affiliationType = 'SWISA';
                }elseif($request->affiliation_type == 4){
                    $affiliationType = 'Others';
                }

                //accreditation
                if($request->farm_accreditation == 1){
                    $accreditation = 'NONE';
                }elseif($request->farm_accreditation == 2){
                    $accreditation = 'SEC';
                }elseif($request->farm_accreditation == 3){
                    $accreditation = 'CDA';
                }elseif($request->farm_accreditation == 4){
                    $accreditation = 'DOLE';
                }elseif($request->farm_accreditation == 5){
                    $accreditation = 'OTHERS';
                }

                $farmer_name = strtoupper($request->first_name).' '.strtoupper($request->middle_name).' '.strtoupper($request->last_name).' '.strtoupper($request->suffix_name);
                $rsbsa_area = $region_name.', '.$province_name.', '.$municipality_name;

                DB::connection('distribution_db')->table($farmer_profile_table)
                ->insert([
                    'farmerID' => $newFarmerID,
                    'firstName' => strtoupper($request->first_name),
                    'midName' => strtoupper($request->middle_name),
                    'lastName' => strtoupper($request->last_name),
                    'extName' => strtoupper($request->suffix_name),
                    'fullName' => strtoupper($request->first_name).' '.strtoupper($request->middle_name).' '.strtoupper($request->last_name).' '.strtoupper($request->suffix_name),
                    'sex' => $request->farmer_gender == 1 ? 'Male' : 'Female',
                    'birthdate' => $request->birth_date,
                    'region' => $region_name,
                    'province' => $province_name,
                    'municipality' => $municipality_name,
                    'barangay' => $brgy_name,
                    'affiliationType' => $affiliationType,
                    'affiliationName' => $request->affiliation_name,
                    'affiliationAccreditation' => $accreditation
                ]);

                DB::connection('distribution_db')->table($farmer_area_history)
                ->insert([
                    'farmerId' => $newFarmerID,
                    'region' => $region_name,
                    'province' => $province_name,
                    'municipality' => $municipality_name,
                    'barangay' => $brgy_name,
                    'area' => $request->rice_area,
                    'dateCreated' => date("Y-m-d H:i:s"),
                ]);

                DB::connection('distribution_db')->table($farmer_performance)
                ->insert([
                    'farmerID' => $newFarmerID,
                    'variety_used' => $request->variety_used_prefix.' '.$request->variety_used,
                    'seed_usage' => $request->seed_usage,
                    'yield' => $request->yield
                ]);

                Session::flash("success", "you have successfully saved the record of ($farmer_name) to the RSBSA data of the Area: ($rsbsa_area)!");
                return redirect()->route('rcef.registry');

            }catch(\Illuminate\Database\QueryException $ex){
                Session::flash("error_rsbsa", "The RSBSA List in that area does not exist!");
                return redirect()->route('rcef.registry');
            }

        }else{
            $farmer_profile = new RegistryFarmerProfile;
            $farmer_profile->rsbsa_stub_control = $request->rsbsa_stub_control;
            $farmer_profile->farmerID = '';
            $farmer_profile->farmer_first_name = strtoupper($request->first_name);
            $farmer_profile->farmer_middle_name = strtoupper($request->middle_name);
            $farmer_profile->farmer_last_name = strtoupper($request->last_name);
            $farmer_profile->farmer_suffix_name = strtoupper($request->suffix_name);
            $farmer_profile->farmer_birth_date = date("Y-m-d", strtotime($request->birth_date));
            $farmer_profile->farmer_gender = $request->farmer_gender;
            $farmer_profile->farmer_contact_number = $request->contact_number;
            $farmer_profile->save();

            $farmer_details = new RegistryFarmDetails;
            $farmer_details->farmerID = '';
            $farmer_details->farm_area = $request->farm_area;
            $farmer_details->rice_area = $request->rice_area;
            $farmer_details->tenurial_status = $request->tenurial_status;
            $farmer_details->tenurial_type = $request->tenurial_type;
            $farmer_details->farm_province = $request->province;
            $farmer_details->farm_municipality = $request->municipality;
            $farmer_details->farm_brgy = $request->brgy;
            $farmer_details->save();

            $farm_performance = new RegistryFarmPerformance;
            $farm_performance->farmerID = '';
            $farm_performance->variety_used = $request->variety_used_prefix.' '.$request->variety_used;
            $farm_performance->seed_usage = $request->seed_usage;
            $farm_performance->yield = $request->yield;
            $farm_performance->save();

            $farmer_affiliation = new RegistryFarmerAffiliation;
            $farmer_affiliation->farmerID = '';
            $farmer_affiliation->affiliation_type = $request->affiliation_type;
            $farmer_affiliation->affiliation_type_others = $request->affiliation_type == 4 ? $request->affiliation_type_others : 'N/A';
            $farmer_affiliation->affiliation_name = $request->affiliation_name;
            $farmer_affiliation->farm_accreditation = $request->farm_accreditation;
            $farmer_affiliation->farm_accreditation_others = $request->farm_accreditation == 5 ? $request->farm_accreditation_others : 'N/A';
            $farmer_affiliation->save();

            Session::flash("success", "you have successfully saved the record of ($farmer_profile->farmer_first_name $farmer_profile->farmer_last_name) to the Registry!");
            return redirect()->route('rcef.registry');
        }
        
       


        
    }

    public function RegisteredFarmers(){
        //$province = DB::connection('mysql')->table('lib_provinces')->where("provCode", "=", "0128")->value('provDesc');
        //dd($province);
        $profiles = RegistryFarmerProfile::all();
        return view('registry.index')->with('profiles', $profiles);
    }
}
