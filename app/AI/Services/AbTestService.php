<?php

namespace App\AI\Services;

use App\Models\AbPromptResult;
use App\Models\AbPromptTest;

/**
 * Manages A/B testing between two system prompt variants.
 *
 * Assigns sessions to variant A or B based on traffic split percentage.
 * Records every assignment so results can be compared by positive vote rate.
 * Sits on top of PromptService - variants are just two prompt keys competing.
 */
class AbTestService
{
    public function __construct(
        private PromptService $promptService
    ) {}

    /**
     * Get the prompt content for this session.
     * Assigns a variant if none exists, returns the prompt for that variant.
     * Returns null if no active test exists.
     */
    public function getPromptForSession(string $sessionId, string $fallback = ''): ?string
    {
        $test = AbPromptTest::where('is_active', true)->first();

        if (! $test) {
            return null;
        }

        $result = AbPromptResult::firstOrCreate(
            ['ab_prompt_test_id' => $test->id, 'session_id' => $sessionId],
            [
                'variant' => $this->assignVariant($test),
                'prompt_key' => '',
            ]
        );

        if (empty($result->prompt_key)) {
            $promptKey = $result->variant === 'A' ? $test->prompt_key_a : $test->prompt_key_b;
            $result->update(['prompt_key' => $promptKey]);
        }

        return $this->promptService->get($result->prompt_key, $fallback);
    }

    /**
     * Record a feedback vote against the variant this session was assigned.
     * Links AiFeedback votes back to the A/B test result for comparison.
     */
    public function recordVote(string $sessionId, int $vote): void
    {
        AbPromptResult::where('session_id', $sessionId)
            ->whereNull('vote')
            ->latest()
            ->first()
            ?->update(['vote' => $vote]);
    }

    /**
     * Calculate positive vote rate for each variant in a test.
     * Returns percentage of positive votes (vote = 1) per variant.
     */
    public function getResults(string $testName): array
    {
        $test = AbPromptTest::where('name', $testName)->firstOrFail();

        return collect(['A', 'B'])->mapWithKeys(function ($variant) use ($test) {
            $results = $test->results()->where('variant', $variant)->whereNotNull('vote');
            $total = $results->count();
            $positive = $results->where('vote', 1)->count();

            return [$variant => [
                'total_votes' => $total,
                'positive_votes' => $positive,
                'positive_rate' => $total > 0 ? round($positive / $total * 100, 1) : null,
                'prompt_key' => $variant === 'A' ? $test->prompt_key_a : $test->prompt_key_b,
            ]];
        })->toArray();
    }

    /**
     * Randomly assign variant A or B based on the test's traffic split percentage.
     * traffic_split = 70 means 70% of sessions get variant A.
     */
    private function assignVariant(AbPromptTest $test): string
    {
        return rand(1, 100) <= $test->traffic_split ? 'A' : 'B';
    }
}
