<?php

namespace App\Livewire\Configurators;

use App\Actions\SaveOrderItem;
use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Společný základ konfigurátorů: živý serverový přepočet ceny, validace,
 * SVG náhled a uložení položky. Cena se NIKDY nepočítá v prohlížeči —
 * komponenta při každé změně vstupu přepočítá cenu na serveru.
 */
abstract class AbstractConfigurator extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public ?OrderItem $item = null;

    public string $material = '';

    public ?int $count = 1;

    public string $notes = '';

    // Výstupy kalkulace — pouze pro zobrazení; ukládaná cena vzniká
    // vždy znovu na serveru v SaveOrderItem.
    public ?float $unitPrice = null;

    public ?float $unitPriceVat = null;

    public ?float $totalPrice = null;

    public ?float $totalPriceVat = null;

    /** @var list<array{label: string, amount: float}> */
    public array $priceLines = [];

    abstract public function itemType(): ItemType;

    /**
     * Validační pravidla polí specifických pro typ výrobku.
     *
     * @return array<string, list<mixed>>
     */
    abstract protected function typeRules(): array;

    /**
     * Sestaví typované parametry z aktuálních hodnot formuláře.
     * Volat jen nad zvalidovanými vstupy.
     */
    abstract protected function buildParameters(): ItemParameters;

    /**
     * Naplní formulář z uložených parametrů (režim úprav).
     */
    abstract protected function fillFromParameters(ItemParameters $parameters): void;

    public function mount(Order $order, ?OrderItem $item = null): void
    {
        $this->authorize('update', $order);

        $this->order = $order;

        if ($item !== null) {
            abort_unless($item->order_id === $order->id && $item->type === $this->itemType(), 404);

            $this->item = $item;
            $this->material = $item->material;
            $this->count = $item->count;
            $this->notes = $item->notes ?? '';
            $this->fillFromParameters($item->parameters);
        }

        $this->recalculate();
    }

    public function updated(string $property): void
    {
        $this->recalculate();
        $this->validateOnly($property);
    }

    public function save(SaveOrderItem $saveOrderItem)
    {
        $this->authorize('update', $this->order);
        $this->validate();

        $saveOrderItem->handle(
            order: $this->order,
            type: $this->itemType(),
            parameters: $this->buildParameters(),
            material: trim($this->material),
            count: (int) $this->count,
            room: $this->roomValue(),
            notes: trim($this->notes) !== '' ? trim($this->notes) : null,
            existing: $this->item,
        );

        session()->flash('status', $this->item !== null
            ? 'Položka byla upravena a cena přepočítána.'
            : 'Položka byla přidána do zakázky.');

        return $this->redirectRoute('orders.show', $this->order);
    }

    /**
     * Místnost zadávají jen záclony a závěsy — ostatní typy vrací null.
     */
    protected function roomValue(): ?string
    {
        return null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'material' => ['required', 'string', 'max:255'],
            'count' => ['required', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...$this->typeRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'material' => 'materiál',
            'count' => 'počet kusů',
            'notes' => 'poznámka',
            ...$this->typeValidationAttributes(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function typeValidationAttributes(): array
    {
        return [];
    }

    /**
     * Přepočítá cenu, pokud jsou vstupy validní; jinak cenu skryje.
     * Chybové hlášky řeší validateOnly() — tady se validuje potichu.
     */
    protected function recalculate(): void
    {
        if (! $this->hasValidInput()) {
            $this->unitPrice = null;
            $this->unitPriceVat = null;
            $this->totalPrice = null;
            $this->totalPriceVat = null;
            $this->priceLines = [];

            return;
        }

        $pricer = app(ItemPricer::class);
        $result = $pricer->price($this->itemType(), $this->buildParameters());
        $vatRate = $pricer->vatRate();

        $this->unitPrice = $result->unitPrice;
        $this->unitPriceVat = $result->unitPriceWithVat($vatRate);
        $this->totalPrice = $result->totalPrice((int) $this->count);
        $this->totalPriceVat = $result->totalPriceWithVat($vatRate, (int) $this->count);
        $this->priceLines = $result->linesToArray();
    }

    protected function hasValidInput(): bool
    {
        $rules = $this->rules();

        $data = [];

        foreach (array_keys($rules) as $field) {
            $root = strtok($field, '.');
            $data[$root] = $this->{$root};
        }

        return ! Validator::make($data, $rules)->fails();
    }

    /**
     * SVG náhled aktuální konfigurace; null, dokud nejsou vstupy validní.
     */
    public function schemaSvg(): ?string
    {
        if (! $this->hasValidInput()) {
            return null;
        }

        return app($this->itemType()->schemaRendererClass())->render($this->buildParameters());
    }
}
