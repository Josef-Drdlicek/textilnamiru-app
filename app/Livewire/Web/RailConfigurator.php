<?php

namespace App\Livewire\Web;

use App\Domain\Pricing\RailPricer;
use App\Enums\InquiryProduct;
use App\Enums\RailMounting;
use App\Models\RailVariant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Výpočet ceny kolejnice či dekorační garnýže na stránce produktu: zákazník vybere
 * držák (stropní / stěnový), zadá délku a počet kusů. Cena se bere z Ceníku kolejnic
 * (sestava × držák × pásmo po 50 cm do 6 m, RailPricer). Sestava / barva garnýže
 * se volí na stránce nad kalkulačkou (?varianta=).
 */
class RailConfigurator extends Component
{
    public RailVariant $variant;

    /** Pořadí varianty v produktu – pro odkaz „Upravit“ z poptávky. */
    public int $variantIndex = 0;

    public string $mounting = '';

    public ?float $length = null;

    public ?int $count = 1;

    // Výstupy kalkulace – jen pro zobrazení; snapshot pro poptávku vzniká znovu v sendToInquiry().
    public ?float $totalPrice = null;

    public ?float $totalPriceVat = null;

    /** Délka je v rozsahu, ale cena pásma ještě není vyplněná. */
    public bool $priceMissing = false;

    /** @var list<array{label: string, amount: float}> */
    public array $priceLines = [];

    public function mount(RailVariant $variant, int $variantIndex = 0): void
    {
        $this->variant = $variant->loadMissing('product');
        $this->variantIndex = $variantIndex;

        // Jediný možný držák (např. sádrokarton jen do stropu) rovnou vybrat.
        $options = $this->variant->product->mountingOptions();
        if (count($options) === 1) {
            $this->mounting = $options[0]->value;
        }
    }

    /**
     * @return list<RailMounting>
     */
    public function mountingOptions(): array
    {
        return $this->variant->product->mountingOptions();
    }

    public function selectedMounting(): ?RailMounting
    {
        return RailMounting::tryFrom($this->mounting);
    }

    public function updated(string $property): void
    {
        // Nejdřív přepočet: validateOnly při chybě vyhodí výjimku a stará cena by zůstala vidět.
        $this->recalculate();

        if (in_array($property, ['length', 'count', 'mounting'], true)) {
            $this->validateOnly($property, $this->rules());
        }
    }

    public function hasPriceList(): bool
    {
        return $this->variant->prices()->exists();
    }

    /**
     * Pásmo, do kterého zadaná délka spadá, např. „101–150 cm“.
     */
    public function bandLabel(): ?string
    {
        if (! is_numeric($this->length) || $this->length <= 0 || $this->length > RailPricer::MAX_LENGTH) {
            return null;
        }

        return RailPricer::bandLabel((int) (ceil($this->length / RailPricer::BAND) * RailPricer::BAND));
    }

    public function sendToInquiry()
    {
        $this->validate();

        $pricer = app(RailPricer::class);
        $count = (int) $this->count;
        $mounting = RailMounting::from($this->mounting);
        $result = $pricer->price($this->variant, $mounting, (float) $this->length);

        $configuration = [
            // Kolejnice nemají typ položky výroby (ItemType) – poptávka je pozná podle „product“.
            'type' => null,
            'product' => InquiryProduct::Rails->value,
            'parameters' => [
                'variant' => $this->variant->product->name.' – '.$this->variant->name,
                'mounting' => $mounting->label(),
                'length' => (float) $this->length,
            ],
            'count' => $count,
            'summary' => $this->summary(),
            'edit_url' => route('web.zaveseni.show', [$this->variant->product, 'varianta' => $this->variantIndex]).'#vypocet-ceny',
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
            $this->selectedMounting()?->label(),
            is_numeric($this->length) ? rtrim(rtrim(number_format((float) $this->length, 1, ',', ' '), '0'), ',').' cm' : null,
            $this->count.' ks',
        ]));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'mounting' => ['required', Rule::in(array_map(fn (RailMounting $mounting) => $mounting->value, $this->mountingOptions()))],
            'length' => ['required', 'numeric', 'min:1', 'max:'.RailPricer::MAX_LENGTH],
            'count' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ['mounting' => 'držák', 'length' => 'délka', 'count' => 'počet kusů'];
    }

    protected function recalculate(): void
    {
        $this->totalPrice = null;
        $this->totalPriceVat = null;
        $this->priceMissing = false;
        $this->priceLines = [];

        $data = ['mounting' => $this->mounting, 'length' => $this->length, 'count' => $this->count];
        if (Validator::make($data, $this->rules())->fails()) {
            return;
        }

        $pricer = app(RailPricer::class);
        $result = $pricer->price($this->variant, RailMounting::from($this->mounting), (float) $this->length);

        if ($result === null) {
            $this->priceMissing = true;

            return;
        }

        $this->totalPrice = $result->totalPrice((int) $this->count);
        $this->totalPriceVat = $result->totalPriceWithVat($pricer->vatRate(), (int) $this->count);
        $this->priceLines = $result->linesToArray();
    }

    public function render(): View
    {
        return view('livewire.web.rail-configurator');
    }
}
