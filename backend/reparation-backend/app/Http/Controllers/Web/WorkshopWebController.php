<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RepairRequest;

class WorkshopWebController extends Controller
{
    public function forRepair(RepairRequest $repairRequest)
    {
        return view('workshops.index', ['repair' => $repairRequest]);
    }
}
