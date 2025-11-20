<?php

namespace App\Http\Controllers\SeedReplacement;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\SeedReplacementRoles;
use App\Models\SeedReplacementRequest;
use Validator;
use Storage;

class SRRequestController extends SRBaseController
{
    /**
     * Return the actual user model
     */
    protected function getCurrentUser()
    {
        return $this->userObject(); // returns Eloquent model
    }

    protected function generateSRID()
    {
        $year = date('Y');
        $monthDay = date('md');
        $minSec = date('is'); // minute + second: mmss
        $prefix = 'SR' . $year . '-' . $monthDay . '-' . $minSec . '-';
        $counter = 1;

        while (true) {
            $id = $prefix . sprintf('%02d', $counter);
            if (!SeedReplacementRequest::where('id', $id)->exists()) {
                return $id;
            }
            $counter++;
        }
    }

    protected function userHasAnyRole($user, $roles)
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

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

        if (!$this->userHasAnyRole($user, $roles_filtered)) {
            $mss = "No Access Privilege";
            return view('utility.pageClosed', compact('mss'));
        }

        return view('seed_replacement.requests.index', [
            'currentUser' => $user,
            'currentUserRoles' => $user->roles,
            'roles' => $roles,
            'apiToken' => $user->api_token,
        ]);
    }

    public function datatable()
    {
        $currentUser = $this->getCurrentUser();
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
                $canApprove = $this->userHasAnyRole($currentUser, $roles_filtered);

                if (is_null($request->is_approved)) {
                    $editUrl = route('replacement.request.edit', $request->id);
                    $deleteUrl = route('replacement.request.delete', $request->id);
                    $actionBtn .= '<a href="'.$editUrl.'" class="btn btn-sm btn-primary">Edit</a> '.
                                  '<a href="'.$deleteUrl.'" class="btn btn-sm btn-danger">Delete</a> ';
                }

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
            ->escapeColumns([])
            ->make(true);
    }

    public function approve($id)
    {
        $request = SeedReplacementRequest::findOrFail($id);
        $currentUser = $this->getCurrentUser();
        $allowedRoles = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        if (!$this->userHasAnyRole($currentUser, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $request->is_approved = 1;
        $request->save();

        return response()->json(['success' => true, 'message' => 'Request approved.']);
    }

    public function decline($id)
    {
        $request = SeedReplacementRequest::findOrFail($id);
        $currentUser = $this->getCurrentUser();
        $allowedRoles = [
            "branch-it","buffer-inspector","dro","delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator",
            "rcef-programmer"
        ];

        if (!$this->userHasAnyRole($currentUser, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $request->is_approved = 0;
        $request->save();

        return response()->json(['success' => true, 'message' => 'Request declined.']);
    }

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
        $data['user_id'] = $currentUser->userId;

        if ($request->hasFile('attachment_dir')) {
            $data['attachment_dir'] = $request->file('attachment_dir');
        }

        SeedReplacementRequest::create($data);

        return response()->json(['success' => true, 'id' => $data['id']]);
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
