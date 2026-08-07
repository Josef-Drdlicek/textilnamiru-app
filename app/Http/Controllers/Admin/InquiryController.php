<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiryController extends Controller
{
    /**
     * Přehled poptávek z webu ve stavových sekcích.
     */
    public function index(): View
    {
        $inquiries = Inquiry::query()
            ->with('order')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (Inquiry $inquiry) => $inquiry->status->value);

        return view('admin.inquiries.index', [
            'sections' => InquiryStatus::adminSections(),
            'inquiries' => $inquiries,
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        $inquiry->load('order.user');

        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
            'designers' => User::query()
                ->where('is_active', true)
                ->where('is_admin', false)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Tvrdé smazání — slouží i jako výmaz osobních údajů na žádost (GDPR).
     */
    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()
            ->route('admin.inquiries.index')
            ->with('status', "Poptávka od „{$inquiry->name}“ byla smazána.");
    }
}
