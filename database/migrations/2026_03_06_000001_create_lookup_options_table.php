<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookup_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();        // e.g. 'language', 'cuisine_type', 'property_type', 'difficulty'
            $table->string('value', 100);               // stored value, e.g. 'az', 'azerbaijani', 'apartment'
            $table->string('label', 255);               // display label, e.g. 'Azərbaycanca', 'Apartment'
            $table->smallInteger('sort_order')->default(0);

            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_options');
    }
};
