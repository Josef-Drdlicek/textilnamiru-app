<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RailImage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fotka kolejnice či garnýže ze stejného úložiště jako ostatní fotky.
 */
class RailImageController extends Controller
{
    public function __invoke(RailImage $image): StreamedResponse
    {
        abort_unless($image->image_path && Storage::disk()->exists($image->image_path), 404);

        return Storage::disk()->response($image->image_path, headers: ['Cache-Control' => 'public, max-age=604800']);
    }
}
