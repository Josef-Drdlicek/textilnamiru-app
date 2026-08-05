<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Response;

/**
 * Trvalý technický nákres uložené položky (nahrazuje původní schema.php,
 * včetně kontroly vlastnictví — zadání §11.2).
 */
class OrderItemSchemaController extends Controller
{
    public function __invoke(OrderItem $item): Response
    {
        $this->authorize('view', $item);

        $svg = app($item->type->schemaRendererClass())->render($item->parameters);

        return response($svg, headers: ['Content-Type' => 'image/svg+xml']);
    }
}
