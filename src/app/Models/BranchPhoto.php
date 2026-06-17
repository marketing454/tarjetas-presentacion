<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchPhoto extends Model
{
    protected $fillable = ['branch_id', 'url', 'position'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
