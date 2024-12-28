<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Revenue', Order::sum('total')),
            Stat::make('Total User', User::where('role_id', 2)->count()),
            Stat::make('Total Order', Order::count()),
        ];
    }
}
