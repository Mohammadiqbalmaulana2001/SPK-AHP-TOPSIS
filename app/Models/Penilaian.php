<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    protected $table = 'penilaians';

    protected $fillable = [
        'alternatif_id',
        'sub_kriteria_id',
        'nilai'
    ];

    public function alternatif(): BelongsTo
    {
        return $this->belongsTo(Alternatif::class);
    }

    public function subKriteria(): BelongsTo
    {
        return $this->belongsTo(SubKriteria::class);
    }
}