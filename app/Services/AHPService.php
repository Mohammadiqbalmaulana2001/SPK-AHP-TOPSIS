<?php

namespace App\Services;

class AHPService
{
    // Nilai Random Index (RI) berdasarkan ukuran matriks
    private $randomIndex = [
        1 => 0,
        2 => 0,
        3 => 0.58,
        4 => 0.9,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
        11 => 1.51,
        12 => 1.48,
        13 => 1.56,
        14 => 1.57,
        15 => 1.59
    ];

    /**
     * Membuat matriks perbandingan berpasangan dari nilai input
     * 
     * @param array $pairwiseValues Array dari nilai perbandingan berpasangan
     * @param array $kriteriaIds Array dari ID kriteria
     * @return array Matriks perbandingan berpasangan
     */
    public function createPairwiseMatrix(array $pairwiseValues, array $kriteriaIds)
    {
        $n = count($kriteriaIds);
        $matrix = [];
        
        // Inisialisasi matriks dengan nilai 1 pada diagonal
        for ($i = 0; $i < $n; $i++) {
            $matrix[$i][$i] = 1;
        }
        
        // Isi matriks dengan nilai perbandingan
        foreach ($pairwiseValues as $comparison) {
            $row = array_search($comparison['kriteria1_id'], $kriteriaIds);
            $col = array_search($comparison['kriteria2_id'], $kriteriaIds);
            $value = $comparison['nilai'];
            
            // Nilai perbandingan untuk pasangan i,j
            $matrix[$row][$col] = $value;
            
            // Nilai kebalikan untuk pasangan j,i
            $matrix[$col][$row] = 1 / $value;
        }
        
        return $matrix;
    }

    /**
     * Normalisasi matriks perbandingan berpasangan
     * 
     * @param array $matrix Matriks perbandingan berpasangan
     * @return array Matriks ternormalisasi
     */
    public function normalizeMatrix(array $matrix)
    {
        $n = count($matrix);
        $normalizedMatrix = [];
        $columnSums = [];
        
        // Hitung jumlah untuk setiap kolom
        for ($j = 0; $j < $n; $j++) {
            $sum = 0;
            for ($i = 0; $i < $n; $i++) {
                $sum += $matrix[$i][$j];
            }
            $columnSums[$j] = $sum;
        }
        
        // Normalisasi setiap elemen matriks
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normalizedMatrix[$i][$j] = $matrix[$i][$j] / $columnSums[$j];
            }
        }
        
        return $normalizedMatrix;
    }

    /**
     * Hitung priority vector (bobot kriteria)
     * 
     * @param array $normalizedMatrix Matriks ternormalisasi
     * @return array Priority vector (bobot untuk setiap kriteria)
     */
    public function calculatePriorityVector(array $normalizedMatrix)
    {
        $n = count($normalizedMatrix);
        $priorityVector = [];
        
        // Hitung rata-rata setiap baris
        for ($i = 0; $i < $n; $i++) {
            $rowSum = array_sum($normalizedMatrix[$i]);
            $priorityVector[$i] = $rowSum / $n;
        }
        
        return $priorityVector;
    }

    /**
     * Hitung nilai lambda maksimum
     * 
     * @param array $matrix Matriks perbandingan berpasangan asli
     * @param array $priorityVector Priority vector hasil perhitungan
     * @return float Nilai lambda maksimum
     */
    public function calculateLambdaMax(array $matrix, array $priorityVector)
    {
        $n = count($matrix);
        $weightedSum = [];
        
        // Hitung weighted sum vector
        for ($i = 0; $i < $n; $i++) {
            $sum = 0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $matrix[$i][$j] * $priorityVector[$j];
            }
            $weightedSum[$i] = $sum;
        }
        
        // Hitung rasio
        $ratios = [];
        for ($i = 0; $i < $n; $i++) {
            $ratios[$i] = $weightedSum[$i] / $priorityVector[$i];
        }
        
        // Lambda max adalah rata-rata dari rasio
        return array_sum($ratios) / $n;
    }

    /**
     * Hitung Consistency Index (CI)
     * 
     * @param float $lambdaMax Nilai lambda maksimum
     * @param int $n Ukuran matriks
     * @return float Consistency Index
     */
    public function calculateConsistencyIndex($lambdaMax, $n)
    {
        return ($lambdaMax - $n) / ($n - 1);
    }

    /**
     * Hitung Consistency Ratio (CR)
     * 
     * @param float $ci Consistency Index
     * @param int $n Ukuran matriks
     * @return float Consistency Ratio
     */
    public function calculateConsistencyRatio($ci, $n)
    {
        if (!isset($this->randomIndex[$n])) {
            throw new \Exception("Random Index untuk ukuran matriks $n tidak tersedia.");
        }
        
        $ri = $this->randomIndex[$n];
        
        // Jika RI = 0 (untuk n = 1,2), CR = 0
        if ($ri == 0) {
            return 0;
        }
        
        return $ci / $ri;
    }

    /**
     * Melakukan seluruh proses perhitungan AHP
     * 
     * @param array $pairwiseValues Array dari nilai perbandingan berpasangan
     * @param array $kriteriaIds Array dari ID kriteria dalam urutan yang sama dengan matriks
     * @return array Hasil perhitungan AHP
     */
    public function process(array $pairwiseValues, array $kriteriaIds)
    {
        $n = count($kriteriaIds);
        
        // Langkah 1: Buat matriks perbandingan berpasangan
        $matrix = $this->createPairwiseMatrix($pairwiseValues, $kriteriaIds);
        
        // Langkah 2: Normalisasi matriks
        $normalizedMatrix = $this->normalizeMatrix($matrix);
        
        // Langkah 3: Hitung priority vector (bobot kriteria)
        $priorityVector = $this->calculatePriorityVector($normalizedMatrix);
        
        // Langkah 4: Hitung consistency ratio
        $lambdaMax = $this->calculateLambdaMax($matrix, $priorityVector);
        $ci = $this->calculateConsistencyIndex($lambdaMax, $n);
        $cr = $this->calculateConsistencyRatio($ci, $n);
        
        // Hasilkan array hasil
        $result = [
            'matrix' => $matrix,
            'normalized_matrix' => $normalizedMatrix,
            'priority_vector' => $priorityVector,
            'weights' => array_combine($kriteriaIds, $priorityVector),
            'lambda_max' => $lambdaMax,
            'consistency_index' => $ci,
            'consistency_ratio' => $cr,
            'is_consistent' => $cr <= 0.1, // CR <= 0.1 dianggap konsisten
        ];
        
        return $result;
    }
}