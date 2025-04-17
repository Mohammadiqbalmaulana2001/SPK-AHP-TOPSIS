<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKriteria extends Model
{
    protected $table = 'sub_kriterias';

    protected $fillable = [
        'kriteria_id', 
        'tipe',
        'kode',
        'nama', 
        'bobot',
        'bobot_global'
    ];

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class);
    }
    
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }
}