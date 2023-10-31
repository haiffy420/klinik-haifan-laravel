<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Drug extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'stock', 'expiration_date'];

    public function prescribedDrugs(): BelongsToMany
    {
        return $this->belongsToMany(PrescribedDrugs::class, 'prescribed_drugs');
    }
}
