<?php

namespace App\Filament\Widgets;

use App\Models\Prescription;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class EarningsChart extends ChartWidget
{
    protected static ?string $heading = null;

    public function __construct()
    {
        self::$heading = 'Pendapatan Bulan ' . now()->format('F');
    }

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = now()->startOfMonth();
        $endDate = now();

        $data = Prescription::rightJoin('prescribed_drugs', 'prescriptions.id', '=', 'prescribed_drugs.prescription_id')
            ->rightJoin('drugs', 'prescribed_drugs.drug_id', '=', 'drugs.id')
            ->selectRaw('DATE(prescriptions.prescription_date) as date, SUM(drugs.price * prescribed_drugs.quantity) as earnings')
            ->whereBetween('prescriptions.prescription_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        $earningsData = [];

        foreach ($dateRange as $date) {
            $matchingData = $data->where('date', $date)->first();

            if ($matchingData) {
                $earningsData[] = $matchingData->earnings;
            } else {
                $earningsData[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $earningsData,
                    'fill' => true,
                    'backgroundColor' => 'rgba(34, 153, 221, 0.2)',
                ],
            ],
            'labels' => array_map(function ($date) {
                return Carbon::parse($date)->format('d');
            }, $dateRange),
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
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems, data) {
                            const date = tooltipItems[0].label
                            return 'Tanggal: ' + date + ' ' + new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                        },
                        label: (context) => {
                            const earnings = context.dataset.data[context.dataIndex];
                            return 'Pendapatan: Rp. ' + earnings;
                        },
                    },
                },
            }
        }
    JS);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
