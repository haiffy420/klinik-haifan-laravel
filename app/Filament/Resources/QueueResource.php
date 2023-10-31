<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Antrian';

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
                            ->preload()
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
                            ->preload()
                            ->native(false)
                            ->live()
                            ->searchable(),
                        Forms\Components\DatePicker::make('entry_date')
                            ->label('Tanggal')
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->label('Sudah ditangani?')
                            ->inline(false)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.user.name')
                    ->label('Nama Pasien')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Nama Dokter')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Sudah Ditangani')
                    ->boolean(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Mendaftar')
                    ->date('d F Y')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'view' => Pages\ViewQueue::route('/{record}'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
