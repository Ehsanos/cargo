<?php

namespace App\Filament\Admin\Resources;

use App\Enums\CategoryTypeEnum;
use App\Enums\LevelUserEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\Popover\Tables\PopoverColumn;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use Filament\Forms\Components\Tabs;
use App\Enums\BayTypeEnum;
use Filament\Infolists\Infolist;


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
                                        function ($state,callable $set){
                                            if(OrderStatusEnum::RETURNED->value ===$state)
                                                $set('active',true);
                                            else
                                                $set('active',false);

                                        }

                                    )->live(),
                                Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')->label('اسم الفرع المرسل')
                                ->afterStateUpdated(function ($state,$set){
                                    $branch=Branch::find($state);
                                    if($branch){
                                        $set('city_source_id',$branch->city_id);
                                    }
                                })->live(),
                                Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                                    ->afterStateUpdated(function ($state,$set){
                                        $branch=Branch::find($state);
                                        if($branch){
                                            $set('city_target_id',$branch->city_id);
                                        }
                                    })->live(),
                                Forms\Components\DatePicker::make('shipping_date')->label('تاريخ الطلب')->format('Y-m-d')->default(now()->format('Y-m-d')),

                                  Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')
                                      ->afterStateUpdated(function ($state,$set){
                                          $user=User::find($state);
                                          if($user){
                                              $set('sender_phone',$user->phone);
                                              $set('sender_address',$user->address);
                                          }
                                      })->live(),

                                Forms\Components\Grid::make()->schema([
                                    Forms\Components\Select::make('receive_id')->options(User::all()->pluck('name','id')->toArray())->searchable()
                                        ->afterStateUpdated(function ($state,$set){
                                            $user=User::find($state);
                                            if($user){
                                                $set('receive_phone',$user?->phone);
                                                $set('receive_address',$user?->address);

                                                $set('sender_name',$user?->name);
                                            }
                                        })->live()->label('هاتف المستلم'),
                                    Forms\Components\TextInput::make('sender_name')->dehydrated(false)->label('اسم المستلم'),
                                ]),
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل'),

                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),


                                Forms\Components\Select::make('weight_id')
                                    ->relationship('weight','name')
                                    ->label
                                    ('الوزن'),

                                Forms\Components\Select::make('size_id')
                                    ->relationship('size','name')
                                    ->label
                                    ('الحجم'),
                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')->label('من مدينة'),
                                Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')->label('الى مدينة'),

                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع'),
                                Forms\Components\TextInput::make('price')->numeric()->label('التحصيل'),
                                Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن'),
//                                Forms\Components\TextInput::make('total_weight')->numeric()->label('الوزن الكلي'),
                                Forms\Components\TextInput::make('canceled_info')
                                    ->hidden(fn(Forms\Get $get):bool=>!$get('active'))->live()
                                    ->label('سبب الارجاع في حال ارجاع الطلب'),


                            ]),

                        Tabs\Tab::make('محتويات الطلب')
                            ->schema([
                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                    SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),

                                Forms\Components\TextInput::make('code')->default(fn()=>"FC". now()->format('dHis')),
                                    Forms\Components\Select::make('unit_id')->relationship('unit','name')->label('الوحدة'),

                                    Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
//                                    Forms\Components\Select::make('weight')->relationship('category','name')->label('من فئة '),
                                    Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),

//                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
//                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
//                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                            ]),
                ]),
                        Tabs\Tab::make('سلسلة التوكيل')->schema([
                            Forms\Components\Repeater::make('agencies')->relationship('agencies')->schema([
                                Forms\Components\Select::make('user_id')->options(User::where(fn($query)=>
                                $query->where('level',LevelUserEnum::DRIVER->value)->orWhere('level',LevelUserEnum::STAFF->value)
                                )->pluck('name','id'))->label('الموظف')->searchable()->required(),
                               Forms\Components\Radio::make('status')->options([
                                   TaskAgencyEnum::TASK->value=>TaskAgencyEnum::TASK->getLabel(),
                                   TaskAgencyEnum::TAKE->value=>TaskAgencyEnum::TAKE->getLabel(),
                                   TaskAgencyEnum::DELIVER->value=>TaskAgencyEnum::DELIVER->getLabel(),
                               ])->label('المهمة'),
                                Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),

                            ])
                        ])




            ])->columnSpanFull()
            ]);

    }
    public static function table(Table $table ) : Table
    {

        return $table

            ->columns([
                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(\LaraZeus\Qr\Facades\Qr::render('2222'))
            ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('code'),

                Tables\Columns\TextColumn::make('status')->label('حالة الطلب'),
                Tables\Columns\TextColumn::make('type')->label('نوع الطلب'),
                Tables\Columns\TextColumn::make('bay_type')->label('حالة الدفع'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل'),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة'),
                Tables\Columns\TextColumn::make('receive.name')->label('اسم المستلم '),
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
            RelationManagers\AgenciesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status',  OrderStatusEnum::PENDING)->count();
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
