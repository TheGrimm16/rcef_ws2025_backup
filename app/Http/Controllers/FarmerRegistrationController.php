<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Farmer;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use Response;
use Auth;
use DB;

class FarmerRegistrationController extends Controller
{
    public function index()
    {
        // $regions = new Regions();
        $provinces = new Provinces();

        $my_province = Auth::user()->province;

        // Get assigned region
        $my_region = $provinces->assigned_region($my_province);

        // Get provinces assigned
        $provinces_list = $provinces->provinces_assigned($my_region->regCode);

        return view('farmer_registration.index')
        ->with(compact('provinces_list'));
    }

    public function search_farmer()
    {
        $term = $_GET['term'];

        $results = array();

        $farmers = new Farmer();
        $names = $farmers->rsbsa_codes($term);
        
        if (!empty($names)) {
            foreach ($names as $item) {
                $area = $farmers->farmer_area($item->farmerID);

                $results[] = [
                    'id' => $item->farmerID,
                    'value' => $item->rsbsa_control_no,
                    'area' => $area->area
                ];
            }

            return Response::json($results);
        }
    }

    public function search_municipalities($province)
    {
        // Get municipalities
        $municipalities = new Municipalities();
        $municipalities_list = $municipalities->search_municipalities($province);

        // Get region
        $region = DB::table('lib_regions as region')
        ->leftJoin('lib_provinces as province', 'province.regCode', '=', 'region.regCode')
        ->select('region.regDesc')
        ->where('province.provDesc', $province)
        ->first();

        $data = array(
            'municipalities' => $municipalities_list,
            'region' => $region->regDesc
        );

        echo json_encode($data);
    }

    public function update_farmer(Request $request)
    {
        $input = $request->all();

        $profile = array(
            'distributionID' => $input['distribution_id'],
            'lastName' => $input['last_name'],
            'firstName' => $input['first_name'],
            'midName' => $input['middle_name'],
            'extName' => $input['suffix_name'],
            'sex' => $input['farmer_gender'],
            'birthdate' => date('Y-m-d', strtotime($input['birth_date'])),
            'region' => $input['region'],
            'province' => $input['province'],
            'municipality' => $input['municipality'],
            'barangay' => $input['barangay'],
            'affiliationType' => $input['affiliation_type'],
            'affiliationName' => $input['affiliation_name'],
            'affiliationAccreditation' => $input['affiliation_accreditation'],
            'update' => 1
        );

        $area = array(
            'region' => $input['region'],
            'province' => $input['province'],
            'municipality' => $input['municipality'],
            'barangay' => $input['barangay'],
        );

        $performance = array(
            'farmerID' => $input['farmer_id'],
            'area_planted' => $input['area_planted'],
            'variety_used' => $input['variety_used'],
            'seed_usage' => $input['seed_usage'],
            'yield' => $input['yield']
        );

        $farmers = new Farmer();
        $result = $farmers->update_farmer($input['farmer_id'], $profile, $area, $performance);
        echo json_encode($result);
    }
}
