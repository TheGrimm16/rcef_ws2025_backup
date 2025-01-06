<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;

class APIController extends Controller
{
    public function __construct(UrlGenerator $url){
        $this->url = $url;
    }

    public function provinceList(Request $request){
        $provinces = DB::connection('mysql')
            ->table('lib_provinces')
            ->select('provDesc', 'provCode')
            ->where('regCode', '=', $request->region)
            ->orderBy('provDesc', 'asc')
            ->get();
        $return_str= '';
        foreach($provinces as $province){
            $return_str .= "<option value='$province->provCode'>$province->provDesc</option>";
        }
        return $return_str;
    }

    public function MunicipalityList(Request $request){
        $municipalities = DB::connection('mysql')
            ->table('lib_municipalities')
            ->select('citymunDesc', 'citymunCode')
            ->where('provCode', '=', $request->province)
            ->orderBy('citymunDesc', 'asc')
            ->get();
        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->citymunCode'>$municipality->citymunDesc</option>";
        }
        return $return_str;
    }

    public function SelectedEmployeeProfile(Request $request){
        $profile = DB::connection('hris_db')->table('employees')
                ->select('emp_station','emp_division','emp_office','emp_unit')
                ->where('emp_idno', '=', $request->id_number)
                ->first();
        
        $branch =  $profile->emp_station == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_stations')->where('id_station', '=', $profile->emp_station)->first()->station_abbr.' - '.DB::connection('hris_db')->table('lib_stations')->where('id_station', '=', $profile->emp_station)->first()->station_name;               
        $division =  $profile->emp_division == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_divisions')->where('id_division', '=', $profile->emp_division)->first()->division_abbr.' - '.DB::connection('hris_db')->table('lib_divisions')->where('id_division', '=', $profile->emp_division)->first()->division_name;
        $office =  $profile->emp_office == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_offices')->where('id_office', '=', $profile->emp_office)->first()->office_abbr.' - '.DB::connection('hris_db')->table('lib_offices')->where('id_office', '=', $profile->emp_office)->first()->office_name;
        $unit =  $profile->emp_unit == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_units')->where('id_unit', '=', $profile->emp_unit)->first()->unit_abbr.' - '.DB::connection('hris_db')->table('lib_units')->where('id_unit', '=', $profile->emp_unit)->first()->unit_name;
    
        $data = array(
            'division' => $division,
            'office' => $office,
            'branch' => $branch,
            'unit' => $unit
        );

        return $data;
    }

    public function getProvinceDropoffDetails(Request $request){
        $provinces = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('region', '=', $request->region)
            ->where('inspectorAllocated', '=', '0')
            ->groupBy('province')
            ->get();
        $province_str = '';
        foreach($provinces as $province){
            $province_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $province_str;
    }

    public function getMunicipalitiesDropoffDetails(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('province', '=', $request->province)
            ->where('inspectorAllocated', '=', '0')
            ->groupBy('municipality')->get();
        $municipalities_str = '';
        foreach($municipalities as $municipality){
            $municipalities_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $municipalities_str;
    }

    public function searchDropOffDelivery(Request $request){
        $batches = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('region', '=', $request->region)
                ->where('province', '=', $request->province)
                ->where('municipality', '=', $request->municipality)
                ->where('inspectorAllocated', '=', '0')
                ->groupBy('dropOffPoint')->get();
        $batch_str = '';
        foreach($batches as $batch){
            $batch_str .= "<option value='$batch->deliveryId'>$batch->dropOffPoint</option>";
        }
        return $batch_str;
    }

    public function SelectedDropOffDetails(Request $request){
        $batch = DB::connection('delivery_inspection_db')->table('tbl_delivery')                
                ->where('tbl_delivery.deliveryId', '=', $request->deliveryId)
                ->first();
        
        $data = array(
            'delivery_date' => date("F j, Y g:i A", strtotime($batch->deliveryDate)),
        );

        return $data;
    }

    public function getTicketDuration(Request $request){
        $ticket = DB::connection('delivery_inspection_db')->table('tbl_schedule')                
                ->where('tbl_schedule.scheduleId', '=', $request->scheduleId)
                ->first();
        
        $data = array(
            'duration_from' => date("m/d/Y", strtotime($ticket->duration_from)),
            'duration_to' => date("m/d/Y", strtotime($ticket->duration_to))
        );

        return $data;
    }

    public function RegisteredFarmerDetails(Request $request){
        $profile = DB::connection('registry_db')->table('farmer_profiles')->where('id', '=', $request->profileID)->first();
        $details = DB::connection('registry_db')->table('farm_details')->where('id', '=', $profile->farmer_details_id)->first();
        $affiliations = DB::connection('registry_db')->table('farmer_affiliations')->where('id', '=', $profile->farmer_affiliation_id)->first();

        if($profile->farmer_suffix_name != ''){
            $full_name = $profile->farmer_first_name.' '.$profile->farmer_middle_name.' '.$profile->farmer_last_name.' '.$profile->farmer_suffix_name;
        }else{
            $full_name = $profile->farmer_first_name.' '.$profile->farmer_middle_name.' '.$profile->farmer_last_name;
        }

        switch($affiliations->farm_accreditation){
            case 1:
                $farm_accreditation = "None";
                break;
            case 2:
                $farm_accreditation = "SEC";
                break;
            case 3:
                $farm_accreditation = "CDA";
                break;
            case 4:
                $farm_accreditation = "DOLE";
                break;
            case 5:
                $farm_accreditation = $affiliations->farm_accreditation_others;
                break;
        }

        switch($affiliations->affiliation_type){
            case 1:
                $affiliation_type = "IA - Irrigators Assocation";
                break;
            case 2:
                $affiliation_type = "COOP - Farmer's Cooperative";
                break;
            case 3:
                $affiliation_type = "SWISA - Small Water Irrigation Ssystem Association";
                break;
            case 4:
                $affiliation_type = $affiliations->farm_accreditation_others;
                break;
        }
        
        $data = array(
            'full_name' => $full_name,
            'sex' => $profile->farmer_gender == 1 ? 'Male' : 'Female',
            'contact_number' => $profile->farmer_contact_number == '' ? 'Not Available' : $profile->farmer_contact_number,
            'birth_date' => date("F j, Y", strtotime($profile->farmer_birth_date)),
            'farm_area' => $details->farm_area.' ha',
            'rice_area' => $details->rice_area.' ha',
            'tenurial_status' => $details->tenurial_status == 1 ? 'Owner' : 'Rent / Lease',
            'tenurial_type' => DB::connection('registry_db')->table('farmer_roles')->where('id', '=', $details->tenurial_type)->first()->farmer_role_description,
            'affiliation_type' => $affiliation_type,
            'affiliation_name' => $affiliations->affiliation_name,
            'farm_accreditation' => $farm_accreditation
        );

        return $data;
    }

    public function APIRegisteredFarmers(Request $request){
        return Datatables::of(DB::connection('registry_db')->table('farmer_profiles')
                    ->select('farmer_profiles.farmerID',
                             'farmer_profiles.farmer_first_name',
                             'farmer_profiles.farmer_middle_name',
                             'farmer_profiles.farmer_last_name',
                             'farmer_profiles.farmer_suffix_name',
                             'farm_details.farm_brgy',
                             'farm_details.farm_municipality',
                             'farm_details.farm_province')
                    ->join('farm_details', 'farmer_profiles.farmerID', '=','farm_details.farmerID')
            ->orderBy('farmer_profiles.created_at', 'desc')
        )
        ->addColumn('full_name', function($row){
            $full_name = $row->farmer_first_name.' '.$row->farmer_last_name;
            return $full_name; 
        })
        ->addColumn('address', function($row){
            $province = DB::connection('mysql')->table('lib_provinces')->where("provCode", "=", "$row->farm_province")->value('provDesc');
            $municipality = DB::connection('mysql')->table('lib_municipalities')->where("citymunCode", "=", "$row->farm_municipality")->value('citymunDesc');
        
            $address='';
            if($row->farm_brgy != ''){
                $address = $province.', '.$municipality.', '.$row->farm_brgy;
            }else{
                $address = $province.', '.$municipality;
            }

            return $address;
            //return $province;
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#farm_details" data-id="'.$row->farmerID.'" class="btn btn-round btn-success" style="margin-right:0"> <i class="fa fa-map"></i> </a>
            <a href="" class="btn btn-round btn-primary" style="margin-right:0"> <i class="fa fa-bar-chart"></i> </a>'; 
        })
        ->make(true);
    }

    public function ConfirmedDeliveries(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('status', '=', 0)
            ->orderBy('deliveryDate', 'desc')
        )
        ->addColumn('seed_grower_fld', function($row){
            $seed_grower = DB::connection('seed_grower_db')->table('seed_growers_all')->where('Code_Number', '=', $row->sgAccreditation)->first();
            return "<b>$seed_grower->Name</b><hr style='margin-top: 5px;margin-bottom: 5px;border: 0;border-top: 1px solid #cdcdd4;'>
                    <b>Seed Variety: </b>$row->seedVariety ($row->seedClass)<br>
                    <b>Seed Tag: </b>$row->seedTag<br>
                    <b>Weight: </b>$row->totalWeight / $row->weightPerBag per bag";
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#verify_allocation_modal" data-id="'.$row->ticketNumber.'" class="btn btn-success allocate-open-modal" style="margin-right:0"> <i class="fa fa-eye"></i> Designate Inspector </a><br>
                    <a href="" data-id="'.$row->ticketNumber.'" class="btn btn-danger" style="margin-right:0"> <i class="fa fa-thumbs-down"></i> Cancel Delivery </a>'; 
        })
        ->make(true);
    }

    public function getInspectorTickets(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')
            ->table('tbl_schedule')
            ->select('tbl_schedule.userId','tbl_schedule.ticketNumber',
                     'tbl_schedule.duration_from', 'tbl_schedule.duration_to',
                     'tbl_schedule.pmo_remarks', 'tbl_delivery.coopAccreditation', 
                     'tbl_delivery.seedTag', 'tbl_delivery.seedVariety',
                     'tbl_delivery.totalWeight', 'tbl_delivery.weightPerBag',
                     'tbl_delivery.deliveryDate', 'tbl_delivery.deliverTo',
                     'tbl_delivery.deliverTo', 'tbl_delivery.status',
                     'tbl_schedule.scheduleId')
            ->join('tbl_delivery', 'tbl_schedule.ticketNumber', '=', 'tbl_delivery.ticketNumber')
            ->where('tbl_schedule.userId', '=', $request->userId)
        )
        ->addColumn('coopName_fld', function($row){
            $coopName = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('coopId', '=', $row->coopAccreditation)->first()->coopName;
            $weight = $row->totalWeight.' / '.$row->weightPerBag.' per bag';
            return "<b><u>$coopName</u></b><br>
                    Seed Variety: $row->seedVariety<br>
                    Seed Tag: $row->seedTag<br>
                    Weight: $weight<br>
                    Deliver To: $row->deliverTo";
        })
        ->addColumn('duration_fld', function($row){
            return "From: ".date("F j, Y", strtotime($row->duration_from)).'<br>'.
                   "To: ".date("F j, Y", strtotime($row->duration_to));
        })
        ->addColumn('status_fld', function($row){
            $delivery_status = "";
            if($row->status == "0"){
                $delivery_status = 'Pending for Inspection';
            }elseif($row->status == "1"){
                $delivery_status = 'Inspection Completed';
            }elseif($row->status == "2"){
                $delivery_status = 'Rejected';
            }
            return $delivery_status;
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#schedule_duration_modal" data-id="'.$row->scheduleId.'" class="btn btn-round btn-warning" style="margin-right:0"> <i class="fa fa-calendar"></i></a>
            <a href="" class="btn btn-round btn-primary" style="margin-right:0"> <i class="fa fa-bar-chart"></i> </a>'; 
        })
        ->make(true);
    }

    public function getInspectorProfiles(Request $request){
        return Datatables::of(DB::connection('mysql')->table('users')
            ->select('users.firstName', 'users.middleName', 'users.lastName','users.extName','users.userId')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', 14)
            ->orderBy('firstName', 'asc')
            ->orderBy('middleName', 'asc')
            ->orderBy('lastName', 'asc')
        )
        ->addColumn('action', function($row){
            $url = $this->url->to('inspection/profile/'.$row->userId);
            return "<a href='$url' class='btn btn-warning btn-sm'><i class='fa fa-folder-open'></i></a>
                    <a href='$url' class='btn btn-danger btn-sm' style='margin-left: -5px;'><i class='fa fa-undo'></i></a>";
        })
        ->make(true);
    }
}
