<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('favorites'));
        $this->assertTrue(Schema::hasColumns('favorites', [
            'id', 'user_id', 'listing_id', 'created_at',
        ]));
    }

    public function test_user_has_favorites_relationship(): void
    {
        $user    = User::factory()->create();
        $listing = Listing::factory()->create();

        \App\Models\Favorite::create([
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this->assertCount(1, $user->favorites);
        $this->assertEquals($listing->id, $user->favorites->first()->listing_id);
    }

    public function test_user_has_favorite_listings_relationship(): void
    {
        $user    = User::factory()->create();
        $listing = Listing::factory()->create();

        \App\Models\Favorite::create([
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this->assertCount(1, $user->favoriteListings);
        $this->assertEquals($listing->id, $user->favoriteListings->first()->id);
    }

    public function test_listing_has_favorited_by_relationship(): void
    {
        $user    = User::factory()->create();
        $listing = Listing::factory()->create();

        \App\Models\Favorite::create([
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this->assertCount(1, $listing->favoritedBy);
        $this->assertEquals($user->id, $listing->favoritedBy->first()->id);
    }

    public function test_unique_constraint_prevents_duplicate_favorites(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $user    = User::factory()->create();
        $listing = Listing::factory()->create();

        \App\Models\Favorite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);
        \App\Models\Favorite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);
    }

    private function makeUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_guest_cannot_store_favorite(): void
    {
        $listing = Listing::factory()->create();

        $this->post(route('dashboard.favorites.store', $listing))
             ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_store_favorite(): void
    {
        $user    = $this->makeUser();
        $listing = Listing::factory()->create();

        $this->actingAs($user)
             ->post(route('dashboard.favorites.store', $listing))
             ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_storing_favorite_twice_is_idempotent(): void
    {
        $user    = $this->makeUser();
        $listing = Listing::factory()->create();

        $this->actingAs($user)->post(route('dashboard.favorites.store', $listing));
        $this->actingAs($user)->post(route('dashboard.favorites.store', $listing));

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_authenticated_user_can_destroy_favorite(): void
    {
        $user    = $this->makeUser();
        $listing = Listing::factory()->create();

        Favorite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);

        $this->actingAs($user)
             ->delete(route('dashboard.favorites.destroy', $listing))
             ->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_destroy_is_scoped_to_authenticated_user(): void
    {
        $owner   = $this->makeUser();
        $other   = $this->makeUser();
        $listing = Listing::factory()->create();

        Favorite::create(['user_id' => $owner->id, 'listing_id' => $listing->id]);

        $this->actingAs($other)
             ->delete(route('dashboard.favorites.destroy', $listing));

        $this->assertDatabaseHas('favorites', [
            'user_id'    => $owner->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_favorites_index_returns_paginated_listings(): void
    {
        $user     = $this->makeUser();
        $listings = Listing::factory()->count(3)->create();

        foreach ($listings as $listing) {
            Favorite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);
        }

        $this->actingAs($user)
             ->get(route('dashboard.favorites.index'))
             ->assertOk()
             ->assertViewHas('favorites');
    }

    public function test_listing_index_exposes_is_favorited_for_authenticated_user(): void
    {
        $user    = $this->makeUser();
        $listing = Listing::factory()->create(['status' => 'published']);

        Favorite::create(['user_id' => $user->id, 'listing_id' => $listing->id]);

        $response = $this->actingAs($user)->get(route('listings.index'));
        $response->assertOk();

        $passedListings = $response->viewData('listings');
        $found = $passedListings->getCollection()->firstWhere('id', $listing->id);

        $this->assertNotNull($found);
        $this->assertTrue((bool) $found->is_favorited);
    }

    public function test_listing_index_is_favorited_false_for_non_favorited(): void
    {
        $user    = $this->makeUser();
        $listing = Listing::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user)->get(route('listings.index'));
        $response->assertOk();

        $passedListings = $response->viewData('listings');
        $found = $passedListings->getCollection()->firstWhere('id', $listing->id);

        $this->assertFalse((bool) ($found->is_favorited ?? false));
    }
}
