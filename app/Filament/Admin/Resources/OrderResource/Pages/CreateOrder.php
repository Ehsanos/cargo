<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {


//        $temp=User::where('user_name',$data[''])
   $data['code']="AWB".now()->format('YmdHis'); // الطابع الزمني بتنسيق قصير
//$data['city_source_id']=$data['']

        return $data;



    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }




}
