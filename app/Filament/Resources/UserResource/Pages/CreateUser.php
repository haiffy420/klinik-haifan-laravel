<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = parent::handleRecordCreation($data);

        if ($data['role_id'] == 2) {
            Doctor::create([
                'user_id' => $user->id,
                'specialization' => $data['doctor']['specialization'],
            ]);
        }
        if ($data['role_id'] == 3) {
            Staff::create([
                'user_id' => $user->id,
            ]);
        }
        if ($data['role_id'] == 4) {
            Patient::create([
                'user_id' => $user->id,
                'bpjs' => $data['patient']['bpjs'],
            ]);
        }

        return $user;
    }
}
