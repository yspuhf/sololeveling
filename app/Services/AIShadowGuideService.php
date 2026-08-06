<?php

namespace App\Services;

use App\Models\User;

class AIShadowGuideService
{
    /**
     * Generate the daily coaching message for the hunter.
     * Ready for LLM integration.
     */
    public function generateDailyCoachingMessage(User $user): string
    {
        $rank = $user->rank;
        $level = $user->level;
        $name = $user->name;

        // Custom gamified fallback message
        return "System Notification: Hello Hunter {$name}. Your current status is [{$rank}] at Level {$level}. The system detects that consistency is the key to unlocking your true potential. Do not falter on today's contracts. Remember, the shadows are watching your progress. Wake up and ARISE.";
    }

    /**
     * Assess weekly growth patterns based on the checked-in Life Domains.
     * Ready for LLM analysis of domain scores.
     */
    public function assessWeeklyGrowthPatterns(User $user): array
    {
        $domain = $user->lifeDomain;

        if (!$domain) {
            return [
                'assessment' => 'No life domain data available yet. Complete your first evaluation to begin growth tracking.',
                'strengths' => [],
                'weaknesses' => [],
                'recommendation' => 'Visit the Life Score visualizer to initialize your profile.',
            ];
        }

        // Return a mock structured analysis representing what an LLM would yield
        return [
            'assessment' => "Hunter {$user->name} has maintained a stable performance. Health and Career domains show high potential, while Finance requires focus.",
            'strengths' => [
                'Health score is strong (' . max($domain->health_physical_score, $domain->health_mental_score) . '/100)',
                'Career engagement is rising (' . $domain->career_score . '/100)',
            ],
            'weaknesses' => [
                'Finance is lagging behind (' . $domain->finance_score . '/100). Double your gold rewards or start a career contract.',
                'Relationships score can be improved (' . $domain->relationship_score . '/100).',
            ],
            'recommendation' => "Focus on accepting Medium difficulty career and physical health contracts this week. System recommends allocating 5 skill points into Innovative Thinking.",
        ];
    }
}
