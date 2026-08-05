<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case New = 'new';
    case Accepted = 'accepted';
    case ToInvoice = 'to_invoice';
    case Done = 'done';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::New],
            self::New => [self::Accepted],
            self::Accepted => [self::ToInvoice],
            self::ToInvoice => [self::Done],
            self::Done => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rozpracovaná',
            self::New => 'Nová',
            self::Accepted => 'Přijatá',
            self::ToInvoice => 'K fakturaci',
            self::Done => 'Hotová',
        };
    }

    /**
     * Text tlačítka, kterým admin posouvá zakázku DO tohoto stavu.
     */
    public function transitionActionLabel(): ?string
    {
        return match ($this) {
            self::Accepted => 'Přijmout',
            self::ToInvoice => 'Fakturace',
            self::Done => 'Uzavřít',
            default => null,
        };
    }

    /**
     * Stavy viditelné v administraci, v pořadí sekcí přehledu.
     *
     * @return list<self>
     */
    public static function adminSections(): array
    {
        return [self::New, self::Accepted, self::ToInvoice, self::Done];
    }
}
