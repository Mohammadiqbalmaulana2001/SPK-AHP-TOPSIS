<?php

namespace App\Services;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use Illuminate\Support\Collection;

class TopsisService
{
    protected Collection $kriterias;
    protected Collection $alternatifs;

    public function __construct()
    {
        $this->kriterias = Kriteria::with('subKriterias')->get();
        $this->alternatifs = Alternatif::with('penilaians.subKriteria')->get();
    }

    public function calculate(): array
    {
        $decisionMatrix = [];
        $weights = [];
        $types = [];

        // Step 1: Membuat matriks keputusan awal
        foreach ($this->alternatifs as $alt) {
            $decisionMatrix[$alt->id] = [];

            foreach ($this->kriterias as $kriteria) {
                $nilai = $alt->penilaians->first(function ($p) use ($kriteria) {
                    return $p->subKriteria->kriteria_id == $kriteria->id;
                })?->subKriteria?->nilai ?? 0;

                $decisionMatrix[$alt->id][$kriteria->id] = $nilai;
            }

            $alt->nama_alt = $alt->nama;
        }

        // Step 2: Normalisasi
        $normalizationDivisor = [];
        foreach ($this->kriterias as $kriteria) {
            $sumSquares = 0;
            foreach ($decisionMatrix as $row) {
                $sumSquares += pow($row[$kriteria->id], 2);
            }
            $normalizationDivisor[$kriteria->id] = sqrt($sumSquares);
        }

        $normalizedMatrix = [];
        foreach ($decisionMatrix as $altId => $row) {
            foreach ($row as $kriteriaId => $value) {
                $normalizedMatrix[$altId][$kriteriaId] = $normalizationDivisor[$kriteriaId] == 0
                    ? 0
                    : $value / $normalizationDivisor[$kriteriaId];
            }
        }

        // Step 3: Matriks keputusan ternormalisasi terbobot
        foreach ($this->kriterias as $kriteria) {
            $weights[$kriteria->id] = $kriteria->bobot;
            $types[$kriteria->id] = $kriteria->tipe;
        }

        $weightedMatrix = [];
        foreach ($normalizedMatrix as $altId => $row) {
            foreach ($row as $kriteriaId => $value) {
                $weightedMatrix[$altId][$kriteriaId] = $value * $weights[$kriteriaId];
            }
        }

        // Step 4: Solusi ideal positif & negatif
        $idealPos = [];
        $idealNeg = [];
        foreach ($this->kriterias as $kriteria) {
            $column = array_column(array_map(fn ($row) => $row[$kriteria->id], $weightedMatrix), null);
            if ($types[$kriteria->id] === 'benefit') {
                $idealPos[$kriteria->id] = max($column);
                $idealNeg[$kriteria->id] = min($column);
            } else {
                $idealPos[$kriteria->id] = min($column);
                $idealNeg[$kriteria->id] = max($column);
            }
        }

        // Step 5: Hitung jarak ke solusi ideal
        $results = [];
        foreach ($weightedMatrix as $altId => $row) {
            $dPlus = 0;
            $dMin = 0;

            foreach ($row as $kriteriaId => $value) {
                $dPlus += pow($value - $idealPos[$kriteriaId], 2);
                $dMin += pow($value - $idealNeg[$kriteriaId], 2);
            }

            $dPlus = sqrt($dPlus);
            $dMin = sqrt($dMin);
            $score = $dPlus + $dMin == 0 ? 0 : $dMin / ($dPlus + $dMin);

            $results[] = [
                'alternatif_id' => $altId,
                'nama' => Alternatif::find($altId)->nama,
                'nilai_preferensi' => round($score, 4),
                'd_plus' => round($dPlus, 4),
                'd_minus' => round($dMin, 4)
            ];
        }

        // Step 6: Urutkan
        usort($results, fn($a, $b) => $b['nilai_preferensi'] <=> $a['nilai_preferensi']);

        return $results;
    }
}
