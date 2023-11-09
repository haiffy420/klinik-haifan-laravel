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
    protected static ?string $heading = null;

    public function __construct()
    {
        self::$heading = 'Transaksi Bulan ' . now()->format('F');
    }

    protected static string $color = 'info';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Calculate the start and end dates for the current month
        $startDate = now()->startOfMonth();
        $endDate = now();

        // Query for the data within the current month
        $data = Prescription::selectRaw('DATE(prescriptions.prescription_date) as date, COUNT(prescriptions.id) as transactions')
            ->whereBetween('prescription_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        // Generate an array of dates for the current month
        $dateRange = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->toDateString();
            $currentDate->addDay(); // Increment by one day
        }

        // Initialize an array to store the transactions data
        $transactionsData = [];

        // Loop through the date range and match data if available
        foreach ($dateRange as $date) {
            $matchingData = $data->where('date', $date)->first();

            if ($matchingData) {
                $transactionsData[] = $matchingData->transactions;
            } else {
                $transactionsData[] = 0; // If no data available for a date, set transactions to 0
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $transactionsData,
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
                        precision: 0,
                    },
                },
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems, data) {
                            const date = tooltipItems[0].label
                            return 'Tanggal: ' + date + ' ' + new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                        },
                    },
                },
                legend: {
                    display: false,
                }
            },
        }
    JS);
    }

    protected function getType(): string
    {
        return 'line';
    }
}
