<?php

namespace App\Http\Controllers;

use App\Docs\RecommendationDocumentation;
use App\Jobs\AnalyzePlateRecommendation;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller implements RecommendationDocumentation
{
    public function analyze(Request $request, Plate $plate)
    {
        $this->abortIfHiddenForCustomer($request, $plate);

        $recommendation = Recommendation::create([
            'user_id' => $request->user()->id,
            'plate_id' => $plate->id,
            'score' => 0,
            'label' => null,
            'warning_message' => null,
            'details' => null,
            'status' => Recommendation::STATUS_PROCESSING,
        ]);

        AnalyzePlateRecommendation::dispatch($recommendation->id);

        return response()->json([
            'message' => 'Recommendation analysis started.',
            'recommendation' => $this->serializeRecommendation(
                $recommendation->fresh()->load('plate')
            ),
        ], 202);
    }

    public function index(Request $request)
    {
        $recommendations = Recommendation::query()
            ->with('plate.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Recommendation $recommendation) => $this->serializeRecommendation($recommendation));

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }

    public function show(Request $request, Plate $plate)
    {
        $this->abortIfHiddenForCustomer($request, $plate);

        $recommendation = Recommendation::query()
            ->with('plate.category')
            ->where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->latest()
            ->first();

        abort_if(! $recommendation, 404, 'Recommendation not found.');

        return response()->json([
            'recommendation' => $this->serializeRecommendation($recommendation),
        ]);
    }

    private function serializeRecommendation(Recommendation $recommendation): array
    {
        return [
            'id' => $recommendation->id,
            'score' => (float) $recommendation->score,
            'label' => $recommendation->label,
            'warning_message' => $recommendation->warning_message,
            'status' => $recommendation->status,
            'details' => $recommendation->details,
            'created_at' => $recommendation->created_at?->toJSON(),
            'updated_at' => $recommendation->updated_at?->toJSON(),
            'plate' => $recommendation->relationLoaded('plate') && $recommendation->plate
                ? [
                    'id' => $recommendation->plate->id,
                    'name' => $recommendation->plate->name,
                    'category_id' => $recommendation->plate->category_id,
                ]
                : null,
        ];
    }

    private function abortIfHiddenForCustomer(Request $request, Plate $plate): void
    {
        abort_if(
            ! $request->user()->isAdmin() && (
                ! $plate->is_available
                || ! $plate->category
                || ! $plate->category->is_active
            ),
            404,
            'Plate not found.'
        );
    }
}
