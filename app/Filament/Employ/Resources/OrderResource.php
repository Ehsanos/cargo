<?php

namespace App\Filament\Employ\Resources;

use App\Enums\ActivateAgencyEnum;
use App\Enums\ActivateStatusEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\LevelUserEnum;
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
use Carbon\Carbon;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use LaraZeus\Popover\Tables\PopoverColumn;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الشحنات';

    protected static ?string $label = 'شحنة';
    protected static ?string $navigationLabel = 'الشحنات';


    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record->status == OrderStatusEnum::PENDING; // TODO: Change the autogenerated stub
    }

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

//                                        OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
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

                                )->live(),

                                Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('sender_phone', $user->phone);
                                            $set('sender_address', $user->address);
                                            $set('city_source_id', $user?->city_id);
                                            $set('branch_source_id', $user?->branch_id);
                                        }
                                    })->live(),


                                Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')->label('اسم الفرع المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_source_id', $branch->city_id);
                                        }
                                    })->live()->required(),
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل'),
                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),
                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')
                                    ->label('من مدينة')->searchable()->preload(),
                                Forms\Components\Select::make('receive_id')->relationship('receive', 'name')->label('اسم المستلم')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('receive_phone', $user->phone);
                                            $set('receive_address', $user->address);
                                            $set('city_target_id', $user?->city_id);
                                            $set('branch_target_id', $user?->branch_id);

                                        }
                                    })->live(),

                                Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_target_id', $branch->city_id);
                                        }
                                    })->live(),

                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                                Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')
                                    ->label('الى مدينة')->searchable()->preload(),

                                Forms\Components\Select::make('size_id')
                                    ->relationship('size', 'name')
                                    ->label
                                    ('فئة الحجم'),
                                Forms\Components\Select::make('unit_id')->relationship('packages.unit','name')->label('الوحدة'),


//                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
//
//                                    Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة')->required()])
//                                    ->deletable(false)
//                                    ->addable(false)->label('نوع الشحنة'),

                                Forms\Components\Select::make('weight_id')
                                    ->relationship('weight', 'name')
                                    ->label
                                    ('فئة الوزن')->searchable()->preload(),


                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع')->hidden(),
                                Forms\Components\TextInput::make('price')->numeric()->label('التحصيل'),
                                Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن')->default(1),

                                Forms\Components\Radio::make('far_sender')
                                    ->options([
                                        true => 'المرسل',
                                        false => 'المستلم'
                                    ])->required()->default(true)->inline()
                                    ->label('أجور الشحن')->default(1),
//                                Forms\Components\TextInput::make('total_weight')->numeric()->label('الوزن الكلي'),
                                Forms\Components\TextInput::make('canceled_info')
                                    ->hidden(fn(Forms\Get $get): bool => !$get('active'))->live()
                                    ->label('سبب الارجاع في حال ارجاع الطلب'),
                                Forms\Components\DatePicker::make('shipping_date')->label('تاريخ الطلب')->default(now()),


                            ]),

                        Tabs\Tab::make('محتويات الطلب')
                            ->schema([
                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                    SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),


                                    Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
//                                    Forms\Components\Select::make('weight')->relationship('category','name')->label('من فئة '),
                                    Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),

//                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
//                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
//                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                                ]),
                            ]),
                        Tabs\Tab::make('تأكيد الالتقاط ')->schema([

                            Forms\Components\Repeater::make('agencies')->relationship('agencies')
                                ->schema([

                                    Forms\Components\Select::make('user_id')
                                        ->options(User::where('id', auth()->user()->id)->pluck('name', 'id'))
                                        ->label('الموظف')->required(),
                                    Forms\Components\Radio::make('status')->options([
                                        TaskAgencyEnum::TAKE->value => TaskAgencyEnum::TAKE->getLabel(),
                                    ])->label('المهمة')->required(),
                                    Forms\Components\TextInput::make('task')->label('  ملاحظاتك '),

                                ])->defaultItems(1)->minItems(1)
                                ->collapsible()
                                ->grid(1)
                                ->addable(false)
                                ->collapsed()->deletable(false)
                                ->label('المهام')
                                ->itemLabel(fn(array $state): ?string => $state['package_name'] ?? ' مهمة...'), //
                            // استخدام اسم الشحنة كتسمية


                        ])->icon('heroicon-o-clipboard-document-list')

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
                Tables\Columns\TextColumn::make('price')->label('التحصيل'),
                Tables\Columns\TextColumn::make('far')->label('أجور الشحن'),
                Tables\Columns\TextColumn::make('packages.unit.name')->label('نوع الشحنة'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل'),
                Tables\Columns\TextColumn::make('sender.phone')->label('هاتف المرسل')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive->phone, '+')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة'),
                Tables\Columns\TextColumn::make('receive.name')->label('اسم المستلم '),
                Tables\Columns\TextColumn::make('receive.address')->label('عنوان المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('receive.phone')->label('هاتف المستلم ')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive->phone, '+')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الشحنة')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()), // عرض الزمن بشكل نسبي


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
