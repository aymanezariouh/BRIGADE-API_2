<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_only_see_active_categories_and_available_category_plates(): void
    {
        $customer = User::factory()->create();
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        Plate::factory()->create(['category_id' => $activeCategory->id, 'is_available' => true]);
        Plate::factory()->create(['category_id' => $activeCategory->id, 'is_available' => false]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.id', $activeCategory->id);

        $this->getJson("/api/categories/{$activeCategory->id}/plates")
            ->assertOk()
            ->assertJsonCount(1, 'plates');

        $this->getJson("/api/categories/{$inactiveCategory->id}")
            ->assertNotFound();
    }

    public function test_admin_can_manage_categories_and_customer_cannot_create_them(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        Sanctum::actingAs($customer);
        $this->postJson('/api/categories', [
            'name' => 'Desserts',
            'description' => 'Sweet dishes',
            'color' => '#FF5733',
            'is_active' => true,
        ])->assertForbidden();

        Sanctum::actingAs($admin);

        $createResponse = $this->postJson('/api/categories', [
            'name' => 'Desserts',
            'description' => 'Sweet dishes',
            'color' => '#FF5733',
            'is_active' => true,
        ]);

        $categoryId = $createResponse->json('category.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('category.name', 'Desserts');

        $this->putJson("/api/categories/{$categoryId}", [
            'name' => 'Healthy Desserts',
            'description' => 'Sweet but light dishes',
            'color' => '#00AA55',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('category.is_active', false);

        $this->deleteJson("/api/categories/{$categoryId}")
            ->assertOk();
    }
}
