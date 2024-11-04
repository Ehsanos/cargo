<?php

namespace App\Filament\Employ\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TaskWidget extends BaseWidget
{
    protected static ?string $heading="المهام الإدارية";
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::where('user_id', auth()->id())->where('is_complete', false),
            )
            ->columns([
//                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('إستلام من'),
                Tables\Columns\TextColumn::make('to')->label('التسليم لـ'),
                Tables\Columns\TextColumn::make('task')->label('المهمة'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('منذ'),
            ])->actions([
                Tables\Actions\Action::make('complete')->label('إتمام')->requiresConfirmation()->action(fn($record) => $record->update(['is_complete'=> true]))
            ]);
    }
}
