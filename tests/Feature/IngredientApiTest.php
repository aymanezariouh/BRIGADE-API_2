<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IngredientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_ingredients(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $createResponse = $this->postJson('/api/ingredients', [
            'name' => 'Cheese',
            'tags' => ['contains_lactose'],
        ]);

        $ingredientId = $createResponse->json('ingredient.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('ingredient.name', 'Cheese');

        $this->getJson('/api/ingredients')
            ->assertOk()
            ->assertJsonCount(1, 'ingredients');

        $this->putJson("/api/ingredients/{$ingredientId}", [
            'name' => 'Aged Cheese',
            'tags' => ['contains_lactose', 'contains_cholesterol'],
        ])->assertOk()
            ->assertJsonPath('ingredient.tags.1', 'contains_cholesterol');

        $this->deleteJson("/api/ingredients/{$ingredientId}")
            ->assertOk();

        $this->assertDatabaseCount('ingredients', 0);
    }

    public function test_customer_cannot_access_ingredient_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/ingredients')->assertForbidden();

        $this->postJson('/api/ingredients', [
            'name' => 'Sugar',
            'tags' => ['contains_sugar'],
        ])->assertForbidden();
    }
}
