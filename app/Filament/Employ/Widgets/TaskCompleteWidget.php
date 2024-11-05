<?php

namespace App\Filament\Employ\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TaskCompleteWidget extends BaseWidget
{
    protected static ?string $heading="المهام الإدارية المنجزة";
    protected int | string | array $columnSpan=2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::where('user_id', auth()->id())->where('is_complete', true)->latest(),
            )
            ->poll(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('التسلسل'),
                Tables\Columns\TextColumn::make('from')->label('إستلام من'),
                Tables\Columns\TextColumn::make('sender_phone')->label('هاتف المرسل')->url(fn($state)=>"https://wa.me/".trim($state,'+'),true),
                Tables\Columns\TextColumn::make('to')->label('التسليم لـ'),
                Tables\Columns\TextColumn::make('receive_phone')->label('هاتف المستلم')->url(fn($state)=>"https://wa.me/".trim($state,'+'),true),

                Tables\Columns\TextColumn::make('task')->label('المهمة'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('منذ')->sortable(),

            ])->actions([
//                Tables\Actions\Action::make('complete')->label('إتمام')->requiresConfirmation()->action(fn($record) => $record->update(['is_complete'=> true]))
            ]);
    }
}
