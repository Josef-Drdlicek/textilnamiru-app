<?php

/*
 * Kontaktní údaje firmy — jediný zdroj pro patičku, JSON-LD, GDPR a poptávku.
 * Hodnoty lze přepsat v .env (COMPANY_PHONE, COMPANY_EMAIL, …).
 */
return [
    'name' => env('COMPANY_NAME', 'Textil na míru'),
    'claim' => 'Zaměříme, ušijeme, pověsíme.',
    'phone' => env('COMPANY_PHONE', '+420516453000'),
    'phone_display' => env('COMPANY_PHONE_DISPLAY', '+420 516 453 000'),
    'email' => env('COMPANY_EMAIL', 'info@textilnamiru.cz'),
    'street' => env('COMPANY_STREET', 'Masarykovo náměstí 49'),
    'zip' => env('COMPANY_ZIP', '680 01'),
    'city' => env('COMPANY_CITY', 'Boskovice'),
    'geo' => ['lat' => 49.4875, 'lng' => 16.6603],
    'hours' => [
        ['Pondělí – Pátek', '8:00 – 17:00'],
        ['Sobota', 'Po domluvě'],
        ['Neděle', 'Zavřeno'],
    ],
    'opening_hours_schema' => 'Mo-Fr 08:00-17:00',
    'privacy_updated_at' => '2026-08-07',
];
