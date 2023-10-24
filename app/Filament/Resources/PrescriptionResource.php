<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Filament\Resources\PrescriptionResource\RelationManagers;
use App\Filament\Resources\PrescriptionResource\RelationManagers\PrescribedDrugsRelationManager;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Doctor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
        if (auth()->user()->role_id == 3) {
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
                                        return Drug::where('expiration_date', '>', now())
                                            ->get()
                                            ->pluck('name', 'id');
                                    })
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->numeric(),
                            ])
                            ->columns(2)
                            ->columnSpan(1)
                            ->itemLabel(function (array $state) {
                                $drugId = $state['drug_id'] ?? null;
                                if ($drugId) {
                                    // Fetch the drug name based on the drug ID
                                    $drug = Drug::find($drugId);
                                    return $drug ? $drug->name : null;
                                }
                                return null;
                            }),
                    ])->columns(1)
                    ->visibleOn(['create', 'view', 'edit']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->date()
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
                    // ->button()
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
