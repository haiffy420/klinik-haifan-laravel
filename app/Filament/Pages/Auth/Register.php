<?php

namespace App\Filament\Pages\Auth;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;

class Register extends BaseRegister
{
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = $this->getUserModel()::create($data);

        app()->bind(
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
            \Filament\Listeners\Auth\SendEmailVerificationNotification::class,
        );
        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        if ($data['role_id'] == 2) {
            $doctorData = [
                'user_id' => $user->id,
                'specialization' => $data['doctor']['specialization'],
            ];

            Doctor::create($doctorData);
        }

        if ($data['role_id'] == 3) {
            $staffData = [
                'user_id' => $user->id,
            ];

            Staff::create($staffData);
        }

        if ($data['role_id'] == 4) {
            $patientData = [
                'user_id' => $user->id,
                'bpjs' => $data['patient']['bpjs'],
            ];

            Patient::create($patientData);
        }

        return app(RegistrationResponse::class);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
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
                        Select::make('role_id')
                            ->label('Role')
                            ->options([
                                1 => 'Admin',
                                2 => 'Dokter',
                                3 => 'Apoteker',
                                4 => 'Pasien',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('doctor.specialization')
                            ->label('Spesialisasi')
                            ->visible(function (Get $get) {
                                if ($get('role_id') == 2) {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (Get $get) {
                                if ($get('role_id') == 2) {
                                    return false;
                                }
                                return true;
                            })
                            ->required(fn (\Filament\Forms\Get $get) => $get('role_id') == 2),
                        TextInput::make('patient.bpjs')
                            ->label('BPJS')
                            ->visible(function (Get $get) {
                                if ($get('role_id') == 4) {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (Get $get) {
                                if ($get('role_id') == 4) {
                                    return false;
                                }
                                return true;
                            })
                            ->required(fn (\Filament\Forms\Get $get) => $get('role_id') == 4),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    // ->columns(2)
                    ->statePath('data'),
            ),
        ];
    }
}
