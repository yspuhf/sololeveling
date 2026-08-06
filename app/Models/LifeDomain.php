<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeDomain extends Model
{
    protected $fillable = [
        'user_id',
        'health_physical_score',
        'health_mental_score',
        'finance_score',
        'relationship_score',
        'career_score',
        'spirituality_score',
        'overall_life_score',
        'metrics_data',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'health_physical_score' => 'integer',
        'health_mental_score' => 'integer',
        'finance_score' => 'integer',
        'relationship_score' => 'integer',
        'career_score' => 'integer',
        'spirituality_score' => 'integer',
        'overall_life_score' => 'integer',
        'metrics_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to sum metrics for a domain, capping at 100.
     */
    public function calculateScoreFromMetrics(string $domainKey): int
    {
        if (empty($this->metrics_data) || !isset($this->metrics_data[$domainKey])) {
            $col = [
                'physical' => 'health_physical_score',
                'mental' => 'health_mental_score',
                'finance' => 'finance_score',
                'relationship' => 'relationship_score',
                'career' => 'career_score',
                'spirituality' => 'spirituality_score'
            ][$domainKey];
            return $this->getAttribute($col) ?? 20;
        }

        $sum = 0;
        foreach ($this->metrics_data[$domainKey] as $metric) {
            $isCompleted = isset($metric['completed']) ? (bool) $metric['completed'] : (isset($metric['score']) && $metric['score'] > 0);
            if ($isCompleted) {
                $sum += $metric['score'] ?? 20;
            }
        }
        return min(100, $sum);
    }

    /**
     * Recalculates the overall life score by averaging the domains.
     */
    public function recalculateOverallScore(): void
    {
        if (!empty($this->metrics_data)) {
            $this->health_physical_score = $this->calculateScoreFromMetrics('physical');
            $this->health_mental_score = $this->calculateScoreFromMetrics('mental');
            $this->finance_score = $this->calculateScoreFromMetrics('finance');
            $this->relationship_score = $this->calculateScoreFromMetrics('relationship');
            $this->career_score = $this->calculateScoreFromMetrics('career');
            $this->spirituality_score = $this->calculateScoreFromMetrics('spirituality');
        }

        // Straight average of 6 stats
        $calculated = (
            $this->health_physical_score +
            $this->health_mental_score +
            $this->finance_score +
            $this->relationship_score +
            $this->career_score +
            $this->spirituality_score
        ) / 6;

        $this->overall_life_score = (int) round($calculated);
        $this->save();
    }

    /**
     * Get the Hunter Rank translation.
     */
    public function getLifeRankAttribute(): string
    {
        $score = $this->overall_life_score;

        if ($score < 40) return 'E-Rank';
        if ($score < 55) return 'D-Rank';
        if ($score < 75) return 'C-Rank';
        if ($score < 85) return 'B-Rank';
        if ($score < 95) return 'A-Rank';
        return 'S-Rank';
    }
}
