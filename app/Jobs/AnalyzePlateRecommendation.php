<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Services\GroqRecommendationService;
use App\Services\RecommendationAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzePlateRecommendation implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(private readonly int $recommendationId)
    {
    }

    public function handle(
        RecommendationAnalysisService $analysisService,
        GroqRecommendationService $groqRecommendationService
    ): void
    {
        $recommendation = Recommendation::query()
            ->with(['user.profile', 'plate.ingredients'])
            ->find($this->recommendationId);

        if (! $recommendation || ! $recommendation->plate || ! $recommendation->user) {
            return;
        }

        $analysis = $analysisService->analyze($recommendation->user, $recommendation->plate);
        $details = $analysis['details'];
        $details['warning_source'] = 'rules';
        $details['rule_based_warning_message'] = $analysis['warning_message'];

        $warningMessage = $analysis['warning_message'];
        $aiExplanation = $groqRecommendationService->generateWarningMessage(
            $recommendation->user,
            $recommendation->plate,
            $analysis
        );

        if ($aiExplanation) {
            $warningMessage = $aiExplanation['message'];
            $details['warning_source'] = 'groq';
            $details['ai_model'] = $aiExplanation['model'];
            $details['groq_response_id'] = $aiExplanation['response_id'];
        }

        $recommendation->update([
            'score' => $analysis['score'],
            'label' => $analysis['label'],
            'warning_message' => $warningMessage,
            'details' => $details,
            'status' => $analysis['status'],
        ]);
    }
}
