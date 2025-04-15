<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kriteria extends Model
{
    protected $table = 'kriterias';

    protected $fillable = [
        'kode',
        'nama', 
        'bobot', 
        'tipe' // benefit atau cost
    ];

    public function subKriterias(): HasMany
    {
        return $this->hasMany(SubKriteria::class);
    }
}