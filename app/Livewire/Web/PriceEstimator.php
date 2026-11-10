<?php

namespace App\Livewire\Web;

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\CeilingColor;
use App\Enums\CeilingFinish;
use App\Enums\CeilingLighting;
use App\Enums\CeilingSurface;
use App\Enums\CeilingVariant;
use App\Enums\ItemType;
use App\Enums\ProductCategory;
use DomainException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Veřejná orientační kalkulačka jednoho oboru (šití, vypínané stropy).
 * Vystavuje jen laické parametry (rozměry, počet, nařasení, výplň) —
 * odborné parametry doplňuje PublicPresets, takže čísla sedí s katalogovou
 * „cenou od". Cena se počítá výhradně na serveru.
 */
class PriceEstimator extends Component
{
    #[Locked]
    public string $category = ProductCategory::Sewing->value;

    public string $type = '';

    /** Provedení z ProductCategory::estimatorVariants(); prázdné u oborů bez provedení. */
    public string $variant = '';

    // Specifikace stropu: druh a barva u jednobarevných provedení, světlo u translucentního.
    public string $finish = CeilingFinish::Matte->value;

    public string $color = CeilingColor::White->value;

    public string $lighting = CeilingLighting::Perimeter->value;

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

    public function mount(string $category = 'sewing'): void
    {
        $this->category = ProductCategory::from($category)->value;

        $requested = ItemType::tryFrom((string) request()->query('typ'));
        $types = $this->productCategory()->itemTypes();

        $this->type = ($requested !== null && in_array($requested, $types, true) ? $requested : $types[0])->value;

        $variants = $this->productCategory()->estimatorVariants();
        $requestedVariant = CeilingVariant::tryFrom((string) request()->query('provedeni'));

        if ($variants !== []) {
            $this->variant = ($requestedVariant !== null && in_array($requestedVariant, $variants, true) ? $requestedVariant : $variants[0])->value;
        }

        $this->prefillDimensions();
        $this->recalculate();
    }

    public function productCategory(): ProductCategory
    {
        return ProductCategory::from($this->category);
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
        } catch (DomainException) {
            $this->addError('width', 'Tuto konfiguraci neumíme nacenit online — poptejte nás prosím napřímo.');

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

    public function selectedVariant(): ?CeilingVariant
    {
        return CeilingVariant::tryFrom($this->variant);
    }

    public function selectedFinish(): ?CeilingFinish
    {
        return CeilingFinish::tryFrom($this->finish);
    }

    public function selectedColor(): ?CeilingColor
    {
        return CeilingColor::tryFrom($this->color);
    }

    public function selectedLighting(): ?CeilingLighting
    {
        return CeilingLighting::tryFrom($this->lighting);
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
        $max = PublicPresets::maxDimensions($this->itemType());
        $variants = array_map(fn (CeilingVariant $variant) => $variant->value, $this->productCategory()->estimatorVariants());
        $variant = $this->selectedVariant();

        return [
            'type' => ['required', Rule::in(array_map(fn (ItemType $type) => $type->value, $this->productCategory()->itemTypes()))],
            'variant' => $variants === [] ? ['nullable'] : ['required', Rule::in($variants)],
            'finish' => $variant?->hasFinishAndColor() ? ['required', Rule::enum(CeilingFinish::class)] : ['nullable'],
            'color' => $variant?->hasFinishAndColor() ? ['required', Rule::enum(CeilingColor::class)] : ['nullable'],
            'lighting' => $variant?->hasLighting() ? ['required', Rule::enum(CeilingLighting::class)] : ['nullable'],
            'width' => ['required', 'numeric', 'min:10', "max:{$max['width']}"],
            'height' => ['required', 'numeric', 'min:10', "max:{$max['height']}"],
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
            'height' => mb_strtolower($this->itemType()->heightLabel()),
            'count' => 'počet kusů',
            'ruffle' => 'nařasení',
            'finish' => 'druh',
            'color' => 'barva',
            'lighting' => 'světlo',
        ];
    }

    /**
     * Lidsky čitelný souhrn konfigurace pro poptávku a admin.
     */
    public function summary(): string
    {
        $parts = array_filter([
            $this->itemType()->label(),
            $this->selectedVariant()?->label(),
            ...$this->specificationLabels(),
            sprintf('%s × %s cm', $this->formatNumber((float) $this->width), $this->formatNumber((float) $this->height)),
            $this->count.' ks',
        ]);

        if ($this->isFabricType()) {
            $parts[] = mb_strtolower(PublicPresets::RUFFLE_OPTIONS[$this->ruffle]['label']).' nařasení';
        }

        if ($this->hasStuffingOption()) {
            $parts[] = $this->stuffing ? 'včetně výplně' : 'bez výplně';
        }

        return implode(' · ', $parts);
    }

    /**
     * Zvolená specifikace stropu jako text („mat, bílá" / „světlo po obvodu").
     *
     * @return list<string>
     */
    protected function specificationLabels(): array
    {
        $variant = $this->selectedVariant();

        if ($variant?->hasFinishAndColor()) {
            return [mb_strtolower($this->selectedFinish()?->label() ?? '').', '.mb_strtolower($this->selectedColor()?->label() ?? '')];
        }

        if ($variant?->hasLighting()) {
            return [mb_strtolower($this->selectedLighting()?->label() ?? '')];
        }

        return [];
    }

    /**
     * @return array{width: float, height: float, ratio: float, stuffing: bool, surface?: CeilingSurface, light_strip?: bool}
     */
    protected function estimatorInput(): array
    {
        $input = [
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'ratio' => PublicPresets::RUFFLE_OPTIONS[$this->ruffle]['ratio'],
            'stuffing' => $this->stuffing,
        ];

        if ($variant = $this->selectedVariant()) {
            $input['surface'] = $variant->hasLighting()
                ? CeilingSurface::Backlit
                : ($this->selectedFinish() ?? CeilingFinish::Matte)->surface();
            $input['light_strip'] = $variant === CeilingVariant::LightStrip;
        }

        return $input;
    }

    protected function prefillDimensions(): void
    {
        $reference = PublicPresets::referenceDimensions($this->itemType());

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
            $vatRate = $pricer->vatRate();
        } catch (DomainException) {
            $this->clearPrice();

            return;
        }

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

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', ' '), '0'), ',');
    }

    public function render(): View
    {
        return view('livewire.web.price-estimator');
    }
}
