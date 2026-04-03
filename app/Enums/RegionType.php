<?php

namespace App\Enums;

enum RegionType: string
{
    case Country  = 'country';
    case Region   = 'region';
    case District = 'district';
    case City     = 'city';
}
