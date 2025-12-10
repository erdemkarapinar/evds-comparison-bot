<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvdsRate extends Model
{
    protected $fillable = [
        'series',
        'date',
        'value',
    ];
}
