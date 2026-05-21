<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['learning_material_id', 'dimension', 'statement'])]
class PulseInstrument extends Model
{
    use HasFactory;

    /**
     * Get the learning material for this pulse instrument.
     */
    public function learningMaterial(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }

    /**
     * Get responses for this instrument.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(PulseResponse::class, 'pulse_instrument_id');
    }
}
