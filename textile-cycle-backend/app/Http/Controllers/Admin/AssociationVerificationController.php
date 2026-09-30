<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssociationVerificationController extends Controller
{
    public function index(): View
    {
        return view('don.admin.associations', [
            'associations' => Association::with('user')->orderByRaw('verified_at is not null')->latest()->get(),
        ]);
    }

    public function toggle(Association $association): RedirectResponse
    {
        $association->update(['verified_at' => $association->isVerified() ? null : now()]);

        return back()->with('status', $association->isVerified()
            ? $association->name . ' est maintenant vérifiée et proposée aux donateurs.'
            : $association->name . ' n\'est plus proposée aux donateurs.');
    }
}
