<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescribedDrugs extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['id','prescription_id','drug_id','quantity'];

    public function drugs(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'drug_id', 'id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
