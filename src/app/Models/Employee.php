<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory;

    public const CARD_TYPE_NORMAL = 'normal';
    public const CARD_TYPE_CREDIT = 'credit';
    public const CARD_TYPE_CORPORATE = 'corporate';

    protected $fillable = [
        'branch_id', 'name', 'position', 'card_type', 'photo', 'card_background',
        'whatsapp', 'instagram', 'facebook', 'slug',
    ];

    public static function cardTypes(): array
    {
        return [
            self::CARD_TYPE_NORMAL => [
                'label' => 'Asesor Comercial',
                'description' => 'Tarjeta comercial principal de COMPULAGO.',
                'icon' => 'fa-id-card',
            ],
            self::CARD_TYPE_CREDIT => [
                'label' => 'Asesor de credito',
                'description' => 'Estilo financiero para creditos y aprobaciones.',
                'icon' => 'fa-hand-holding-dollar',
            ],
            self::CARD_TYPE_CORPORATE => [
                'label' => 'Asesor corporativo',
                'description' => 'Presentacion sobria para cuentas empresariales.',
                'icon' => 'fa-briefcase',
            ],
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Storage::disk('public')->exists($this->photo)) {
            return asset('storage/' . $this->photo);
        }
        return '';
    }

    public function getCardBackgroundUrlAttribute(): string
    {
        if ($this->card_background && \Storage::disk('public')->exists($this->card_background)) {
            return asset('storage/' . $this->card_background);
        }

        return CardTypeBanner::urlFor($this->card_type);
    }

    public function getCustomCardBackgroundUrlAttribute(): string
    {
        if ($this->card_background && \Storage::disk('public')->exists($this->card_background)) {
            return asset('storage/' . $this->card_background);
        }

        return '';
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
        return $initials;
    }

    public function getCardTypeLabelAttribute(): string
    {
        return self::cardTypes()[$this->card_type ?? self::CARD_TYPE_NORMAL]['label']
            ?? self::cardTypes()[self::CARD_TYPE_NORMAL]['label'];
    }

    public function getCardThemeAttribute(): array
    {
        return static::themeFor($this->card_type);
    }

    public static function themeFor(?string $cardType): array
    {
        return match ($cardType) {
            self::CARD_TYPE_CREDIT => [
                'page_start' => '#07182E',
                'page_mid' => '#075985',
                'page_end' => '#0C1F36',
                'header_start' => '#075985',
                'header_end' => '#F59E0B',
                'surface' => '#FFFBEB',
                'footer' => '#FEF3C7',
                'avatar_start' => '#F59E0B',
                'avatar_end' => '#38BDF8',
                'avatar_bg' => '#E0F2FE',
                'avatar_text' => '#075985',
                'branch_bg' => '#E0F2FE',
                'branch_text' => '#075985',
                'accent' => '#F59E0B',
                'accent_dark' => '#B45309',
                'glow' => '#FBBF24',
            ],
            self::CARD_TYPE_CORPORATE => [
                'page_start' => '#020617',
                'page_mid' => '#334155',
                'page_end' => '#020617',
                'header_start' => '#0F172A',
                'header_end' => '#334155',
                'surface' => '#F8FAFC',
                'footer' => '#E2E8F0',
                'avatar_start' => '#38BDF8',
                'avatar_end' => '#CBD5E1',
                'avatar_bg' => '#E0F2FE',
                'avatar_text' => '#0F172A',
                'branch_bg' => '#E0F2FE',
                'branch_text' => '#0369A1',
                'accent' => '#38BDF8',
                'accent_dark' => '#0369A1',
                'glow' => '#7DD3FC',
            ],
            default => [
                'page_start' => '#0A1F0C',
                'page_mid' => '#14532D',
                'page_end' => '#0A1F0C',
                'header_start' => '#14532D',
                'header_end' => '#16A34A',
                'surface' => '#FFFFFF',
                'footer' => '#F8FAFC',
                'avatar_start' => '#16A34A',
                'avatar_end' => '#4ADE80',
                'avatar_bg' => '#DCFCE7',
                'avatar_text' => '#15803D',
                'branch_bg' => '#F0FDF4',
                'branch_text' => '#15803D',
                'accent' => '#16A34A',
                'accent_dark' => '#14532D',
                'glow' => '#4ADE80',
            ],
        };
    }
}
