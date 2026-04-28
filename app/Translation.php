<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'translation_key_id',
        'locale_id',
        'value',
        'is_reviewed',
    ];

    protected $casts = [
        'is_reviewed' => 'boolean',
    ];

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function translationKey()
    {
        return $this->belongsTo(TranslationKey::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
