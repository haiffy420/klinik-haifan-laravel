<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Database\Eloquent\Model;

class EditProfile extends BaseEditProfile
{
    protected function getSpecializationFormComponent(): Component
    {
        return TextInput::make('doctor.specialization')
            ->label('Spesialisasi')
            ->disabled(function () {
                return auth()->user()->role_id != 2;
            })
            ->hidden(function () {
                return auth()->user()->role_id != 2;
            })
            ->required(function () {
                return auth()->user()->role_id == 2;
            })
            ->maxLength(255);
    }

    protected function getBPJSFormComponent(): Component
    {
        return TextInput::make('patient.bpjs')
            ->label('BPJS')
            ->disabled(function () {
                return auth()->user()->role_id != 4;
            })
            ->hidden(function () {
                return auth()->user()->role_id != 4;
            })
            ->required(function () {
                return auth()->user()->role_id == 4;
            })
            ->maxLength(255);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('')
                    ->schema([
                        $this->getNameFormComponent(),
                        TextInput::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir')
                            ->columnSpan(0.5)
                            ->required(),
                        TextInput::make('contact_number')
                            ->label('Kontak')
                            ->required()
                            ->maxLength(255),
                        $this->getSpecializationFormComponent(),
                        $this->getBPJSFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(2),
                FileUpload::make('avatar')
                    ->label('Foto')
                    ->directory('avatars')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->imagePreviewHeight('100')
                    ->panelAspectRatio('1.5:1')
                    ->minSize(1)
                    ->maxSize(4096)
                    ->required()
                    ->visibility('private'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record instanceof User && $record->role_id == 2) {
            // Handle specialization update for doctors
            $doctor = $record->doctor;
            $doctor->specialization = $data['doctor']['specialization'];
            $doctor->save();
            // Handle bpjs update for patients
            $patient = $record->patient;
            $patient->bpjs = $data['patient']['bpjs'];
            $patient->save();
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function fillForm(): void
    {
        $user = $this->getUser();

        $data = $user->attributesToArray();

        $this->callHook('beforeFill');

        if ($user->role_id == 2) {
            $data['doctor']['specialization'] = $user->doctor->specialization;
        }
        if ($user->role_id == 2) {
            $data['bpjs']['bpjs'] = $user->bpjs->bpjs;
        }

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
}
