<?php

namespace App\Enums;

/**
 * Stav poptávky z veřejného webu. Na rozdíl od OrderStatus bez vynucených
 * přechodů — dílna mění stav volně podle toho, jak komunikace probíhá.
 */
enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nová',
            self::Contacted => 'Kontaktovaná',
            self::Resolved => 'Vyřízená',
            self::Rejected => 'Zamítnutá',
        };
    }

    /**
     * Stavy viditelné v administraci, v pořadí sekcí přehledu.
     *
     * @return list<self>
     */
    public static function adminSections(): array
    {
        return [self::New, self::Contacted, self::Resolved, self::Rejected];
    }
}
