<?php

namespace App\Livewire\Web;

use App\Domain\Pricing\Exceptions\NoBlindPriceBand;
use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Veřejná orientační kalkulačka. Vystavuje jen laické parametry (rozměry,
 * počet, nařasení, výplň) — odborné parametry doplňuje PublicPresets, takže
 * čísla sedí s katalogovou „cenou od". Cena se počítá výhradně na serveru.
 */
class PriceEstimator extends Component
{
    public string $type = 'curtain';

    public ?float $width = null;

    public ?float $height = null;

    public ?int $count = 1;

    /** Klíč z PublicPresets::RUFFLE_OPTIONS (jen záclony a závěsy). */
    public string $ruffle = 'standard';

    /** Včetně výplně (jen polštáře a sedáky). */
    public bool $stuffing = false;

    // Výstupy kalkulace — pouze pro zobrazení; snapshot pro poptávku vzniká
    // vždy znovu na serveru v sendToInquiry().
    public ?float $unitPriceVat = null;

    public ?float $totalPrice = null;

    public ?float $totalPriceVat = null;

    /** @var list<array{label: string, amount: float}> */
    public array $priceLines = [];

    public function mount(): void
    {
        $requested = ItemType::tryFrom((string) request()->query('typ'));

        if ($requested !== null) {
            $this->type = $requested->value;
        }

        $this->prefillDimensions();
        $this->recalculate();
    }

    public function itemType(): ItemType
    {
        return ItemType::from($this->type);
    }

    public function updated(string $property): void
    {
        $this->recalculate();

        if ($property !== 'type') {
            $this->validateOnly($property);
        }
    }

    public function updatedType(): void
    {
        $this->resetValidation();
        $this->prefillDimensions();
        $this->recalculate();
    }

    /**
     * Uloží serverový snapshot konfigurace do session a přejde na poptávku.
     */
    public function sendToInquiry()
    {
        $this->validate();

        $pricer = app(ItemPricer::class);
        $type = $this->itemType();
        $parameters = PublicPresets::estimatorParameters($type, $this->estimatorInput());

        try {
            $result = $pricer->price($type, $parameters);
        } catch (NoBlindPriceBand) {
            $this->addError('width', 'Tento rozměr neumíme nacenit online — poptejte nás prosím napřímo.');

            return null;
        }

        $vatRate = $pricer->vatRate();
        $count = (int) $this->count;

        session()->put('inquiry.configuration', [
            'type' => $type->value,
            'parameters' => $parameters->toArray(),
            'count' => $count,
            'unit_price' => $result->unitPrice,
            'unit_price_vat' => $result->unitPriceWithVat($vatRate),
            'total_price_vat' => $result->totalPriceWithVat($vatRate, $count),
            'vat_rate' => $vatRate,
            'price_lines' => $result->linesToArray(),
            'summary' => $this->summary(),
            'calculated_at' => now()->toIso8601String(),
        ]);

        return $this->redirectRoute('web.inquiry.create');
    }

    public function isFabricType(): bool
    {
        return in_array($this->itemType(), [ItemType::Curtain, ItemType::Drape], true);
    }

    public function hasStuffingOption(): bool
    {
        return in_array($this->itemType(), [ItemType::Pillow, ItemType::Cushion], true);
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        [$maxWidth, $maxHeight] = match ($this->itemType()) {
            ItemType::Curtain, ItemType::Drape => [2000, 500],
            ItemType::Blind => [300, 300],
            ItemType::Pillow, ItemType::Cushion => [100, 100],
        };

        return [
            'type' => ['required', Rule::enum(ItemType::class)],
            'width' => ['required', 'numeric', 'min:10', "max:{$maxWidth}"],
            'height' => ['required', 'numeric', 'min:10', "max:{$maxHeight}"],
            'count' => ['required', 'integer', 'min:1', 'max:999'],
            'ruffle' => ['required', Rule::in(array_keys(PublicPresets::RUFFLE_OPTIONS))],
            'stuffing' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'height' => 'výška',
            'count' => 'počet kusů',
            'ruffle' => 'nařasení',
        ];
    }

    /**
     * Lidsky čitelný souhrn konfigurace pro poptávku a admin.
     */
    public function summary(): string
    {
        $parts = [
            $this->itemType()->label(),
            sprintf('%s × %s cm', rtrim(rtrim(number_format((float) $this->width, 1, ',', ' '), '0'), ','), rtrim(rtrim(number_format((float) $this->height, 1, ',', ' '), '0'), ',')),
            $this->count.' ks',
        ];

        if ($this->isFabricType()) {
            $parts[] = mb_strtolower(PublicPresets::RUFFLE_OPTIONS[$this->ruffle]['label']).' nařasení';
        }

        if ($this->hasStuffingOption()) {
            $parts[] = $this->stuffing ? 'včetně výplně' : 'bez výplně';
        }

        return implode(' · ', $parts);
    }

    /**
     * @return array{width: float, height: float, ratio: float, stuffing: bool}
     */
    protected function estimatorInput(): array
    {
        return [
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'ratio' => PublicPresets::RUFFLE_OPTIONS[$this->ruffle]['ratio'],
            'stuffing' => $this->stuffing,
        ];
    }

    protected function prefillDimensions(): void
    {
        $reference = PublicPresets::referenceParameters($this->itemType())->toArray();

        $this->width = $reference['width'];
        $this->height = $reference['height'];
        $this->count = 1;
        $this->ruffle = 'standard';
        $this->stuffing = false;
    }

    protected function recalculate(): void
    {
        if (! $this->hasValidInput()) {
            $this->clearPrice();

            return;
        }

        $pricer = app(ItemPricer::class);

        try {
            $result = $pricer->price(
                $this->itemType(),
                PublicPresets::estimatorParameters($this->itemType(), $this->estimatorInput()),
            );
        } catch (NoBlindPriceBand) {
            $this->clearPrice();

            return;
        }

        $vatRate = $pricer->vatRate();

        $this->unitPriceVat = $result->unitPriceWithVat($vatRate);
        $this->totalPrice = $result->totalPrice((int) $this->count);
        $this->totalPriceVat = $result->totalPriceWithVat($vatRate, (int) $this->count);
        $this->priceLines = $result->linesToArray();
    }

    protected function clearPrice(): void
    {
        $this->unitPriceVat = null;
        $this->totalPrice = null;
        $this->totalPriceVat = null;
        $this->priceLines = [];
    }

    protected function hasValidInput(): bool
    {
        $rules = $this->rules();

        $data = [];

        foreach (array_keys($rules) as $field) {
            $data[$field] = $this->{$field};
        }

        return ! Validator::make($data, $rules)->fails();
    }

    public function render(): View
    {
        return view('livewire.web.price-estimator');
    }
}
