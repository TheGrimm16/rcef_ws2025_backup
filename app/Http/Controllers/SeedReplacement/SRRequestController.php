<?php

namespace App\Http\Controllers\SeedReplacement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Yajra\Datatables\Datatables;
use App\Models\SeedReplacementRoles;
use App\Models\SeedReplacementUser;
use App\Models\SeedReplacementRequest;
use Validator;
use Storage;

class SRRequestController extends Controller
{
    protected function getCurrentUser()
    {
        $user = Auth::guard('seed_replacement')->user();
        if (!$user) abort(403, 'Unauthorized access');

        return [
            'userId' => $user->userId,
            'firstName' => $user->firstName,
            'middleName' => $user->middleName,
            'lastName' => $user->lastName,
            'extName' => $user->extName,
            'email' => $user->email,
            'username' => $user->username,
            'stationId' => $user->stationId,
            'api_token' => $user->api_token,
            'roles' => $user->roles->pluck('name')->toArray(),
            'can' => function($permission) use ($user) { return $user->can($permission); },
            'hasRole' => function($roles) use ($user) {
                $roles = is_array($roles) ? $roles : [$roles];
                foreach ($roles as $r) if ($user->hasRole($r)) return true;
                return false;
            }
        ];
    }

    protected function generateSRID()
    {
        // Date parts
        $year = date('Y');
        $monthDay = date('md');
        $minSec = date('is'); // minute + second: mmss

        // Final prefix: SRYYYY-MMDD-mmss-
        $prefix = 'SR' . $year . '-' . $monthDay . '-' . $minSec . '-';

        $counter = 1;

        while (true) {
            // Create ID with 2-digit counter
            $id = $prefix . sprintf('%02d', $counter);

            // Check via your Model instead of DB::table()
            $exists = SeedReplacementRequest::where('id', $id)->exists();

            if (!$exists) {
                return $id;
            }

            $counter++;
        }
    }

    // $srId = $this->generateSRID();

    // DB::table('tbl_requests')->insert([
    //     'sr_id'         => $srId,
    //     'user_id'       => $request->user_id,
    //     'geo_code'      => $request->geo_code,
    //     'purpose_id'    => $request->purpose_id,
    //     'attachment_dir'=> $request->attachment_dir,
    //     'created_at'    => now(),
    //     'updated_at'    => now(),
    // ]);

    public function index()
    {
        $user = $this->getCurrentUser();
        $roles = SeedReplacementRoles::getAllRoles();
        $roles_filtered = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        if (!$user['hasRole']($roles_filtered)) {
            $mss = "No Access Privilege";
            return view('utility.pageClosed', compact('mss'));
        }

        return view('seed_replacement.requests.index', [
            'currentUser' => $user,
            'currentUserRoles' => $user['roles'],
            'roles' => $roles,
            'apiToken' => $user['api_token'],
        ]);
    }

    public function datatable()
    {
        $currentUser = $this->getCurrentUser(); // get logged-in users

        // Roles allowed to approve requests
        $roles_filtered = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        $requests = SeedReplacementRequest::with('user')->select('tbl_requests.*');

        return Datatables::of($requests)
            ->addColumn('user_id', function($request) {
                return $request->user ? $request->user->userId : $request->user_id;
            })
            ->addColumn('action', function($request) use ($currentUser, $roles_filtered) {
                $actionBtn = '';

                // Check if user can approve/decline
                $canApprove = $currentUser['hasRole']($roles_filtered);

                if (is_null($request->is_approved)) {
                    // Only allow Edit/Delete for pending requests
                    $editUrl = route('replacement.request.edit', $request->id);
                    $deleteUrl = route('replacement.request.delete', $request->id);

                    $actionBtn .= '<a href="'.$editUrl.'" class="btn btn-sm btn-primary">Edit</a> '.
                                '<a href="'.$deleteUrl.'" class="btn btn-sm btn-danger">Delete</a> ';
                }

                // Show approve/decline or status buttons
                if ($canApprove) {
                    if (is_null($request->is_approved)) {
                        $approveBtn = '<button class="btn btn-sm btn-info approve-btn" data-id="'.$request->id.'" data-url="'.route('replacement.request.approve', $request->id).'">Approve</button>';
                        $declineBtn = '<button class="btn btn-sm btn-warning decline-btn" data-id="'.$request->id.'" data-url="'.route('replacement.request.decline', $request->id).'">Decline</button>';
                        $actionBtn .= $approveBtn . ' ' . $declineBtn;
                    } elseif ($request->is_approved == 1) {
                        $actionBtn .= '<button class="btn btn-sm btn-success" disabled>Approved</button>';
                    } elseif ($request->is_approved == 0) {
                        $actionBtn .= '<button class="btn btn-sm btn-danger" disabled>Declined</button>';
                    }
                }

                return $actionBtn;
            })
            ->escapeColumns([]) // allow HTML
            ->make(true);
    }

    public function approve($id)
    {
        $request = SeedReplacementRequest::findOrFail($id);

        // Only users with allowed roles can approve
        $currentUser = $this->getCurrentUser();
        $allowedRoles = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        $canApprove = false;
        foreach ($allowedRoles as $role) {
            if ($currentUser['hasRole']($role)) {
                $canApprove = true;
                break;
            }
        }


        if (!$canApprove) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $request->is_approved = 1;
        $request->save();

        return response()->json(['success' => true, 'message' => 'Request approved.']);
    }

    public function decline($id)
    {
        $request = SeedReplacementRequest::findOrFail($id);

        // Only users with allowed roles can approve or decline
        $currentUser = $this->getCurrentUser();
        $allowedRoles = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        $canApprove = $currentUser['hasRole']($allowedRoles);

        if (!$canApprove) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $request->is_approved = 0; // Declined
        $request->save();

        return response()->json(['success' => true, 'message' => 'Request declined.']);
    }


    // public function create()
    // {
    //     return view('seed_replacement.requests.create');
    // }

    public function store(Request $request)
    {
        $rules = [
            'geo_code'        => 'required|string',
            'purpose_id'      => 'required|integer',
            'new_released_id' => 'required|integer',
        ];

        if ($request->hasFile('attachment_dir')) {
            $rules['attachment_dir'] = 'file|mimes:jpg,jpeg,png,pdf|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $currentUser = $this->getCurrentUser();
        $data = $request->all();
        $data['id'] = $this->generateSRID();
        $data['user_id'] = $currentUser['userId'];

        if ($request->hasFile('attachment_dir')) {
            $data['attachment_dir'] = $request->file('attachment_dir');
        }

        SeedReplacementRequest::create($data);

        return response()->json([
            'success' => true,
            'id' => $data['id']
        ]);
    }

    public function edit($id)
    {
        $request = SeedReplacementRequest::findOrFail($id);
        return view('seed_replacement.requests.edit', compact('request'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|integer',
            'geo_code'         => 'required|string',
            'purpose_id'       => 'required|integer',
            'new_released_id'  => 'required|integer',
            'attachment_dir'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $req = SeedReplacementRequest::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('attachment_dir')) {
            // Delete old file
            if ($req->attachment_dir) Storage::disk('public')->delete($req->attachment_dir);
            $data['attachment_dir'] = $request->file('attachment_dir');
        }

        $req->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $req = SeedReplacementRequest::findOrFail($id);
        if ($req->attachment_dir) Storage::disk('public')->delete($req->attachment_dir);
        $req->delete();
        return response()->json(['success' => true]);
    }
}
