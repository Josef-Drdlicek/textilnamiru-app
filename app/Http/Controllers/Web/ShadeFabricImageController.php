<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShadeFabric;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fotka látky na rolety ze stejného úložiště jako ostatní fotky.
 */
class ShadeFabricImageController extends Controller
{
    public function __invoke(ShadeFabric $fabric): StreamedResponse
    {
        abort_unless($fabric->image_path && Storage::disk()->exists($fabric->image_path), 404);

        return Storage::disk()->response($fabric->image_path, headers: ['Cache-Control' => 'public, max-age=604800']);
    }
}
