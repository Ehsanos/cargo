<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LevelUserEnum: string implements HasLabel ,HasColor,HasIcon
{
    case USER = 'user';
    case ADMIN = 'admin';
    case DRIVER = 'driver';
    case BRANCH = 'branch';


    public function getLabel(): string
    {
        return match ($this) {
            self::USER => 'مستخدم',
            self::ADMIN => 'مدير',
            self::DRIVER => 'سائق',
            self::BRANCH => 'فرع',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::USER => 'info',
            self::ADMIN => 'danger',
            self::DRIVER => 'orange',
            self::BRANCH => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::USER => 'fas-users',
            self::ADMIN => 'fas-user-gear',
            self::DRIVER => 'fas-truck',
            self::BRANCH => 'fas-code-branch',
        };
    }
}
