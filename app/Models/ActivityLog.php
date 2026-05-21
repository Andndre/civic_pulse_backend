<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'student_id',
    'title',
    'description',
    'type',
    'date',
    'location',
    'achievement',
    'points',
    'evidence_url',
    'status',
    'review_notes',
    'reviewed_by',
    'reviewed_at',
])]
class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date' => 'date:Y-m-d',
        'reviewed_at' => 'datetime',
        'points' => 'integer',
    ];

    /**
     * Get the student who submitted the activity.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher who reviewed the activity.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
