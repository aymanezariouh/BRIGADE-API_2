<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_plate_with_ingredients(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $ingredientA = Ingredient::factory()->create(['tags' => ['contains_gluten']]);
        $ingredientB = Ingredient::factory()->create(['tags' => ['contains_lactose']]);

        Sanctum::actingAs($admin);

        $createResponse = $this->postJson('/api/plates', [
            'name' => 'Pasta Alfredo',
            'description' => 'Creamy pasta',
            'price' => 12.5,
            'category_id' => $category->id,
            'ingredient_ids' => [$ingredientA->id, $ingredientB->id],
            'is_available' => true,
        ]);

        $plateId = $createResponse->json('plate.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('plate.category.id', $category->id)
            ->assertJsonCount(2, 'plate.ingredients');

        $this->putJson("/api/plates/{$plateId}", [
            'name' => 'Pasta Verde',
            'description' => 'Green pasta',
            'price' => 13.75,
            'category_id' => $category->id,
            'ingredient_ids' => [$ingredientA->id],
            'is_available' => false,
        ])->assertOk()
            ->assertJsonPath('plate.name', 'Pasta Verde')
            ->assertJsonCount(1, 'plate.ingredients');

        $this->deleteJson("/api/plates/{$plateId}")
            ->assertOk();

        $this->assertDatabaseCount('plats', 0);
    }

    public function test_customer_only_sees_available_plates_from_active_categories(): void
    {
        $customer = User::factory()->create();
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        $visiblePlate = Plate::factory()->create([
            'category_id' => $activeCategory->id,
            'is_available' => true,
        ]);
        $hiddenByAvailability = Plate::factory()->create([
            'category_id' => $activeCategory->id,
            'is_available' => false,
        ]);
        $hiddenByCategory = Plate::factory()->create([
            'category_id' => $inactiveCategory->id,
            'is_available' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/plates')
            ->assertOk()
            ->assertJsonCount(1, 'plates')
            ->assertJsonPath('plates.0.id', $visiblePlate->id);

        $this->getJson("/api/plates/{$visiblePlate->id}")
            ->assertOk();

        $this->getJson("/api/plates/{$hiddenByAvailability->id}")
            ->assertNotFound();

        $this->getJson("/api/plates/{$hiddenByCategory->id}")
            ->assertNotFound();
    }
}
