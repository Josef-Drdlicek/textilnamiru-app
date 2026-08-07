<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ConvertInquiryToOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConvertInquiryRequest;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class InquiryConversionController extends Controller
{
    public function store(
        ConvertInquiryRequest $request,
        Inquiry $inquiry,
        ConvertInquiryToOrder $convertInquiryToOrder,
    ): RedirectResponse {
        if ($inquiry->isConverted()) {
            return back()->withErrors(['user_id' => 'Poptávka už byla převedena na zakázku.']);
        }

        $designer = User::query()->findOrFail($request->validated('user_id'));

        ['order' => $order, 'itemSkipped' => $itemSkipped] = $convertInquiryToOrder->handle(
            inquiry: $inquiry,
            designer: $designer,
            orderName: $request->validated('name'),
        );

        $message = "Založena rozpracovaná zakázka „{$order->name}“ pro {$designer->name}.";

        if ($itemSkipped) {
            $message .= ' Položku z kalkulačky se nepodařilo přenést (ceník se od poptávky změnil) — zadejte ji ručně.';
        }

        return redirect()
            ->route('admin.inquiries.show', $inquiry)
            ->with('status', $message);
    }
}
