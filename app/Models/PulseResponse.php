<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'pulse_instrument_id', 'score'])]
class PulseResponse extends Model
{
    use HasFactory;

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Get the student who responded.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the pulse instrument responded to.
     */
    public function pulseInstrument(): BelongsTo
    {
        return $this->belongsTo(PulseInstrument::class, 'pulse_instrument_id');
    }
}
