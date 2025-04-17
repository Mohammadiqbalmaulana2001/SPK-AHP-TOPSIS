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

class AHPSubkriteriaComparison extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    
    protected static ?string $navigationLabel = 'Perbandingan Subkriteria (AHP)';
    
    protected static ?string $title = 'Pembobotan Subkriteria dengan Metode AHP';
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.ahp-subkriteria-comparison';
    
    public $kriteriaId;
    public $kriteria;
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
        $this->kriteriaId = $kriteriaId;
        $this->kriteria = Kriteria::findOrFail($kriteriaId);
        $this->subKriterias = SubKriteria::where('kriteria_id', $kriteriaId)->get();
        $this->initializeComparisonValues();
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
        $subKriterias = $this->subKriterias;
        
        for ($i = 0; $i < count($subKriterias); $i++) {
            for ($j = $i + 1; $j < count($subKriterias); $j++) {
                $key = $subKriterias[$i]->id . '_' . $subKriterias[$j]->id;
                $this->comparisonValues[$key] = [
                    'subkriteria1_id' => $subKriterias[$i]->id,
                    'subkriteria2_id' => $subKriterias[$j]->id,
                    'nilai' => 1, // Nilai default
                    'inverse' => false
                ];
            }
        }
    }
    
    public function form(Form $form): Form
    {
        $fieldGroups = [];
        
        // Tambahkan pemilihan kriteria di form
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
                        }
                    }),
            ]);
        
        if (!$this->selectedKriteriaId) {
            return $form->schema($fieldGroups);
        }
        
        $subKriterias = $this->subKriterias;
        
        // PERUBAHAN: Kondisi menjadi < 2
        if (count($subKriterias) < 2) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Minimal 2 subkriteria diperlukan untuk melakukan perbandingan.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        $fields = [];
        
        for ($i = 0; $i < count($subKriterias); $i++) {
            for ($j = $i + 1; $j < count($subKriterias); $j++) {
                $key = $subKriterias[$i]->id . '_' . $subKriterias[$j]->id;
                
                $fields[] = Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make("subkriteria_left_{$key}")
                            ->content($subKriterias[$i]->nama)
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
                            ->live(),
                            
                        Forms\Components\Placeholder::make("subkriteria_right_{$key}")
                            ->content($subKriterias[$j]->nama)
                            ->extraAttributes(['class' => 'font-bold']),
                            
                        Forms\Components\Toggle::make("comparisonValues.{$key}.inverse")
                            ->label("Balik Perbandingan")
                            ->columnSpanFull(),
                    ]);
            }
        }
        
        $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
            ->description('Berikan nilai perbandingan untuk setiap pasangan subkriteria pada kriteria ' . $this->kriteria->nama)
            ->schema($fields);
            
        return $form->schema($fieldGroups);
    }
    
    public function calculate()
    {
        if (!$this->selectedKriteriaId) {
            Notification::make()
                ->title('Pilih kriteria terlebih dahulu')
                ->warning()
                ->send();
            return;
        }
        
        try {
            // Konversi data form ke format yang dibutuhkan AHPSubkriteriaService
            $pairwiseValues = [];
            $subKriteriaIds = $this->subKriterias->pluck('id')->toArray();
            
            foreach ($this->comparisonValues as $key => $comparison) {
                $parts = explode('_', $key);
                $subkriteria1_id = (int)$parts[0];
                $subkriteria2_id = (int)$parts[1];
                $nilai = (float)$comparison['nilai'];
                
                // Jika inverse true, balik nilai perbandingan
                if ($comparison['inverse']) {
                    $temp = $subkriteria1_id;
                    $subkriteria1_id = $subkriteria2_id;
                    $subkriteria2_id = $temp;
                }
                
                $pairwiseValues[] = [
                    'subkriteria1_id' => $subkriteria1_id,
                    'subkriteria2_id' => $subkriteria2_id,
                    'nilai' => $nilai
                ];
            }
            
            // Hitung dengan AHP Service
            $ahpService = new AHPSubkriteriaService();
            $this->ahpResults = $ahpService->process($pairwiseValues, $subKriteriaIds);
            
            // Hitung bobot global
            $kriteriaWeight = $this->kriteria->bobot / 100; // Konversi dari persentase ke desimal
            $this->ahpResults['global_weights'] = $ahpService->calculateGlobalWeights(
                $this->ahpResults['weights'], 
                $kriteriaWeight
            );
            
            // Simpan hasil bobot subkriteria ke database
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
                    'bobot' => $weight * 100, // Simpan dalam bentuk persentase
                    'bobot_global' => $globalWeight * 100 // Simpan dalam bentuk persentase
                ]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}