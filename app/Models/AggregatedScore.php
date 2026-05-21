<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'class_id',
    'period_start',
    'period_end',
    'period_type',
    'avg_cognitive_score',
    'pulse_p_score',
    'pulse_u_score',
    'pulse_l_score',
    'pulse_se_score',
])]
class AggregatedScore extends Model
{
    use HasFactory;

    protected $casts = [
        'period_start' => 'date:Y-m-d',
        'period_end' => 'date:Y-m-d',
        'avg_cognitive_score' => 'float',
        'pulse_p_score' => 'float',
        'pulse_u_score' => 'float',
        'pulse_l_score' => 'float',
        'pulse_se_score' => 'float',
    ];

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
