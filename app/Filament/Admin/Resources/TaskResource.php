<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $label = 'المهام الإدارية';
    protected static ?string $navigationLabel = 'المهام الإدارية';
    protected static ?string $pluralLabel = 'المهام الإدارية';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Section::make('مهام')->schema([
                    Forms\Components\Select::make('user_id')->relationship('user', 'name')->label('المستخدم')->searchable(),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('from')->label('إستلام من'),
                        Forms\Components\TextInput::make('sender_phone')->label('رقم الهاتف'),
                    ]),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('to')->label('التسليم لـ'),
                        Forms\Components\TextInput::make('receive_phone')->label('رقم الهاتف'),
                    ]),
                    Forms\Components\Textarea::make('task')->label('ملاحظات')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at','desc')
            ->poll(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('التسلسل'),
                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('إستلام من'),
                Tables\Columns\TextColumn::make('to')->label('التسليم لـ'),
                Tables\Columns\TextColumn::make('task')->label('المهمة'),
                Tables\Columns\TextColumn::make('is_complete')->label('الحالة')->formatStateUsing(fn($state)=>$state?'تم':'بالإنتظار')
                ->color(fn($state)=>$state?'success':'danger')
                ,

                Tables\Columns\TextColumn::make('created_at')->since()->label('منذ')->sortable(),


            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')->relationship('user', 'name')->label('المستخدم')->searchable(),
                TernaryFilter::make('is_complete')->lable('حالة المهمة'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
