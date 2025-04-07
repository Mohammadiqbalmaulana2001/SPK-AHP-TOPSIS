<?php

namespace App\Filament\Pages;

use App\Models\Kriteria;
use App\Services\AHPService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AHPKriteriaComparison extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    
    protected static ?string $navigationLabel = 'Perbandingan Kriteria (AHP)';
    
    protected static ?string $navigationGroup = 'Analisis';
    
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.ahp-kriteria-comparison';
    
    public $kriterias = [];
    public $comparisonValues = [];
    public $ahpResults = null;
    
    public function mount()
    {
        $this->kriterias = Kriteria::all();
        $this->initializeComparisonValues();
    }
    
    protected function initializeComparisonValues()
    {
        $this->comparisonValues = [];
        $kriterias = $this->kriterias;
        
        for ($i = 0; $i < count($kriterias); $i++) {
            for ($j = $i + 1; $j < count($kriterias); $j++) {
                $key = $kriterias[$i]->id . '_' . $kriterias[$j]->id;
                $this->comparisonValues[$key] = [
                    'kriteria1_id' => $kriterias[$i]->id,
                    'kriteria2_id' => $kriterias[$j]->id,
                    'nilai' => 1, // Nilai default
                    'inverse' => false
                ];
            }
        }
    }
    
    public function form(Form $form): Form
    {
        $fieldGroups = [];
        $kriterias = $this->kriterias;
        
        if (count($kriterias) < 2) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Kriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Minimal 2 kriteria diperlukan untuk melakukan perbandingan.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        $fields = [];
        
        for ($i = 0; $i < count($kriterias); $i++) {
            for ($j = $i + 1; $j < count($kriterias); $j++) {
                $key = $kriterias[$i]->id . '_' . $kriterias[$j]->id;
                
                $fields[] = Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make("kriteria_left_{$key}")
                            ->content($kriterias[$i]->nama)
                            ->extraAttributes(['class' => 'text-right font-bold']),
                            
                        Forms\Components\Select::make("comparisonValues.{$key}.nilai")
                            ->options([
                                9 => '9 - Mutlak lebih penting',
                                8 => '8 - Sangat lebih penting',
                                7 => '7 - Lebih penting',
                                6 => '6 - Cukup lebih penting',
                                5 => '5 - Lebih penting',
                                4 => '4 - Sedikit lebih penting',
                                3 => '3 - Cukup penting',
                                2 => '2 - Sedikit penting',
                                1 => '1 - Sama penting',
                                1/2 => '1/2 - Sedikit kurang penting',
                                1/3 => '1/3 - Cukup kurang penting',
                                1/4 => '1/4 - Sedikit kurang penting',
                                1/5 => '1/5 - Kurang penting',
                                1/6 => '1/6 - Cukup kurang penting',
                                1/7 => '1/7 - Kurang penting',
                                1/8 => '1/8 - Sangat kurang penting',
                                1/9 => '1/9 - Mutlak kurang penting',
                            ])
                            ->default(1)
                            ->reactive()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) use ($key) {
                                $parts = explode('_', $key);
                                if (count($parts) === 2) {
                                    $inverseKey = $parts[1] . '_' . $parts[0];
                                    // Tidak perlu mengupdate karena kita akan mengonversi saat submit
                                }
                            }),
                            
                        Forms\Components\Placeholder::make("kriteria_right_{$key}")
                            ->content($kriterias[$j]->nama)
                            ->extraAttributes(['class' => 'font-bold']),
                            
                        Forms\Components\Toggle::make("comparisonValues.{$key}.inverse")
                            ->label("Balik Perbandingan")
                            ->columnSpanFull(),
                    ]);
            }
        }
        
        $fieldGroups[] = Forms\Components\Section::make('Perbandingan Kriteria')
            ->description('Berikan nilai perbandingan untuk setiap pasangan kriteria.')
            ->schema($fields);
            
        return $form->schema($fieldGroups);
    }
    
    public function calculate()
    {
        try {
            // Konversi data form ke format yang dibutuhkan AHPService
            $pairwiseValues = [];
            $kriteriaIds = $this->kriterias->pluck('id')->toArray();
            
            foreach ($this->comparisonValues as $key => $comparison) {
                $parts = explode('_', $key);
                $kriteria1_id = (int)$parts[0];
                $kriteria2_id = (int)$parts[1];
                $nilai = (float)$comparison['nilai'];
                
                // Jika inverse true, balik nilai perbandingan
                if ($comparison['inverse']) {
                    $temp = $kriteria1_id;
                    $kriteria1_id = $kriteria2_id;
                    $kriteria2_id = $temp;
                }
                
                $pairwiseValues[] = [
                    'kriteria1_id' => $kriteria1_id,
                    'kriteria2_id' => $kriteria2_id,
                    'nilai' => $nilai
                ];
            }
            
            // Hitung dengan AHP Service
            $ahpService = new AHPService();
            $this->ahpResults = $ahpService->process($pairwiseValues, $kriteriaIds);
            
            // Simpan hasil bobot kriteria ke database
            $this->saveKriteriaWeights();
            
            Notification::make()
                ->title('Berhasil menghitung perbandingan kriteria')
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
    
    protected function saveKriteriaWeights()
    {
        if (!$this->ahpResults || !isset($this->ahpResults['weights'])) {
            return;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($this->ahpResults['weights'] as $kriteriaId => $weight) {
                Kriteria::where('id', $kriteriaId)->update([
                    'bobot' => $weight * 100 // Simpan dalam bentuk persentase
                ]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}