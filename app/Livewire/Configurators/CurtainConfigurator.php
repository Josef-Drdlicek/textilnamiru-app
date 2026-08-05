<?php

namespace App\Livewire\Configurators;

use App\Domain\Pricing\Parameters\CurtainParameters;
use App\Domain\Pricing\Parameters\ItemParameters;
use App\Enums\ItemType;
use App\Enums\RightLeft;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ribbon;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;

class CurtainConfigurator extends AbstractConfigurator
{
    public ?float $width = null;

    public ?float $height = null;

    public ?float $materialWidth = null;

    public string $ribbonCode = '';

    public ?string $ratio = null;

    public ?float $ratioCustom = null;

    public bool $kanyr = false;

    public ?float $kanyrHeight = null;

    public string $rightLeft = RightLeft::Right->value;

    public bool $corners = false;

    public bool $bottom = false;

    public bool $narasit = false;

    public bool $snails = false;

    public string $room = '';

    public function itemType(): ItemType
    {
        return ItemType::Curtain;
    }

    public function mount(Order $order, ?OrderItem $item = null): void
    {
        if ($this->ribbonCode === '') {
            $firstRibbon = $this->ribbons->first();
            $this->ribbonCode = $firstRibbon?->code ?? '';
            $this->ratio = $this->firstAllowedRatio($firstRibbon);
        }

        parent::mount($order, $item);

        if ($item !== null) {
            $this->room = $item->room ?? '';
        }
    }

    /**
     * @return Collection<int, Ribbon>
     */
    #[Computed]
    public function ribbons(): Collection
    {
        return Ribbon::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    #[Computed]
    public function selectedRibbon(): ?Ribbon
    {
        return $this->ribbons->firstWhere('code', $this->ribbonCode);
    }

    /**
     * Po změně stuhy nabídnout jen její povolené poměry řasení.
     */
    public function updatedRibbonCode(): void
    {
        $ribbon = $this->selectedRibbon;

        $this->ratio = $this->firstAllowedRatio($ribbon);
        $this->ratioCustom = null;

        unset($this->selectedRibbon);

        $this->recalculate();
    }

    protected function roomValue(): ?string
    {
        return trim($this->room) !== '' ? trim($this->room) : null;
    }

    protected function typeRules(): array
    {
        $usesCustomRatio = (bool) $this->selectedRibbon?->allows_custom_ratio;

        return [
            'width' => ['required', 'numeric', 'min:10', 'max:2000'],
            'height' => ['required', 'numeric', 'min:10', 'max:500'],
            'materialWidth' => ['required', 'numeric', 'min:10', 'max:1000'],
            'ribbonCode' => ['required', Rule::exists('ribbons', 'code')->where('is_active', true)],
            'ratio' => [
                Rule::requiredIf(! $usesCustomRatio),
                'nullable',
                'numeric',
                $this->ratioAllowedRule(),
            ],
            'ratioCustom' => [Rule::requiredIf($usesCustomRatio), 'nullable', 'numeric', 'min:1', 'max:10'],
            'kanyr' => ['boolean'],
            'kanyrHeight' => [Rule::requiredIf($this->kanyr), 'nullable', 'numeric', 'min:1', 'max:200'],
            'rightLeft' => ['required', Rule::enum(RightLeft::class)],
            'corners' => ['boolean'],
            'bottom' => ['boolean'],
            'narasit' => ['boolean'],
            'snails' => ['boolean'],
            'room' => ['nullable', 'string', 'max:100'],
            'count' => ['required', 'integer', 'min:1', 'max:999', $this->evenCountForMirrorRule()],
        ];
    }

    protected function typeValidationAttributes(): array
    {
        return [
            'width' => 'hotová šířka',
            'height' => 'hotová výška',
            'materialWidth' => 'šířka materiálu',
            'ribbonCode' => 'řasící stuha',
            'ratio' => 'poměr řasení',
            'ratioCustom' => 'vlastní poměr řasení',
            'kanyrHeight' => 'výška kanýru',
            'rightLeft' => 'provedení',
            'room' => 'místnost',
        ];
    }

    protected function buildParameters(): ItemParameters
    {
        return CurtainParameters::fromArray($this->fabricParameterData());
    }

    protected function fillFromParameters(ItemParameters $parameters): void
    {
        $this->fillFabricFields($parameters->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    protected function fabricParameterData(): array
    {
        return [
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'material_width' => (float) $this->materialWidth,
            'ribbon_code' => $this->ribbonCode,
            'ratio' => $this->effectiveRatio(),
            'kanyr' => $this->kanyr,
            'kanyr_height' => $this->kanyr ? (float) $this->kanyrHeight : null,
            'right_left' => $this->rightLeft,
            'corners' => $this->corners,
            'bottom' => $this->bottom,
            'narasit' => $this->narasit,
            'snails' => $this->snails,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function fillFabricFields(array $data): void
    {
        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->materialWidth = $data['material_width'];
        $this->ribbonCode = $data['ribbon_code'];
        $this->kanyr = $data['kanyr'];
        $this->kanyrHeight = $data['kanyr_height'];
        $this->rightLeft = $data['right_left'];
        $this->corners = $data['corners'];
        $this->bottom = $data['bottom'];
        $this->narasit = $data['narasit'];
        $this->snails = $data['snails'];

        if ($this->selectedRibbon?->allows_custom_ratio) {
            $this->ratioCustom = $data['ratio'];
            $this->ratio = null;
        } else {
            $this->ratio = $this->formatRatio($data['ratio']);
            $this->ratioCustom = null;
        }
    }

    protected function effectiveRatio(): float
    {
        return $this->selectedRibbon?->allows_custom_ratio
            ? (float) $this->ratioCustom
            : (float) $this->ratio;
    }

    protected function firstAllowedRatio(?Ribbon $ribbon): ?string
    {
        if ($ribbon === null || $ribbon->allows_custom_ratio) {
            return null;
        }

        $first = $ribbon->allowed_ratios[0] ?? null;

        return $first !== null ? $this->formatRatio($first) : null;
    }

    protected function formatRatio(float|int|string $ratio): string
    {
        return rtrim(rtrim(number_format((float) $ratio, 2, '.', ''), '0'), '.');
    }

    private function ratioAllowedRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $ribbon = $this->selectedRibbon;

            if ($ribbon === null || $ribbon->allows_custom_ratio || $value === null) {
                return;
            }

            $allowed = array_map(floatval(...), $ribbon->allowed_ratios);

            if (! in_array((float) $value, $allowed, true)) {
                $fail('Zvolený poměr řasení není pro tuto stuhu k dispozici.');
            }
        };
    }

    private function evenCountForMirrorRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($this->rightLeft === RightLeft::Mirror->value && (int) $value % 2 !== 0) {
                $fail('Při zrcadlovém provedení musí být počet kusů sudý.');
            }
        };
    }

    public function render(): View
    {
        return view('livewire.configurators.curtain');
    }
}
