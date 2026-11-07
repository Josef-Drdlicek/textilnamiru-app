<?php

namespace App\Actions;

use App\Enums\InquiryStatus;
use App\Enums\ItemType;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

/**
 * Převezme poptávku z webu do rozpracované zakázky přiřazené studiu.
 * Draft pak projde standardním flow (konfigurace, odeslání, zamrazení cen) —
 * orientační cena z poptávky se nikdy nepřebírá jako závazná.
 */
class ConvertInquiryToOrder
{
    public function __construct(
        private readonly CreateDraftOrder $createDraftOrder,
        private readonly SaveOrderItem $saveOrderItem,
    ) {}

    /**
     * @return array{order: Order, itemSkipped: bool}
     */
    public function handle(Inquiry $inquiry, User $designer, string $orderName): array
    {
        if ($inquiry->isConverted()) {
            throw new LogicException('Poptávka už byla převedena na zakázku.');
        }

        return DB::transaction(function () use ($inquiry, $designer, $orderName): array {
            $order = $this->createDraftOrder->handle($designer, $orderName);

            $itemSkipped = false;

            if ($inquiry->configuration !== null) {
                $itemSkipped = ! $this->tryCreateItemFromSnapshot($order, $inquiry->configuration);
            }

            $order->update([
                'materials_note' => $this->contactNote($inquiry),
            ]);

            $inquiry->update([
                'order_id' => $order->id,
                'status' => InquiryStatus::Contacted,
            ]);

            return ['order' => $order, 'itemSkipped' => $itemSkipped];
        });
    }

    /**
     * Snapshot vznikl nad tehdejším ceníkem — stuha mohla mezitím zmizet,
     * pásma rolet se změnit. Při selhání položku přeskočíme; cena se stejně
     * počítá vždy znovu na serveru.
     *
     * @param  array<string, mixed>  $configuration
     */
    private function tryCreateItemFromSnapshot(Order $order, array $configuration): bool
    {
        try {
            $type = ItemType::from($configuration['type']);
            $parameters = $type->parametersClass()::fromArray($configuration['parameters']);

            $this->saveOrderItem->handle(
                order: $order,
                type: $type,
                parameters: $parameters,
                material: 'Dle poptávky — upřesnit se zákazníkem',
                count: max(1, (int) ($configuration['count'] ?? 1)),
                notes: isset($configuration['summary']) ? 'Z webové kalkulačky: '.$configuration['summary'] : null,
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function contactNote(Inquiry $inquiry): string
    {
        $lines = [
            'Poptávka z webu č. '.$inquiry->id,
            "Zákazník: {$inquiry->name}, {$inquiry->phone}, {$inquiry->email}",
        ];

        if ($inquiry->city !== null) {
            $lines[] = "Město: {$inquiry->city}";
        }

        if ($inquiry->measurement !== null) {
            $lines[] = 'Zaměření: '.$inquiry->measurement->label();
        }

        if ($inquiry->message !== null && trim($inquiry->message) !== '') {
            $lines[] = 'Zpráva: '.trim($inquiry->message);
        }

        return implode("\n", $lines);
    }
}
