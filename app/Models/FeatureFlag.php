<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['feature_key', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
