<?php

namespace App\Models;

use App\Enums\ReviewOutcome;
use Database\Factories\OpportunityReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityReview extends Model
{
    /** @use HasFactory<OpportunityReviewFactory> */
    use HasFactory;

    protected $fillable = ['opportunity_id', 'reviewer_id', 'outcome', 'notes', 'checklist'];

    protected function casts(): array
    {
        return [
            'outcome' => ReviewOutcome::class,
            'checklist' => 'array',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
