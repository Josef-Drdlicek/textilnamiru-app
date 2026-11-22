<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShadeVariantImage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fotka rolety ze stejného úložiště jako fotky látek (funguje lokálně i v cloudu).
 */
class ShadeImageController extends Controller
{
    public function __invoke(ShadeVariantImage $image): StreamedResponse
    {
        abort_unless($image->image_path && Storage::disk()->exists($image->image_path), 404);

        return Storage::disk()->response($image->image_path, headers: ['Cache-Control' => 'public, max-age=604800']);
    }
}
