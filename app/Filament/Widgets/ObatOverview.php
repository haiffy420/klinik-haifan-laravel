<?php

namespace App\Filament\Widgets;

use App\Models\Drug;
use App\Models\PrescribedDrugs;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ObatOverview extends BaseWidget
{
    public static function getTodayEarning(): string
    {
        $totalIncome = 0;
        $formattedNumber = 0;

        $prescribedDrugs = PrescribedDrugs::query()
            ->join('prescriptions', 'prescriptions.id', '=', 'prescribed_drugs.prescription_id')
            ->whereDate('prescriptions.prescription_date', Carbon::today())
            ->get();

        foreach ($prescribedDrugs as $prescribedDrug) {
            $drug = Drug::find($prescribedDrug->drug_id);

            $incomeForDrug = $drug->price * $prescribedDrug->quantity;

            $totalIncome += $incomeForDrug;
        }

        $formattedNumber = number_format($totalIncome, 0, ',', '.');

        return $formattedNumber;
    }

    public static function getTotalEarnings(): string
    {
        $totalIncome = 0;
        $formattedNumber = 0;
        $prescribedDrugs = PrescribedDrugs::all();

        foreach ($prescribedDrugs as $prescribedDrug) {
            $drug = Drug::find($prescribedDrug->drug_id);

            $incomeForDrug = $drug->price * $prescribedDrug->quantity;

            $totalIncome += $incomeForDrug;
        }
        $formattedNumber = number_format($totalIncome, 0, ',', '.');

        return $formattedNumber;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Obat', Drug::query()->where('expiration_date', '>', now())->where('stock', '>', 0)->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Pasien Ditangani', Queue::query()->where('status', 1)->count() . '/' . Queue::all()->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Pendapatan Harian', 'Rp. ' . self::getTodayEarning())
                ->description('Pendapatan Hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([65, 70, 70, 85, 75, 80, 90])
                ->color('success'),
            Stat::make('Semua Pendapatan', 'Rp. ' . self::getTotalEarnings())
                ->description('Total Pendapatan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([65, 70, 70, 85, 75, 80, 90])
                ->color('success'),
        ];
    }
}
