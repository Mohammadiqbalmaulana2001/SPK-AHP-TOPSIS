<?php

namespace App\Services;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Models\SubKriteria;
use Illuminate\Support\Facades\DB;

class TOPSISService
{
    public function process()
    {
        // Get all data
        $alternatifs = Alternatif::all();
        $kriterias = Kriteria::all();
        $subKriterias = SubKriteria::all();
        
        if ($alternatifs->isEmpty() || $kriterias->isEmpty() || $subKriterias->isEmpty()) {
            throw new \Exception("Data alternatif, kriteria, atau subkriteria kosong");
        }
        
        // Get data matrix
        $decisionMatrix = $this->createDecisionMatrix($alternatifs, $subKriterias);
        
        if (empty($decisionMatrix)) {
            throw new \Exception("Data penilaian tidak ditemukan atau tidak lengkap");
        }
        
        // Langkah 1: Normalisasi matrix keputusan
        $normalizedMatrix = $this->normalizeDecisionMatrix($decisionMatrix);
        
        // Langkah 2: Menghitung matrix keputusan normalisasi terbobot
        $weightedMatrix = $this->calculateWeightedNormalizedMatrix($normalizedMatrix, $subKriterias);
        
        // Langkah 3: Menentukan solusi ideal positif dan negatif
        $idealSolutions = $this->determineIdealSolutions($weightedMatrix, $subKriterias);
        
        // Langkah 4: Menghitung jarak setiap alternatif dari solusi ideal positif dan negatif
        $distances = $this->calculateDistances($weightedMatrix, $idealSolutions);
        
        // Langkah 5: Menghitung nilai preferensi untuk setiap alternatif
        $preferenceValues = $this->calculatePreferenceValues($distances);
        
        // Menggabungkan hasil dengan alternatif
        $results = $this->combineResults($alternatifs, $preferenceValues);
        
        return [
            'alternatifs' => $alternatifs,
            'kriterias' => $kriterias,
            'subKriterias' => $subKriterias,
            'decisionMatrix' => $decisionMatrix,
            'normalizedMatrix' => $normalizedMatrix,
            'weightedMatrix' => $weightedMatrix,
            'idealSolutions' => $idealSolutions,
            'distances' => $distances,
            'preferenceValues' => $preferenceValues,
            'results' => $results,
        ];
    }
    
    protected function createDecisionMatrix($alternatifs, $subKriterias)
    {
        $matrix = [];
        
        foreach ($alternatifs as $alternatif) {
            $row = [];
            foreach ($subKriterias as $subKriteria) {
                $penilaian = Penilaian::where('alternatif_id', $alternatif->id)
                    ->where('sub_kriteria_id', $subKriteria->id)
                    ->first();
                
                $row[$subKriteria->id] = $penilaian ? $penilaian->nilai : 0;
            }
            $matrix[$alternatif->id] = $row;
        }
        
        return $matrix;
    }
    
    protected function normalizeDecisionMatrix($decisionMatrix)
    {
        $normalizedMatrix = [];
        $squareSums = [];
        
        foreach ($decisionMatrix as $alternatifId => $row) {
            foreach ($row as $subKriteriaId => $value) {
                if (!isset($squareSums[$subKriteriaId])) {
                    $squareSums[$subKriteriaId] = 0;
                }
                $squareSums[$subKriteriaId] += pow($value, 2);
            }
        }
        
        foreach ($decisionMatrix as $alternatifId => $row) {
            $normalizedRow = [];
            foreach ($row as $subKriteriaId => $value) {
                $denominator = sqrt($squareSums[$subKriteriaId]);
                $normalizedRow[$subKriteriaId] = $denominator > 0 ? $value / $denominator : 0;
            }
            $normalizedMatrix[$alternatifId] = $normalizedRow;
        }
        
        return $normalizedMatrix;
    }
    
    protected function calculateWeightedNormalizedMatrix($normalizedMatrix, $subKriterias)
    {
        $weightedMatrix = [];
        
        foreach ($normalizedMatrix as $alternatifId => $row) {
            $weightedRow = [];
            foreach ($row as $subKriteriaId => $value) {
                $subKriteria = $subKriterias->firstWhere('id', $subKriteriaId);
                $weight = $subKriteria ? ($subKriteria->bobot_global / 100) : 0;
                $weightedRow[$subKriteriaId] = $value * $weight;
            }
            $weightedMatrix[$alternatifId] = $weightedRow;
        }
        
        return $weightedMatrix;
    }
    
    protected function determineIdealSolutions($weightedMatrix, $subKriterias)
    {
        $positiveIdeal = [];
        $negativeIdeal = [];
        
        foreach ($subKriterias as $subKriteria) {
            $values = [];
            foreach ($weightedMatrix as $row) {
                $values[] = $row[$subKriteria->id];
            }
            
            $kriteria = $subKriteria->kriteria;
            
            if ($kriteria->tipe === 'benefit') {
                $positiveIdeal[$subKriteria->id] = max($values);
                $negativeIdeal[$subKriteria->id] = min($values);
            } else {
                $positiveIdeal[$subKriteria->id] = min($values);
                $negativeIdeal[$subKriteria->id] = max($values);
            }
        }
        
        return [
            'positive' => $positiveIdeal,
            'negative' => $negativeIdeal
        ];
    }
    
    protected function calculateDistances($weightedMatrix, $idealSolutions)
    {
        $positiveDistances = [];
        $negativeDistances = [];
        
        foreach ($weightedMatrix as $alternatifId => $row) {
            $positiveSum = 0;
            $negativeSum = 0;
            
            foreach ($row as $subKriteriaId => $value) {
                $positiveSum += pow($value - $idealSolutions['positive'][$subKriteriaId], 2);
                $negativeSum += pow($value - $idealSolutions['negative'][$subKriteriaId], 2);
            }
            
            $positiveDistances[$alternatifId] = sqrt($positiveSum);
            $negativeDistances[$alternatifId] = sqrt($negativeSum);
        }
        
        return [
            'positive' => $positiveDistances,
            'negative' => $negativeDistances
        ];
    }
    
    protected function calculatePreferenceValues($distances)
    {
        $preferenceValues = [];
        
        foreach ($distances['positive'] as $alternatifId => $positiveDistance) {
            $negativeDistance = $distances['negative'][$alternatifId];
            $totalDistance = $positiveDistance + $negativeDistance;
            
            $preferenceValues[$alternatifId] = $totalDistance > 0 ? $negativeDistance / $totalDistance : 0;
        }
        
        return $preferenceValues;
    }
    
    protected function combineResults($alternatifs, $preferenceValues)
    {
        $results = [];
        
        foreach ($alternatifs as $alternatif) {
            $results[] = [
                'id' => $alternatif->id,
                'kode' => $alternatif->kode,
                'nama' => $alternatif->nama,
                'preference_value' => $preferenceValues[$alternatif->id],
            ];
        }
        
        usort($results, function($a, $b) {
            return $b['preference_value'] <=> $a['preference_value'];
        });
        
        $rank = 1;
        foreach ($results as &$result) {
            $result['rank'] = $rank++;
        }
        
        return $results;
    }
}