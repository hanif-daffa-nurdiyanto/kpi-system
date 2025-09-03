<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'short_description',
        'long_description',
        'usage',
        'icon',
        'color',
        'icon_path',
        'image'
    ];
    
    protected $casts = [
        'usage' => 'array',
    ];
}
