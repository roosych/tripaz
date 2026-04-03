<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->enum('cast', ['string', 'integer', 'float', 'boolean', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->string('group', 50)->default('general');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            [
                'key'        => 'free_published_listings_limit',
                'value'      => '2',
                'cast'       => 'integer',
                'group'      => 'monetization',
                'is_public'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'price_per_extra_listing',
                'value'      => '25.00',
                'cast'       => 'float',
                'group'      => 'monetization',
                'is_public'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'listing_payment_currency',
                'value'      => 'AZN',
                'cast'       => 'string',
                'group'      => 'monetization',
                'is_public'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
