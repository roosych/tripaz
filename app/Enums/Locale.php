<?php

namespace App\Enums;

enum Locale: string
{
    case Az = 'az';
    case Ru = 'ru';
    case En = 'en';

    /**
     * Fallback chain: requested → az → en → any.
     * Returns ordered list to try, excluding current.
     *
     * @return array<self>
     */
    public function fallbackChain(): array
    {
        return match($this) {
            self::Az => [self::En, self::Ru],
            self::Ru => [self::Az, self::En],
            self::En => [self::Az, self::Ru],
        };
    }
}
