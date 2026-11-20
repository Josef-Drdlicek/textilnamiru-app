<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('web.home');
    }

    public function kalkulacka(Request $request): View|RedirectResponse
    {
        // Starý odkaz /kalkulacka?typ=… mířil rovnou do kalkulačky šití.
        if ($request->filled('typ')) {
            return redirect()->route('web.kalkulacka.sivani', $request->only('typ'));
        }

        return view('web.kalkulacka');
    }

    public function kalkulackaSivani(): View
    {
        return view('web.kalkulacka-sivani');
    }

    public function kalkulackaStropy(): View
    {
        return view('web.kalkulacka-stropy');
    }

    public function realizace(): View
    {
        return view('web.realizace');
    }

    public function zaclonyAZavesy(): View
    {
        return view('web.zaclony-a-zavesy');
    }

    public function zaclony(): View
    {
        return view('web.zaclony');
    }

    public function zavesy(): View
    {
        return view('web.zavesy');
    }

    public function zaveseniTextilu(): View
    {
        return view('web.zaveseni-textilu');
    }

    public function vypinanyPodhled(): View
    {
        return view('web.vypinany-podhled');
    }

    public function stiniciTechnika(): View
    {
        return view('web.stinici-technika');
    }

    public function privacy(): View
    {
        return view('web.gdpr');
    }
}
