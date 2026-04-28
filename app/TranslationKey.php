<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslationKey extends Model
{
    protected $fillable = [
        'key',
        'description',
    ];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
