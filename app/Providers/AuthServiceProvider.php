<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\Staff;
use App\Models\User;
use App\Policies\DoctorResourcePolicy;
use App\Policies\DrugResourcePolicy;
use App\Policies\PatientResourcePolicy;
use App\Policies\PrescriptionResourcePolicy;
use App\Policies\QueueResourcePolicy;
use App\Policies\StaffResourcePolicy;
use App\Policies\UserResourcePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserResourcePolicy::class,
        Doctor::class => DoctorResourcePolicy::class,
        Staff::class => StaffResourcePolicy::class,
        Patient::class => PatientResourcePolicy::class,
        Drug::class => DrugResourcePolicy::class,
        Prescription::class => PrescriptionResourcePolicy::class,
        Queue::class => QueueResourcePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
