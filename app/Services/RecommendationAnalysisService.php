<?php

namespace App\Services;

use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;

class RecommendationAnalysisService
{
    private const DIETARY_CONFLICT_MAP = [
        'vegan' => 'contains_meat',
        'no_sugar' => 'contains_sugar',
        'no_cholesterol' => 'contains_cholesterol',
        'gluten_free' => 'contains_gluten',
        'no_lactose' => 'contains_lactose',
    ];

    public function analyze(User $user, Plate $plate): array
    {
        $plate->loadMissing('ingredients');

        $dietaryTags = $this->dietaryTagsFor($user);
        $conflicts = [];

        foreach (self::DIETARY_CONFLICT_MAP as $dietaryTag => $ingredientTag) {
            if (! in_array($dietaryTag, $dietaryTags, true)) {
                continue;
            }

            $conflictingIngredients = $plate->ingredients
                ->filter(fn ($ingredient) => in_array($ingredientTag, $ingredient->tags ?? [], true))
                ->pluck('name')
                ->values()
                ->all();

            if ($conflictingIngredients === []) {
                continue;
            }

            $conflicts[] = [
                'dietary_tag' => $dietaryTag,
                'ingredient_tag' => $ingredientTag,
                'ingredients' => $conflictingIngredients,
            ];
        }

        $score = $this->scoreFor($dietaryTags, $conflicts);

        return [
            'score' => $score,
            'label' => $this->labelFor($score),
            'warning_message' => $this->warningMessageFor($conflicts, $score),
            'status' => Recommendation::STATUS_READY,
            'details' => [
                'dietary_tags' => $dietaryTags,
                'total_ingredients' => $plate->ingredients->count(),
                'conflict_count' => count($conflicts),
                'conflicts' => $conflicts,
            ],
        ];
    }

    private function dietaryTagsFor(User $user): array
    {
        $profile = $user->profile()->firstOrCreate(
            [],
            ['dietary_tags' => $user->dietary_tags ?? []]
        );

        return array_values(array_unique($profile->dietary_tags ?? $user->dietary_tags ?? []));
    }

    private function scoreFor(array $dietaryTags, array $conflicts): float
    {
        $restrictionCount = count($dietaryTags);

        if ($restrictionCount === 0) {
            return 100.0;
        }

        return round(max(0, (($restrictionCount - count($conflicts)) / $restrictionCount) * 100), 2);
    }

    private function labelFor(float $score): string
    {
        if ($score >= 80) {
            return Recommendation::LABEL_HIGHLY_RECOMMENDED;
        }

        if ($score >= 50) {
            return Recommendation::LABEL_RECOMMENDED_WITH_NOTES;
        }

        return Recommendation::LABEL_NOT_RECOMMENDED;
    }

    private function warningMessageFor(array $conflicts, float $score): ?string
    {
        if ($score >= 50 || $conflicts === []) {
            return null;
        }

        $parts = array_map(function (array $conflict) {
            $tag = str_replace('_', ' ', $conflict['dietary_tag']);
            $ingredients = implode(', ', $conflict['ingredients']);

            return "{$tag}: {$ingredients}";
        }, $conflicts);

        return 'This plate conflicts with your dietary profile: '.implode(' | ', $parts).'.';
    }
}
