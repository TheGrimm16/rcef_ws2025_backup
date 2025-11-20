<?php

namespace App\Http\Controllers\SeedReplacement;

use App\Http\Controllers\Controller;

class SRDashboardController extends SRBaseController
{
    public function index()
    {
        // Use the standardized user array from SRBaseController
        $user = $this->userObject();

        return view('seed_replacement.dashboard.index', compact('user'));
    }
}
