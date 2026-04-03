<?php

namespace App\Enums;

enum ListingType: string
{
    case Hotel      = 'hotel';
    case Home       = 'home';
    case Tour       = 'tour';
    case Activity   = 'activity';
    case Guide      = 'guide';
    case Restaurant = 'restaurant';

    public function label(): string
    {
        return match($this) {
            self::Hotel      => 'Hotel',
            self::Home       => 'Home / Apartment',
            self::Tour       => 'Tour',
            self::Activity   => 'Activity',
            self::Guide      => 'Guide',
            self::Restaurant => 'Restaurant',
        };
    }
}
