<?php

namespace Database\Seeders;

use App\Models\LookupOption;
use Illuminate\Database\Seeder;

class LookupOptionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        // ----------------------------------------------------------------
        // Languages (ISO-639-1 codes, used by Guide detail)
        // ----------------------------------------------------------------
        foreach ([
            ['az', 'Azərbaycanca'],
            ['ru', 'Русский'],
            ['en', 'English'],
            ['tr', 'Türkçe'],
            ['ar', 'العربية'],
            ['fr', 'Français'],
            ['de', 'Deutsch'],
            ['es', 'Español'],
            ['it', 'Italiano'],
            ['zh', '中文'],
            ['ja', '日本語'],
            ['ko', '한국어'],
            ['fa', 'فارسی'],
        ] as $i => [$value, $label]) {
            $rows[] = ['type' => 'language', 'value' => $value, 'label' => $label, 'sort_order' => $i];
        }

        // ----------------------------------------------------------------
        // Cuisine types (used by Restaurant detail)
        // ----------------------------------------------------------------
        foreach ([
            ['azerbaijani', 'Azerbaijani'],
            ['turkish',     'Turkish'],
            ['russian',     'Russian'],
            ['italian',     'Italian'],
            ['asian',       'Asian'],
            ['european',    'European'],
            ['chinese',     'Chinese'],
            ['japanese',    'Japanese'],
            ['indian',      'Indian'],
            ['fast_food',   'Fast Food'],
        ] as $i => [$value, $label]) {
            $rows[] = ['type' => 'cuisine_type', 'value' => $value, 'label' => $label, 'sort_order' => $i];
        }

        // ----------------------------------------------------------------
        // Property types (used by Home detail)
        // ----------------------------------------------------------------
        foreach ([
            ['apartment', 'Apartment'],
            ['house',     'House'],
            ['villa',     'Villa'],
            ['studio',    'Studio'],
            ['cottage',   'Cottage'],
        ] as $i => [$value, $label]) {
            $rows[] = ['type' => 'property_type', 'value' => $value, 'label' => $label, 'sort_order' => $i];
        }

        // ----------------------------------------------------------------
        // Difficulty levels (used by Tour and Activity details)
        // ----------------------------------------------------------------
        foreach ([
            ['easy',        'Easy'],
            ['moderate',    'Moderate'],
            ['challenging', 'Challenging'],
            ['expert',      'Expert'],
        ] as $i => [$value, $label]) {
            $rows[] = ['type' => 'difficulty', 'value' => $value, 'label' => $label, 'sort_order' => $i];
        }

        // Upsert — safe to re-run
        foreach ($rows as $row) {
            LookupOption::updateOrCreate(
                ['type' => $row['type'], 'value' => $row['value']],
                ['label' => $row['label'], 'sort_order' => $row['sort_order']],
            );
        }
    }
}
