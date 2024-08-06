<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ActivateStatusEnum;

class City extends Model
{
    use HasFactory;

    protected $casts=[
        'status'=>ActivateStatusEnum::class
    ];


    protected $guarded = [];

    public function city(): BelongsTo
    {
        return $this->belongsTo(__CLASS__);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(__CLASS__);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function areas():HasMany
    {
        return $this->hasMany(Area::class);
    }
}
