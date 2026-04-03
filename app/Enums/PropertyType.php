<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House     = 'house';
    case Villa     = 'villa';
    case Studio    = 'studio';
    case Cottage   = 'cottage';
}
