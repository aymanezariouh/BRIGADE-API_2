<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminStatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_global_stats(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $category = Category::factory()->create([
            'is_active' => true,
            'user_id' => $admin->id,
        ]);
        $plate = Plate::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
            'user_id' => $admin->id,
        ]);
        Ingredient::factory()->count(2)->create();
        Recommendation::create([
            'user_id' => $customer->id,
            'plate_id' => $plate->id,
            'score' => 92,
            'label' => 'Highly Recommended',
            'warning_message' => null,
            'status' => 'ready',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonPath('stats.users.admins', 1)
            ->assertJsonPath('stats.users.customers', 1)
            ->assertJsonPath('stats.categories.active', 1)
            ->assertJsonPath('stats.plates.available', 1)
            ->assertJsonPath('stats.ingredients.total', 2)
            ->assertJsonPath('stats.recommendations.ready', 1);
    }

    public function test_customer_cannot_view_admin_stats(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/stats')
            ->assertForbidden();
    }
}
