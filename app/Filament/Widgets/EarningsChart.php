<?php

namespace App\Filament\Widgets;

use App\Models\Prescription;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class EarningsChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Harian';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Prescription::join('prescribed_drugs', 'prescriptions.id', '=', 'prescribed_drugs.prescription_id')
            ->join('drugs', 'prescribed_drugs.drug_id', '=', 'drugs.id')
            ->selectRaw('DATE(prescriptions.prescription_date) as date, SUM(drugs.price * prescribed_drugs.quantity) as earnings')
            ->whereBetween('prescriptions.prescription_date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->groupBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $data->pluck('earnings'),
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
                        callback: (value) => 'Rp. ' + value,
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
