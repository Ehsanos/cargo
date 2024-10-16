<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\OrderResource\Pages;
use App\Filament\User\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\BayTypeEnum;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\Popover\Tables\PopoverColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label='طلب';
    protected static ?string $navigationLabel='الطلبات';

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([

                        Tabs\Tab::make('ارسال طلب للادارة')
                            ->schema([
                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع'),

                                Forms\Components\Select::make('receive_id')
                                    ->relationship('receive', 'name')
                                    ->label('اسم المستلم'),



                       ]),



                        Tabs\Tab::make('معلومات الطلب')
                            ->schema([

                                Forms\Components\Select::make('type')->options([
                                    OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                    OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),
                                ])->label('نوع الطلب')->searchable(),
                                Forms\Components\Select::make('status')->options(
                                    [

                                        OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),

                                    ]
                                )->label('حالة الطلب')->reactive()->afterStateUpdated(
                                    function ($state,callable $set){
                                        if(OrderStatusEnum::RETURNED->value ===$state)
                                            $set('active',true);
                                        else
                                            $set('active',false);

                                    }

                                )->live()->hidden()->default(
                                   [OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel()]

                                ),
                                Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')->label('اسم الفرع المرسل')->hidden(),
                                Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم'),
                                Forms\Components\DateTimePicker::make('shipping_date')->default(now()->format(''))
                                    ->label('تاريخ الطلب')->hidden(),

//                                Forms\Components\TextInput::make('sender_id')->()->label('اسم المرسل'),
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل'),
                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),

                                Forms\Components\Select::make('receive_id')->relationship('receive', 'name')->label('اسم المستلم'),
                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')->label('من مدينة'),
                                Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')->label('الى مدينة'),

                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع'),
                                Forms\Components\TextInput::make('price')->numeric()->label('السعر'),
                                Forms\Components\TextInput::make('total_weight')->numeric()->label('الوزن الكلي'),
                                Forms\Components\TextInput::make('canceled_info')
                                    ->hidden(fn(Forms\Get $get):bool=>!$get('active'))->live()
                                    ->label('سبب الارجاع في حال ارجاع الطلب'),


                            ]),


                        Tabs\Tab::make('محتويات الطلب')
                            ->schema([
                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                    SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),

                                    Forms\Components\TextInput::make('code')->default(fn()=>"FC". now()->format('dHis'))->hidden(),
                                    Forms\Components\Select::make('unit_id')->relationship('unit','name')->label('الوحدة'),
                                    Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
                                    Forms\Components\TextInput::make('weight')->label('وزن الشحنة'),
                                    Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),
                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                                ]),
                            ])
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
                    ->content(\LaraZeus\Qr\Facades\Qr::render('2222'))
                    ->icon('heroicon-o-qr-code')->label('QR Code'),

                Tables\Columns\TextColumn::make('code')->label('كود الطلب'),

                Tables\Columns\TextColumn::make('status')->label('حالة الطلب'),
                Tables\Columns\TextColumn::make('type')->label('نوع الطلب'),
                Tables\Columns\TextColumn::make('bay_type')->label('حالة الدفع'),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة'),
                Tables\Columns\TextColumn::make('receive.name')
                    ->label('اسم المستلم '),
                Tables\Columns\TextColumn::make('cityTarget.name')->label('الى مدينة '),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);


    }



    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('sender_id', auth()->user()->id)
            ->OrWhere('receive_id',auth()->user()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
