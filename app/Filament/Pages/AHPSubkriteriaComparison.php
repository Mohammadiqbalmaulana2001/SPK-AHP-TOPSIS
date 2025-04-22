<?php

namespace App\Filament\Pages;

use App\Models\Kriteria;
use App\Models\SubKriteria;
use App\Services\AHPSubkriteriaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use App\Filament\Resources\SubKriteriaResource;
// Import library yang dibutuhkan untuk ekspor
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class AHPSubkriteriaComparison extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Perbandingan Subkriteria (AHP)';
    protected static ?string $title = 'Pembobotan Subkriteria dengan Metode AHP';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.ahp-subkriteria-comparison';
    
    public $kriteriaId;
    public $kriteria = null;
    public $subKriterias = [];
    public $comparisonValues = [];
    public $ahpResults = null;
    public $selectedKriteriaId = null;
    
    public function mount($kriteriaId = null)
    {
        $this->selectedKriteriaId = $kriteriaId;
        
        if ($this->selectedKriteriaId) {
            $this->loadKriteria($this->selectedKriteriaId);
        }
    }
    
    public function loadKriteria($kriteriaId)
    {
        try {
            $this->kriteriaId = $kriteriaId;
            $this->kriteria = Kriteria::find($kriteriaId);
            
            if (!$this->kriteria) {
                throw new \Exception("Kriteria tidak ditemukan");
            }
            
            $this->subKriterias = SubKriteria::where('kriteria_id', $kriteriaId)->get();
            $this->initializeComparisonValues();
            $this->ahpResults = null;
        } catch (\Exception $e) {
            $this->resetKriteriaData();
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->warning()
                ->send();
        }
    }
    
    protected function resetKriteriaData()
    {
        $this->kriteria = null;
        $this->subKriterias = [];
        $this->comparisonValues = [];
        $this->ahpResults = null;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Subkriteria')
                ->icon('heroicon-o-arrow-left')
                ->url(fn (): string => $this->kriteriaId 
                    ? SubKriteriaResource::getUrl('index', ['tableFilters[kriteria_id][value]' => $this->kriteriaId])
                    : SubKriteriaResource::getUrl('index'))
                ->color('secondary'),
        ];
    }
    
    protected function initializeComparisonValues()
    {
        $this->comparisonValues = [];
        
        if ($this->subKriterias->count() < 2) {
            return;
        }
        
        $subKriteriaIds = $this->subKriterias->pluck('id')->toArray();
        
        for ($i = 0; $i < count($this->subKriterias); $i++) {
            for ($j = $i + 1; $j < count($this->subKriterias); $j++) {
                $sub1 = $this->subKriterias[$i];
                $sub2 = $this->subKriterias[$j];
                
                if (!in_array($sub1->id, $subKriteriaIds) || !in_array($sub2->id, $subKriteriaIds)) {
                    continue;
                }
                
                $key = $sub1->id . '_' . $sub2->id;
                $this->comparisonValues[$key] = [
                    'subkriteria1_id' => $sub1->id,
                    'subkriteria2_id' => $sub2->id,
                    'nilai' => 1,
                    'inverse' => false
                ];
            }
        }
    }
    
    public function form(Form $form): Form
    {
        $fieldGroups = [];
        
        $fieldGroups[] = Forms\Components\Section::make('Pilih Kriteria')
            ->schema([
                Forms\Components\Select::make('selectedKriteriaId')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $this->loadKriteria($state);
                        } else {
                            $this->resetKriteriaData();
                        }
                    }),
            ]);
        
        if (!$this->selectedKriteriaId || !$this->kriteria) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Silakan pilih kriteria terlebih dahulu untuk melakukan perbandingan subkriteria.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        if ($this->subKriterias->count() < 2) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Minimal 2 subkriteria diperlukan untuk melakukan perbandingan.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        $fields = [];
        
        foreach ($this->comparisonValues as $key => $comparison) {
            $parts = explode('_', $key);
            $sub1 = $this->subKriterias->firstWhere('id', $parts[0]);
            $sub2 = $this->subKriterias->firstWhere('id', $parts[1]);
            
            if (!$sub1 || !$sub2) {
                continue;
            }
            
            $fields[] = Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Placeholder::make("subkriteria_left_{$key}")
                        ->content($sub1->nama)
                        ->extraAttributes(['class' => 'text-right font-bold']),
                        
                    Forms\Components\Select::make("comparisonValues.{$key}.nilai")
                        ->options([
                            9 => '9 - Mutlak lebih penting',
                            8 => '8 - Sangat lebih penting',
                            // ... opsi lainnya ...
                            0.11111111111111 => '1/9 - Mutlak kurang penting',
                        ])
                        ->default(1),
                        
                    Forms\Components\Placeholder::make("subkriteria_right_{$key}")
                        ->content($sub2->nama)
                        ->extraAttributes(['class' => 'font-bold']),
                        
                    Forms\Components\Toggle::make("comparisonValues.{$key}.inverse")
                        ->label("Balik Perbandingan")
                        ->columnSpanFull(),
                ]);
        }
        
        $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
            ->description('Berikan nilai perbandingan untuk setiap pasangan subkriteria pada kriteria ' . $this->kriteria->nama)
            ->schema($fields);
            
        return $form->schema($fieldGroups);
    }
    
    public function calculate()
    {
        if (!$this->selectedKriteriaId || !$this->kriteria) {
            Notification::make()
                ->title('Pilih kriteria yang valid terlebih dahulu')
                ->warning()
                ->send();
            return;
        }
        
        if ($this->subKriterias->count() < 2) {
            Notification::make()
                ->title('Minimal 2 subkriteria diperlukan untuk melakukan perbandingan')
                ->warning()
                ->send();
            return;
        }
        
        try {
            $pairwiseValues = [];
            $subKriteriaIds = $this->subKriterias->pluck('id')->toArray();
            
            foreach ($this->comparisonValues as $key => $comparison) {
                $parts = explode('_', $key);
                if (count($parts) !== 2) continue;
                
                $subkriteria1_id = (int)$parts[0];
                $subkriteria2_id = (int)$parts[1];
                
                if (!in_array($subkriteria1_id, $subKriteriaIds) || 
                    !in_array($subkriteria2_id, $subKriteriaIds)) {
                    continue;
                }
                
                $nilai = (float)$comparison['nilai'];
                
                if ($comparison['inverse']) {
                    $temp = $subkriteria1_id;
                    $subkriteria1_id = $subkriteria2_id;
                    $subkriteria2_id = $temp;
                    $nilai = 1/$nilai;
                }
                
                $pairwiseValues[] = [
                    'subkriteria1_id' => $subkriteria1_id,
                    'subkriteria2_id' => $subkriteria2_id,
                    'nilai' => $nilai
                ];
            }
            
            $ahpService = new AHPSubkriteriaService();
            $this->ahpResults = $ahpService->process($pairwiseValues, $subKriteriaIds);
            
            $kriteriaWeight = ($this->kriteria->bobot ?? 0) / 100;
            $this->ahpResults['global_weights'] = $ahpService->calculateGlobalWeights(
                $this->ahpResults['weights'], 
                $kriteriaWeight
            );
            
            $this->saveSubkriteriaWeights();
            
            Notification::make()
                ->title('Berhasil menghitung perbandingan subkriteria')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menghitung')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function saveSubkriteriaWeights()
    {
        if (!$this->ahpResults || !isset($this->ahpResults['weights']) || !isset($this->ahpResults['global_weights'])) {
            return;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($this->ahpResults['weights'] as $subKriteriaId => $weight) {
                $globalWeight = $this->ahpResults['global_weights'][$subKriteriaId];
                
                SubKriteria::where('id', $subKriteriaId)->update([
                    'bobot' => $weight * 100,
                    'bobot_global' => $globalWeight * 100
                ]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Gagal menyimpan bobot')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function beforeRender(): void
    {
        if ($this->selectedKriteriaId && !$this->kriteria) {
            $this->loadKriteria($this->selectedKriteriaId);
        }
    }

    public function exportToPDF()
    {
        if (!$this->ahpResults || !$this->kriteria) {
            Notification::make()
                ->title('Tidak ada data untuk diekspor')
                ->warning()
                ->send();
            return;
        }

        $data = [
            'title' => 'Hasil Perhitungan AHP Subkriteria',
            'kriteria' => $this->kriteria,
            'subKriterias' => $this->subKriterias,
            'ahpResults' => $this->ahpResults,
            'date' => now()->format('d F Y')
        ];

        $pdf = PDF::loadView('exports.ahp-subkriteria-pdf', $data);
        
        $filename = 'ahp-subkriteria-' . Str::slug($this->kriteria->nama) . '-' . now()->format('YmdHis') . '.pdf';
        
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }
    
    public function exportToExcel()
{
    if (!$this->ahpResults || !$this->kriteria) {
        Notification::make()
            ->title('Tidak ada data untuk diekspor')
            ->warning()
            ->send();
        return;
    }
    
    $spreadsheet = new Spreadsheet();
    $activeWorksheet = $spreadsheet->getActiveSheet();
    $activeWorksheet->setTitle('AHP Subkriteria');
    
    // Set Default Font
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(11);
    
    // Styling for Headers
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];
    
    // Styling for Title
    $titleStyle = [
        'font' => [
            'bold' => true,
            'size' => 16,
            'color' => ['rgb' => '1F497D'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DCE6F1'],
        ],
    ];
    
    // Styling for Subtitle
    $subtitleStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ];
    
    // Styling for Section Titles
    $sectionStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => '1F497D'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DCE6F1'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                'color' => ['rgb' => '1F497D'],
            ],
        ],
    ];
    
    // Styling for Data cells
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'B4C6E7'],
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ];
    
    // Numeric cell style
    $numericStyle = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'numberFormat' => [
            'formatCode' => '0.000',
        ],
    ];
    
    // Percentage cell style
    $percentageStyle = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'numberFormat' => [
            'formatCode' => '0.00%',
        ],
    ];
    
    // Left Alignment
    $leftAlignment = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ];
    
    // Konsistensi cell colors
    $konsistenStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '00B050'],
        ],
    ];
    
    $nonKonsistenStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FF0000'],
        ],
    ];
    
    // Set judul
    $activeWorksheet->mergeCells('A1:E1');
    $activeWorksheet->setCellValue('A1', 'HASIL PERHITUNGAN AHP SUBKRITERIA');
    $activeWorksheet->getStyle('A1')->applyFromArray($titleStyle);
    $activeWorksheet->getRowDimension(1)->setRowHeight(30);
    
    $activeWorksheet->mergeCells('A2:E2');
    $activeWorksheet->setCellValue('A2', 'Kriteria: ' . $this->kriteria->nama);
    $activeWorksheet->getStyle('A2')->applyFromArray($subtitleStyle);
    
    $activeWorksheet->mergeCells('A3:E3');
    $activeWorksheet->setCellValue('A3', 'Tanggal: ' . now()->format('d F Y'));
    $activeWorksheet->getStyle('A3')->applyFromArray($subtitleStyle);
    
    // Matriks Perbandingan Berpasangan
    $row = 5;
    $activeWorksheet->mergeCells('A' . $row . ':E' . $row);
    $activeWorksheet->setCellValue('A' . $row, 'MATRIKS PERBANDINGAN BERPASANGAN');
    $activeWorksheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(25);
    $row++;
    
    // Header for Matriks
    $activeWorksheet->setCellValue('A' . $row, 'Subkriteria');
    $column = 'B';
    foreach ($this->subKriterias as $subkriteria) {
        $activeWorksheet->setCellValue($column . $row, $subkriteria->nama);
        $column++;
    }
    
    // Apply header style
    $lastColumn = chr(ord('A') + count($this->subKriterias));
    $activeWorksheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray($headerStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(20);
    $row++;
    
    // Isi Matriks Perbandingan
    $firstDataRow = $row;
    foreach ($this->subKriterias as $i => $subkriteria1) {
        $activeWorksheet->setCellValue('A' . $row, $subkriteria1->nama);
        $activeWorksheet->getStyle('A' . $row)->applyFromArray($leftAlignment);
        
        $column = 'B';
        foreach ($this->subKriterias as $j => $subkriteria2) {
            $cellValue = $this->ahpResults['matrix'][$i][$j];
            $activeWorksheet->setCellValue($column . $row, $cellValue);
            
            // Apply numeric format except for 1.0 values
            if ($cellValue != 1.0) {
                $activeWorksheet->getStyle($column . $row)->applyFromArray($numericStyle);
            } else {
                $activeWorksheet->getStyle($column . $row)->getNumberFormat()->setFormatCode('0.0');
            }
            
            $column++;
        }
        $row++;
    }
    
    // Apply data style to matrix
    $lastDataRow = $row - 1;
    $activeWorksheet->getStyle('A' . $firstDataRow . ':' . $lastColumn . $lastDataRow)->applyFromArray($dataStyle);
    
    // Matriks Ternormalisasi
    $row += 2; // Beri jarak
    $activeWorksheet->mergeCells('A' . $row . ':E' . $row);
    $activeWorksheet->setCellValue('A' . $row, 'MATRIKS TERNORMALISASI');
    $activeWorksheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(25);
    $row++;
    
    // Header for Matriks Ternormalisasi
    $activeWorksheet->setCellValue('A' . $row, 'Subkriteria');
    $column = 'B';
    foreach ($this->subKriterias as $subkriteria) {
        $activeWorksheet->setCellValue($column . $row, $subkriteria->nama);
        $column++;
    }
    
    // Apply header style
    $activeWorksheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray($headerStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(20);
    $row++;
    
    // Isi Matriks Ternormalisasi
    $firstDataRow = $row;
    foreach ($this->subKriterias as $i => $subkriteria1) {
        $activeWorksheet->setCellValue('A' . $row, $subkriteria1->nama);
        $activeWorksheet->getStyle('A' . $row)->applyFromArray($leftAlignment);
        
        $column = 'B';
        foreach ($this->subKriterias as $j => $subkriteria2) {
            $activeWorksheet->setCellValue($column . $row, $this->ahpResults['normalized_matrix'][$i][$j]);
            $activeWorksheet->getStyle($column . $row)->applyFromArray($numericStyle);
            $column++;
        }
        $row++;
    }
    
    // Apply data style to matrix
    $lastDataRow = $row - 1;
    $activeWorksheet->getStyle('A' . $firstDataRow . ':' . $lastColumn . $lastDataRow)->applyFromArray($dataStyle);
    
    // Bobot Subkriteria
    $row += 2; // Beri jarak
    $activeWorksheet->mergeCells('A' . $row . ':E' . $row);
    $activeWorksheet->setCellValue('A' . $row, 'BOBOT SUBKRITERIA (PRIORITY VECTOR)');
    $activeWorksheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(25);
    $row++;
    
    // Header for bobot
    $headers = ['Subkriteria', 'Bobot Lokal', '% Lokal', 'Bobot Global', '% Global'];
    $col = 'A';
    foreach ($headers as $header) {
        $activeWorksheet->setCellValue($col . $row, $header);
        $col++;
    }
    
    // Apply header style
    $activeWorksheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(20);
    $row++;
    
    // Isi bobot
    $firstDataRow = $row;
    foreach ($this->subKriterias as $i => $subkriteria) {
        $activeWorksheet->setCellValue('A' . $row, $subkriteria->nama);
        $activeWorksheet->getStyle('A' . $row)->applyFromArray($leftAlignment);
        
        // Bobot Lokal
        $activeWorksheet->setCellValue('B' . $row, $this->ahpResults['priority_vector'][$i]);
        $activeWorksheet->getStyle('B' . $row)->applyFromArray($numericStyle);
        
        // % Lokal
        $activeWorksheet->setCellValue('C' . $row, $this->ahpResults['priority_vector'][$i]);
        $activeWorksheet->getStyle('C' . $row)->applyFromArray($percentageStyle);
        
        // Bobot Global
        $activeWorksheet->setCellValue('D' . $row, $this->ahpResults['global_weights'][$subkriteria->id]);
        $activeWorksheet->getStyle('D' . $row)->applyFromArray($numericStyle);
        
        // % Global
        $activeWorksheet->setCellValue('E' . $row, $this->ahpResults['global_weights'][$subkriteria->id]);
        $activeWorksheet->getStyle('E' . $row)->applyFromArray($percentageStyle);
        
        $row++;
    }
    
    // Apply data style to weights
    $lastDataRow = $row - 1;
    $activeWorksheet->getStyle('A' . $firstDataRow . ':E' . $lastDataRow)->applyFromArray($dataStyle);
    
    // Highlight important values
    $activeWorksheet->getStyle('B' . $firstDataRow . ':E' . $lastDataRow)
        ->getFont()->setBold(true);
    
    // Konsistensi Penilaian
    $row += 2; // Beri jarak
    $activeWorksheet->mergeCells('A' . $row . ':C' . $row);
    $activeWorksheet->setCellValue('A' . $row, 'KONSISTENSI PENILAIAN');
    $activeWorksheet->getStyle('A' . $row)->applyFromArray($sectionStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(25);
    $row++;
    
    // Header for konsistensi
    $activeWorksheet->setCellValue('A' . $row, 'Parameter');
    $activeWorksheet->setCellValue('B' . $row, 'Nilai');
    $activeWorksheet->setCellValue('C' . $row, 'Keterangan');
    
    // Apply header style
    $activeWorksheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
    $activeWorksheet->getRowDimension($row)->setRowHeight(20);
    $row++;
    
    // Isi konsistensi
    $firstDataRow = $row;
    
    $activeWorksheet->setCellValue('A' . $row, 'Lambda Max');
    $activeWorksheet->setCellValue('B' . $row, $this->ahpResults['lambda_max']);
    $activeWorksheet->getStyle('B' . $row)->applyFromArray($numericStyle);
    $row++;
    
    $activeWorksheet->setCellValue('A' . $row, 'Consistency Index (CI)');
    $activeWorksheet->setCellValue('B' . $row, $this->ahpResults['consistency_index']);
    $activeWorksheet->getStyle('B' . $row)->applyFromArray($numericStyle);
    $row++;
    
    $activeWorksheet->setCellValue('A' . $row, 'Consistency Ratio (CR)');
    $activeWorksheet->setCellValue('B' . $row, $this->ahpResults['consistency_ratio']);
    $activeWorksheet->getStyle('B' . $row)->applyFromArray($numericStyle);
    
    $activeWorksheet->setCellValue('C' . $row, ($this->ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten'));
    
    // Apply styles for consistency status
    if ($this->ahpResults['is_consistent']) {
        $activeWorksheet->getStyle('C' . $row)->applyFromArray($konsistenStyle);
    } else {
        $activeWorksheet->getStyle('C' . $row)->applyFromArray($nonKonsistenStyle);
    }
    
    // Apply data style to consistency
    $lastDataRow = $row;
    $activeWorksheet->getStyle('A' . $firstDataRow . ':C' . $lastDataRow)->applyFromArray($dataStyle);
    
    // Add warning if not consistent
    if (!$this->ahpResults['is_consistent']) {
        $row += 2;
        $activeWorksheet->mergeCells('A' . $row . ':C' . $row);
        $activeWorksheet->setCellValue('A' . $row, 'Peringatan: Nilai CR > 0.1 menunjukkan ketidakkonsistenan. Mohon lakukan perbandingan ulang.');
        $activeWorksheet->getStyle('A' . $row)->applyFromArray($nonKonsistenStyle);
        $activeWorksheet->getStyle('A' . $row)->getFont()->setItalic(true);
    }
    
    // Set kolom widths
    $activeWorksheet->getColumnDimension('A')->setWidth(25);
    $activeWorksheet->getColumnDimension('B')->setWidth(15);
    $activeWorksheet->getColumnDimension('C')->setWidth(15);
    $activeWorksheet->getColumnDimension('D')->setWidth(15);
    $activeWorksheet->getColumnDimension('E')->setWidth(15);
    
    // Auto-size remaining columns
    for ($i = 0; $i < count($this->subKriterias); $i++) {
        $col = chr(ord('B') + $i);
        if ($col <= $lastColumn) {
            $activeWorksheet->getColumnDimension($col)->setWidth(15);
        }
    }
    
    // Freeze panes (header rows)
    $activeWorksheet->freezePane('A4');
    
    // Set zoom level
    $activeWorksheet->getSheetView()->setZoomScale(100);
    
    // Set footer
    $activeWorksheet->getHeaderFooter()->setOddFooter('&L&B' . $this->kriteria->nama . '&R&D ' . date('d/m/Y H:i:s'));
    
    // Simpan file Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'ahp-subkriteria-' . Str::slug($this->kriteria->nama) . '-' . now()->format('YmdHis') . '.xlsx';
    
    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}
}