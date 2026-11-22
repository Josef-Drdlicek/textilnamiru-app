<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TextileProduct;
use App\Models\TextileProductColor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fotka produktu z ceníku textilu. Servíruje se přes aplikaci, takže funguje
 * na jakémkoli disku (lokálně i v cloudu) a nepotřebuje odkaz public/storage.
 */
class TextileImageController extends Controller
{
    public function __invoke(TextileProduct $product): StreamedResponse
    {
        return $this->respond($product->image_path);
    }

    public function color(TextileProductColor $color): StreamedResponse
    {
        return $this->respond($color->image_path);
    }

    private function respond(?string $path): StreamedResponse
    {
        abort_unless($path && Storage::disk()->exists($path), 404);

        return Storage::disk()->response($path, headers: [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
