<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\LevelUserEnum;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }



    protected function mutateFormDataBeforeFill(array $data): array
    {

//        if(isset($data['cheack2']))
//        {
//            $user=User::where('level',LevelUserEnum::ADMIN)->first();
//            $data['receive_id'] = $user->id;
//            $data['branch_source_id'] = auth()->user()->branch_id;
//            $data['sender_phone'] = auth()->user()->phone;
//            $data['sender_address'] = auth()->user()->address;
//            $data['city_source_id'] = auth()->user()->city_id;
//            $data['sender_id'] = auth()->id();
//            unset($data['cheack2']);
//            return $data;
//
//        }
//
//
//        if(isset($data['temp']))
//
//        {
//
//            $reciver_id = User::where('username', $data['temp'])->pluck('id')->first();
//
//            if (!$reciver_id) {
//                Notification::make()->danger()
//                    ->title('خطأ')
//                    ->body('اسم المستلم غير صحيح')
//                    ->send();
//                $this->halt();
//
//            } else {
//                $data['receive_id'] = $reciver_id;
//                unset($data['temp']);
//                $data['branch_source_id'] = auth()->user()->branch_id;
//                $data['sender_phone'] = auth()->user()->phone;
//                $data['sender_address'] = auth()->user()->address;
//                $data['city_source_id'] = auth()->user()->city_id;
//                $data['sender_id'] = auth()->id();
//                return $data;
//            }
//        }
//
//        return [];


return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
