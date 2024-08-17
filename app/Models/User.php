<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Enums\LevelUserEnum;
use App\Enums\JobUserEnum;
use App\Enums\ActivateStatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;
    use HasPanelShield;


    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && $this->level == LevelUserEnum::ADMIN) {

            return true;
        } elseif ($panel->getId() === 'branch' && $this->level == LevelUserEnum::BRANCH) {
            return true;
        } elseif ($panel->getId() === 'employ' && ($this->level === LevelUserEnum::DRIVER || $this->level === LevelUserEnum::STAFF)) {
            return true;
        } elseif($panel->getId()==='user' && $this->level === LevelUserEnum::USER) {
            return true;
        }
return false;

    }


/**
 * The attributes that are mass assignable.
 *
 * @var array<int, string>
 */
//    protected $fillable = [
//        'name',
//        'username',
//        'password',
//        'phone',
//        'url',
//        'latitude',
//        'longitude',
//        'status',
//        'level',
//        'job'
//
//    ];
protected
$guarded = [];
/**
 * The attributes that should be hidden for serialization.
 *
 * @var array<int, string>
 */
protected
$hidden = [
    'password',
    'remember_token',
];

/**
 * The attributes that should be cast.
 *
 * @var array<string, string>
 */
protected
$casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'level' => LevelUserEnum::class,
    'status' => ActivateStatusEnum::class,
    'job' => JobUserEnum::class
];


public
function city(): BelongsTo
{
    return $this->belongsTo(City::class);
}

public
function branch(): BelongsTo
{
    return $this->belongsTo(Branch::class);
}

public
function sentOrders(): HasMany
{
    return $this->hasMany(Order::class, 'sender_id');
}

public
function receivedOrders(): HasMany
{
    return $this->hasMany(Order::class, 'receive_id');
}

public
function balances(): HasMany
{
    return $this->hasMany(Balance::class)->where('balances.is_complete', 1);
}

public
function pendingBalances(): HasMany
{
    return $this->hasMany(Balance::class)->where('balances.is_complete', 0);
}

public
function getTotalBalanceAttribute(): float
{
    $total = DB::table('balances')->where('user_id', $this->id)->where('is_complete', true)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
    return sprintf('%.2f', $total);
}

public
function getPendingBalanceAttribute(): float
{
    $total = DB::table('balances')->where('user_id', $this->id)->where('is_complete', false)->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
    return sprintf('%.2f', $total);
}
}
