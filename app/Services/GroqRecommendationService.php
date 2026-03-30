<?php

namespace App\Services;

use App\Models\Plate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GroqRecommendationService
{
    public function generateWarningMessage(User $user, Plate $plate, array $analysis): ?array
    {
        $fallbackWarning = $analysis['warning_message'] ?? null;

        if (! $fallbackWarning || ! $this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.groq.base_url'), '/'))
                ->withToken(config('services.groq.key'))
                ->acceptJson()
                ->contentType('application/json')
                ->timeout(config('services.groq.timeout', 30))
                ->post('/responses', [
                    'model' => config('services.groq.model', 'openai/gpt-oss-20b'),
                    'input' => $this->promptFor($user, $plate, $analysis),
                ])->throw()->json();

            $message = $this->extractOutputText($response);

            if (! $message) {
                return null;
            }

            return [
                'message' => $message,
                'model' => config('services.groq.model', 'openai/gpt-oss-20b'),
                'response_id' => data_get($response, 'id'),
            ];
        } catch (Throwable $exception) {
            Log::warning('Groq recommendation explanation failed.', [
                'plate_id' => $plate->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.groq.enabled')
            && filled(config('services.groq.key'));
    }

    private function promptFor(User $user, Plate $plate, array $analysis): string
    {
        return implode("\n", [
            'You are writing a short food compatibility warning for a restaurant app.',
            'Write one short sentence in plain English.',
            'Mention only the important dietary conflicts and ingredient names.',
            'Do not use markdown, bullets, emojis, or percentages.',
            'If the data shows serious conflicts, make the warning direct but clear.',
            '',
            'DATA:',
            json_encode([
                'user_id' => $user->id,
                'dietary_tags' => data_get($analysis, 'details.dietary_tags', []),
                'plate' => [
                    'id' => $plate->id,
                    'name' => $plate->name,
                ],
                'score' => $analysis['score'],
                'label' => $analysis['label'],
                'conflicts' => data_get($analysis, 'details.conflicts', []),
                'fallback_warning_message' => $analysis['warning_message'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function extractOutputText(array $response): ?string
    {
        $outputText = data_get($response, 'output_text');

        if (is_string($outputText) && trim($outputText) !== '') {
            return trim($outputText);
        }

        $content = collect(data_get($response, 'output', []))
            ->flatMap(fn (array $item) => data_get($item, 'content', []))
            ->map(fn (array $contentItem) => data_get($contentItem, 'text'))
            ->filter(fn ($text) => is_string($text) && trim($text) !== '')
            ->implode("\n");

        return $content !== '' ? trim($content) : null;
    }
}
