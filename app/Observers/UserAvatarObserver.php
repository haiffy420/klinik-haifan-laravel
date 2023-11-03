<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserAvatarObserver
{
    /**
     * Handle the User "created" event.
     */
    // public function created(User $user): void
    // {
    //     //
    // }

    // /**
    //  * Handle the User "updated" event.
    //  */
    public function updated(User $user): void
    {
        if ($user->isDirty('avatar') && $user->getOriginal('avatar') !== 'avatars/default.png') {
            $oldAvatarPath = $user->getOriginal('avatar');
            if ($oldAvatarPath !== null && Storage::disk('public')->exists($oldAvatarPath)) {
                Storage::disk('public')->delete($oldAvatarPath);
            }
        }
    }

    // /**
    //  * Handle the User "deleted" event.
    //  */
    // public function deleted(User $user): void
    // {
    //     //
    // }

    // /**
    //  * Handle the User "restored" event.
    //  */
    // public function restored(User $user): void
    // {
    //     //
    // }

    // /**
    //  * Handle the User "force deleted" event.
    //  */
    // public function forceDeleted(User $user): void
    // {
    //     //
    // }
}
