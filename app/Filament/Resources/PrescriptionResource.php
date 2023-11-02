<?php

namespace App\Filament\Resources;

use App\Tables\Columns\EarningsColumn;
use App\Filament\Resources\PrescriptionResource\Pages;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Doctor;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getModelLabel(): string
    {
        if (auth()->user()->role_id == 1) {
            return 'Transaksi';
        } else if (auth()->user()->role_id == 2) {
            return 'Resep';
        } else if (auth()->user()->role_id == 3) {
            return 'Invoice';
        }
        return 'Resep Dokter';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('doctor_id')
                            ->label('Nama Dokter')
                            ->options(
                                function () {
                                    return Doctor::query()
                                        ->get()
                                        ->pluck('user.name', 'id');
                                }
                            )
                            ->required()
                            ->native(false)
                            ->live()
                            ->searchable(),
                        Forms\Components\Select::make('patient_id')
                            ->label('Nama Pasien')
                            ->options(
                                function () {
                                    return Patient::query()
                                        ->get()
                                        ->pluck('user.name', 'id');
                                }
                            )
                            ->required()
                            ->native(false)
                            ->live()
                            ->searchable(),
                        Forms\Components\DatePicker::make('prescription_date')
                            ->label('Tanggal')
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Obat')
                    ->schema([
                        Forms\Components\Repeater::make('prescribedDrugs')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('drug_id')
                                    ->label('Nama Obat')
                                    ->options(function () {
                                        return Drug::where('expiration_date', '>', now())->where('stock', '>', 0)
                                            ->get()
                                            ->pluck('name', 'id');
                                    })
                                    ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.drug_id'))
                                            ->reject(fn ($id) => $id === $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->hiddenOn(['view'])
                                    ->required(),
                                Forms\Components\Placeholder::make('view-stock')
                                    ->label('')
                                    ->visible(static function (Get $get) {
                                        return $get('drug_id') != null;
                                    })
                                    ->content(static function (Get $get) {
                                        $stock = 'Stok Obat: ' . Drug::query()->where('id', $get('drug_id'))->pluck('stock')->first();
                                        return $stock;
                                    })
                                    ->hiddenOn(['view']),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->maxValue(static function (Get $get) {
                                        $stock = Drug::query()->where('id', $get('drug_id'))->pluck('stock')->first();
                                        return $stock;
                                    })
                                    ->required()
                                    ->numeric(),
                            ])
                            ->itemLabel(function (array $state) {
                                $drugId = $state['drug_id'] ?? null;
                                if ($drugId) {
                                    $drug = Drug::find($drugId);
                                    return $drug ? $drug->name : null;
                                }
                                return null;
                            })
                            ->grid(3),
                    ])->columns(1)
                    ->visibleOn(['create', 'view', 'edit']),
            ]);
    }

    public static function table(Table $table): Table
    {
        $query = Prescription::query();

        if (auth()->user()->role_id == 2) {
            $query->where('doctor_id', auth()->user()->doctor->id);
        }
        if (auth()->user()->role_id == 4) {
            $query->where('patient_id', auth()->user()->patient->id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Nama Dokter')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.user.name')
                    ->label('Nama Pasien')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prescription_date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                EarningsColumn::make('Pendapatan')
                    ->label(function () {
                        return auth()->user()->role_id == 1 ? 'Pendapatan' : 'Total';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Print')
                    ->button()
                    ->color('success')
                    ->label('Cetak Invoice')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-printer')
                    ->icon('heroicon-o-printer')
                    ->action(fn (Prescription $record) => PrescriptionResource::printPrescription($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'view' => Pages\ViewPrescription::route('/{record}'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }

    public static function printPrescription(Prescription $prescription)
    {
        $prescriptionDate   = $prescription->prescription_date;
        $doctorName          = str($prescription->doctor->user->name)->replace(' ', '')->headline();
        $fileName           = "Invoice_{$prescriptionDate}_{$doctorName}.pdf";
        $total              = 0;
        $pdf                = Pdf::loadView('print', compact('prescription', 'fileName', 'total'));

        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }
}
