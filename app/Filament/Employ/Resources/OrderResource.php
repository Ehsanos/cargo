<?php

namespace App\Filament\Employ\Resources;

use App\Enums\ActivateAgencyEnum;
use App\Enums\ActivateStatusEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Employ\Resources\OrderResource\Pages;
use App\Filament\Employ\Resources\OrderResource\RelationManagers;
use App\Models\Agency;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Error;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use LaraZeus\Popover\Tables\PopoverColumn;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label = 'طلب';
    protected static ?string $navigationLabel = 'الطلبات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('معلومات الطلب')
                            ->schema([

                                Forms\Components\Select::make('type')->options([
                                    OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                    OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),
                                ])->label('نوع الطلب')->searchable(),
                                Forms\Components\Select::make('status')->options(
                                    [

                                        OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                                        OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                                        OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                                        OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                                        OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                                        OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                                        OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),

                                    ]
                                )->label('حالة الطلب')->reactive()->afterStateUpdated(
                                    function ($state, callable $set) {
                                        if (OrderStatusEnum::RETURNED->value === $state)
                                            $set('active', true);
                                        else
                                            $set('active', false);

                                    }

                                )->live(),
                                Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')->label('اسم الفرع المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_source_id', $branch->city_id);
                                        }
                                    })->live(),
                                Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_target_id', $branch->city_id);
                                        }
                                    })->live(),
                                Forms\Components\DateTimePicker::make('shipping_date')->label('تاريخ الطلب'),

                                Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('sender_phone', $user->phone);
                                            $set('sender_address', $user->address);
                                        }
                                    })->live(),
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل'),
                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),

                                Forms\Components\Select::make('receive_id')->relationship('receive', 'name')->label('اسم المستلم')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('receive_phone', $user->phone);
                                            $set('receive_address', $user->address);
                                        }
                                    })->live(),
                                Forms\Components\Select::make('size_id')
                                    ->relationship('size', 'name')
                                    ->label
                                    ('فئة الحجم'),

                                Forms\Components\Select::make('weight_id')
                                    ->relationship('weight', 'name')
                                    ->label
                                    ('فئة الوزن'),
                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')->label('من مدينة'),
                                Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')->label('الى مدينة'),

                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع'),
                                Forms\Components\TextInput::make('price')->numeric()->label('التحصيل'),
                                Forms\Components\Radio::make('far_sender')
                                    ->options([
                                        true => 'المرسل',
                                        false => 'المستلم'
                                    ])->required()->default(true)->inline()
                                    ->label('أجور الشحن'),
//                                Forms\Components\TextInput::make('total_weight')->numeric()->label('الوزن الكلي'),
                                Forms\Components\TextInput::make('canceled_info')
                                    ->hidden(fn(Forms\Get $get): bool => !$get('active'))->live()
                                    ->label('سبب الارجاع في حال ارجاع الطلب'),


                            ]),

                        Tabs\Tab::make('محتويات الطلب')
                            ->schema([
                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                    SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),

                                    Forms\Components\TextInput::make('code')->default(fn() => "FC" . now()->format('dHis')),
                                    Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة'),

                                    Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
//                                    Forms\Components\Select::make('weight')->relationship('category','name')->label('من فئة '),
                                    Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),

//                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
//                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
//                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                                ]),
                            ]),

//                        Tabs\Tab::make('سلسلة التوكيل')->schema([
//                            Forms\Components\Repeater::make('agencies')->relationship('agencies')->schema([
//                                Forms\Components\Select::make('user_id')->options(User::pluck('name', 'id'))->label('الموظف')->searchable()->required(),
//                                Forms\Components\Radio::make('status')->options([
//                                    TaskAgencyEnum::TASK->value => TaskAgencyEnum::TASK->getLabel(),
//                                    TaskAgencyEnum::TAKE->value => TaskAgencyEnum::TAKE->getLabel(),
//                                    TaskAgencyEnum::DELIVER->value => TaskAgencyEnum::DELIVER->getLabel(),
//                                ])->label('المهمة'),
//                                Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),
//
//                            ])
//                        ])


                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('code'),


                Tables\Columns\TextColumn::make('type')->label('نوع الطلب'),
                Tables\Columns\TextColumn::make('bay_type')->label('حالة الدفع'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل'),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة'),
                Tables\Columns\TextColumn::make('receive.name')->label('اسم المستلم '),
                Tables\Columns\TextColumn::make('cityTarget.name')->label('الى مدينة '),
                Tables\Columns\TextColumn::make('agencies.task')
                    ->formatStateUsing(fn($record) => $record->agencies()->where('user_id', auth()->id())->first()?->task)
                    ->label('المهمة الموكلة'),

                /*  Tables\Columns\TextColumn::make('agencies.activate')
                      ->formatStateUsing(fn($record) => $record->agencies()
                          ->where('user_id', auth()->id())->first()?->activate->getLabel())
                      ->icon(fn($record) => $record->agencies()->where('user_id', auth()->id())->first()?->activate->getIcon())
                      ->color(fn($record) => $record->agencies()->where('user_id', auth()->id())->first()?->activate->getColor())
                      ->label('حالة المهمة'),*/


            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete_task')
                    ->form(function ($record) {
                        $agency = Agency::whereNot('status', TaskAgencyEnum::DELIVER->value)->where([
                            'user_id' => auth()->id(),
                            'order_id' => $record->id,
                            'activate' => ActivateAgencyEnum::PENDING->value,
                        ])->first();
                        if ($agency->status == TaskAgencyEnum::TAKE && $record->far > 0 && $record->far_sender == true) {
                            return [
                                Forms\Components\Placeholder::make('place')->content('هل أنت متأكد من إنهاء المهمة وإستلام أجور الشحن ' . $record->far . ' $')
                            ];

                        }
                        return [Forms\Components\Placeholder::make('place')->label('هل أنت متأكد من إنهاء المهمة')];


                    })
                    ->action(function ($record) {
                        $agency = Agency::whereNot('status', TaskAgencyEnum::DELIVER->value)->where([
                            'user_id' => auth()->id(),
                            'order_id' => $record->id,
                            'activate' => ActivateAgencyEnum::PENDING->value,

                        ])->first();
                        \DB::beginTransaction();
                        try {
                            if ($agency->status == TaskAgencyEnum::TAKE && $record->far > 0) {
                                Balance::create([
                                    'type' => BalanceTypeEnum::CATCH->value,
                                    'credit' => 0,
                                    'debit' => $record->far,
                                    'info' => 'تحصيل أجور شحن الطلب #' . $record->id,
                                    'user_id' => $agency->user->id,
                                    'total' => $agency->user->total_balance - $record->far,
                                    'is_complete' => true,
                                ]);
                                $user = $record->sender;
                                if ($record->far_sender === false) {
                                    $user = $record->receive;
                                }
                                Balance::create([
                                    'type' => BalanceTypeEnum::PUSH->value,
                                    'credit' => $record->far,
                                    'debit' => 0,
                                    'info' => 'دفع أجور شحن الطلب #' . $record->id,
                                    'user_id' => $user->id,
                                    'total' => $user->total_balance + $record->far,
                                    'is_complete' => true,
                                ]);
                            }
                            $agency?->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            \DB::commit();
                            Notification::make('success')->title('تمت العملية بنجاح')->success()->send();
                        } catch (\Exception | Error $e) {

                            DB::rollBack();
                            Notification::make('success')->title('فشلت العملية ')->body($e->getMessage().'- '.$e->getLine())->danger()->send();
                        }

                    })->requiresConfirmation()->label('إنهاء المهمة')
                    ->visible(function ($record) {
                        return Agency::whereNot('status', TaskAgencyEnum::DELIVER->value)->where([
                            'user_id' => auth()->id(),
                            'order_id' => $record->id,
                            'activate' => ActivateAgencyEnum::PENDING->value,

                        ])->exists();

                    }),
                // Complete
                Tables\Actions\Action::make('complete_task_deliver')->label('إنهاء المهمة')
                    ->form(fn($record) => [
                        Forms\Components\Placeholder::make('success')->label('تنبيه')->content(function($record){
                           if($record->far_sender==false){
                               return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->total_price} $";
                           }
                            return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->price} $";

                        })->extraAttributes(['style'=>'color:red'])
                    ])
                    ->action(function ($record) {
                        \DB::beginTransaction();
                        try {
                          $agency=  Agency::where(['agencies.user_id' => auth()->id(), 'order_id' => $record->id, 'agencies.activate' => ActivateAgencyEnum::PENDING->value, 'agencies.status' => TaskAgencyEnum::DELIVER->value])->first();
                          $agency->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            $record->update([
                                'status' => OrderStatusEnum::SUCCESS->value
                            ]);
                            $price=$record->total_price;

                            if($record->far_sender==true){
                                $price=$record->price;
                            }
                            Balance::create([
                                'debit'=>$price,
                                'credit'=>0,
                                'type'=>BalanceTypeEnum::CATCH->value,
                                'info'=>'إستلام قيمة الطلب #'.$record->id,
                                'is_complete'=>true,
                                'order_id'=>$record->id,
                                'total'=>$agency->user->total_balance - $price,
                                'user_id'=>$agency->user->id,
                            ]);
                            Balance::create([
                                'debit'=>0,
                                'credit'=>$price,
                                'type'=>BalanceTypeEnum::PUSH->value,
                                'info'=>'إستلام قيمة الطلب #'.$record->id,
                                'is_complete'=>true,
                                'order_id'=>$record->id,
                                'total'=>$agency->user->total_balance + $price,
                                'user_id'=>$record->receive->id,
                            ]);
                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->success()->send();
                        } catch (\Exception | Error $e) {
                            \DB::rollBack();
                            Notification::make('success')->title('فشل العملية')->danger()->body($e->getLine())->send();
                        }

                    })
                    ->visible(function ($record) {
                        $pending = Agency::where([
                            'user_id' => auth()->id(),
                            'order_id' => $record->id,
                            'activate' => ActivateAgencyEnum::PENDING->value,
                            'status' => TaskAgencyEnum::DELIVER->value
                        ])->exists();

                        $agency2 = !Agency::whereNot('status', TaskAgencyEnum::DELIVER->value)->where([
                            'user_id' => auth()->id(),
                            'order_id' => $record->id,
                            'activate' => ActivateAgencyEnum::PENDING->value,

                        ])->exists();
                        return $pending && $agency2;
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('agencies', fn($query) => $query->where('agencies.user_id', auth()->id()))
            ->orWhere(['orders.take_id' => auth()->user()->id, 'orders.delivery_id' => auth()->user()->id]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AgenciesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
