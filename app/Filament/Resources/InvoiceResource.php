<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    // public static function shouldRegisterNavigation(): bool
    // {
    //     $user = auth()->user();
    //     if ($user->role_id == 3) {
    //         return true;
    //     }
    //     return false;
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('staff_id')
                    ->options(
                        function () {
                            return Staff::query()
                                ->get()
                                ->pluck('user.name', 'id');
                        }
                    )
                    ->required()
                    ->native(false)
                    ->live()
                    ->searchable(),
                Forms\Components\Select::make('patient_id')
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
                Forms\Components\DatePicker::make('invoice_date')
                    ->required(),
                Forms\Components\DatePicker::make('expiration_date')
                    ->required(),
                Forms\Components\Repeater::make('invoiceItems')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('drug_id')
                            ->options(function () {
                                return \App\Models\Drug::all()->pluck('name', 'id');
                            })
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            InvoiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
