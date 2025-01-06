<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Releasing;

class SyncingController extends Controller
{
    public function index()
    {
        return view('syncing.index');
    }

    public function send_pending_release()
    {
        // Set time limit
        set_time_limit(5000);

        // Get pending releases with send flag is 1
        $releasing = new Releasing();
        $pending_releases = $releasing->pending_releases();

        $result="";

        if ($pending_releases) {
            foreach ($pending_releases as $item) {
                $data = array(
                    'farmer_id' => $item->farmer_id,
                    'batch_ticket_no' => $item->batch_ticket_no,
                    'province' => $item->province,
                    'municipality' => $item->municipality,
                    'dropOffPoint' => $item->dropOffPoint,
                    'seed_variety' => $item->seed_variety,
                    'bags' => $item->bags,
                    'date_created' => $item->date_created,
                    'is_released' => $item->is_released,
                    'created_by' => $item->created_by,
                    'send' => 1
                );

                // Send to central server
                $result = $releasing->insert_pending_to_server($data, $item->pending_id);
            }
        } else {
            $result="empty";
        }

        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Pending release data sent to central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }
    }

    public function send_released()
    {
        // Set time limit
        set_time_limit(5000);

        // Get released data with send flag is 1
        $releasing = new Releasing();
        $released = $releasing->releases();

        $result="";

        if ($released) {
            foreach ($released as $item) {
                $data = array(
                    'farmer_id' => $item->farmer_id,
                    'batch_ticket_no' => $item->batch_ticket_no,
                    'province' => $item->province,
                    'municipality' => $item->municipality,
                    'dropOffPoint' => $item->dropOffPoint,
                    'seed_variety' => $item->seed_variety,
                    'bags' => $item->bags,
                    'date_released' => $item->date_released,
                    'released_by' => $item->released_by,
                    'send' => 1
                );

                // Send to central server
                $result = $releasing->insert_released_to_server($data, $item->release_id);
            }
        } else {
            $result="empty";
        }

        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Released data sent to central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }
    }

    public function send_distribution_list_new()
    {
        // Set time limit
        set_time_limit(5000);

        // Get farmer profile data with send flag is 1
        $releasing = new Releasing();
        $profile_list = $releasing->farmer_profile_list();

        $result="";

        if ($profile_list) {
            foreach ($profile_list as $item) {
                // Farmer profile
                $farmer_profile = array(
                    'farmerID' => $item->farmerID,
                    'distributionID' => $item->distributionID,
                    'lastName' => $item->lastName,
                    'firstName' => $item->firstName,
                    'midName' => $item->midName,
                    'extName' => $item->extName,
                    'fullName' => $item->fullName,
                    'sex' => $item->sex,
                    'birthdate' => $item->birthdate,
                    'region' => $item->region,
                    'province' => $item->province,
                    'municipality' => $item->municipality,
                    'barangay' => $item->barangay,
                    'affiliationType' => $item->affiliationType,
                    'affiliationName' => $item->affiliationName,
                    'affiliationAccreditation' => $item->affiliationAccreditation,
                    'isDaAccredited' => $item->isDaAccredited,
                    'isLGU' => $item->isLGU,
                    'rsbsa_control_no' => $item->rsbsa_control_no,
                    'isNew' => $item->isNew
                );

                // Farmer area
                $farmer_area = array();
                $area = $releasing->farmer_area_list($farmer_profile['farmerID']);
                if ($area) {
                    foreach ($area as $item2) {
                        $farmer_area[] = array(
                            'farmerId' => $farmer_profile['farmerID'],
                            'region' => $item2->region,
                            'province' => $item2->province,
                            'municipality' => $item2->municipality,
                            'barangay' => $item2->barangay,
                            'area' => $item2->area,
                            'dateCreated' => $item2->dateCreated
                        );
                    }
                }

                // Farmer performance data
                $farmer_performance = array();
                $performance = $releasing->farmer_performance_list($farmer_profile['farmerID']);
                if ($performance) {
                    foreach ($performance as $item3) {
                        $farmer_performance[] = array(
                            'farmerID' => $farmer_profile['farmerID'],
                            'variety_used' => $item3->variety_used,
                            'seed_usage' => $item3->seed_usage,
                            'yield' => $item3->yield,
                            'preferred_variety' => $item3->preferred_variety
                        );
                    }
                }

                // Send to central server
                $result = $releasing->insert_farmer_profile_to_server($farmer_profile['farmerID'], $farmer_profile, $farmer_area, $farmer_performance);
            }
        } else {
            $result="empty";
        }

        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Distribution list data sent to central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }
    }

    public function send_distribution_list_updates()
    {
        // Set time limit
        set_time_limit(5000);

        // Get farmer profile data with update flag is 1
        $releasing = new Releasing();
        $profile_list = $releasing->farmer_profile_list_update();

        $result="";

        if ($profile_list) {
            foreach ($profile_list as $item) {
                // Farmer profile
                $farmer_profile = array(
                    'farmerID' => $item->farmerID,
                    'distributionID' => $item->distributionID,
                    'lastName' => $item->lastName,
                    'firstName' => $item->firstName,
                    'midName' => $item->midName,
                    'extName' => $item->extName,
                    'fullName' => $item->fullName,
                    'sex' => $item->sex,
                    'birthdate' => $item->birthdate,
                    'region' => $item->region,
                    'province' => $item->province,
                    'municipality' => $item->municipality,
                    'barangay' => $item->barangay,
                    'affiliationType' => $item->affiliationType,
                    'affiliationName' => $item->affiliationName,
                    'affiliationAccreditation' => $item->affiliationAccreditation,
                    'isDaAccredited' => $item->isDaAccredited,
                    'isLGU' => $item->isLGU,
                    'rsbsa_control_no' => $item->rsbsa_control_no,
                    'isNew' => $item->isNew
                );

                // Send to central server
                $result = $releasing->update_farmer_profile_to_server($farmer_profile['farmerID'], $farmer_profile);
            }
        } else {
            $result="empty";
        }

        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Farmer profile updates sent to central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }
    }

    public function download_distribution_list()
    {
        // Set time limit
        set_time_limit(5000);

        // Get farmer profile data from server
        $releasing = new Releasing();
        $profile_list = $releasing->farmer_profile_list_server();

        var_dump($profile_list);

        $result="";

        // Truncate distribution tables
        /*$truncate = $releasing->truncate_distribution_tables();

        if ($truncate == "success") {
            if ($profile_list) {
                foreach ($profile_list as $item) {
                    // Farmer profile
                    $farmer_profile = array(
                        'farmerID' => $item->farmerID,
                        'distributionID' => $item->distributionID,
                        'lastName' => $item->lastName,
                        'firstName' => $item->firstName,
                        'midName' => $item->midName,
                        'extName' => $item->extName,
                        'fullName' => $item->fullName,
                        'sex' => $item->sex,
                        'birthdate' => $item->birthdate,
                        'region' => $item->region,
                        'province' => $item->province,
                        'municipality' => $item->municipality,
                        'barangay' => $item->barangay,
                        'affiliationType' => $item->affiliationType,
                        'affiliationName' => $item->affiliationName,
                        'affiliationAccreditation' => $item->affiliationAccreditation,
                        'isDaAccredited' => $item->isDaAccredited,
                        'isLGU' => $item->isLGU,
                        'rsbsa_control_no' => $item->rsbsa_control_no,
                        'isNew' => $item->isNew
                    );

                    // Farmer area
                    $farmer_area = array();
                    $area = $releasing->farmer_area_list_server($farmer_profile['farmerID']);
                    if ($area) {
                        foreach ($area as $item2) {
                            $farmer_area[] = array(
                                'farmerId' => $farmer_profile['farmerID'],
                                'region' => $item2->region,
                                'province' => $item2->province,
                                'municipality' => $item2->municipality,
                                'barangay' => $item2->barangay,
                                'area' => $item2->area,
                                'dateCreated' => $item2->dateCreated
                            );
                        }
                    }

                    // Farmer performance data
                    $farmer_performance = array();
                    $performance = $releasing->farmer_performance_list_server($farmer_profile['farmerID']);
                    if ($performance) {
                        foreach ($performance as $item3) {
                            $farmer_performance[] = array(
                                'farmerID' => $farmer_profile['farmerID'],
                                'variety_used' => $item3->variety_used,
                                'seed_usage' => $item3->seed_usage,
                                'yield' => $item3->yield,
                                'preferred_variety' => $item3->preferred_variety
                            );
                        }
                    }

                    // Send to central server
                    $result = $releasing->insert_farmer_profile_to_local($farmer_profile['farmerID'], $farmer_profile, $farmer_area, $farmer_performance);
                }
            } else {
                $result="empty";
            }
        } else {
            $result=""; // Error truncate
        }

        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Distribution list data downloaded from central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }*/
    }

    public function send_actual_delivery()
    {
        // Set time limit
        set_time_limit(5000);

        // Get actual delivery data with send flag is 1
        $releasing = new Releasing();
        $delivery_data = $releasing->actual_delivery_data();

        $result="";

        if ($delivery_data) {
            foreach ($delivery_data as $item) {
                $data = array(
                    'batchTicketNumber' => $item->batchTicketNumber,
                    'region' => $item->region,
                    'province' => $item->province,
                    'municipality' => $item->municipality,
                    'dropOffPoint' => $item->dropOffPoint,
                    'seedVariety' => $item->seedVariety,
                    'totalBagCount' => $item->totalBagCount,
                    'dateCreated' => $item->dateCreated,
                    'seedTag' => $item->seedTag
                );

                // Send to central server
                $result = $releasing->send_delivery_data_to_server($item->actualDeliveryId, $data);
            }
        } else {
            $result="empty";
        }
        
        if ($result == 'success') {
            return redirect()->route('syncing.index')
                ->with('success', 'Success! Actual delivery data sent to central server.');
        } elseif ($result == 'empty') {
            return redirect()->route('syncing.index')
                ->with('warning', 'No data to send.');
        } elseif ($result == 'no connection') {
            return redirect()->route('syncing.index')
                ->with('error', 'No connection to central server.');
        } else {
            return redirect()->route('syncing.index')
                ->with('error', 'Error. Please try again.');
        }
    }
}
