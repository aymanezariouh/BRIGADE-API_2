<?php

namespace Tests\Feature;

use App\Jobs\AnalyzePlateRecommendation;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Profile;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_analyze_a_plate_and_read_the_saved_result(): void
    {
        config()->set('queue.default', 'sync');

        $customer = User::factory()->create([
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ]);
        Profile::factory()->create([
            'user_id' => $customer->id,
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ]);
        $category = Category::factory()->create(['is_active' => true]);
        $plate = Plate::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
        ]);
        $gluten = Ingredient::factory()->create([
            'name' => 'Flour',
            'tags' => ['contains_gluten'],
        ]);
        $lactose = Ingredient::factory()->create([
            'name' => 'Cream',
            'tags' => ['contains_lactose'],
        ]);
        $plate->ingredients()->sync([$gluten->id, $lactose->id]);

        Sanctum::actingAs($customer);

        $this->postJson("/api/recommendations/analyze/{$plate->id}")
            ->assertAccepted()
            ->assertJsonPath('recommendation.status', Recommendation::STATUS_READY)
            ->assertJsonPath('recommendation.label', Recommendation::LABEL_NOT_RECOMMENDED)
            ->assertJsonPath('recommendation.details.conflict_count', 2);

        $this->getJson('/api/recommendations')
            ->assertOk()
            ->assertJsonCount(1, 'recommendations')
            ->assertJsonPath('recommendations.0.plate.id', $plate->id)
            ->assertJsonPath('recommendations.0.status', Recommendation::STATUS_READY);

        $this->getJson("/api/recommendations/{$plate->id}")
            ->assertOk()
            ->assertJsonPath('recommendation.score', 0)
            ->assertJsonPath('recommendation.details.conflicts.0.ingredient_tag', 'contains_gluten')
            ->assertJsonPath('recommendation.warning_message', 'This plate conflicts with your dietary profile: gluten free: Flour | no lactose: Cream.');

        $this->getJson("/api/plates/{$plate->id}")
            ->assertOk()
            ->assertJsonPath('plate.recommendation.label', Recommendation::LABEL_NOT_RECOMMENDED)
            ->assertJsonPath('plate.recommendation.details.conflict_count', 2);
    }

    public function test_analysis_is_queued_when_queue_is_not_running_inline(): void
    {
        Queue::fake();

        $customer = User::factory()->create();
        $category = Category::factory()->create(['is_active' => true]);
        $plate = Plate::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->postJson("/api/recommendations/analyze/{$plate->id}")
            ->assertAccepted()
            ->assertJsonPath('recommendation.status', Recommendation::STATUS_PROCESSING);

        Queue::assertPushed(AnalyzePlateRecommendation::class);
        $this->assertDatabaseHas('recommendations', [
            'user_id' => $customer->id,
            'plate_id' => $plate->id,
            'status' => Recommendation::STATUS_PROCESSING,
        ]);
    }

    public function test_ai_warning_message_is_used_when_groq_is_enabled(): void
    {
        config()->set('queue.default', 'sync');
        config()->set('services.groq.enabled', true);
        config()->set('services.groq.key', 'test-groq-key');
        config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');

        Http::fake([
            'https://api.groq.com/openai/v1/responses' => Http::response([
                'id' => 'resp_test_123',
                'output_text' => 'This plate is not recommended because Flour contains gluten and Cream contains lactose.',
            ], 200),
        ]);

        $customer = User::factory()->create([
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ]);
        Profile::factory()->create([
            'user_id' => $customer->id,
            'dietary_tags' => ['gluten_free', 'no_lactose'],
        ]);
        $category = Category::factory()->create(['is_active' => true]);
        $plate = Plate::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
        ]);
        $gluten = Ingredient::factory()->create([
            'name' => 'Flour',
            'tags' => ['contains_gluten'],
        ]);
        $lactose = Ingredient::factory()->create([
            'name' => 'Cream',
            'tags' => ['contains_lactose'],
        ]);
        $plate->ingredients()->sync([$gluten->id, $lactose->id]);

        Sanctum::actingAs($customer);

        $this->postJson("/api/recommendations/analyze/{$plate->id}")
            ->assertAccepted()
            ->assertJsonPath(
                'recommendation.warning_message',
                'This plate is not recommended because Flour contains gluten and Cream contains lactose.'
            )
            ->assertJsonPath('recommendation.details.warning_source', 'groq')
            ->assertJsonPath('recommendation.details.ai_model', 'openai/gpt-oss-20b');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.groq.com/openai/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-groq-key')
                && data_get($request->data(), 'model') === 'openai/gpt-oss-20b';
        });
    }

    public function test_customer_cannot_analyze_hidden_plate(): void
    {
        $customer = User::factory()->create();
        $category = Category::factory()->create(['is_active' => false]);
        $plate = Plate::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
        ]);

        Sanctum::actingAs($customer);

        $this->postJson("/api/recommendations/analyze/{$plate->id}")
            ->assertNotFound();
    }
}
