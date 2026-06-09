<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CardTypeBanner extends Model
{
    protected $fillable = ['card_type', 'banner_path'];

    public function getUrlAttribute(): string
    {
        if ($this->banner_path && Storage::disk('public')->exists($this->banner_path)) {
            return asset('storage/' . $this->banner_path);
        }

        return '';
    }

    public static function urlFor(?string $cardType): string
    {
        $type = $cardType ?: Employee::CARD_TYPE_NORMAL;
        $banner = static::where('card_type', $type)->first();

        return $banner?->url ?? '';
    }
}
