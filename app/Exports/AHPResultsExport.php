<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AHPResultsExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize, WithEvents
{
    protected $kriterias;
    protected $ahpResults;

    public function __construct($kriterias, $ahpResults)
    {
        $this->kriterias = $kriterias;
        $this->ahpResults = $ahpResults;
    }

    public function collection()
    {
        $data = [];
        $kriterias = $this->kriterias;
        $ahpResults = $this->ahpResults;
        
        // Baris judul
        $data[] = ['Hasil Perhitungan AHP - Tanggal: ' . Carbon::now()->format('d-m-Y H:i:s')];
        $data[] = [''];
        
        // Matriks Perbandingan Berpasangan
        $data[] = ['Matriks Perbandingan Berpasangan'];
        
        // Header untuk matriks
        $matrixHeader = ['Kriteria'];
        foreach ($kriterias as $kriteria) {
            $matrixHeader[] = $kriteria->nama;
        }
        $data[] = $matrixHeader;
        
        // Isi matriks
        foreach ($kriterias as $i => $kriteria1) {
            $row = [$kriteria1->nama];
            foreach ($kriterias as $j => $kriteria2) {
                $row[] = number_format($ahpResults['matrix'][$i][$j], 3);
            }
            $data[] = $row;
        }
        
        $data[] = [''];
        
        // Matriks Ternormalisasi
        $data[] = ['Matriks Ternormalisasi'];
        
        // Header untuk matriks ternormalisasi
        $data[] = $matrixHeader;
        
        // Isi matriks ternormalisasi
        foreach ($kriterias as $i => $kriteria1) {
            $row = [$kriteria1->nama];
            foreach ($kriterias as $j => $kriteria2) {
                $row[] = number_format($ahpResults['normalized_matrix'][$i][$j], 3);
            }
            $data[] = $row;
        }
        
        $data[] = [''];
        
        // Bobot Kriteria
        $data[] = ['Bobot Kriteria (Priority Vector)'];
        $data[] = ['Kriteria', 'Bobot', 'Persentase'];
        
        foreach ($kriterias as $i => $kriteria) {
            $data[] = [
                $kriteria->nama,
                number_format($ahpResults['priority_vector'][$i], 4),
                number_format($ahpResults['priority_vector'][$i] * 100, 2) . '%'
            ];
        }
        
        $data[] = [''];
        
        // Konsistensi Penilaian
        $data[] = ['Konsistensi Penilaian'];
        $data[] = ['Nilai Lambda Max', number_format($ahpResults['lambda_max'], 4)];
        $data[] = ['Consistency Index (CI)', number_format($ahpResults['consistency_index'], 4)];
        $data[] = [
            'Consistency Ratio (CR)', 
            number_format($ahpResults['consistency_ratio'], 4) . ' (' . 
            ($ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten') . ')'
        ];
        
        return new Collection($data);
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Hasil AHP';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            count($this->kriterias) + 6 => ['font' => ['bold' => true]],
            count($this->kriterias) + 7 => ['font' => ['bold' => true]],
            count($this->kriterias) * 2 + 9 => ['font' => ['bold' => true]],
            count($this->kriterias) * 2 + 10 => ['font' => ['bold' => true]],
            count($this->kriterias) * 2 + count($this->kriterias) + 12 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Menyesuaikan beberapa format sel tergantung kebutuhan
                $event->sheet->getStyle('A1:Z1000')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('A1:Z1000')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
                // Menambahkan border pada tabel-tabel
                $matrixRange = 'A4:' . chr(65 + count($this->kriterias)) . (4 + count($this->kriterias));
                $event->sheet->getStyle($matrixRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $normalizedRange = 'A' . (count($this->kriterias) + 7) . ':' . chr(65 + count($this->kriterias)) . (count($this->kriterias) * 2 + 7);
                $event->sheet->getStyle($normalizedRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                $weightsRange = 'A' . (count($this->kriterias) * 2 + 10) . ':C' . (count($this->kriterias) * 2 + 10 + count($this->kriterias));
                $event->sheet->getStyle($weightsRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Memberi warna pada header
                $event->sheet->getStyle('A4:' . chr(65 + count($this->kriterias)) . '4')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9EAD3');
                
                $event->sheet->getStyle('A' . (count($this->kriterias) + 7) . ':' . chr(65 + count($this->kriterias)) . (count($this->kriterias) + 7))->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9EAD3');
                
                $event->sheet->getStyle('A' . (count($this->kriterias) * 2 + 10) . ':C' . (count($this->kriterias) * 2 + 10))->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9EAD3');
                
                // Memberikan warna pada baris CR berdasarkan konsistensi
                $crRow = count($this->kriterias) * 2 + count($this->kriterias) + 15;
                $crColor = $this->ahpResults['is_consistent'] ? 'B6D7A8' : 'F4CCCC';
                $event->sheet->getStyle('A' . $crRow . ':B' . $crRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($crColor);
            },
        ];
    }
}