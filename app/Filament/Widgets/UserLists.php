<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserLists extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::where('role_id', 2)->orderBy("id", "desc")->limit(10))
            ->columns([
                TextColumn::make("name"),
                TextColumn::make("phone"),
                TextColumn::make("address")->formatStateUsing(function ($state) {
                    return "{$state->detail}, {$state->village->name}, {$state->district->name}, {$state->regency->name}, {$state->province->name}";
                }),
                ToggleColumn::make("status")->label("Status")->afterStateUpdated(function ($state, $record) {
                    Notification::make()
                        ->title('Update status successfully')
                        ->success()
                        ->send();
                }),
            ]);
    }
}
