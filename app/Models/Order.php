<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\BayTypeEnum;
use App\Enums\ActivateStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;


class Order extends Model
{
    use HasFactory;
    protected $casts = [
        'options' => 'array',
         'status'=>OrderStatusEnum::class,
        'type'=>OrderTypeEnum::class,
        'bay_type'=>BayTypeEnum::class


    ];
    protected $guarded = [];

    public function citySource(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_source_id');
    }

    public function cityTarget(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_target_id');
    }

    public function branchSource(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_source_id');
    }

    public function branchTarget(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_target_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receive_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function agencies(): HasMany
    {
        return $this->hasMany(Agency::class);
    }
}
