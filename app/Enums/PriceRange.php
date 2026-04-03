<?php

namespace App\Enums;

enum PriceRange: string
{
    case Budget     = 'budget';
    case Mid        = 'mid';
    case Upscale    = 'upscale';
    case FineDining = 'fine_dining';
}
