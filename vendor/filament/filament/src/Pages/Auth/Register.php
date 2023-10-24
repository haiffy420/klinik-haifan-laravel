<?php

namespace Filament\Pages\Auth;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @property Form $form
 */
class Register extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.auth.register';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected string $userModel;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

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

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
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
                                3 => 'Staff',
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

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute('Minimal 8 Karakter.');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->required()
            ->dehydrated(false);
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::pages/auth/register.actions.login.label'))
            ->url(filament()->getLoginUrl());
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/register.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/register.heading');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
            ->submit('register');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
