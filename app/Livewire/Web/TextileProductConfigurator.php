<?php

namespace App\Livewire\Web;

use App\Domain\Pricing\TextileCurtainSpec;
use App\Domain\Pricing\TextileProductPricer;
use App\Enums\CurtainHem;
use App\Enums\CurtainMakeType;
use App\Models\Ribbon;
use App\Models\TextileProduct;
use App\Models\TextileProductColor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * „Objednávka šití na míru“ na produktové stránce látky: průvodce po krocích
 * (typ výrobku → barva → způsob šití → opona → rozměry → řasící páska),
 * každý krok jde znovu otevřít přes „Upravit“. Cena vzniká jen na serveru
 * v TextileProductPricer (látka z ceníku textilu + ušití z ceníku šití).
 */
class TextileProductConfigurator extends Component
{
    /** Kroky průvodce v pořadí; klíč = název kroku v šabloně. */
    public const array STEPS = ['type', 'color', 'hem', 'split', 'dimensions', 'tape'];

    public const int MIN_WIDTH = 50;

    public const int MAX_WIDTH = 2000;

    public const int MIN_HEIGHT = 100;

    public TextileProduct $product;

    /** Právě rozbalený krok. */
    public string $step = 'type';

    public string $makeType = '';

    public string $color = '';

    public string $hem = '';

    /** 'one' = jeden kus, 'two' = opona ze dvou dílů. */
    public string $split = '';

    public ?float $width = null;

    public ?float $height = null;

    /** Řasící páska a poměr jako „kód|poměr“, např. „siroky70|2“. */
    public string $tape = '';

    public ?int $count = 1;

    // Výstupy kalkulace — jen pro zobrazení; snapshot pro poptávku vzniká znovu v sendToInquiry().
    public ?float $totalPrice = null;

    public ?float $totalPriceVat = null;

    public ?float $fabricMeters = null;

    /** @var list<array{label: string, amount: float}> */
    public array $priceLines = [];

    public function mount(TextileProduct $product): void
    {
        $this->product = $product->loadMissing('colors');
    }

    /**
     * Barvu vybral zákazník v náhledech u fotky nahoře (mimo komponentu).
     */
    #[On('textile-color-picked')]
    public function pickColor(string $name): void
    {
        if ($this->product->colors->contains('name', $name)) {
            $this->color = $name;
            $this->recalculate();
        }
    }

    /**
     * Po výběru barvy v kroku „Barva“ vyměnit fotku nahoře (resources/js/textile-gallery.js).
     */
    public function updatedColor(): void
    {
        $color = $this->selectedColor();

        if ($color?->imageUrl()) {
            $this->dispatch('textile-color-changed', name: $color->name, image: $color->imageUrl());
        }
    }

    public function selectedColor(): ?TextileProductColor
    {
        return $this->product->colors->firstWhere('name', $this->color);
    }

    /**
     * Online cena jde spočítat jen u typu s řasící páskou a u látky s cenou.
     */
    public function canPrice(): bool
    {
        return $this->selectedMakeType()?->isPricedOnline() === true && $this->product->hasPrice();
    }

    public function openStep(string $step): void
    {
        if (in_array($step, $this->availableSteps(), true)) {
            $this->step = $step;
        }
    }

    /**
     * „Pokračovat“: zkontroluje vyplnění kroku a otevře další.
     */
    public function next(): void
    {
        $this->validate($this->stepRules($this->step));

        $steps = $this->availableSteps();
        $position = array_search($this->step, $steps, true);
        $this->step = $steps[$position + 1] ?? $this->step;
    }

    public function back(): void
    {
        $steps = $this->availableSteps();
        $position = array_search($this->step, $steps, true);
        $this->step = $steps[max(0, $position - 1)];
    }

    public function updated(string $property): void
    {
        // Nejdřív přepočet: validateOnly při chybě vyhodí výjimku a stará cena by zůstala vidět.
        $this->recalculate();

        if (in_array($property, ['width', 'height', 'count'], true)) {
            $this->validateOnly($property, $this->rules());
        }
    }

    /**
     * Kroky, které daný typ výrobku má. Typy na poptávku končí výběrem typu.
     *
     * @return list<string>
     */
    public function availableSteps(): array
    {
        return $this->selectedMakeType()?->isPricedOnline() === false ? ['type'] : self::STEPS;
    }

    public function selectedMakeType(): ?CurtainMakeType
    {
        return CurtainMakeType::tryFrom($this->makeType);
    }

    /**
     * Řasící pásky z ceníku šití, každá s povolenými poměry řasení.
     *
     * @return array<string, string> „kód|poměr“ => popisek
     */
    public function tapeOptions(): array
    {
        $options = [];

        $ribbons = Ribbon::query()
            ->where('is_active', true)
            ->where('allows_custom_ratio', false)
            ->orderBy('sort_order')
            ->get();

        foreach ($ribbons as $ribbon) {
            foreach ($ribbon->allowed_ratios as $ratio) {
                $options["{$ribbon->code}|{$ratio}"] = sprintf('%s 1:%s', $ribbon->name, str_replace('.', ',', (string) $ratio));
            }
        }

        return $options;
    }

    /**
     * Co je v kroku vybráno, pro hlavičku kroku; null = nevybráno.
     */
    public function stepValue(string $step): ?string
    {
        return match ($step) {
            'type' => $this->selectedMakeType()?->label($this->product->category),
            'color' => $this->selectedColor()?->label(),
            'hem' => CurtainHem::tryFrom($this->hem)?->label(),
            'split' => match ($this->split) {
                'one' => 'Jeden kus',
                'two' => 'Opona – dva díly',
                default => null,
            },
            'dimensions' => $this->width && $this->height
                ? sprintf('%s × %s cm', $this->formatNumber($this->width), $this->formatNumber($this->height))
                : null,
            'tape' => $this->tapeOptions()[$this->tape] ?? null,
            default => null,
        };
    }

    public function stepTitle(string $step): string
    {
        return match ($step) {
            'type' => 'Typ výrobku',
            'color' => 'Barva',
            'hem' => 'Způsob šití',
            'split' => 'Opona',
            'dimensions' => 'Rozměry výrobku',
            'tape' => 'Řasící páska',
        };
    }

    public function isComplete(): bool
    {
        return $this->canPrice() && $this->hasValidInput();
    }

    /**
     * Uloží serverový snapshot konfigurace do session a přejde na poptávku.
     */
    public function sendToInquiry()
    {
        if ($this->selectedMakeType()?->isPricedOnline() === false) {
            session()->put('inquiry.configuration', [
                'type' => $this->product->category->itemType()->value,
                'parameters' => [],
                'count' => 1,
                'summary' => $this->product->name.' · '.$this->selectedMakeType()->label($this->product->category).' · na poptávku',
                'calculated_at' => now()->toIso8601String(),
            ]);

            return $this->redirectRoute('web.inquiry.create');
        }

        $this->validate();

        $pricer = app(TextileProductPricer::class);
        $spec = $this->spec();

        if (! $this->product->hasPrice()) {
            // Látka zatím bez ceny: poptávka nese celé zadání, cenu doplní dílna.
            session()->put('inquiry.configuration', [
                'type' => $this->product->category->itemType()->value,
                'parameters' => $pricer->pieceParameters($this->product, $spec)->toArray(),
                'count' => (int) $this->count,
                'summary' => $this->summary(),
                'calculated_at' => now()->toIso8601String(),
            ]);

            return $this->redirectRoute('web.inquiry.create');
        }

        $result = $pricer->price($this->product, $spec);
        $vatRate = $pricer->vatRate();
        $count = (int) $this->count;

        session()->put('inquiry.configuration', [
            'type' => $this->product->category->itemType()->value,
            'parameters' => $pricer->pieceParameters($this->product, $spec)->toArray(),
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

    /**
     * Lidsky čitelný souhrn pro poptávku a admin.
     */
    public function summary(): string
    {
        return implode(' · ', array_filter([
            $this->product->name,
            ...array_map(fn (string $step) => $this->stepValue($step), self::STEPS),
            $this->count.' ks',
        ]));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        $rules = array_merge(...array_map(fn (string $step) => $this->stepRules($step), self::STEPS));
        $rules['count'] = ['required', 'integer', 'min:1', 'max:99'];

        return $rules;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function stepRules(string $step): array
    {
        return match ($step) {
            'type' => ['makeType' => ['required', Rule::enum(CurtainMakeType::class)]],
            'color' => ['color' => ['required', Rule::in($this->product->colors->pluck('name')->all())]],
            'hem' => ['hem' => ['required', Rule::enum(CurtainHem::class)]],
            'split' => ['split' => ['required', Rule::in(['one', 'two'])]],
            'dimensions' => [
                'width' => ['required', 'numeric', 'min:'.self::MIN_WIDTH, 'max:'.self::MAX_WIDTH],
                'height' => ['required', 'numeric', 'min:'.self::MIN_HEIGHT, 'max:'.$this->product->maxHeight()],
            ],
            'tape' => ['tape' => ['required', Rule::in(array_keys($this->tapeOptions()))]],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'makeType' => 'typ výrobku',
            'color' => 'barva',
            'hem' => 'způsob šití',
            'split' => 'opona',
            'width' => 'šířka',
            'height' => 'výška',
            'tape' => 'řasící páska',
            'count' => 'počet kusů',
        ];
    }

    protected function spec(): TextileCurtainSpec
    {
        [$ribbonCode, $ratio] = explode('|', $this->tape);

        return new TextileCurtainSpec(
            width: (float) $this->width,
            height: (float) $this->height,
            hem: CurtainHem::from($this->hem),
            split: $this->split === 'two',
            ribbonCode: $ribbonCode,
            ratio: (float) $ratio,
        );
    }

    protected function recalculate(): void
    {
        if (! $this->isComplete()) {
            $this->clearPrice();

            return;
        }

        $pricer = app(TextileProductPricer::class);
        $result = $pricer->price($this->product, $this->spec());
        $vatRate = $pricer->vatRate();

        $this->totalPrice = $result->totalPrice((int) $this->count);
        $this->totalPriceVat = $result->totalPriceWithVat($vatRate, (int) $this->count);
        $this->fabricMeters = round($result->consumedMeters * (int) $this->count, 2);
        $this->priceLines = $result->linesToArray();
    }

    protected function clearPrice(): void
    {
        $this->totalPrice = null;
        $this->totalPriceVat = null;
        $this->fabricMeters = null;
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
        return view('livewire.web.textile-product-configurator');
    }
}
