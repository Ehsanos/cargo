<?php

namespace App\Filament\Admin\Resources;

use App\Enums\CategoryTypeEnum;
use App\Enums\LevelUserEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Models\Branch;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\Popover\Tables\PopoverColumn;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use Filament\Forms\Components\Tabs;
use App\Enums\BayTypeEnum;
use Filament\Infolists\Infolist;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label = 'شحنة';
    protected static ?string $navigationLabel = 'الشحنات';
    protected static ?string $navigationIcon = 'heroicon-o-truck';

//
//    public static function canEdit(Model $record): bool
//    {
//        return parent::canEdit($record) && $record->status==OrderStatusEnum::PENDING; // TODO: Change the autogenerated stub
//    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('الطلب')->schema([
                    Forms\Components\Fieldset::make('معلومات الطلب')
                        ->schema([

                            Forms\Components\Select::make('type')->options([
                                OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),
                            ])->label('نوع الطلب')
                                ->required()
                                ->searchable(),
                        /*    Forms\Components\Select::make('status')->options(
                                [
                                    OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                                    OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
//                                        OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
//                                        OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
//                                        OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
//                                        OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
//                                        OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),

                                ]
                            )->label('حالة الطلب')->reactive()->afterStateUpdated(
                                function ($state, callable $set) {
                                    if (OrderStatusEnum::RETURNED->value === $state)
                                        $set('active', true);
                                    else
                                        $set('active', false);

                                }

                            )->live()->required(),*/


//                                ->afterStateUpdated(function ($state,$set){
//                                    $branch=Branch::find($state);
//                                    if($branch){
//                                        $set('city_source_id',$branch->city_id);
//                                    }
//                                })


//                                    ->afterStateUpdated(function ($state,$set){
//                                        $branch=Branch::find($state);
//                                        if($branch){
//                                            $set('city_target_id',$branch->city_id);
//                                        }
//                                    })
//


//                                Forms\Components\DatePicker::make('shipping_date')->label('تاريخ الطلب')->format('Y-m-d')->default(now()->format('Y-m-d')),

                            Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')->required()
                                ->afterStateUpdated(function ($state, $set) {
                                    $user = User::with('city')->find($state);
                                    if ($user) {
                                        $set('sender_phone', $user?->phone);
                                        $set('sender_address', $user?->address);
                                        $set('city_source_id', $user?->city_id);
                                        $set('branch_source_id', $user?->branch_id);

                                    }
                                })->live()->searchable()->preload(),

                            Forms\Components\Select::make('city_source_id')
                                ->relationship('citySource', 'name')
                                ->label('من مدينة')->reactive()->required()->searchable()->preload(),

                            Forms\Components\Select::make('branch_source_id')
                                ->relationship('branchSource', 'name'/*,fn($query,$get)=>$query->where('city_id',$get('city_source_id'))*/)
                                ->label('اسم الفرع المرسل')->reactive()->required(),




                            Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل')->required(),


                            Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل')->required(),

                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('receive_id')->options(User::all()->pluck('iban', 'id')
                                    ->toArray())->searchable()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::with('city')->find($state);
                                        if ($user) {
                                            $set('receive_phone', $user?->phone);
                                            $set('receive_address', $user?->address);

                                            $set('sender_name', $user?->name);
                                            $set('city_target_id', $user?->city_id);
                                            $set('branch_target_id', $user?->branch_id);
                                        }
                                    })->live()->label('ايبان المستلم'),

                                Forms\Components\Select::make('sender_name')->label('معرف المستلم')
                                    ->options(User::all()->pluck('name', 'id')->toArray())->searchable()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::with('city')->find($state);
                                        if ($user) {
                                            $set('receive_phone', $user?->phone);
                                            $set('receive_address', $user?->address);

                                            $set('sender_name', $user?->name);
                                            $set('city_target_id', $user?->city_id);
                                            $set('branch_target_id', $user?->branch_id);
                                            $set('receive_id', $user?->id);
                                        }
                                    })->live()->dehydrated(false),
                            ]),

                            Forms\Components\TextInput::make('global_name')->label('اسم المستلم'),






                            Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم')->required(),
                            Forms\Components\Select::make('city_target_id')
                                ->relationship('cityTarget', 'name')
                                ->label('الى مدينة')->required()->searchable()->preload(),

                            Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                                ->searchable()->preload()
                                ->live()->required(),

                            Forms\Components\Select::make('weight_id')
                                ->relationship('weight', 'name')
                                ->label
                                ('الوزن')->searchable()->preload(),

                            Forms\Components\Select::make('size_id')
                                ->relationship('size', 'name')
                                ->label
                                ('الحجم')->searchable()->preload(),


                            Forms\Components\Select::make('unit_id')
                                ->relationship('unit', 'name')->label('الوحدة'),

                            // ttrrtt
                            Forms\Components\Select::make('bay_type')->options([
                                BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                            ])->label('التحصيل على ')->required()->hidden(),


                            Forms\Components\TextInput::make('price')->numeric()->label('التحصيل')->default(0)->columnSpan(2),
                            Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن')->default(1),
                            Forms\Components\Radio::make('far_sender')
                                ->options([
                                    true => 'المرسل',
                                    false => 'المستلم'
                                ])->required()->default(true)->inline()
                                ->label('أجور الشحن'),

                            Forms\Components\TextInput::make('canceled_info')
                                ->hidden(fn(Forms\Get $get): bool => !$get('active'))->live()
                                ->label('سبب الارجاع في حال ارجاع الطلب'),


                        ])->columns(2),
                    Forms\Components\Fieldset::make('محتويات الطلب')
                        ->schema([
                            Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),

//                                Forms\Components\TextInput::make('code')->default(fn()=>"FC". now()->format('dHis')),
//                                    Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة')->required(),

                                Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
//                                    Forms\Components\Select::make('weight')->relationship('category','name')->label('من فئة '),
                                Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),

//                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
//                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
//                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                            ])
                                ->label('محتويات الطلب')
                                ->addable(false)
                                ->deletable(false)->columnSpan(2)

                        ])->columnSpan(2),
                    Forms\Components\Fieldset::make('سلسلة التوكيل')
                        ->schema([
                            Forms\Components\Repeater::make('agencies')->relationship('agencies')
                                ->schema([

                                    Forms\Components\Select::make('user_id')->options(User::where(fn($query) => $query->where('level', LevelUserEnum::DRIVER->value)->orWhere('level', LevelUserEnum::STAFF->value)
                                    )->pluck('name', 'id'))->label('الموظف')->searchable()->required(),
                                    Forms\Components\Radio::make('status')->options([
                                        TaskAgencyEnum::TASK->value => TaskAgencyEnum::TASK->getLabel(),
                                        TaskAgencyEnum::TRANSPORT->value => TaskAgencyEnum::TRANSPORT->getLabel(),

                                    ])->label('المهمة'),
                                    Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),

                                ])->defaultItems(2)->minItems(2)
                                ->collapsible()
                                ->grid(2)
                                ->deletable(true)
                                ->addActionLabel('إضافة مهمة')
                                ->label('المهام')
                                ->itemLabel(fn(array $state): ?string => $state['package_name'] ?? ' مهمة...')->columnSpan(2), //
                            // استخدام اسم الشحنة كتسمية


                        ])
                ]),

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

                Tables\Columns\TextColumn::make('code')->copyable(),


                Tables\Columns\TextColumn::make('status')->label('حالة الطلب')
                ,

                Tables\Columns\TextColumn::make('type')->label('نوع الطلب')->searchable(),
                Tables\Columns\TextColumn::make('bay_type')->label('حالة الدفع')->searchable(),

                Tables\Columns\TextColumn::make('unit.name')->label('نوع الشحنة'),

                Tables\Columns\TextColumn::make('price')->label('التحصيل'),
                Tables\Columns\TextColumn::make('far')->label('أجور الشحن'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل')->searchable(),
                Tables\Columns\TextColumn::make('sender.phone')->label('هاتف المرسل')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive?->phone, '+')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة')->searchable(),
                Tables\Columns\TextColumn::make('receive.name')->label('معرف المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('receive.address')->label('عنوان المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('receive.phone')->label('هاتف المستلم ')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive?->phone, '+')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('global_name')->label('اسم المستلم'),
                Tables\Columns\TextColumn::make('cityTarget.name')->label('الى مدينة ')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الشحنة')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()) // عرض الزمن بشكل نسبي


            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل'),
                Tables\Filters\SelectFilter::make('receive_id')->relationship('receive', 'name')->label('اسم المستلم'),
                Tables\Filters\SelectFilter::make('branch_source_id')->relationship('branchSource', 'name')
                    ->label('اسم الفرع المرسل'),
                Tables\Filters\SelectFilter::make('branch_source_id')->relationship('branchSource', 'name')
                    ->label('اسم الفرع المرسل'),
                Tables\Filters\SelectFilter::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                ,
                Tables\Filters\SelectFilter::make('status')->options([
                    OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                    OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                    OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                    OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                    OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                    OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                    OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),


                ])->label('حالة الطلب'),
                Tables\Filters\SelectFilter::make('city_target_id')
                    ->relationship('cityTarget', 'name')
                    ->label('الى مدينة'),

                Tables\Filters\SelectFilter::make('city_source_id')
                    ->relationship('citySource', 'name')
                    ->label('من مدينة')
                ,

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')->label('الى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })

            ])->filtersFormMaxHeight('300px')
            ->actions([


                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('change_status')
                    ->form(function ($record) {
                        switch ($record->status->value) {
                            case OrderStatusEnum::PENDING:
                                $list = [
                                    OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                                    OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                                ];
                                break;
                            case OrderStatusEnum::AGREE:
                                $list = [
                                    OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                                    OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                                    OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                                ];
                                break;
                            case OrderStatusEnum::PICK:
                            case OrderStatusEnum::TRANSFER:
                                $list = [
                                    OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                                    OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                                    OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                                ];
                                break;
                            default:
                                $list = [
                                    OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                                    OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                                ];
                        }
                        return [
                            Forms\Components\Placeholder::make('msg')->content('عند تحديدك لحالة معينة لا يمكنك التراجع إلى الخلف')->extraAttributes(['style' => 'color:red;font-weight:bolder;font-size:1rem'])->label('تحذير'),
                            Forms\Components\Select::make('status')->options($list)->label('حالة الطلب')->required(),

                        ];
                    })
                    ->action(function ($record, $data) {
                        if ($record->pick_id == null && $data['status'] == OrderStatusEnum::AGREE->value) {
                            Notification::make('error')->title('خطأ')->body('لا يمكن قبول الطلب قبل تحديد موظف الإلتقاط')->danger()->send();
                            return;
                        }

                        if ($record->given_id == null && $data['status'] == OrderStatusEnum::SUCCESS->value) {
                            Notification::make('error')->title('خطأ')->body('لا يمكن إنهاء الطلب قبل تحديد موظف التسليم')->danger()->send();
                            return;
                        }

                        $record->update(['status' => $data['status']]);
                        Notification::make('success')->title('نجاح العملية')->body("تم تعديل حالة الطلب إلى " . OrderStatusEnum::tryFrom($data['status'])?->getLabel())->danger()->send();

                    })
                    ->label('تغيير حالة الطلب')->visible(fn($record) => $record->status != OrderStatusEnum::AGREE && $record->status != OrderStatusEnum::CANCELED && $record->status != OrderStatusEnum::SUCCESS && $record->status != OrderStatusEnum::RETURNED)->button(),
                Tables\Actions\Action::make('set_picker')->form([
                    Forms\Components\Select::make('pick_id')->options(User::where('users.level', LevelUserEnum::STAFF->value)->pluck('name', 'id'))->searchable()->label('موظف الإلتقاط'),
                ])->action(function ($record, $data) {
                    $record->update(['pick_id' => $data['pick_id']]);
                    Notification::make('success')->title('نجاح العملية')->body("تم تحديد موظف الإلتقاط بنجاح ")->danger()->send();

                })->visible(fn($record) => $record->pick_id == null)->label('تحديد موظف الإلتقاط')->button()->color('info'),

                Tables\Actions\Action::make('set_given')->form([
                    Forms\Components\Select::make('given_id')->options(User::where('users.level', LevelUserEnum::STAFF->value)->pluck('name', 'id'))->searchable()->label('موظف الإلتقاط'),
                ])->action(function ($record, $data) {
                    $record->update(['given_id' => $data['given_id']]);
                    Notification::make('success')->title('نجاح العملية')->body("تم تحديد موظف التسليم بنجاح ")->danger()->send();

                })->visible(fn($record) => $record->given_id == null && $record->status != OrderStatusEnum::PENDING)->label('تحديد موظف التسليم')->button()->color('info'),

//                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
//                    ExportBulkAction::make()

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AgenciesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', OrderStatusEnum::PENDING)->count();
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
