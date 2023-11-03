<?php

namespace App\Filament\Widgets;

use App\Models\Prescription;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class DrugChart extends ChartWidget
{
    protected static ?string $heading = 'Transaksi';

    protected static string $color = 'info';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Prescription::query()
            ->selectRaw('DATE(prescriptions.prescription_date) as date, COUNT(prescriptions.id) as transactions')
            ->whereBetween('prescription_date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->groupBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $data->pluck('transactions'),
                ],
            ],
            'labels' => $data->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d F');
            }),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        precision: 0,
                    },
                },
            },
        }
    JS);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
