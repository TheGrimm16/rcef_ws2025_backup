<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;
use DB;
use Session;
use Auth;
use App\Farmer;
use App\InspectedSeeds;
use App\Releasing;
use App\Regions;
use App\Seeds;
use App\Provinces;
use App\Municipalities;
use App\DropoffPoints;

class ReleasingController extends Controller {

    public function index() {

        if (Session::has('dropoff_point')) {
            $distribution_province = Session::get('distribution_province');
            $distribution_municipality = Session::get('distribution_municipality');
            $dropoff_point = Session::get('dropoff_point');

            $inspected_seeds = new InspectedSeeds();
            $seeds = new Seeds();
            $available_seeds = $inspected_seeds->available_seeds($distribution_province, $distribution_municipality, $dropoff_point);

			$releasing = new Releasing();
			$check_direct_seeded = $releasing->check_direct_seeded();
            $seed_varieties = $seeds->seed_all();
            // dd($available_seeds);

            $pending = array();

            foreach ($available_seeds as $item) {
                $pending_seeds = $inspected_seeds->pending_variety($distribution_province, $distribution_municipality, $dropoff_point, $item->seedVariety);

                $data = array(
                    'variety' => $item->seedVariety,
                    'pending' => $item->totalBagCount - $pending_seeds->bags
                );
                array_push($pending, $data);
            }

            return view('releasing.index')
                            ->with(compact('available_seeds'))
                            ->with(compact('pending'))
                            ->with(compact('check_direct_seeded'))
                            ->with(compact('seed_varieties'));
        } else {
            // Get Provinces
            $provinces = new Provinces();
            $provinces_list = $provinces->delivery_provinces();

            return view('releasing.login')->with(compact('provinces_list'));
        }
    }

    public function get_municipalities($province) {
        // Get municipalities
        $municipalities = new Municipalities();
        $municipalities_list = $municipalities->delivery_municipalities($province);

        echo json_encode($municipalities_list);
    }

    public function get_dropoff_points($province, $municipality) {
        // Get dropoff points
        $dropoff_points = new DropoffPoints();
        $dropoff_points_list = $dropoff_points->delivery_dropoff_points($province, $municipality);

        echo json_encode($dropoff_points_list);
    }

    public function select_distribution_location(Request $request) {
        $input = $request->all();
        Session::set('distribution_province', $input['province']);
        Session::set('distribution_municipality', $input['municipality']);
        Session::set('dropoff_point', $input['dropoff_point']);
        Session::set('rsbsa_checking', $input['rsbsa_checking']);
        Session::set('prv', $input['prv']);
        echo json_encode("success");
    }

    public function search_rsbsa_no(Request $request) {

        $input = $request->all();
        $rsbsa_control_no = $input['rsbsa_control_no'];
        $farmer = new Farmer();
        $search_data_count = $farmer->_search_rsbsa_no($rsbsa_control_no, 1);
//        $check_pending = $farmer->get_farmer_rsbsa($rsbsa_control_no);

        $search_data = $farmer->_search_rsbsa_no($rsbsa_control_no, 2);
        if ($search_data_count == 0) {
            echo json_encode('Not found');
        } else {
            foreach ($search_data as $result):
                $get_info = $farmer->_other_info($rsbsa_control_no, $result->farmerID);
                if ($get_info != false) {
                    $mother_fname = $get_info['mother_fname'];
                    $mother_lname = $get_info['mother_lname'];
                    $mother_mname = $get_info['mother_mname'];
                    $mother_suffix = $get_info['mother_suffix'];
                    $birthdate = $get_info['birthdate'];
                    $phone = $get_info['phone'];
                } else {
                    $mother_fname = "";
                    $mother_lname = "";
                    $mother_mname = "";
                    $mother_suffix = "";
                    $birthdate = "";
                    $phone = "";
                }
                $data = array(
                    'farmerID' => $result->farmerID,
                    'firstName' => $result->firstName,
                    'lastName' => $result->lastName,
                    'midName' => $result->midName,
                    'extName' => $result->extName,
                    'sex' => $result->sex,
                    'birthdate' => $birthdate,
                    'mother_fname' => $mother_fname,
                    'mother_lname' => $mother_lname,
                    'phone' => $phone,
                    'mother_mname' => $mother_mname,
                    'mother_suffix' => $mother_suffix
                );
            endforeach;
            echo json_encode($data);
        }
    }

    public function search_farmer() {
        $term = $_GET['term'];

        $results = array();

        $farmers = new Farmer();
        $names = $farmers->names($term);

        foreach ($names as $item) {
            $area = $farmers->farmer_area($item->farmerID);

            if ($area->province != "" && $area->municipality != "" && $area->barangay != "")
                $address = $area->barangay . ', ' . $area->municipality . ', ' . $area->province;
            else
                $address = $item->barangay . ', ' . $item->municipality . ', ' . $item->province;


            if (trim($item->midName) != "" && trim($item->extName) != "")
                $results[] = [
                    'id' => $item->farmerID,
                    'value' => $item->firstName . ' ' . $item->midName . ' ' . $item->lastName . ', ' . $item->extName,
                    'birthdate' => ($item->birthdate != '') ? date('F d, Y', strtotime($item->birthdate)) : '',
                    'sex' => $item->sex,
                    'address' => $address,
                    'affiliation_name' => $item->affiliationName,
                    'affiliation_accreditation' => $item->affiliationAccreditation,
                    'area' => $area->area
                ];
            elseif (trim($item->midName) != "" && trim($item->extName) == "")
                $results[] = [
                    'id' => $item->farmerID,
                    'value' => $item->firstName . ' ' . $item->midName . ' ' . $item->lastName,
                    'birthdate' => ($item->birthdate != '') ? date('F d, Y', strtotime($item->birthdate)) : '',
                    'sex' => $item->sex,
                    'address' => $address,
                    'affiliation_name' => $item->affiliationName,
                    'affiliation_accreditation' => $item->affiliationAccreditation,
                    'area' => $area->area
                ];
            elseif (trim($item->midName) == "" && trim($item->extName) != "")
                $results[] = [
                    'id' => $item->farmerID,
                    'value' => $item->firstName . ' ' . $item->lastName . ', ' . $item->extName,
                    'birthdate' => ($item->birthdate != '') ? date('F d, Y', strtotime($item->birthdate)) : '',
                    'sex' => $item->sex,
                    'address' => $address,
                    'affiliation_name' => $item->affiliationName,
                    'affiliation_accreditation' => $item->affiliationAccreditation,
                    'area' => $area->area
                ];
            elseif (trim($item->midName) == "" && trim($item->extName) == "")
                $results[] = [
                    'id' => $item->farmerID,
                    'value' => $item->firstName . ' ' . $item->lastName,
                    'birthdate' => ($item->birthdate != '') ? date('F d, Y', strtotime($item->birthdate)) : '',
                    'sex' => $item->sex,
                    'address' => $address,
                    'affiliation_name' => $item->affiliationName,
                    'affiliation_accreditation' => $item->affiliationAccreditation,
                    'area' => $area->area
                ];
        }

        return Response::json($results);
    }

    public function formula($actual_area, $flag, $bags_distributed, $is_changed, $prv) {

        $dropoff_point = Session::get('dropoff_point');
        $farmer = new Farmer();

        $get_max_pmo = $farmer->_get_max_pmo("PMOMAX");
        $get_max_lgu = $farmer->_get_lgu_max($dropoff_point);
		
		
        $releasing = new Releasing();
        $check_direct_seeded = $releasing->check_direct_seeded();
        $province = substr(Auth::user()->province, 0,2);
		if($province == '03' and $check_direct_seeded == 0){
			$get_max_pmo = 2;
		}
		if($prv == '034904' or $prv=='034932' or $prv == '034924'){
			$get_max_pmo = 3;
		}
		
        if ($actual_area > $get_max_pmo and ( $get_max_lgu != 0 and $actual_area > $get_max_lgu) and $get_max_lgu <= $get_max_pmo) {
            $area = $get_max_lgu;
        } else if ($get_max_lgu == 0 and $actual_area > $get_max_pmo) {
            $area = $get_max_pmo;
        } else if ($get_max_lgu > $get_max_pmo and $actual_area > $get_max_pmo) {
            $area = $get_max_pmo;
        } else if ($get_max_lgu < $get_max_pmo and $get_max_lgu < $actual_area and $get_max_lgu != 0) {
            $area = $get_max_lgu;
        } else {
            $area = $actual_area;
        }
        $return_area = $area;
        $area = ($area % 2 == 0 ? $area . '.0' : $area);
        if (count(explode(".", $area)) > 1) {
            $bags_explode = explode(".", $area);
            $decimal = '.' . $bags_explode[1];
            $whole = ($bags_explode[0] != '' ? $bags_explode[0] : 0);
        } else {
            $decimal = 0;
            $whole = $area;
        }
        $whole_bags = floor($whole / .5);
        if (($decimal <= .5 and $decimal > 0)) {
            $plus = 1;
        } else if ($decimal > .5 and $decimal <= .99) {
            $plus = 2;
        } else
            $plus = 0;

        if ($check_direct_seeded > 0) {
            $times = 2;
        } else {
            $times = 1;
        }
        $bags_total = $whole_bags + $plus;
		
		if($is_changed == 1){
			$final_bag = $bags_distributed;
		}
		else{
			$final_bag = ($bags_total) * $times;
		}
		//echo $final_bag;
		
        return ($flag == 1 ? $return_area : $final_bag);
    }

    public function store(Request $request) {
        $input = $request->all();

        $transaction = 0;
        $distribution_province = Session::get('distribution_province');
        $distribution_municipality = Session::get('distribution_municipality');
        $dropoff_point = Session::get('dropoff_point');
		$prv_id = explode("-",$dropoff_point);
        // check if has previous release
        $releasing = new Releasing();
        $farmer = new Farmer();
        $previous = $releasing->released($input['farmer_id']);

        // check if variety selected is available
        $inspected_seeds = new InspectedSeeds();
        $available_variety = $inspected_seeds->variety($distribution_province, $distribution_municipality, $dropoff_point, $input['variety']);

        // Compute remaining allocation
//        if ($input['farm_area'] > 2) {
//            $remaining_area = 2 - (($previous->bags / 4) * 2);
//        } else {
//            $remaining_area = $input['farm_area'] - (($previous->bags / 4) * 2);
//        }
        //PMO 3
        // LGU 2
        //4


        $remaining = $this->formula($input['farm_area'], 2, $input['bags_distributed'], $input['is_changed'], $prv_id[0]);
//        if ($input['farm_area'] == 0 && $remaining_area == 0) // For farmers with 0 area in database
//            $remaining_bags = 1;
//        elseif ($remaining_area > 0 && $remaining_area <= .5)
//            $remaining_bags = 1;
//        elseif ($remaining_area > .5 && $remaining_area <= 1)
//            $remaining_bags = 2;
//        elseif ($remaining_area > 1 && $remaining_area <= 1.5)
//            $remaining_bags = 3;
//        elseif ($remaining_area > 1.5)
//            $remaining_bags = 4;
//        else
//            $remaining_bags = 0;
        $existing_bags = $farmer->get_farmer_rsbsa($input['rsbsa_control_no']);

        $remaining_bags = $remaining - $existing_bags;

        if ($remaining_bags == 0) {
            echo json_encode(array('status' => "limit reached"));
        } else {
            // Get total pending varieties
            $pending_variety = $inspected_seeds->pending_variety($distribution_province, $distribution_municipality, $dropoff_point, $input['variety']);
            // $pending_variety = ($pending_variety == null) ? 0 : $pending_variety->bags;

            $available_bags = $available_variety->totalBagCount - $pending_variety->bags; // compute remaining bags based on delivered minus pending release
            if ($available_bags >= $remaining_bags) {
                // add to pending release
                $data = array(
                    'farmer_id' => $input['farmer_id'],
                    'province' => $distribution_province,
                    'municipality' => $distribution_municipality,
                    'prv_dropoff_id' => $dropoff_point,
                    'seed_variety' => $input['variety'],
                    'rsbsa_control_no' => $input['rsbsa_control_no'],
                    'bags' => $remaining_bags,
                    'is_released' => 1,
                    'created_by' => Auth::user()->username,
                    'send' => 1
                );
                // var_dump($data);
                $pending = $releasing->add_pending($data);
				
				$data_released = array(
                    'farmer_id' => $input['farmer_id'],
                    'province' => $distribution_province,
                    'municipality' => $distribution_municipality,
                    'prv_dropoff_id' => $dropoff_point,
                    'seed_variety' => $input['variety'],
                    'rsbsa_control_no' => $input['rsbsa_control_no'],
                    'bags' => $remaining_bags,
                    'released_by' => Auth::user()->username,
                    'send' => 1
                );
			
                $released = $releasing->add_released($data_released);
				
				
                $transaction = 1;
                echo json_encode(array('status' => $pending, 'stock_pending_release' => $remaining_bags, 'farmer_id' => $input['farmer_id']));
            } elseif ($available_bags < $remaining_bags && $available_bags > 0) {
                $transaction = 2;
                $confirm_bags = $available_bags;
                // $batch_ticket_no = $item->batchTicketNumber;
            } elseif ($available_bags <= 0) {
                $transaction = 3;
            }
            /* foreach ($available_variety as $item) {
              // Get total pending varieties
              $pending_variety = $inspected_seeds->pending_variety($distribution_province, $distribution_municipality, $dropoff_point, $input['variety']);
              // $pending_variety = ($pending_variety == null) ? 0 : $pending_variety->bags;

              $available_bags = $item->totalBagCount - $pending_variety->bags; // compute remaining bags based on delivered minus pending release
              if ($available_bags >= $remaining_bags) {
              // add to pending release
              $data = array(
              'farmer_id' => $input['farmer_id'],
              'province' => $distribution_province,
              'municipality' => $distribution_municipality,
              'dropOffPoint' => $dropoff_point,
              'seed_variety' => $item->seedVariety,
              'bags' => $remaining_bags,
              'created_by' => Auth::user()->username,
              'send' => 1
              );
              // var_dump($data);
              $pending = $releasing->add_pending($data);
              $transaction = 1;
              echo json_encode(array('status' => $pending, 'stock_pending_release' => $remaining_bags, 'farmer_id' => $input['farmer_id']));
              break;
              } elseif ($available_bags < $remaining_bags && $available_bags > 0) {
              $transaction = 2;
              $confirm_bags = $available_bags;
              $batch_ticket_no = $item->batchTicketNumber;
              } elseif ($available_bags <= 0) {
              $transaction = 3;
              }
              } */

            // New area
            if (isset($input['new_area'])) {
                if ($input['new_area'] == 1) {
                    $farmer = new Farmer();

                    // GET ADDRESS OF Area
                    $address = $farmer->farmer_area_address($input['farmer_id']);

                    // Update new area
                    $data = array(
                        'farmerId' => $input['farmer_id'],
                        'region' => $address->region,
                        'province' => $address->province,
                        'municipality' => $address->municipality,
                        'barangay' => $address->barangay,
                        'area' => $input['farm_area']
                    );

                    $update = $farmer->update_area($data);
                }
            }

            if ($transaction == 2) {
                // available bags cannot suffice remaining area
                echo json_encode(array('status' => "cannot suffice", 'confirm_bags' => $confirm_bags, 'variety' => $input['variety']));
            } elseif ($transaction == 3) {
                echo json_encode(array('status' => "stocks depleted"));
            }
        }
    }

    public function confirm_store(Request $request) {
        $input = $request->all();

        $distribution_province = Session::get('distribution_province');
        $distribution_municipality = Session::get('distribution_municipality');
        $dropoff_point = Session::get('dropoff_point');

        // check if batch ticket no has available stocks and equal to remaining bags for pending release
        $inspected_seeds = new InspectedSeeds();
        $variety = $inspected_seeds->confirm_variety($distribution_province, $distribution_municipality, $dropoff_point, $input['variety']);

        // Get total pending varieties
        $pending_variety = $inspected_seeds->pending_variety($distribution_province, $distribution_municipality, $dropoff_point, $input['variety']);

        $available_bags = $variety->totalBagCount - $pending_variety->bags; // compute remaining bags based on delivered minus pending release

        if ($available_bags == $input['bags']) {
            // add to pending release
            $data = array(
                'farmer_id' => $input['farmer_id'],
                'province' => $distribution_province,
                'municipality' => $distribution_municipality,
                'dropOffPoint' => $dropoff_point,
                'seed_variety' => $input['variety'],
                'bags' => $input['bags'],
                'created_by' => Auth::user()->username,
                'send' => 1
            );

            $releasing = new Releasing();
            $pending = $releasing->add_pending($data);

            echo json_encode(array('status' => $pending, 'stock_pending_release' => $input['bags'], 'farmer_id' => $input['farmer_id']));
        } elseif ($available_bags < $input['bags'] && $available_bags > 0) {
            echo json_encode(array('status' => "cannot suffice", 'confirm_bags' => $confirm_bags, 'variety' => $input['variety']));
        } elseif ($available_bags <= 0) {
            echo json_encode(array('status' => "stocks depleted"));
        }
    }

    public function search_address($farmer_id) {
        // Search address
        $farmers = new Farmer();
        $area = $farmers->farmer_area($farmer_id);

        // Get provinces
        $provinces = new Provinces();
        $provinces_list = $provinces->all_provinces();

        // Get municipalities
        $municipalities = new Municipalities();
        $municipalities_list = $municipalities->search_municipalities($area->province);

        $data = array(
            'area' => $area,
            'provinces' => $provinces_list,
            'municipalities' => $municipalities_list
        );


        echo json_encode($data);
    }

    public function search_municipalities($province) {
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

    // REPLACED BY FARM PERFORMANCE
    /* public function insert_address(Request $request)
      {
      $input = $request->all();

      $address = array(
      'farmerId' => $input['farmer_id'],
      'region' => $input['region'],
      'province' => $input['province'],
      'municipality' => $input['municipality'],
      'barangay' => $input['barangay'],
      'area' => $input['area'],
      'dateCreated' => date('Y-m-d')
      );

      // Insert new farm address in the database
      $farmer = new Farmer();
      $result = $farmer->insert_farm_address($address);

      echo json_encode($result);
      } */

    public function insert_farm_performance(Request $request) {
        $input = $request->all();

        $farm_performance = array(
            'farmerID' => $input['farmer_id'],
            'area_planted' => $input['area_planted'],
            'variety_used' => $input['variety_used_prefix'] . ' ' . $input['variety_used'],
            'seed_usage' => $input['seed_usage'],
            'yield' => $input['yield']
        );

        // Insert farm performance in the database
        $farmer = new Farmer();
        $result = $farmer->insert_farm_performance($farm_performance);

        if ($result) {
            // Insert farmer distribution ID
            $result = $farmer->insert_distribution_id($input['farmer_id'], $input['qr_code']);
        }

        echo json_encode($result);
    }

    // Checking of farmer's seeds allocation method
    public function farmer_allocation($distribution_id) {
        // Get prv in the distribution_id sequence
        // P63 03 02 000 000001
        $prv = substr($distribution_id, 3, 4);

        // Get farmerID in farmer profile table
        $farmer = new Farmer();
        $profile = $farmer->farmer_profile($prv, $distribution_id);

        if ($profile) {
            $farmer_id = $profile->farmerID;

            // Get allocated seeds to farmer
            $releasing = new Releasing();
            $allocated_seeds = $releasing->allocated_seeds($prv, $profile->rsbsa_control_no, $farmer_id);

            if ($allocated_seeds) {
                $seeds = array();
                foreach ($allocated_seeds as $item) {
                    $seeds[] = array(
                        'variety' => $item->seed_variety,
                        'bags' => $item->bags,
                        'released' => $item->is_released
                    );
                }

                // Success and farmer has allocated seeds
                $data = array(
                    'status' => 'success',
                    'message' => 'has allocated seeds',
                    'data' => $seeds
                );
            } else {
                // Farmer has no allocated seeds
                $data = array(
                    'status' => 'error',
                    'message' => "no allocated seeds",
                    'data' => array()
                );
            }
        } else {
            // Distribution ID does not exist or wrong QR code scanned
            $data = array(
                'status' => "error",
                'message' => "qr code error",
                'data' => array()
            );
        }

        echo json_encode($data);
    }

    public function rsbsa_requirement() {
        if (Session::get('rsbsa_checking') == true) {
            echo json_encode('required');
        } else {
            echo json_encode('not required');
        }
    }

    public function farmer_rsbsa($farmer_id) {
        // Check if farmer is from rsbsa list
        $farmer = new Farmer();
        $profile = $farmer->farmer_rsbsa($farmer_id);

        if ($profile->isLGU == 1) {
            echo json_encode('lgu');
        } else {
            echo json_encode('rsbsa');
        }
    }

    public function update_farmer_rsbsa(Request $request) {
        $input = $request->all();
        $farmer_id = $input['farmer_id'];
        $rsbsa_control_no = $input['rsbsa_control_no'];

        // Update farmer's rsbsa control number
        $farmer = new Farmer();
        $result = $farmer->update_farmer_rsbsa($farmer_id, $rsbsa_control_no);

        echo json_encode($result);
    }

    // Returns csrf_token for releasing of seeds POST method
    public function get_csrf_token() {
        echo json_encode(csrf_token());
    }

    public function write_dropoff($prvCode, $coopAccreditation, $dropoffpoint, $createdBy) {
        //convert slash to rcef3310, rcef3310 to slash
        $code = 'rcef3310';

        $coopAccreditation = str_ireplace($code, "/", $coopAccreditation);
        $dropoffpoint = str_ireplace($code, "/", $dropoffpoint);

        $farmer = new Farmer();
        $check_dropoff_coop = $farmer->_check_dropoff_coop($prvCode, $dropoffpoint, $coopAccreditation);
		$count_other = 0;
        if ($check_dropoff_coop == 0) {
            $check_dropoff = $farmer->_check_dropoff($prvCode, $dropoffpoint, 1);
            if ($check_dropoff == 0) {
                $count_other = $farmer->_check_other_dropoff($prvCode, $dropoffpoint);
				//echo $count_other;
                $suffix = $count_other + 1;
                $dropoff_id = $prvCode . '-' . $suffix;
            } else {
                $drop_offdata = $farmer->_check_dropoff($prvCode, $dropoffpoint, 2);
                $dropoff_id = $drop_offdata->prv_dropoff_id;
            }
            $get_geonames = $farmer->_get_geonames($prvCode);

            $data = array(
                'prv_dropoff_id' => $dropoff_id,
                'coop_accreditation' => $coopAccreditation,
                'region' => $get_geonames->regionName,
                'province' => $get_geonames->province,
                'municipality' => $get_geonames->municipality,
                'dropOffPoint' => $dropoffpoint,
                'prv' => $prvCode,
                'is_active' => 1,
                'date_created' => date("Y:m:d H:i:s"),
                'created_by' => $createdBy
            );
            $drop_off = $farmer->_insert_dropoff($data);
            //echo json_encode($drop_off);
			echo json_encode("success");
        } else {
            echo json_encode("existing");
        }
    }

    // Releasing of seeds method
    public function release_seeds(Request $request) {
        $input = $request->all();

        // Inputs from mobile app
        // $prv = $input['prv']; // Province code or geo code of province, used in identifying table to connect
        $distribution_id = $input['distribution_id']; // Scanned from QR Code
        $username = $input['username']; // Traceability on who released the seeds to farmer
        // Get prv in the distribution_id sequence
        // P63 03 02 000 000001
        $prv = substr($distribution_id, 3, 4);

        // Get farmer profile -> farmer id
        $farmer = new Farmer();
        $profile = $farmer->farmer_profile($prv, $distribution_id);

        if ($profile) {
            // Get pending releases for farmer
            $releasing = new Releasing();
            $pending = $releasing->allocated_seeds($prv, $profile->farmerID);

            if ($pending) {
                foreach ($pending as $item) {
                    $data = array(
                        'farmer_id' => $item->farmer_id,
                        'batch_ticket_no' => $item->batch_ticket_no,
                        'seed_variety' => $item->seed_variety,
                        'bags' => $item->bags,
                        'released_by' => $username
                    );

                    // Update pending seeds to released in pending release table
                    $result = $releasing->release($prv, $data);

                    if ($result) {
                        // Insert released data in release table
                        $result = $releasing->insert_released($prv, $data);
                    }
                }
            } else {
                $result = "failed";
            }
        } else {
            $result = "failed";
        }

        // returns success of failed
        echo json_encode($result);
    }

    // Releasing of seeds method "GET"
    public function release_seeds2($distribution_id, $username) {
        // $input = $request->all();
        // Inputs from mobile app
        // $prv = $input['prv']; // Province code or geo code of province, used in identifying table to connect
        $distribution_id = $distribution_id; // Scanned from QR Code
        $username = $username; // Traceability on who released the seeds to farmer
        // Get prv in the distribution_id sequence
        // P63 03 02 000 000001
        $prv = substr($distribution_id, 3, 4);

        // Get farmer profile -> farmer id
        $farmer = new Farmer();
        $profile = $farmer->farmer_profile($prv, $distribution_id);

        if ($profile) {
            // Get pending releases for farmer
            $releasing = new Releasing();
            $pending = $releasing->allocated_seeds($prv, $profile->rsbsa_control_no, $profile->farmerID);

            if ($pending) {
                foreach ($pending as $item) {
                    $data = array(
                        'farmer_id' => $item->farmer_id,
                        // 'batch_ticket_no' => $item->batch_ticket_no,
                        'province' => $item->province,
                        'municipality' => $item->municipality,
                        'prv_dropoff_id' => $item->prv_dropoff_id,
                        'rsbsa_control_no' => $item->rsbsa_control_no,
                        'seed_variety' => $item->seed_variety,
                        'bags' => $item->bags,
                        'released_by' => $username
                    );

                    // Update pending seeds to released in pending release table
                    $result = $releasing->release($prv, $data);

                    if ($result) {
                        // Insert released data in release table
                        $result = $releasing->insert_released($prv, $data);
                    }
                }
            } else {
                $result = "failed";
            }
        } else {
            $result = "failed";
        }

        // returns success of failed
        echo json_encode($result);
    }

    public function add_farmer_rsbsa(Request $request) {
        $input = $request->all();
        $rsbsa_control_no = $input['rsbsa_control_no'];
        $actual_area = $input['actual_area'];
        $sex = $input['sex'];
        $distributionID = $input['qr_code'];
        $lastName = $input['lastName'];
        $firstName = $input['firstName'];
        $phone = $input['phone'];
        $midName = $input['midName'];
        $extName = $input['extName'];
        $birthdate = $input['birthdate'];
        $mfname = $input['mfname'];
        $mmname = $input['mmname'];
        $mlname = $input['mlname'];
        $mextname = $input['mextname'];
        $valid_id = $input['valid_id'];
        $relationship = $input['relationship'];
        $is_representative = $input['is_representative'];
        $variety_used = $input['variety_used'];
        $bags_distributed = $input['bags_distributed'];
        $is_changed = $input['is_changed'];
        $seed_usage = $input['seed_usage'];
        $preferred_variety = $input['preferred_variety'];
        $area_planted = $input['area_planted'];
        $yields = $input['yields'];

        $farmer = new Farmer();
		
        $dropoff_point = Session::get('dropoff_point');
		$prv_id = explode("-",$dropoff_point);
        $area = $this->formula($actual_area, 1, $bags_distributed, $is_changed, $prv_id[0]);
        $bags_limit = $this->formula($actual_area, 2, $bags_distributed, $is_changed, $prv_id[0]);

        $existing_bags = $farmer->get_farmer_rsbsa($rsbsa_control_no);

        $check_qr = $farmer->check_qr($distributionID, $rsbsa_control_no);
        $remaining = $bags_limit - $existing_bags;
        if ($remaining <= 0) {
            $result = "limit reach";
        } else if ($check_qr > 0) {
            $result = "qr code already inputted";
        } else {
            if (file_exists('../rel/uploads/files/' . $distributionID . '.jpg') or file_exists('../rel/uploads/files/' . $rsbsa_control_no . '.jpg')) {
                $have_pic = 1;
            } else {
                $have_pic = 0;
            }
// Add farmer's rsbsa control number
            $result = $farmer->add_farmer_rsbsa($rsbsa_control_no, $area, $distributionID, $sex, $lastName, $firstName, $midName, $extName, $actual_area);

            if ($result['result'] == "success") {
                $farmer->add_other_info($phone, $rsbsa_control_no, $result['farmerID'], $birthdate, $mfname, $mmname, $mlname, $mextname, $valid_id, $relationship, $is_representative, $have_pic);
                $farmer->add_performance($rsbsa_control_no, $result['farmerID'], $variety_used, $seed_usage, $preferred_variety, $area_planted,$yields);
            }
        }

        echo json_encode($result);
    }

}
