<?php

return [
    'allowed_mimes' => env('LISTING_PHOTO_MIMES', 'jpeg,jpg'),
    'max_kb'        => (int) env('LISTING_PHOTO_MAX_KB', 5120), // 5 MB default
    'disk'          => env('LISTING_MEDIA_DISK', 'public'),
];
