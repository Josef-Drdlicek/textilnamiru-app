<?php

namespace App\Http\Controllers\Web;

use App\Enums\InquiryProduct;
use App\Enums\InquiryStatus;
use App\Enums\ItemType;
use App\Events\InquirySubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class InquiryController extends Controller
{
    /** Formulář odeslaný rychleji je téměř jistě bot. */
    private const int MIN_SECONDS_TO_FILL = 3;

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->boolean('bez-konfigurace')) {
            session()->forget('inquiry.configuration');

            return redirect()->route('web.inquiry.create');
        }

        $configuration = session('inquiry.configuration');

        $preselected = array_values(array_filter([
            InquiryProduct::tryFrom((string) $request->query('produkt'))?->value,
            $configuration !== null
                ? InquiryProduct::fromItemType(ItemType::from($configuration['type']))->value
                : null,
        ]));

        return view('web.inquiry', [
            'configuration' => $configuration,
            'preselectedProducts' => $preselected,
            'honeypotTime' => Crypt::encryptString((string) now()->timestamp),
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        // Honeypot: botům odpovídáme stejně jako lidem, jen nic neuložíme.
        if ($this->isSpam($request)) {
            return redirect()->route('web.inquiry.thanks');
        }

        $inquiry = Inquiry::create([
            ...$request->safe()->except(['gdpr']),
            'status' => InquiryStatus::New,
            'gdpr_consented_at' => now(),
            'ip' => $request->ip(),
            'configuration' => session('inquiry.configuration'),
        ]);

        session()->forget('inquiry.configuration');

        InquirySubmitted::dispatch($inquiry);

        return redirect()->route('web.inquiry.thanks');
    }

    public function thanks(): View
    {
        return view('web.inquiry-thanks');
    }

    private function isSpam(Request $request): bool
    {
        // Skryté pole, které člověk nevidí a nevyplní.
        if ($request->filled('website')) {
            return true;
        }

        try {
            $renderedAt = (int) Crypt::decryptString((string) $request->input('_hp_time'));
        } catch (DecryptException) {
            return true;
        }

        return now()->timestamp - $renderedAt < self::MIN_SECONDS_TO_FILL;
    }
}
