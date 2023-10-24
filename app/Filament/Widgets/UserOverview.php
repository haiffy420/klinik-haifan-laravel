<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pasien', User::query()->where('role_id', 4)->count())
                ->description('Total pasien')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 60, 15, 4, 40])
                ->color('success'),
            Stat::make('Dokter', User::query()->where('role_id', 2)->count())
                ->description('Total dokter')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 60, 15, 4, 40])
                ->color('success'),
            Stat::make('Staff', User::query()->where('role_id', 3)->count())
                ->description('Total staff')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 60, 15, 4, 40])
                ->color('success'),
        ];
    }
}
