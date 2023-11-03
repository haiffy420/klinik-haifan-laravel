<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static function authorizeUsing($user, $ability)
    {
        if ($ability === 'viewAny') {
            return $user->can('viewAny', User::class);
        }
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Semua Pengguna';

    protected static ?string $navigationGroup = 'Pengguna';

    protected static ?string $navigationLabel = 'Semua Pengguna';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('Tanggal Lahir')
                                    ->columnSpan(0.5)
                                    ->required(),
                                Forms\Components\TextInput::make('contact_number')
                                    ->label('Kontak')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('role_id')
                                    ->label('Role')
                                    ->options([
                                        1 => 'Admin',
                                        2 => 'Dokter',
                                        3 => 'Staff',
                                        4 => 'Pasien',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->live(),
                                Forms\Components\TextInput::make('doctor.specialization')
                                    ->label('Spesialisasi')
                                    ->visible(function (\Filament\Forms\Get $get) {
                                        if ($get('role_id') == 2) {
                                            return true;
                                        }
                                        return false;
                                    })
                                    ->disabled(function (\Filament\Forms\Get $get) {
                                        if ($get('role_id') == 2) {
                                            return false;
                                        }
                                        return true;
                                    })
                                    ->visibleOn('create')
                                    ->required(fn (\Filament\Forms\Get $get) => $get('role_id') == 2),
                                Forms\Components\TextInput::make('patient.bpjs')
                                    ->label('BPJS')
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('role_id') == 4)
                                    ->disabled(fn (\Filament\Forms\Get $get) => $get('role_id') != 4)
                                    ->visibleOn('create')
                                    ->required(fn (\Filament\Forms\Get $get) => $get('role_id') == 4),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->disabled(function (\Filament\Forms\Get $get) {
                                        if ($get('role_id') == 1) {
                                            return true;
                                        }
                                        return false;
                                    })
                                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255),
                            ])->columns(3),
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Foto')
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
                            ->default('avatars/default.png')
                            ->visibility('private'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Foto')
                    ->defaultImageUrl(url('images/default.png'))
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
