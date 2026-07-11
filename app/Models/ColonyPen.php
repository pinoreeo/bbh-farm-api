<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColonyPen extends Model
{
    protected $table = 'animal_pens';

    protected $fillable = [
        'pen_code',
        'colony_type',
        'location',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function breedingPeriods(): HasMany
    {
        return $this->hasMany(BreedingPeriod::class, 'colony_pen_id');
    }
}
