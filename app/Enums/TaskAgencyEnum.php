<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskAgencyEnum:string implements HasLabel ,HasColor,HasIcon
{
    case TAKE='take';
    case DELIVER='deliver';
    case TASK='branch';


    public function getLabel(): string
    {
        return match ($this) {
            self::TAKE => 'إلتقاط',
            self::DELIVER => 'تسليم',
            self::TASK => 'مهمة إدارية',

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TAKE => 'info',
            self::DELIVER => 'success',
            self::TASK => 'warning',

        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TAKE => 'fas-door-open',
            self::DELIVER => 'fas-cart-flatbed',
            self::TASK => 'fas-cart-flatbed',

        };
    }

}
