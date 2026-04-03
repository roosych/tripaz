<?php

namespace App\Enums;

enum TourDifficulty: string
{
    case Easy        = 'easy';
    case Moderate    = 'moderate';
    case Challenging = 'challenging';
    case Expert      = 'expert';
}
