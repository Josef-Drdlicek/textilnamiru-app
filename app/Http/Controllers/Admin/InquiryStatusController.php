<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InquiryStatusController extends Controller
{
    /**
     * Poptávkové workflow nevynucuje pořadí přechodů — dílna mění stav
     * volně podle průběhu komunikace se zákazníkem.
     */
    public function store(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(InquiryStatus::class)],
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return back()->with('status', 'Stav poptávky byl změněn.');
    }
}
