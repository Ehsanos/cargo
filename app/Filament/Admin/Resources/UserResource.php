<?php /** @noinspection ALL */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Enums\JobUserEnum;
use App\Enums\LevelUserEnum;
use App\Models\Branch;
use App\Enums\ActivateStatusEnum;
use Dotswan\MapPicker\Fields\Map;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Tabs;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?string $label='مستخدم';
    protected static ?string $navigationLabel='المستخدمون';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Tabs::make('Tabs')->tabs(
                    [
                        Tabs\Tab::make('المعلومات الاساسية')
                            ->schema([
                                Forms\Components\CheckboxList::make('roles')
                                    ->relationship('roles', 'name')->label('الصلاحيات'),
                                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                Forms\Components\TextInput::make('email')->label('البريد الالكتروني')->email()->required(),
                                Forms\Components\TextInput::make('username')->label('اسم')->required(),
                                Forms\Components\TextInput::make('password')->password()->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))->label('كلمة المرور'),
                                Forms\Components\TextInput::make('phone')->label('الهاتف')->tel(),
                                Forms\Components\RichEditor::make('address')->label('العنوان'),
                                Forms\Components\Select::make('city_id')->relationship('city','name')->label('المدينة')->live()
                               ->reactive()->afterStateUpdated(function ($state, callable $set) {
                                    $set('branch_id', null);
                                    $set('temp', Branch::where('city_id', $state)->pluck('name'));
                                })->live(),


                                Forms\Components\Select::make('branch_id')->label('الفرع')
                              ->options(fn (callable $get) => $get('temp') ?? [])->hidden(fn(Forms\Get $get):bool =>
                                    !$get('city_id'))->live(),
                                  Forms\Components\TextInput::make('full_name')->label('الاسم الكامل'),
                                Forms\Components\DateTimePicker::make('birth_date')->label('تاريخ الميلاد'),

                            ]),

                        Tabs\Tab::make('التوصيف الوظيفي')
                            ->schema([
                                Forms\Components\Select::make('status')->options(
                                    [
                                        ActivateStatusEnum::ACTIVE->value=>   ActivateStatusEnum::ACTIVE->getLabel(),
                                        ActivateStatusEnum::INACTIVE->value=>   ActivateStatusEnum::INACTIVE->getLabel(),
                                        ActivateStatusEnum::BLOCK->value=>   ActivateStatusEnum::BLOCK->getLabel(),

                                    ]
                                )->label('حالة المستخدم')->default('active'),
                                Forms\Components\Select::make('level')->options(
                                    [
                                        LevelUserEnum::USER->value=>   LevelUserEnum::USER->getLabel(),
                                        LevelUserEnum::ADMIN->value=>   LevelUserEnum::ADMIN->getLabel(),
                                        LevelUserEnum::DRIVER->value=>   LevelUserEnum::DRIVER->getLabel(),
                                        LevelUserEnum::BRANCH->value=>   LevelUserEnum::BRANCH->getLabel(),
                                    ]
                                )->label('رتبة المستخدم'),
                                Forms\Components\Select::make('job')->options(
                                    [
                                        JobUserEnum::STAFF->value=>   JobUserEnum::STAFF->getLabel(),
                                        JobUserEnum::ACCOUNTING->value=>   JobUserEnum::ACCOUNTING->getLabel(),
                                        JobUserEnum::MANGER->value=>   JobUserEnum::MANGER->getLabel(),
                                    ]
                                )->label('وظيفة المستخدم'),
                            ]),

                        Tabs\Tab::make('الخارطة')
                            ->schema([
                                Forms\Components\TextInput::make('latitude')->label('خط العرض'),
                                Forms\Components\TextInput::make('longitude')->label('خط الطول'),
                                Map::make('location')
                                    ->label('Location')
                                    ->columnSpanFull()
                                    ->extraStyles([
                                        'min-height: 70vh',
                                        'border-radius: 50px'
                                    ])
                                    ->liveLocation()
                                    ->showMarker()
                                    ->markerColor("#22c55eff")
                                    ->draggable()
                                    ->zoom(15)
                                    ->showMyLocationButton()
                                    ->extraTileControl([])
                                    ->extraControl([
                                        'zoomDelta'           => 1,
                                        'zoomSnap'            => 2,
                                    ])



                            ]),


                    ]

                )->contained(false)

                    ->columnSpanFull(),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم'),
                Tables\Columns\TextColumn::make('status')->badge()->label('حالة المستخدم'),
                Tables\Columns\TextColumn::make('level')->badge()
                    ->label('فئة المستخدم'),

                Tables\Columns\TextColumn::make('job')->badge()->label('نوع الموظف'),

                Tables\Columns\TextColumn::make('branch.name')->label('فرع'),
                Tables\Columns\TextColumn::make('city.name')->label('المدينة'),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            RelationManagers\BalancesRelationManager::class,
            RelationManagers\PendingBalancesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
