<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'city', 'address', 'maps_url', 'phone'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function getMapsLinkAttribute(): string
    {
        if ($this->maps_url) {
            return $this->maps_url;
        }

        if ($this->address && filter_var($this->address, FILTER_VALIDATE_URL)) {
            return $this->address;
        }

        $query = $this->address
            ? $this->address . ', ' . $this->city . ', Colombia'
            : 'COMPULAGO ' . $this->city . ', Colombia';

        return 'https://maps.google.com/?q=' . urlencode($query);
    }
}
