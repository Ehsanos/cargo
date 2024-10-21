<?php

namespace App\Filament\Branch\Resources;

use App\Enums\BayTypeEnum;
use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Branch\Resources\OrderResource\Pages;
use App\Filament\Branch\Resources\OrderResource\RelationManagers;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Livewire\Notifications;
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

                                Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')->label('اسم الفرع المستلم')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_target_id', $branch->city_id);
                                        }
                                    })->live(),

                                Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('sender_phone', $user->phone);
                                            $set('sender_address', $user->address);
                                            $set('city_source_id', $user?->city_id);
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
                                            $set('city_target_id', $user?->city_id);
                                            $set('branch_target_id', $user?->branch_id);
                                        }
                                    })->live(),
                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')
                                    ->label('من مدينة')->reactive(),
                                Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')
                                    ->reactive()
                                    ->label('الى مدينة'),

                                Forms\Components\Select::make('bay_type')->options([
                                    BayTypeEnum::AFTER->value => BayTypeEnum::AFTER->getLabel(),
                                    BayTypeEnum::BEFORE->value => BayTypeEnum::BEFORE->getLabel()

                                ])->label('نوع الدفع')->required(),


                                Forms\Components\Select::make('weight_id')
                                    ->relationship('weight', 'name')
                                    ->label
                                    ('الوزن'),

                                Forms\Components\Select::make('size_id')
                                    ->relationship('size', 'name')
                                    ->label
                                    ('الحجم'),
                                Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة'),
                                Forms\Components\TextInput::make('price')->numeric()->label('التحصيل'),
                                Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن')->default(1),
                                Forms\Components\Radio::make('far_sender')
                                    ->options([
                                        true => 'المرسل',
                                        false => 'المستلم'
                                    ])->required()->default(true)->inline()
                                    ->label('أجور الشحن'),
                                Forms\Components\TextInput::make('total_weight')->numeric()->label('الوزن الكلي'),

                            ]),

                        Tabs\Tab::make('محتويات الطلب')
                            ->schema([
                                Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                    SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),

                                    Forms\Components\TextInput::make('code')->default(fn() => "FC" . now()->format('dHis')),
                                    Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة'),
                                    Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
                                    Forms\Components\TextInput::make('weight')->label('وزن الشحنة'),
                                    Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),
//                                    Forms\Components\TextInput::make('length')->numeric()->label('الطول'),
//                                    Forms\Components\TextInput::make('width')->numeric()->label('العرض'),
//                                    Forms\Components\TextInput::make('height')->numeric()->label('الارتفاع'),
                                ]),
                            ]),
                        Tabs\Tab::make('سلسلة التوكيل')->schema([
                            Forms\Components\Repeater::make('agencies')->relationship('agencies')->schema([
                                Forms\Components\Select::make('user_id')->options(User::pluck('name', 'id'))->label('الموظف')->searchable()->required(),
                                Forms\Components\Radio::make('status')->options([
                                    TaskAgencyEnum::TASK->value => TaskAgencyEnum::TASK->getLabel(),
//                                    TaskAgencyEnum::TAKE->value => TaskAgencyEnum::TAKE->getLabel(),
                                    TaskAgencyEnum::DELIVER->value => TaskAgencyEnum::DELIVER->getLabel(),
                                ])->label('المهمة'),
                                Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),

                            ])
                        ])
                    ])->columnSpanFull()


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           // ->poll(10)
            ->columns([
                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('code'),

                Tables\Columns\TextColumn::make('status')->label('حالة الطلب'),
                Tables\Columns\TextColumn::make('type')->label('نوع الطلب'),
                Tables\Columns\TextColumn::make('price')->label('التحصيل'),
                Tables\Columns\TextColumn::make('far')->label('أجور الشحن'),

                Tables\Columns\TextColumn::make('bay_type')->label('حالة الدفع'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل'),
                Tables\Columns\TextColumn::make('sender.phone')->label('هاتف المرسل')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive->phone, '+')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('citySource.name')->label('من مدينة'),
                Tables\Columns\TextColumn::make('receive.name')->label('معرف المستلم '),
                Tables\Columns\TextColumn::make('global_name')->label('اسم المستلم'),

                Tables\Columns\TextColumn::make('receive.address')->label('عنوان المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('receive.phone')->label('هاتف المستلم ')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive->phone, '+')))->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cityTarget.name')->label('الى مدينة '),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الشحنة')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()) // عرض الزمن بشكل نسبي


            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('select_pick_id')
                    ->form([
                    Forms\Components\Select::make('pick_id')
                        ->options(User::where('users.branch_id',auth()->user()->branch_id)->where(fn($query)=>$query->where('level',LevelUserEnum::STAFF->value)->orWhere('level',LevelUserEnum::STAFF->value))->pluck('name','id'))->searchable()->label('موظف الإلتقاط')
                ])
                    ->action(function ($record, $data) {
                        $record->update(['pick_id' => $data['pick_id'],'status'=>OrderStatusEnum::AGREE->value]);
                        Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف الإلتقاط بنجاح')->success()->send();
                    })
                    ->visible(fn($record) => $record->pick_id == null)
                    ->label('تحديد موظف الإلتقاط')->color('info')->button(),

                Tables\Actions\Action::make('select_given_id')->form([
                    Forms\Components\Select::make('given_id')->options(User::where('users.branch_id',auth()->user()->branch_id)->where(fn($query)=>$query->where('level',LevelUserEnum::STAFF->value)->orWhere('level',LevelUserEnum::STAFF->value))->pluck('name','id'))->searchable()->label('موظف الإلتقاط')
                ])
                    ->action(function ($record, $data) {
                        $record->update(['given_id' => $data['given_id']]);
                        Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف التسليم بنجاح')->success()->send();
                    })
                    ->visible(fn($record) => $record->given_id == null)
                    ->label('تحديد موظف التسليم')->color('info')->button(),
                Tables\Actions\Action::make('cancel_order')
                    ->form( [
                        Forms\Components\Radio::make('status')->options([
                            OrderStatusEnum::CANCELED->value=>OrderStatusEnum::CANCELED->getLabel(),
                            OrderStatusEnum::RETURNED->value=>OrderStatusEnum::RETURNED->getLabel(),
                        ])->label('الحالة')->required()->default(OrderStatusEnum::CANCELED->value),
                        Forms\Components\Textarea::make('msg_cancel')->label('سبب الإلغاء / الإعادة')
                    ])
                    ->action(function ($record, $data) {
                        DB::beginTransaction();
                        try {
                            $record->update(['status' => $data['status'],'canceled_info'=>$data['msg_cancel']]);
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                        }
                    })->label('الإلغاء / الإعادة')->button()->color('danger')
                    ->visible(fn($record)=>$record->status === OrderStatusEnum::PENDING || $record->status === OrderStatusEnum::AGREE || $record->status === OrderStatusEnum::PICK || $record->status === OrderStatusEnum::TRANSFER)

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


   /* public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('orders.sender_id', auth()->user()->id)
            ->orWhere('orders.branch_target_id', auth()->user()->id)
            ->orWhere('orders.branch_source_id', auth()->user()->id);
    }*/


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
