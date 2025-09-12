<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IARUtil;
use App\Inspmonitoring;
use DB;
use Validator;

class IARUtilController extends Controller
{
    public function index()
    {
        $model = new Inspmonitoring();

        $provinces = $model->_inspected_provinces(); // full static province list
        $people = IARUtil::getPeopleDetails();
        $positions = IARUtil::getPositions();
        $regionsWithProvinces = IARUtil::getRegionsWithProvinces();
        
        // $people = json_encode($people);
        // dd($people);
        
        return view('iar_util.index', compact(
            'provinces',
            'people',
            'positions',
            'regionsWithProvinces'
        ));
    }

    public function savePerson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'honorific_prefix' => 'sometimes|string|max:50',
            'complete_name'    => 'required|string|max:255',
            'post_nominal'     => 'sometimes|string|max:50',
            'cell_number'      => 'sometimes|string|max:11',
            'email'            => 'sometimes|email|max:255',
            'sex'              => 'sometimes|string|in:M,F',
            'civil_status'     => 'sometimes|string|max:20',
            'role_id'          => 'sometimes|integer',
            'position_id'      => 'sometimes|integer',
            'position_name'    => 'sometimes|string|max:255',
            'is_right'         => 'required|boolean',
            'regions_provinces'=> 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->valid();

        // Handle position
        if (!empty($data['position_name']) && empty($data['position_id'])) {
            $positionName = strtoupper(trim($data['position_name']));
            $existingPosition = DB::table('lib_signatories_position')
                ->whereRaw('UPPER(name) = ?', [$positionName])
                ->first();

            if ($existingPosition) {
                $data['position_id'] = $existingPosition->id;
            } else {
                $data['position_id'] = DB::table('lib_signatories_position')->insertGetId([
                    'name'       => $positionName,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        unset($data['position_name']);

        $id = $request->input('person_id'); // hidden input

        IARUtil::savePersonDetails($data, $id);

        $personName = $data['complete_name'];
        $message = $id 
            ? "Updated successfully: {$personName}" 
            : "Created successfully: {$personName}";

        return redirect()->back()->with([
            'toast_message' => "{$message}",
            'toast_color'   => '#0cb629ff', // red toast
        ]);
    }

    public function deletePerson(Request $request)
    {
        $personId = $request->input('person_id');
        $person = IARUtil::find($personId);

        if (!$person) {
            return redirect()->route('IAR_util.index')->with([
                'toast_message' => 'Invalid person selected for deletion.',
                'toast_color'   => '#f44336',
            ]);
        }

        $personName = $person->complete_name;

        IARUtil::deletePersonWithProvinces($personId);

        return redirect()->route('IAR_util.index')->with([
            'toast_message' => "Deleted successfully: {$personName}",
            'toast_color'   => '#f44336', // red toast
        ]);
    }
}
