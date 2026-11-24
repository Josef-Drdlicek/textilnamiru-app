<?php

namespace App\Livewire\Web;

use App\Domain\Pricing\ShadePricer;
use App\Enums\InquiryProduct;
use App\Models\ShadeFabric;
use App\Models\ShadeVariant;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Výpočet ceny rolety na stránce produktu: zákazník zadá rozměr okna, vybere látku
 * (Textil pro rolety) a počet kusů. Cena se bere z cenové tabulky provedení
 * pro cenovou skupinu látky A–D (ShadePricer). Provedení se volí na stránce
 * nad kalkulačkou (?varianta=). Doplňkový sortiment přibude jako další krok v STEPS.
 */
class ShadeConfigurator extends Component
{
    /** Kroky průvodce v pořadí; klíč = název kroku v šabloně. */
    public const array STEPS = ['dimensions', 'fabric'];

    public ShadeVariant $variant;

    /** Pořadí provedení v produktu – pro odkaz „Upravit“ z poptávky. */
    #[Locked] // návštěvník ho nesmí měnit, jde do odkazu v poptávce
    public int $variantIndex = 0;

    public string $step = 'dimensions';

    public ?float $width = null;

    public ?float $height = null;

    public ?int $fabricId = null;

    public ?int $count = 1;

    // Výstupy kalkulace – jen pro zobrazení; snapshot pro poptávku vzniká znovu v sendToInquiry().
    public ?float $totalPrice = null;

    public ?float $totalPriceVat = null;

    /** Rozměr je v rozsahu, ale políčko ceníku ještě není vyplněné. */
    public bool $priceMissing = false;

    /** @var list<array{label: string, amount: float}> */
    public array $priceLines = [];

    public function mount(ShadeVariant $variant, int $variantIndex = 0): void
    {
        $this->variant = $variant->loadMissing('product');
        $this->variantIndex = $variantIndex;
    }

    /**
     * Látky provedení seřazené podle cenové skupiny.
     *
     * @return Collection<int, ShadeFabric>
     */
    #[Computed]
    public function fabrics(): Collection
    {
        return $this->variant->fabrics();
    }

    public function selectedFabric(): ?ShadeFabric
    {
        return $this->fabricId ? $this->fabrics->firstWhere('id', $this->fabricId) : null;
    }

    /**
     * Po výběru látky ukázat její fotku nahoře (resources/js/textile-gallery.js).
     */
    public function updatedFabricId(): void
    {
        if ($image = $this->selectedFabric()?->imageUrl()) {
            $this->dispatch('textile-color-changed', name: 'latka-'.$this->fabricId, image: $image);
        }
    }

    public function updated(string $property): void
    {
        // Nejdřív přepočet: validateOnly při chybě vyhodí výjimku a stará cena by zůstala vidět.
        $this->recalculate();

        if (in_array($property, ['width', 'height', 'count', 'fabricId'], true)) {
            $this->validateOnly($property, $this->rules());
        }

        // Užší látka už nemusí na novou šířku stačit.
        if ($property === 'width' && $this->fabricId !== null) {
            $this->validateOnly('fabricId', $this->rules());
        }
    }

    public function openStep(string $step): void
    {
        if (in_array($step, self::STEPS, true)) {
            $this->step = $step;
        }
    }

    public function next(): void
    {
        $this->validate($this->stepRules($this->step));

        $position = array_search($this->step, self::STEPS, true);
        $this->step = self::STEPS[$position + 1] ?? $this->step;
    }

    public function back(): void
    {
        $position = array_search($this->step, self::STEPS, true);
        $this->step = self::STEPS[max(0, $position - 1)];
    }

    public function hasPriceList(): bool
    {
        return $this->variant->prices()->exists();
    }

    public function stepTitle(string $step): string
    {
        return match ($step) {
            'dimensions' => 'Rozměry rolety',
            'fabric' => 'Látka',
        };
    }

    public function stepValue(string $step): ?string
    {
        return match ($step) {
            'dimensions' => $this->width && $this->height
                ? sprintf('%s × %s cm', $this->formatNumber($this->width), $this->formatNumber($this->height))
                : null,
            'fabric' => ($fabric = $this->selectedFabric())
                ? sprintf('%s · skupina %s', $fabric->name, $fabric->price_category->value)
                : null,
            default => null,
        };
    }

    /**
     * Uloží serverový snapshot konfigurace do session a přejde na poptávku.
     */
    public function sendToInquiry()
    {
        $this->validate();

        $pricer = app(ShadePricer::class);
        $count = (int) $this->count;
        $fabric = $this->selectedFabric();
        $result = $fabric ? $pricer->price($this->variant, $fabric, (float) $this->width, (float) $this->height) : null;

        $configuration = [
            // Rolety nemají typ položky výroby (ItemType) – poptávka je pozná podle „product“.
            'type' => null,
            'product' => InquiryProduct::Shades->value,
            'parameters' => [
                'variant' => $this->variant->product->name.' – '.$this->variant->name,
                'fabric' => $fabric ? sprintf('%s (skupina %s)', $fabric->name, $fabric->price_category->value) : null,
                'width' => (float) $this->width,
                'height' => (float) $this->height,
            ],
            'count' => $count,
            'summary' => $this->summary(),
            'edit_url' => route('web.rolety.show', [$this->variant->product, 'varianta' => $this->variantIndex]).'#vypocet-ceny',
            'calculated_at' => now()->toIso8601String(),
        ];

        if ($result !== null) {
            $vatRate = $pricer->vatRate();
            $configuration += [
                'unit_price' => $result->unitPrice,
                'unit_price_vat' => $result->unitPriceWithVat($vatRate),
                'total_price_vat' => $result->totalPriceWithVat($vatRate, $count),
                'vat_rate' => $vatRate,
                'price_lines' => $result->linesToArray(),
            ];
        }

        session()->put('inquiry.configuration', $configuration);

        return $this->redirectRoute('web.inquiry.create');
    }

    public function summary(): string
    {
        return implode(' · ', array_filter([
            $this->variant->product->name,
            $this->variant->name,
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
            'dimensions' => [
                'width' => ['required', 'numeric', 'min:'.$this->variant->min_width, 'max:'.$this->variant->max_width],
                'height' => ['required', 'numeric', 'min:'.$this->variant->min_height, 'max:'.$this->variant->max_height],
            ],
            // Bez látek v nabídce jde poslat poptávku bez látky (vzorník ukáže dílna).
            'fabric' => ['fabricId' => [
                Rule::requiredIf(fn () => $this->fabrics->isNotEmpty()),
                'nullable',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) {
                    $fabric = $this->fabrics->firstWhere('id', (int) $value);

                    if ($fabric === null) {
                        $fail('Vyberte látku z nabídky.');
                    } elseif (is_numeric($this->width) && ! $fabric->fitsWidth((float) $this->width)) {
                        $fail("Látka {$fabric->name} je jen do šířky {$fabric->roll_width} cm. Vyberte jinou nebo zmenšete šířku.");
                    }
                },
            ]],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'width' => 'šířka',
            'height' => 'výška',
            'fabricId' => 'látka',
            'count' => 'počet kusů',
        ];
    }

    protected function recalculate(): void
    {
        $this->clearPrice();

        if (! $this->hasValidInput() || $this->selectedFabric() === null) {
            return;
        }

        $pricer = app(ShadePricer::class);
        $result = $pricer->price($this->variant, $this->selectedFabric(), (float) $this->width, (float) $this->height);

        if ($result === null) {
            $this->priceMissing = true;

            return;
        }

        $this->totalPrice = $result->totalPrice((int) $this->count);
        $this->totalPriceVat = $result->totalPriceWithVat($pricer->vatRate(), (int) $this->count);
        $this->priceLines = $result->linesToArray();
    }

    protected function clearPrice(): void
    {
        $this->totalPrice = null;
        $this->totalPriceVat = null;
        $this->priceMissing = false;
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
        return view('livewire.web.shade-configurator');
    }
}
