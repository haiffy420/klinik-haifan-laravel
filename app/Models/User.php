<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'date_of_birth',
        'contact_number',
        'address',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function booted(): void
    {
        self::deleting(function (User $record) {
            if ($record->avatar == 'avatars/default.png') {
                return;
            }

            $filePath = $record->avatar;

            if (Storage::disk('public')->exists($filePath)) {
                // Try to delete the file
                $deleted = Storage::disk('public')->delete($filePath);

                if (!$deleted) {
                    // Log an error or throw an exception
                    Log::error("Failed to delete file: $filePath");
                }
            }
        });
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatar = auth()->user()->avatar;
        // return 'http://127.0.0.1:8000/storage/' . $avatar;
        return 'https://klinik.haifan-tribuwono.my.id//storage/' . $avatar;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }
}
