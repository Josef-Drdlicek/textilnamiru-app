<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonException;

/**
 * Vydá sestavenou JS aplikaci zakázek jen přihlášenému uživateli.
 * Soubor leží mimo public/, aby šel otevřít jen přes tuto routu.
 */
class B2bAppController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $path = resource_path('b2b-app/ukazka-online.html');
        abort_unless(is_readable($path), 503, 'Aplikace zakázek není nainstalovaná. Spusťte scripts/sync-b2b-app.ps1.');

        $html = file_get_contents($path);
        abort_unless($html !== false, 503, 'Aplikaci zakázek se nepodařilo načíst.');

        $html = $this->vlozitLaravelRelaci($html, $request->user(), csrf_token());

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Do HTML vloží relaci JS aplikace podle Laravel uživatele
     * a CSRF token pro odhlášení. Heslo se nepředává.
     */
    private function vlozitLaravelRelaci(string $html, User $user, string $csrfToken): string
    {
        try {
            $json = json_encode([
                'user_id' => $user->id,
                'login' => $user->username,
                'name' => $user->name,
                'admin' => $user->isAdmin(),
                'active' => true,
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $csrfJson = json_encode($csrfToken, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            abort(500, 'Nepodařilo se připravit relaci aplikace.');
        }

        $skript = <<<HTML
<script>
/* Relace z Laravel přihlášení — heslo se do JS nepředává. */
window.__TNM_CSRF = {$csrfJson};
(function () {
  var u = {$json};
  localStorage.setItem('tnm_session', JSON.stringify({ user: u, loggedAt: Date.now() }));
})();
</script>
HTML;

        if (str_contains($html, '<body>')) {
            return preg_replace('/<body>/', '<body>'.$skript, $html, 1) ?? $html;
        }

        return $skript.$html;
    }
}
