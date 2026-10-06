<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RepairRequest;

class RepairWebController extends Controller
{
    public function index()
    {
        $repairs = RepairRequest::latest()->get();
        return view('repairs.index', ['repairs' => $repairs]);
    }

    public function create()
    {
        return view('repairs.create');
    }

    public function show(RepairRequest $repairRequest)
    {
        return view('repairs.show', ['repair' => $repairRequest->load('workshop')]);
    }
}
