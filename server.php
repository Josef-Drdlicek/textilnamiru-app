<?php

/**
 * Router pro vestavěný PHP server při lokálním vývoji:
 *
 *   php -S 127.0.0.1:8000 -t public server.php
 *
 * Náhrada za `php artisan serve`, které na Windows selhává, pokud cesta
 * k projektu obsahuje diakritiku (PHP mrší UTF-8 cesty v argumentech
 * podprocesů). Tento skript se předává relativní cestou, takže problém
 * obchází; uvnitř procesu už PHP s UTF-8 cestami pracuje správně.
 */
$publicPath = __DIR__.'/public';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Existující soubory (build assety, favicon…) nechá obsloužit server.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
