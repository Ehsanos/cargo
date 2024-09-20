<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\City;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['phone'])) {
            $data['country_code'] = substr($data['phone'], 0, strpos($data['phone'], ' ') ?: 3); // استخرج الرمز الدولي
            $data['phone_number'] = substr($data['phone'], strlen($data['country_code'])); // استخرج الرقم الفعلي
        }

        return $data;
    }



    protected function mutateFormDataBeforeSave(array $data): array
    {
        $temp = City::where('id', $data['city_id'])->pluck('branch_id')->first();

        $data['branch_id']=$temp;

        $data['phone'] = $data['country_code'] . $data['phone_number'];
        unset($data['country_code'], $data['phone_number']); // حذف الحقول المنفصلة بعد الجمع



        return $data;



    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
