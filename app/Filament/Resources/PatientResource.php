<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static function authorizeUsing($user, $ability)
    {
        if ($ability === 'viewAny') {
            return $user->can('viewAny', User::class);
        }
    }

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $modelLabel = 'Pasien';

    protected static ?string $navigationGroup = 'Pengguna';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('user_id')
                    ->relationship('user', 'id')
                    ->label('Data Pasien')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir')
                            ->required(),
                        Forms\Components\TextInput::make('contact_number')
                            ->label('Kontak')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('bpjs')
                    ->label('BPJS')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Fieldset::make('user_id')
                    ->relationship('user', 'id')
                    ->label('Foto Pasien')
                    ->schema([
                        Forms\Components\FileUpload::make('user.avatar')
                            ->label('')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->minSize(1)
                            ->imagePreviewHeight('200')
                            // ->panelAspectRatio('1.5:1')
                            ->maxSize(4096)
                            ->required()
                            ->visibility('private'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('Foto')
                    ->defaultImageUrl(url('avatars/default.png'))
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.contact_number')
                    ->label('Kontak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.address')
                    ->label('Alamat')
                    ->limit(30),
                Tables\Columns\TextColumn::make('bpjs')
                    ->label('BPJS')
                    ->searchable(),
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('../users/create'),
            'view' => Pages\ViewPatient::route('/{record}'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
