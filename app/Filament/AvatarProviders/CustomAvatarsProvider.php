<?php

namespace App\Filament\AvatarProviders;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class CustomAvatarsProvider implements Contracts\AvatarProvider
{
    public function get(Model | Authenticatable $record): string
    {
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        return 'https://klinik.haifan-tribuwono.my.id//storage/avatars/BHue66Zk6iY8onrVq63iNZvn7bTLhC-metacGZwXzEuZ2lm-.gif';
        //  . urlencode($name);
    }
}
